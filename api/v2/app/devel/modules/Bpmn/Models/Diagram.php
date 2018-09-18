<?php
namespace App\Bpmn\Models;

use Micro\Helpers\Text,
    App\Bpmn\Models\Shape,
    Phalcon\Mvc\Model\Relation;

class Diagram extends \Micro\Model {

    const DEFAULT_COVER = 'defaults/diagram-2.png';

    public function initialize() {
        $this->hasMany(
            'id', 
            'App\Bpmn\Models\Shape', 
            'diagram_id', 
            array( 
                'alias' => 'Shapes',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                ) 
            )
        );

        $this->hasMany(
            'id', 
            'App\Bpmn\Models\Link', 
            'diagram_id', 
            array( 
                'alias' => 'Links',
                'foreignKey' => array(
                    'action' => Relation::ACTION_CASCADE
                )
            )
        );
    }

    public function getSource() {
        return 'bpm_diagrams';
    }

    public function toArray($columns = NULL) {
        $array = parent::toArray($columns);

        // handle cover
        $cover  = PUBPATH.'resources/diagrams/'.$array['cover'];
        $exists = file_exists($cover) && ! is_dir($cover);

        if ( ! $exists) {
            $array['cover'] = self::DEFAULT_COVER;
        }

        $URL = $this->getDI()->get('url');
        $array['cover_url'] = $URL->getBaseUrl().'public/resources/diagrams/'.$array['cover'];
        $array['cover_thumb'] = $URL->getSiteUrl('assets/thumb?s=public/resources/diagrams/'.$array['cover']);

        return $array;
    }

    public function beforeSave() {
        $slug = Text::slug($this->name);

        if (isset($this->id)) {
            $found = self::find(array(
                'slug LIKE :slug: AND id != :id:',
                'bind' => array(
                    'slug' => $slug.'%',
                    'id' => $this->id
                )
            ));
        } else {
            $found = self::find(array(
                'slug LIKE :slug:',
                'bind' => array(
                    'slug' => $slug.'%'
                )
            ));
        }

        if (count($found) > 0) {
            $max = 0;
            foreach($found as $item) {
                if (preg_match('/(\d+)$/', $item->slug, $match)) {
                    $num = (int) $match[1];
                    if ($num > $max) {
                        $max = $num;
                    }
                }
            }
            $slug = $slug.'-'.($max + 1);
        }

        $this->slug = $slug;
    }

    public function getStart() {
        return $this->getShapes(array(
            'type = :type:',
            'bind' => array(
                'type' => Shape::SHAPE_TYPE_START
            )
        ))->getFirst();
    }

    public static function expand($id) {
        $diagram = self::findFirst($id);

        $result = array(
            'success' => FALSE,
            'data' => NULL
        );

        if ($diagram) {
            $result['success'] = TRUE;

            $result['data'] = array(
                'props' => $diagram->toArray(),
                'shapes' => array(),
                'links' => array()
            );

            $shapes = Shape::find(array(
                'diagram_id = :id:',
                'bind' => array('id' => $diagram->id)
            ));

            foreach($shapes as $shape) {
                $shapeArray  = $shape->toArray();
                $shapeParams = $shapeArray['params'];

                unset($shapeArray['params']);

                $result['data']['shapes'][] = array(
                    'props'  => $shapeArray,
                    'params' => $shapeParams
                );
            }


            $links = Link::find(array(
                'diagram_id = :id:',
                'bind' => array('id' => $diagram->id)
            ));

            foreach($links as $link) {
                $linkArray  = $link->toArray();
                $linkParams = $linkArray['params'];

                unset($linkArray['params']);

                $result['data']['links'][] = array(
                    'props'  => $linkArray,
                    'params' => $linkParams
                );
            }
        }

        return $result;
    }

    public static function store(Array $options) {
        if (isset($options['props']['id'])) {
            return self::__update($options);
        } else {
            return self::__create($options);
        }
    }

    private static function __create($options) {
        $options = self::__parse($options);
        
        $data = $options['props'];
        $data['created_date'] = date('Y-m-d H:i:s');
        unset($data['id']);

        $diagram = new Diagram();
        $success = $diagram->save($data);

        if ($success) {
            $diagramId = $diagram->id;

            if (isset($options['shapes'])) {
                $shapes = array();
                
                self::__cascade(
                    $options['shapes'], 
                    function($item) use ($diagramId, &$shapes) {
                        $saved = Shape::store(array(
                            'action' => 'create',
                            'payload' => $item['props'],
                            'parent' => isset($shapes[$item['props']['parent']]) ? $shapes[$item['props']['parent']] : NULL,
                            'diagramId' => $diagramId,
                            'params' => $item['params']
                        ));

                        if ($saved) {
                            $shapes[$item['props']['guid']] = $saved->id;
                        }
                    }
                );

                if (isset($options['links'])) {
                    foreach($options['links'] as $item) {
                        Link::store(array(
                            'action' => 'create',
                            'payload' => $item['props'],
                            'diagramId' => $diagramId,
                            'params' => $item['params'],
                            'sourceId' => isset($shapes[$item['props']['source']]) ? $shapes[$item['props']['source']] : NULL,
                            'targetId' => isset($shapes[$item['props']['target']]) ? $shapes[$item['props']['target']] : NULL
                        ));
                    }
                }
            }
            return self::expand($diagramId);
        }

        return array(
            'success' => FALSE,
            'data' => NULL
        );
    }

    private static function __update($options) {
        $options = self::__parse($options);

        $diagramId = $options['props']['id'];
        $diagram = self::findFirst($diagramId);

        if ($diagram) {
            
            $oldslug = $diagram->slug;
            $success = $diagram->save($options['props']);

            if ($success) {

                if ($oldslug != $diagram->slug) {
                    // we need to update task statuses here...
                    \App\Tasks\Models\TaskStatus::find(array(
                        'tts_worker = :slug:',
                        'bind' => array( 'slug' => $oldslug )
                    ))->update(array(
                        'tts_worker' => $diagram->slug
                    ));    
                }

                if (isset($options['shapes']) && count($options['shapes']) > 0) {
                    $exists = array();
                    $shapes = array();

                    self::__cascade($options['shapes'], function($item) use ($diagramId, &$shapes, &$exists){
                        $shapeId = $item['props']['id'];
                        $shapeGuid = $item['props']['guid'];
                        $shapeParent = $item['props']['parent'];

                        if (empty($item['props']['id'])) {
                            $saved = Shape::store(array(
                                'diagramId' => $diagramId,
                                'action' => 'create',
                                'payload' => $item['props'],
                                'parent' => isset($shapes[$shapeParent]) ? $shapes[$shapeParent] : NULL,
                                'params' => $item['params']
                            ));

                            if ($saved) {
                                $shapes[$shapeGuid] = $saved->id;
                                $exists[] = $shapes[$shapeGuid];
                            }
                        } else {
                            $saved = Shape::store(array(
                                'diagramId' => $diagramId,
                                'action' => 'update',
                                'payload' => $item['props'],
                                'shapeId' => $shapeId,
                                'params' => $item['params'],
                                'parent' => isset($shapes[$shapeParent]) ? $shapes[$shapeParent] : NULL
                            ));

                            $exists[] = $shapeId;
                            $shapes[$shapeGuid] = $shapeId;
                        }
                    });

                    if (count($exists) > 0) {
                        Shape::find(array(
                            'diagram_id = :diagram: AND id NOT IN(' . implode(',', $exists) . ')',
                            'bind' => array('diagram' => $diagramId)
                        ))->delete();
                    }

                    if (isset($options['links']) && count($options['links']) > 0) {
                        $exists = array();

                        foreach($options['links'] as $item) {
                            $linkId = $item['props']['id'];
                            $linkSource = $item['props']['source'];
                            $linkTarget = $item['props']['target'];

                            if (empty($linkId)) {
                                $saved = Link::store(array(
                                    'diagramId' => $diagramId,
                                    'action' => 'create',
                                    'payload' => $item['props'],
                                    'params' => $item['params'],
                                    'sourceId' => isset($shapes[$linkSource]) ? $shapes[$linkSource] : NULL,
                                    'targetId' => isset($shapes[$linkTarget]) ? $shapes[$linkTarget] : NULL
                                ));

                                if ($saved) {
                                    $exists[] = $saved->id;
                                }
                            } else {
                                $saved = Link::store(array(
                                    'diagramId' => $diagramId,
                                    'payload' => $item['props'],
                                    'linkId' => $linkId,
                                    'params' => $item['params'],
                                    'sourceId' => isset($shapes[$linkSource]) ? $shapes[$linkSource] : NULL,
                                    'targetId' => isset($shapes[$linkTarget]) ? $shapes[$linkTarget] : NULL,
                                    'action' => 'update'
                                ));

                                $exists[] = $linkId;
                            }
                        }

                        if (count($exists) > 0) {
                            Link::find(array(
                                'diagram_id = :diagram: AND id NOT IN('. implode(',', $exists) .')',
                                'bind' => array('diagram' => $diagramId)
                            ))->delete();
                        }
                    } else {
                        $diagram->links->delete();
                    }
                } else {
                    $diagram->shapes->delete();
                    $diagram->links->delete();
                }
            }
        }

        return self::expand($diagramId);
    }

    private static function __parse($options) {
        $parsed = array(
            'props' => array(),
            'links' => array(),
            'shapes' => array()
        );

        $parsed['props'] = array_merge($parsed['props'], $options['props']);

        if (isset($options['shapes'], $options['links'])) {
            $parsed['shapes'] = self::__tree($options['shapes']);
            $parsed['links'] = array_merge($parsed['links'], $options['links']);
        }

        return $parsed;
    }

    private static function __tree($shapes) {
        $tree = array();

        if (empty($shapes)) {
            return array();
        }

        $offset = array_flip(
            array_map(
                function($shape){ 
                    return $shape['props']['guid']; 
                }, 
                $shapes
            )
        );

        foreach($shapes as $idx => &$shape) {
            $key = $shape['props']['guid'];
            $pid = $shape['props']['parent'];

            if (empty($pid)) {
                $tree[$idx] = &$shape;
            } else {
                if ( ! isset($shapes[$offset[$pid]]['children'])) {
                    $shapes[$offset[$pid]]['children'] = array();
                }
                $shapes[$offset[$pid]]['children'][$offset[$key]] = &$shape;
            }
        }

        $tree = self::__reindex($tree);
        return $tree;
    }

    public static function __reindex($tree) {
        $branch = array_values($tree);
        
        foreach($branch as &$node) {
            if (isset($node['children'])) {
                $node['children'] = self::__reindex($node['children']);
            }
        }

        return $branch;
    }

    private static function __cascade($shapes, $callback) {
        foreach($shapes as $shape) {
            $callback($shape);
            if (isset($shape['children'])) {
                self::__cascade($shape['children'], $callback);
            }
        }
    }
}