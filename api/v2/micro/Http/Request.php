<?php
namespace Micro\Http;

class Request extends \Phalcon\Http\Request {
    
    public function getParams() {
        $query = $this->getQuery();
        unset($query['_url']);

        return $query;
    }

    public function getJson($field = NULL, $filters = NULL) {
        $json = $this->getJsonRawBody(TRUE);
        $di = $this->getDI();

        if (is_null($field)) {
            if ( ! is_null($json) && $di->has('sanitize')) {
                $json = $di->getShared('sanitize')->sanitizeBatch($json);
            }
            return $json;
        }

        $value = isset($json[$field]) ? $json[$field] : NULL;

        if ( ! is_null($value) && ! is_null($filters)) {
            $value = $di->getShared('filter')->sanitize($value, $filters);
        }

        return $value;
    }

    public function getFiles() {
        return $this->getUploadedFiles();
    }

}