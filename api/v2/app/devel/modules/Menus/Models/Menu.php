<?php
namespace App\Menus\Models;

use Phalcon\Mvc\Model\Relation;

class Menu extends \Micro\Model {

    public function initialize() {
        $this->hasOne(
            'smn_path',
            'App\Modules\Models\Module',
            'sm_api',
            array(
                'alias' => 'Module'
            )
        );

        $this->hasMany(
            'smn_id',
            'App\Roles\Models\RoleMenu',
            'srm_smn_id',
            array(
                'alias' => 'MenuRoles',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );

        $this->hasMany(
            'smn_id',
            'App\Users\Models\UserMenu',
            'sum_smn_id',
            array(
                'alias' => 'MenuUsers',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'sys_menus';
    }

    public function toArray($columns = NULL) {
        $data = parent::toArray($columns);

        if ($this->module) {
            $data = array_merge($data, $this->module->toArray());
        }
        return $data;
    }

    public function getTitlePath() {
        $path = array();

        $this->bubble(function($node) use (&$path){
            $path[] = $node->smn_title;
        });

        $path = array_reverse($path);
        $path = implode(' > ', $path);

        return $path;
    }

    public function cascade($handler) {
        $handler($this);

        $child = self::find(array(
            'smn_parent_id = :parent:',
            'bind' => array(
                'parent' => $this->smn_id
            )
        ));

        foreach($child as $row) {
            $row->cascade($handler);
        }
    }

    public function bubble($handler) {
        $handler($this);

        $parent = self::findFirst(array(
            'smn_id = :parent:',
            'bind' => array(
                'parent' => $this->smn_parent_id
            )
        ));

        if ($parent) {
            $parent->bubble($handler);
        }
    }

    public static function grid($options = array()) {
        $items = self::get()
            ->orderBy('smn_order ASC')
            ->execute()
            ->map(function($rec) { return $rec; });

        $nodes = array();
        self::__createGrid($items, 0, $nodes, 0);

        return (object) array(
            'success' => TRUE,
            'data' => $nodes,
            'total' => count($nodes)
        );
    }

    private static function __createGrid($items, $parentId, &$nodes, $level = 0) {
        $stack = array_slice($items, 0);

        foreach($items as $idx => $elem) {
            if ($elem->smn_parent_id == $parentId) {
                $node = $elem->toArray();
                $node['smn_level'] = $level;
                $node['smn_pad'] = ($level * 24).'px';

                $nodes[] = $node;
                
                if (self::__isParent($elem, $stack)) {
                    self::__createGrid($items, $elem->smn_id, $nodes, ($level + 1));
                } else {
                    array_splice($stack, $idx, 1);
                }
            }
        }
    }

    public static function tree($options = array()) {
        

        $items = self::get()
            ->where('smn_visible = 1')
            ->orderBy('smn_order ASC')
            ->execute()
            ->map(function($rec){ return $rec; });

        $nodes = array();

        self::__createTree($items, 0, $nodes);

        return (object) array(
            'success' => TRUE,
            'data' => $nodes
        );
    }

    private static function __createTree($items, $parentId, &$nodes) {
        $stack = array_slice($items, 0);

        foreach($items as $idx => $elem) {
            if ($elem->smn_parent_id == $parentId) {
                $node = $elem->toArray();

                if (self::__isParent($elem, $stack)) {
                    $node['smn_children']  = array();
                    self::__createTree($items, $elem->smn_id, $node['smn_children']);
                } else {
                    array_splice($stack, $idx, 1);
                }
                $nodes[] = $node;
            }
        }
    }

    private static function __isParent($node, $stack) {
        foreach($stack as $elem) {
            if ($elem->smn_parent_id == $node->smn_id) {
                return TRUE;
            }
        }
        return FALSE;
    }
}