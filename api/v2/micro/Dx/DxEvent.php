<?php
namespace Micro\Dx;

class DxEvent {

    public $type;
    public $detail;

    private $__defaultPrevented = FALSE;

    public function __construct($type, Array $data = NULL) {
        $this->type = $type;
                
        if (is_null($data)) {
            $this->detail = new \stdClass();
        } else {
            $this->detail = (object) $data;
        }
    }

    public function preventDefault() {
        $this->__defaultPrevented = TRUE;
    }

    public function isDefaultPrevented() {
        return $this->__defaultPrevented;
    }

}