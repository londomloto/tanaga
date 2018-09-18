<?php
namespace Micro\Ldap;

class Entry implements \ArrayAccess, \Jsonserializable {

    protected $_data;

    public function __construct($data = array()) {
        
        foreach($data as $key => $val) {
            if ( ! is_array($data[$key])) {
                $data[$key] = array($val);
            }
        }

        $this->_data = $data;
    }

    public final function __get($attr) {
        if (isset($this->_data[$attr])) {
            return $this->get($attr);
        }
        return $this->_data[$attr];
    }

    public function key() {
        return $this->_data['dn'][0];
    }

    public function getRaw($attr, $default = NULL) {
        if (isset($this->_data[$attr])) {
            return $this->_data[$attr];
        }
        return $default;
    }

    public function get($attr, $default = NULL) {
        if (isset($this->_data[$attr][0])) {
            return $this->_data[$attr][0];
        }
        return $default;
    }

    public function toArray() {
        return $this->_data;
    }

    public function jsonSerialize() {
        return $this->_data;
    }

    public function offsetExists($attr) {
        return isset($this->_data[$attr]);
    }

    public function offsetGet($attr) {
        return $this->getRaw($attr);
    }

    // readonly
    public function offsetSet($attr, $value) {}
    public function offsetUnset($attr) {}
}