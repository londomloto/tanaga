<?php
namespace App\Bpmn\Controllers;

use App\Bpmn\Models\Diagram;
use App\Bpmn\Models\Shape;
use App\Bpmn\Models\Link;

class DiagramsController extends \Micro\Controller {
    
    public function findAction() {
        $query = Diagram::get();

        $q = $this->request->getQuery('query');

        if ( ! empty($q)) {
            $query->where('name LIKE :q:', array('q' => '%'.$q.'%'));
        }

        return $query
            ->orderBy('created_date DESC')
            ->paginate();
    }

    public function findByIdAction($id) {
        return Diagram::get($id);
    }

    public function expandAction($id) {
        return Diagram::expand($id);
    }

    public function storeAction() {
        $post = $this->request->getJson();
        return Diagram::store($post);
    }
    
    public function createAction() {
        $post = $this->request->getJson();

        $diagram = new Diagram();
        $post['created_date'] = date('Y-m-d H:i:s');
        $diagram->save($post);

        return array(
            'success' => TRUE,
            'data' => $diagram
        );
    }

    public function updateAction($id) {
        $query = Diagram::get($id);
        $data = $this->request->getJson();
        if ($query->data) {
            $query->data->save($data);
        }
        return $query;
    }

    public function deleteAction($id) {
        $query = Diagram::get($id);

        if ($query->data) {
            // delete cover first
            if ( ! empty($query->data->cover) && ($query->data->cover != Diagram::DEFAULT_COVER)) {
                $cover = PUBPATH.'resources/diagrams/'.$query->data->cover;
                if (file_exists($cover)) {
                    @unlink($cover);
                }
            }

            if ($query->data->delete()) {
                return array(
                    'success' => TRUE
                );
            }
        }
        
        return array(
            'success' => FALSE
        );
    }

    public function uploadAction($id) {
        $query = Diagram::get($id);

        if ($query->data) {
            foreach($this->request->getFiles() as $file) {
                $name = md5($query->data->slug.date('YmdHis')).'.jpg';
                $path = PUBPATH.'resources/diagrams/'.$name;
                if (@$file->moveTo($path)) {

                    if ($query->data->cover != Diagram::DEFAULT_COVER) {
                        @unlink(PUBPATH.'resources/diagrams/'.$query->data->cover);
                    }

                    $query->data->save(array(
                        'cover' => $name
                    ));
                }
                break;
            }
        }
        return Diagram::expand($id);
    }

    public function sourceAction() {
        $post = $this->request->getJson();

        if (isset($post['sql']) && ! empty($post['sql'])) {
            $sql = $post['sql'];
            if (preg_match('/SELECT\s(.*)\sFROM/', $post['sql'], $matches)) {
                $columns = trim($matches[1]);
                if ($columns == '*') {
                    
                } else {

                }
            }
        }
    }

    public function exportAction($id) {
        $diagram = Diagram::get($id)->data;

        if ($diagram) {

            $json = array();

            $json['diagram'] = $diagram->toArray();
            $json['shapes'] = $diagram->getShapes()->toArray();
            $json['links'] = $diagram->getLinks()->toArray();

            $file = $diagram->slug.'.json';
            $text = json_encode($json, JSON_PRETTY_PRINT);

            header("Content-Disposition: attachment; filename=\"".$file."\"");
            header("Content-Type: application/force-download");
            header("Content-Length: " . strlen($text));
            header("Connection: close");

            echo $text;

            exit();
        } else {
            throw new \Phalcon\Exception("Diagram not found", 404);
        }
        
    }

    public function importAction() {

        if ($this->request->hasFiles()) {
            $upload = $this->request->getFiles();
            $file = $upload[0];

            if (file_exists($file->getTempName())) {
                $json = json_decode(file_get_contents($file->getTempName()), TRUE);

                if ($json) {
            
                    $diagramArray = $json['diagram'];
                    // unset several fields
                    unset($diagramArray['id'], $diagramArray['slug']);

                    $diagram = new Diagram();

                    if ($diagram->save($diagramArray)) {
                        $diagram = $diagram->refresh();

                        $mapping = array();
                        $shapesArray = $json['shapes'];

                        foreach($shapesArray as $item) {
                            $old = $item['id'];
                            
                            $item['diagram_id'] = $diagram->id;
                            unset($item['id']);

                            $shape = new Shape();

                            if ($shape->save($item)) {
                                $shape = $shape->refresh();
                                $mapping[$old] = $shape->id;
                            }
                        }

                        $linksArray = $json['links'];

                        foreach($linksArray as $item) {
                            $item['diagram_id'] = $diagram->id;

                            unset($item['id']);

                            $oldSource = $item['source_id'];
                            $oldTarget = $item['target_id'];

                            if (isset($mapping[$oldSource])) {
                                $item['source_id'] = $mapping[$oldSource];
                            }

                            if (isset($mapping[$oldTarget])) {
                                $item['target_id'] = $mapping[$oldTarget];
                            }

                            $link = new Link();
                            $link->save($item);
                        }

                    }

                    return array(
                        'success' => TRUE,
                        'data' => $diagram->toArray()
                    );

                }
            }
        }

        return array(
            'success' => FALSE,
            'data' => NULL
        );
        
    }
}