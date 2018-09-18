<?php
namespace Micro\Role;

class RoleProvider extends \Micro\Component {

    protected $_providers;
    protected $_auth;

    public function __construct() {
        $app = $this->getApp();

        $this->_providers = $app->config->role->providers;
        $this->_auth = $app->auth;
    }

    protected function _run($class, $method, $args) {
        return call_user_func_array(array($class, $method), $args);
    }

    protected function _get($model = FALSE) {
        static $role;

        if (is_null($role)) {
            $user = $this->_auth->user();    
            if ($user && ! empty($user['su_sr_id'])) {
                $role = $this->_run($this->_providers->role, 'findFirst', array($user['su_sr_id']));
            } else {
                $role = FALSE;
            }
        }
        
        return $role ? ($model ? $role : $role->toArray()) : NULL;
    }

    public function is($name = NULL) {
        return $this->name() == $name;
    }

    public function model() {
        return $this->_get(TRUE);
    }

    public function id() {
        $role = $this->_get();
        return $role ? $role['sr_id'] : NULL;
    }

    public function name() {
        $role = $this->_get();
        return $role ? $role['sr_slug'] : NULL;
    }

    public function menus() {
        $user = $this->_auth->user(NULL, TRUE);
        if ($user) {

            $nodes = array();
            
            $menus = $user->getMenus()->filter(function($elem){ 
                if ($elem->smn_visible == 1) {
                    return $elem;
                }
            });

            $stack = array_flip(array_map(function($elem){ return $elem->smn_id; }, $menus));

            foreach($menus as $menu) {
                if ($menu->smn_parent_id != 0) {
                    $menu->bubble(function($elem) use (&$stack, &$nodes){
                        if ( ! isset($stack[$elem->smn_id])) {
                            $nodes[] = $elem;
                            $stack[$elem->smn_id] = TRUE;
                        }
                    });
                }
                $nodes[] = $menu;
            }

            usort($nodes, function($a, $b){
                $va = $a->smn_order;
                $vb = $b->smn_order;

                if ($va == $vb) return 0;
                return $va < $vb ? -1 : 1;
            });

            $tree = array();

            self::__createTreeMenu($nodes, 0, $tree);

            return $tree;
        }

        return array();
    }

    private static function __createTreeMenu($menus, $parentId, &$result) {
        $stack = array_slice($menus, 0);

        foreach($menus as $idx => $elem) {
            if ($elem->smn_parent_id == $parentId) {
                $node = $elem->toArray();

                if (self::__menuIsParent($elem, $stack)) {
                    $node['smn_children']  = array();
                    self::__createTreeMenu($menus, $elem->smn_id, $node['smn_children']);
                } else {
                    array_splice($stack, $idx, 1);
                }
                $result[] = $node;
            }
        }
    }

    private static function __menuIsParent($menu, $menus) {
        foreach($menus as $elem) {
            if ($elem->smn_parent_id == $menu->smn_id) {
                return TRUE;
            }
        }
        return FALSE;
    }

    public function accesses() {
        static $accesses;

        if (is_null($accesses)) {
            $user = $this->_auth->user(NULL, TRUE);
            $accesses = array();

            if ($user) {
                $accesses = $user->getAccesses();
            }
        }

        return $accesses;
    }

    public function permissions() {
        static $perms;

        if (is_null($perms)) {
            $user = $this->_auth->user(NULL, TRUE);
            $perms = array();

            if ($user) {
                $perms = $user->getPermissions();
            }
        }

        return $perms;
    }

    public function has($permission) {
        static $stack;

        if (is_null($stack)) {
            $stack = array();
            if ($this->_providers->offsetExists('capability')) {

                $part = explode('@', $permission);
                $name = $part[0];

                $find = call_user_func_array(
                    $this->_providers->capability.'::find', 
                    array(
                        array(
                            'smc_name = :name:',
                            'bind' => array(
                                'name' => $name
                            )
                        )
                    )
                );

                foreach($find as $row) {
                    $perm = $row->getNamespace();
                    $stack[$perm] = TRUE;
                }
            }
        }

        return isset($stack[$permission]);
    }

    public function can($permission) {
        static $stack;

        if (is_null($stack)) {
            $stack = array();

            foreach($this->permissions() as $e) {
                if ($e['authorized'] == 1) {
                    $stack[$e['permission']] = TRUE;    
                }
            }
        }
        
        return isset($stack[$permission]);
    }

    public function validate($permission) {
        if ( ! $this->can($permission)) {
            throw new \Phalcon\Exception("You don't have privilege to perform this action", 403);
        }
    }

}