<?php
namespace Micro\Dx;

class DxProvider extends \Micro\Component  {

    protected $_providers;
    protected static $_profiles = array();

    public function __construct() {
        $app = $this->getApp();

        if ($app->config->offsetExists('dx')) {
            $this->_providers = $app->config->dx->providers;
        }
    }

    public function profiles() {
        $query = self::__run($this->_providers->profile, 'get');
        $query->columns('profile_name');
        $query->orderBy('profile_name ASC');

        $profiles = array();

        foreach($query->execute() as $row) {
            $profiles[] = $this->profile($row->profile_name);
        }

        return $profiles;
    }

    public function profile($name, $reset = TRUE) {
        $profile = isset(self::$_profiles[$name]) ? self::$_profiles[$name] : NULL;    

        if (is_null($profile)) {
            $profile = new DxProfile($name, $this->_providers);
            self::$_profiles[$name] = $profile;
        }

        if ($reset) {
            $profile->reset();
        }

        return $profile;
    }

    private static function __run($class, $method, $args = array()) {
        return call_user_func_array(array($class, $method), $args);
    }
}