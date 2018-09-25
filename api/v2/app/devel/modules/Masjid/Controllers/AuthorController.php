<?php
namespace App\Masjid\Controllers;

use App\Masjid\Models\Author;

class AuthorController extends \Micro\Controller {

    public function findAction() {
        return Author::get()->filterable()->sortable()->paginate();
    }

    public function createAction() {
        $post = $this->request->getJson();
        $data = new Author();
        if ($data->save($post)) {
            return Author::get($data->id_author);
        }
        return Author::none();
    }

    public function updateAction($id) {
        $post = $this->request->getJson();
        $item = Author::get($id);

        if ($item->data) {
            $item->data->save($post);
        }

        return $item;
    }

    public function deleteAction($id) {
        $data = Author::get($id)->data;
        $done = FALSE;
        if ($data) {
            $done = $data->delete();
        }
        return array(
            'success' => $done
        );
    }
}