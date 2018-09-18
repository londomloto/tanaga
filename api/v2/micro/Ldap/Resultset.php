<?php
namespace Micro\Ldap;

class Resultset implements 
    \ArrayAccess, 
    \SeekableIterator, 
    \Countable, 
    \JsonSerializable {

    protected $_items;
    protected $_index;
    protected $_count;

    public function __construct($items) {
        $this->_items = array_values($items);
        $this->_index = 0;
        $this->_count = count($items);
    }

    public function count() {
        return $this->_count;
    }

    public function seek($index) {
        if ( ! isset($this->_items[$index])) {
            throw new \OutOfBoundsException("invalid seek position ($index)");
        }
        $this->_index = $index;
    }

    public function current() {
        return $this->_items[$this->_index];
    }

    public function key() {
        return $this->_index;
    }

    public function next() {
        ++$this->_index;
    }

    public function rewind() {
        $this->_index = 0;
    }

    public function valid() {
        return isset($this->_items[$this->_index]);
    }

    public function jsonSerialize() {
        $array = array();
        foreach($this->_items as $item) {
            $array[] = $item->toArray();
        }
        return $array;
    }

    public function offsetExists($index) {
        return isset($this->_items[$index]);
    }

    public function offsetGet($index) {
        return $this->_items[$index];
    }

    // readonly
    public function offsetSet($index, $value) {}
    public function offsetUnset($index) {}
}