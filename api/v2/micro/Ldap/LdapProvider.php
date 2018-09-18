<?php
namespace Micro\Ldap;

class LdapProvider extends \Micro\Component {

    const SCOPE_BASE = 'base';
    const SCOPE_LIST = 'list';
    const SCOPE_TREE = 'tree';

    protected $_config;
    protected $_caches;

    public function __construct() {
        $this->_config = NULL;

        $this->_caches = array(
            'attrs' => array(),
            'links' => array(),
            'username' => NULL,
            'password' => NULL,
            'userdata' => NULL
        );

        $this->_configure();
    }

    public function test() {
        // $this->setLoginType('bind');
        if ($this->login('roso@kct.co.id', '123')) {
            $user = $this->userdata();
            print_r($user);    
        }
        //$r = $this->find('(mail=vidi*)');
        //print_r($r);
    }

    public function ping() {
        return $this->connect();
    }

    public function setLoginType($type) {
        $this->_config->login->offsetSet('type', $type);
    }

    public function login($username = NULL, $password = NULL) {
        $config = $this->_config;

        if ($config->login->type == 'bind') {
            
            if ($this->authenticate($username, $password)) {
                $userdata = $this->userdata();
                return $this->_validateLogin($userdata);
            }

            return FALSE;
        } else {
            $params = func_get_args();
            array_unshift($params, $config->login->format);

            $filter = call_user_func_array('sprintf', $params);

            $options = array(
                'filter' => $filter,
                'attrs' => $config->login->attrs->toArray(),
                'items' => 1,
                'scope' => self::SCOPE_TREE
            );

            $bases = $this->_resolveLoginBaseDN();
            $result = array();

            foreach($bases as $base) {
                $options['base'] = $base;
                $result = $this->query($options);
                if (count($result) == 1) {
                    break;
                }

            } 

            if (count($result) != 1) {
                return FALSE;
            }

            $userdata = array_shift($result);
            $this->_caches['userdata'] = $userdata;

            return $this->_validateLogin($userdata, $username, $password);
        }
    }

    public function logout() {
        if ($this->_config->login->type == 'bind') {
            $this->invalidate();
        } else {
            $this->_caches['userdata'] = NULL;
        }
    }

    public function userdata() {
        return $this->_caches['userdata'];
    }

    protected function _configure() {

        $config = new \Phalcon\Config(array(
            'server' => array(
                'host' => '127.0.0.1',
                'port' => 389,
                'tls' => FALSE,
                'base' => array(),
                'auth' => array(
                    'anon' => TRUE,
                    'attr' => 'dn',
                    'base' => array(),
                    'class' => array(),
                    'user' => NULL,
                    'pass' => NULL
                )
            ),
            'login' => array(
                'type' => 'user',
                'base' => array(),
                'format' => '',
                'attrs' => array()
            ),
            'deref' => \LDAP_DEREF_NEVER,
            'debug' => TRUE,
            'timeout' => 0
        ));

        $app = $this->getApp();

        if ($app->config->offsetExists('ldap')) {
            $custom = $app->config->ldap;
            $config->merge($custom);
        }

        // fixup
        self::__fixupMerge($config->server, 'base');
        self::__fixupMerge($config->server->auth, 'base');
        self::__fixupMerge($config->server->auth, 'class');
        self::__fixupMerge($config->login, 'base');
        self::__fixupMerge($config->login, 'attrs');

        $this->_configureLogin($config->login);
        $this->_config = $config;
    }

    protected function _configureLogin($login) {
        if ($login->type != 'bind') {
            
            $format = $login->format;

            if (empty($format)) {
                if ($login->offsetExists('params')) {
                    $params = $login->params->toArray();
                    if (count($params) > 0) {
                        $format = '(&('.join(')(', array_map(function($e){ return $e.'=%s'; }, $params)).'))';
                    }
                }

                if (empty($format)) {
                    // fallback to bind
                    $login->offsetSet('type', 'bind');
                }
            }
            
            $login->offsetSet('format', $format);
        }

        $attrs = $login->attrs->toArray();  
        
        if (count($attrs) == 0) {
            $attrs = array('*');
        }

        if ( ! in_array('*', $attrs) && ! in_array('dn', $attrs)) {
            $attrs[] = 'dn';
        }

        $login->offsetSet('attrs', $attrs);
    }

    protected function _validateLogin($login, $username = NULL, $password = NULL) {
        $validator = NULL;
        $container = \Phalcon\DI::getDefault();

        if ($container->offsetExists('ldap_validator')) {
            $validator = $container->getShared('ldap_validator');
        }

        $valid = TRUE;

        if ( ! is_null($validator)) {
            $valid = $validator($this, $login, $username, $password);
        }

        if ( ! $valid) {
            $this->_caches['login'] = NULL;
        }

        return $valid;
    }

