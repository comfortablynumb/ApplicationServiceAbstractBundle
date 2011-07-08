<?php

namespace ENC\Bundle\ApplicationServiceAbstractBundle\Data;

class DataHolder implements \Iterator, \ArrayAccess
{
    protected $position;
    protected $data;
    
    public function __construct(array $data)
    {
        $this->position = 0;
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) 
    {
        return isset($this->data[$offset]);
    }
    
    public function offsetUnset($offset) 
    {
        unset($this->data[$offset]);
    }
    
    public function offsetGet($offset) 
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->data[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }

    function valid() {
        return isset($this->data[$this->position]);
    }
}