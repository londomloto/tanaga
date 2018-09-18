<?php
namespace App\Users\Models;

use App\Users\Models\UserMenu,
    App\Users\Models\UserPermission,
    Phalcon\Mvc\Model\Relation,
    Phalcon\Validation,
    Phalcon\Validation\Validator\Uniqueness;

class User extends \Micro\Model {
    
    const AVATAR_DEFAULT = 'defaults/avatar-0.jpg';

    public function initialize() {

        $this->hasMany(
            'su_id',
            'App\Users\Models\UserKanban',
            'suk_su_id',
            array(
                'alias' => 'Kanban',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'su_id',
            'App\Bpmn\Models\FormUser',
            'bfu_su_id',
            array(
                'alias' => 'FormUsers',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->belongsTo(
            'su_sr_id',
            'App\Roles\Models\Role',
            'sr_id',
            array(
                'alias' => 'Role'
            )
        );
        
        $this->hasMany(
            'su_id',
            'App\Users\Models\UserPermission',
            'sup_su_id',
            array(
                'alias' => 'UserPermissions',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        // $this->hasManyToMany(
        //     'su_id',
        //     'App\Users\Models\UserPermission',
        //     'sup_su_id',
        //     'sup_smc_id',
        //     'App\Modules\Models\ModuleCapability',
        //     'smc_id',
        //     array(
        //         'alias' => 'Permissions'
        //     )
        // );

        $this->hasMany(
            'su_id',
            'App\Users\Models\UserMenu',
            'sum_su_id',
            array(
                'alias' => 'UserMenus'
            )
        );

        $this->hasManyToMany(
            'su_id',
            'App\Users\Models\UserMenu',
            'sum_su_id',
            'sum_smn_id',
            'App\Menus\Models\Menu',
            'smn_id',
            array(
                'alias' => 'Menus'
            )
        );

        $this->hasManyToMany(
            'su_id',
            'App\Projects\Models\ProjectUser',
            'spu_su_id',
            'spu_sp_id',
            'App\Projects\Models\Project',
            'sp_id',
            array(
                'alias' => 'Projects'
            )
        );

        $this->hasMany(
            'su_id',
            'App\Tasks\Models\TaskUser',
            'ttu_su_id',
            array(
                'alias' => 'Tasks',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

    }

    public function getSource() {
        return 'sys_users';
    }

    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            "su_email",
            new Uniqueness(
                [
                    "message" => "Email name already registered",
                ]
            )
        );

        return $this->validate($validator);
    }

    // @Override
    public function toArray($columns =  NULL) {
        $array = parent::toArray($columns);
        $array['su_fullname'] = $this->getName();
        $array['su_avatar'] = $this->getAvatar();
        $array['su_avatar_url'] = $this->getAvatarUrl();
        $array['su_avatar_thumb'] = $this->getAvatarThumb();

        // handle password
        unset($array['su_passwd']);    

        if ($this->role) {
            $array = array_merge($array, $this->role->toArray());
        }

        // need to reinvite
        $array['su_need_reinvite'] = FALSE;

        if ( ! empty($array['su_invite_token'])) {
            $array['su_need_reinvite'] = TRUE;
        }

        return $array;
    }

    public function getName() {
        return empty($this->su_fullname) ? $this->su_email : $this->su_fullname;
    }

    public function getAvatar() {
        // cache for multiple call in same time
        static $avatar = array();
        
        $key = $this->su_id;

        if ( ! isset($avatar[$key])) {
            $avatar[$key] = $this->su_avatar;
            $exists = FALSE;

            if ( ! empty($avatar[$key])) {
                $exists = file_exists(PUBPATH.'resources/avatars/'.$avatar[$key]);
            }

            if ( ! $exists) {
                $avatar[$key] = self::AVATAR_DEFAULT;
            }
        }

        return $avatar[$key];
    }

    public function getAvatarUrl() {
        $app = \Micro\App::getDefault();
        $avatar = $this->getAvatar();
        return $app->url->getBaseUrl().'public/resources/avatars/'.$avatar;
    }

    public function getAvatarThumb() {
        $app = \Micro\App::getDefault();
        $avatar = $this->getAvatar();
        return $app->url->getSiteUrl('assets/thumb?s=public/resources/avatars/'.$avatar);
    }

    public function getMenus($options = array()) {
        if ($this->userMenus->count() == 0) {
            if ($this->role) {
                return $this->role->getMenus();
            } else {
                return \App\Menus\Models\Menu::find('smn_id = -1');
            }
        } else {
            $conditions = '';

            if (isset($options[0]) && is_string($options[0])) {
                $conditions = $options[0];
                unset($options[0]);
            } else if (isset($options['conditions'])) {
                $conditions = $options['conditions'];
                unset($options['conditions']);
            }

            if ( ! empty($conditions)) {
                $conditions .= ' AND ';
            }
            
            $conditions .= 'App\Users\Models\UserMenu.sum_selected = 1';
            $options['conditions'] = $conditions;

            return parent::getMenus($options);
        }
    }

    public function getPermissions($options = array()) {
        if ($this->userPermissions->count() == 0) {
            if ($this->role) {
                return $this->role->getPermissions();
            } else {
                return array();
            }
        } 

        $perms = array();

        foreach($this->getUserPermissions($options) as $prof) {
            if (($cap = $prof->capability) && ($mod = $cap->module)) {
                $perms[] = array(
                    'authorized' => $prof->sup_selected ? TRUE : FALSE,
                    'permission' => strtolower($cap->smc_name).'@'.$mod->sm_name,
                    'capability' => $cap->smc_name,
                    'module_name' => $mod->sm_name,
                    'module_path' => $mod->sm_api
                );
            }
        }

        return $perms;
    }

    public function getAccesses() {
        if ($this->role) {
            return $this->role->getAccesses();
        }
        return array();
    }

    // @Override
    public static function findFirstByEmail($email) {
        return self::get()
            ->where('su_email = :email:', array('email' => $email))
            ->execute()
            ->getFirst();
    }

    public function saveKanban($items) {

        if (count($items) === 0) {
            // reset
            $this->kanban->delete();
            return;
        }

        $create = array();
        $update = array();
        $delete = array();

        $exists = array_map(
            function($item) {
                return $item['suk_id'];
            },
            $this->kanban->toArray()
        );

        $sliced = array();

        foreach($items as $item) {
            if (empty($item['suk_id'])) {
                if ($item['suk_selected']) {
                    $create[] = $item;    
                }
            } else {
                if (in_array($item['suk_id'], $exists)) {
                    if ($item['suk_selected']) {
                        $update[] = $item;
                        $sliced[] = $item['suk_id'];    
                    }
                }
            }
        }

        for ($i = count($exists) - 1; $i >= 0; $i--) {
            if ( ! in_array($exists[$i], $sliced)) {
                $delete[] = $exists[$i];
            }
        }

        if (count($delete) > 0) {
            $deleted = UserKanban::find('suk_id IN ('.implode(',', $delete).')');

            foreach($deleted as $item) {
                $item->suk_selected = 0;
                $item->save();
            }
        }

        if (count($update) > 0) {
            foreach($update as $item) {
                $k = UserKanban::findFirst($item['suk_id']);
                $k->save($item);
            }
        }

        if (count($create) > 0) {
            foreach($create as $item) {
                $k = new UserKanban();
                $item['suk_su_id'] = $this->su_id;
                $k->save($item);
            }
        }
    }

    public function saveMenus($items) {

        if (count($items) === 0) {
            // reset
            $this->userMenus->delete();
            return;
        }

        $create = array();
        $update = array();
        $exists = array();

        foreach($this->userMenus as $um) {
            $exists[$um->sum_smn_id] = $um;
        }

        foreach($items as $id) {
            if ( ! isset($exists[$id])) {
                $create[] = $id;
            } else {
                $update[] = $id;
                unset($exists[$id]);
            }
        }

        foreach($exists as $menu => $rm) {
            $rm->sum_selected = 0;
            $rm->save();
        }

        foreach($update as $id) {
            $um = UserMenu::findFirst(array(
                'sum_su_id = :user: AND sum_smn_id = :menu:',
                'bind' => array(
                    'user' => $this->su_id,
                    'menu' => $id
                )
            ));

            if ($um) {
                $um->sum_selected = 1;
                $um->save();
            }
        }

        foreach($create as $id) {
            $um = new UserMenu();

            $um->sum_su_id = $this->su_id;
            $um->sum_smn_id = $id;
            $um->sum_selected = 1;
            
            $um->save();
        }
    }

    public function savePermissions($items) {
        if (count($items) === 0) {
            // reset
            $this->userPermissions->delete();
            return;
        }
        
        $create = array();
        $update = array();
        $exists = array();

        foreach($this->userPermissions as $up) {
            $exists[$up->sup_smc_id] = $up;
        }

        foreach($items as $id) {
            if ( ! isset($exists[$id])) {
                $create[] = $id;
            } else {
                $update[] = $id;
                unset($exists[$id]);
            }
        }

        foreach($exists as $cap => $up) {
            $up->sup_selected = 0;
            $up->save();
        }

        foreach($update as $id) {
            $up = UserPermission::findFirst(array(
                'sup_su_id = :user: AND sup_smc_id = :capability:',
                'bind' => array(
                    'user' => $this->su_id,
                    'capability' => $id
                )
            ));

            if ($up) {
                $up->sup_selected = 1;
                $up->save();
            }
        }

        foreach($create as $id) {
            $up = new UserPermission();
            $up->sup_su_id = $this->su_id;
            $up->sup_smc_id = $id;
            $up->sup_selected = 1;
            $up->save();
        }
    }

}