<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace is\includes;

abstract class AbstractDTO {

    abstract public function __toArray($ext = []) : array;
    abstract public static function getProperties() : array;

    protected function setProperty($property, $value){
        if(property_exists($this, $property) && $property != 'isValid'){
            $this->$property = $value;
        }
        return $this;
    }

    public static function getFields(){
        return array_keys(static::getProperties());
    }
}