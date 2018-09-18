<?php
namespace Micro;

abstract class Model extends \Phalcon\Mvc\Model {

    private static $__templates = array();

    public final static function getTemplate() {
        $class = get_called_class();

        if ( ! isset(self::$__templates[$class])) {
            self::$__templates[$class] = new $class();
        }

        return self::$__templates[$class];
    }

    public final static function get($id = NULL) {
        if (is_null($id)) {
            return new ModelQuery(self::getTemplate(), \Phalcon\DI::getDefault());
        } else {
            $result = new \stdClass();
            $result->success = FALSE;
            $result->data = NULL;

            if (($result->data = self::findFirst($id))) {
                $result->success = TRUE;
            }

            return $result;
        }
    }

    public static function none() {
        $result = new \stdClass();
        $result->success = FALSE;
        $result->data = NULL;
        return $result;
    }
}