    // bind to database
    public function authenticate($username = NULL, $password = NULL) {
        $this->_caches['username'] = NULL;
        $this->_caches['password'] = NULL;

        $user = NULL;

        if ( ! is_null($username)) {
            if ($this->_config->server->auth->attr == 'dn') {
                $user = $username;
            } else {
                $user = $this->_resolveAuthName($username);
            }
        }

        if (empty($user) && !$this->_config->server->auth->anon) {
            $this->_debug('Anonymous LDAP authentication is not allowed');
            return FALSE;
        }

        $this->_caches['username'] = $user;
        $this->_caches['password'] = $password;

        $link = $this->connect();

        if ( ! $link) {
            return FALSE;
        } else {
            $userdata = $this->_getDNAttrValues($username);
            print_r($userdata);
            return TRUE;
        }
    }

    public function invalidate() {
        $username = $this->_caches['username'];

        if (isset($this->_caches['links'][$username])) {
            @ldap_unbind($this->_caches['links'][$username]);
            $this->_caches['links'][$username] = NULL;
            unset($this->_caches['links'][$username]);
        }

        $this->_caches['username'] = NULL;
        $this->_caches['password'] = NULL;
    }

    public function connect() {

        $username = $this->_resolveAuthUser();
        $password = $this->_resolveAuthPass();

        if (empty($username) && ! $this->_config->server->auth->anon) {
            throw new \Phalcon\Exception("Anonymous LDAP authentication is not allowed");
        }

        if (isset($this->_caches['links'][$username])) {
            $this->_caches['links'][$username];
        }

        $link = NULL;

        if ($this->_config->server->port) {
            $link = ldap_connect($this->_config->server->host, $this->_config->server->port);
        } else {
            $link = ldap_connect($this->_config->server->host);
        }

        ldap_set_option($link, \LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($link, \LDAP_OPT_REFERRALS, 0);
        ldap_set_option($link, \LDAP_OPT_NETWORK_TIMEOUT, $this->_config->timeout);

        if ($this->_config->server->tls) {
            $this->_startTLS($link);
        }

        $bind_user = !empty($this->_config->server->auth->user) ? $this->_config->server->auth->user : $username;

        $bind_pass = !empty($this->_config->server->auth->pass) ? $this->_config->server->auth->pass : $password;

        $connected = @ldap_bind($link, $bind_user, $bind_pass);

        if ($connected) {
            $this->_caches['links'][$username] = $link;
            return $link;
        } else {
            $this->_debug('Unable to connect to server using `'.$username.'`');
            return FALSE;
        }
    }

    public function find($options = NULL) {
        $result = $this->query($options);
        return new Resultset($result);
    }

    public function query($options = NULL) {
        if (is_null($options)) {
            $options = array();
        } else if (is_string($options)) {
            $options = array(
                'filter' => $options
            );
        }

        if ( ! isset($options['attrs'])) {
            $options['attrs'] = array();
        } else {
            $options['attrs'] = array_values($options['attrs']);
        }

        if ( ! isset($options['base'])) {
            $bases = $this->_resolveBaseDN();
            $options['base'] = array_shift($bases);
        }

        if ( ! isset($options['deref'])) {
            $options['deref'] = $this->_config->deref;
        }

        if ( ! isset($options['filter'])) {
            $options['filter'] = '(&(objectClass=*))';
        }

        if ( ! isset($options['scope'])) {
            $options['scope'] = self::SCOPE_TREE;
        }

        if ( ! isset($options['items'])) {
            $options['items'] = 0;
        }

        if ( ! isset($options['timeout'])) {
            $options['timeout'] = $this->_config->timeout;
        }

        if (is_array($options['base'])) {
            return array();
        }

        $link = $this->connect();

        if ( ! $link) {
            return array();
        }

        switch($options['scope']) {
            case self::SCOPE_BASE:
                $find = @ldap_read($link, $options['base'], $options['filter'], $options['attrs'], 0, $options['items'], $options['timeout'], $options['deref']);
                break;
            case self::SCOPE_LIST:
                $find = @ldap_list($link, $options['base'], $options['filter'], $options['attrs'], 0, $options['items'], $options['timeout'], $options['deref']);
                break;
            case self::SCOPE_TREE:
                $find = @ldap_search($link, $options['base'], $options['filter'], $options['attrs'], 0, $options['items'], $options['timeout'], $options['deref']);
                break;
        }

        if ( ! $find) {
            return array();
        }

        $result = array();
        $entry = ldap_first_entry($link, $find);

        if ($entry) {
            do {
                $attrs = ldap_get_attributes($link, $entry);
                $dn = ldap_get_dn($link, $entry);

                foreach($attrs as $key => $val) {
                    if ( ! is_array($val)) {
                        unset($attrs[$key]);
                        continue;
                    }
                    if (isset($val['count'])) {
                        unset($attrs[$key]['count']);
                    }
                }

                $attrs['dn'] = $dn;

                ksort($attrs);

                $result[$dn] = new Entry($attrs);

            } while($entry = ldap_next_entry($link, $entry));
        }

        return $result;
    }

    protected function _startTLS($link) {
        if (function_exists('ldap_start_tls')) {
            if ( ! @ldap_start_tls($link)) {
                $this->_debug('Unable to start TLS');
            }
        }
    }

    protected function _resolveBaseDN() {

        if (isset($this->_caches['bases'])) {
            return $this->_caches['bases'];
        }

        if ($this->_config->server->base->count() > 0) {
            $this->_caches['bases'] = $this->_config->server->base->toArray();
        } else {
            $entry = $this->_getDNAttrValues('');
            $bases = array();

            if ( ! is_null($entry)) {
                $bases = $entry->getRaw('namingContexts', array());    
            }

            $this->_caches['bases'] = $bases;
        }

        return $this->_caches['bases'];
    }

    protected function _resolveAuthBaseDN() {
        if ($this->_config->server->auth->base->count() > 0) {
            return $this->_config->server->auth->base->toArray();
        } else {
            return $this->_resolveBaseDN();
        }
    }

    protected function _resolveLoginBaseDN() {
        if ($this->_config->login->base->count() > 0) {
            return $this->_config->login->base->toArray();
        } else {
            return $this->_resolveBaseDN();
        }   
    }

    protected function _getDNAttrValues($dn, $attrs = FALSE, $cache = TRUE) {

        if ($attrs === FALSE) {
            $attrs = array('*', '+');
        }

        $token = NULL;

        if (in_array('*', $attrs) && in_array('+', $attrs)) {
            $token = '&';
        } else if (in_array('*', $attrs)) {
            $token = '*';
        } else if (in_array('+', $attrs)) {
            $token = '+';
        }

        if ($cache && ! is_null($token) && isset($this->_caches['attrs'][$dn][$token])) {
            return $this->_caches['attrs'][$dn][$token];
        }

        $options = array(
            'base' => self::__escapeDN($dn),
            'scope' => 'base',
            'attrs' => $attrs
        );

        $result = $this->query($options);

        if (count($result)) {
            $result = array_pop($result);
        } else {
            return NULL;
        }

        if ( ! is_null($token)) {
            $this->_caches['attrs'][$dn][$token] = $result;
        }

        return $result;
    }

    protected function _resolveAuthName($user) {
        $options = array();

        $options['filter'] = sprintf(
            '(&(%s=%s)%s)',
            $this->_config->server->auth->attr,
            $user,
            $this->_resolveAuthClass()
        );

        $options['attrs'] = array('dn');

        $result = array();
        $bases = $this->_resolveAuthBaseDN();

        foreach($bases as $base) {
            $options['base'] = $base;
            $result = $this->query($options);

            if (count($result) == 1) {
                break;
            }
        }

        if (count($result) != 1) {
            return NULL;
        }

        $item = array_shift($result);

        if ( ! isset($item['dn'])) {
            return NULL;
        }

        return $item['dn'];
    }

    protected function _resolveAuthClass() {
        $class = $this->_config->server->auth->class->toArray();
        return self::__compileParameters('objectclass', $class);
    }

    protected function __resolveLoginClass() {
        $class = $this->_config->login->class->toArray();
        return self::__compileParameters('objectclass', $class);
    }

    protected function _resolveAuthUser() {
        $username = $this->_caches['username'];

        // try default
        if (empty($username)) {
            $user = $this->_config->server->auth->user;
        }

        return $username;
    }

    protected function _resolveAuthPass() {
        $password = $this->_caches['password'];

        // try default
        if (empty($password)) {
            $pass = $this->_config->server->auth->pass;
        }
        
        return $password;
    }

    protected function _debug($message) {
        if ($this->_config->debug) {
            echo 'DEBUG => '.$message."\n";    
        }
    }

    private static function __escapeDN($dn) {
        if ( ! trim($dn)) {
            return $dn;
        }

        while (preg_match('/([^\\\\]),(\s*[^=]*\s*),/', $dn)) {
            $dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*),/','$1\\\\2C$2,', $dn);
        }

        $dn = preg_replace('/([^\\\\]),(\s*[^=]*\s*)([^,])$/','$1\\\\2C$2$3', $dn);

        return $dn;
    }

    private static function __unescapeDN($dn) {
        if (is_array($dn)) {
            $result = array();
            foreach($dn as $key => $item) {
                $result[$key] = preg_replace(
                    '/\\\([0-9A-Fa-f]{2})/e',
                    function($e){
                        return "'".chr(hexdec($e))."'";
                    },
                    $item
                );
            }
            return $result;
        } else {
            return preg_replace_callback(
                '/\\\([0-9A-Fa-f]{2})/', 
                function($e){
                    return "'".chr(hexdec($e))."'";
                },
                $dn
            );
        }
    }

    private static function __fixupMerge($config, $key) {
        $arr = array_unique($config->get($key)->toArray());
        $config->offsetSet($key, $arr);
    }

    private static function __compileParameters($param, $value) {
        if (is_array($value) && count($value) > 0)  {
            return sprintf(
                '(%s=%s)',
                $param,
                join(')('.$param.'=', $value)
            );
        }
        return '';
    }
}