<?php
/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 12.07.17
 * Time: 13:10
 */

namespace is\includes;


use \ArrayAccess;
use \Countable;
use \Iterator;

abstract class AbstractList implements Iterator, ArrayAccess, Countable
{
    protected $holder = [];

    protected static $instanceName;

    /**
     * @param array $ext
     * @return array
     */
    public function __toArray($ext=[]){
        $result = [];
        if ($this->count() > 0 ){
            foreach ($this->holder as $item) {
                $result[] = $item->__toArray($ext);
            }
        }
        return $result;
    }

    /**
     * @param array $ext
     * @return array
     */
    public function __toSqlArray(){
        $result = [];
        if ($this->count() > 0 ){
            foreach ($this->holder as $item) {
                $result[] = $item->__toSqlArray();
            }
        }
        return $result;
    }

    public function rewind()
    {
        reset($this->holder);
        return $this;
    }

    public function current()
    {
        return current($this->holder);
    }

    public function key()
    {
        return key($this->holder);
    }

    public function next()
    {
        next($this->holder);
    }

    public function valid()
    {
        return false !== $this->current();
    }

    public function offsetSet($offset, $value)
    {
        if ($this->checkInstance($value)) {
            if (is_null($offset)) {
                $this->holder[] = $value;
            } else {
                $this->holder[$offset] = $value;
            }
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->holder[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->holder[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->holder[$offset]) ? $this->holder[$offset] : null;
    }

    /**
     * @param $value
     * @param null $offset
     * @return $this
     */
    public function attach($value,$offset = null){
        $this->offsetSet($offset,$value);
        return $this;
    }

    protected function checkInstance($object){
        return ( $object instanceof static::$instanceName );
    }

    public function normalize(){
        return array_values($this->holder);
    }

    public function count(){
        return count($this->holder);
    }

    public function isEmpty(){
        return empty($this->holder);
    }

    public function walk(callable $callback){
        $this->rewind();
        while( $this->valid() ){
            $callback($this->current());
            $this->next();
        }
    }

    public function map(callable $callback){
        $result = [];
        $this->rewind();
        while( $this->valid() ){
            $result[] = $callback($this->current());
            $this->next();
        }
        return $result;
    }

    public function getKeys(){
        return array_keys($this->holder);
    }

    public function valueExists($value){
        return in_array($value,$this->holder);
    }

    public function merge(AbstractList $list, callable $getOffset = null){
        if( $this->isNormalized() ){
            $getOffset = function($elem){
                return null;
            };
        }
        if( is_null($getOffset) || !is_callable($getOffset) ){
            $getOffset = function($elem){
                return $elem->getId();
            };
        }

        $list->rewind();
        while( $list->valid() ){
                $this->offsetSet( $getOffset($list->current()), $list->current());
                $list->next();
        }
        return $this;
    }

    public function reduce(callable $reduce, $initial = null){
        $carry = $initial;
        $this->rewind();
        while( $this->valid() ){
            $carry = $reduce($carry, $this->current());
            $this->next();
        }
        return $carry;
    }

    public function isNormalized(){
        return array_key_exists(0, $this->holder);
    }

    /**
     * @return mixed|null
     */
    public function shift(){
        $firstElem = $this->rewind()->current();
        return ($firstElem == false) ? null : $firstElem;
    }

    /**
     * @param callable $sortValue
     * @param int $sortDirect
     * @param null $usortClosure
     * @return $this
     */
    public function usort(callable $sortValue, $sortDirect = SORT_ASC, $usortClosure = null){
        if( is_null($usortClosure) ){
            $usortClosure = function($a, $b) use ($sortValue){
                return $sortValue($a) <=> $sortValue($b);
            };
        }
        usort($this->holder, $usortClosure);
        if( $sortDirect == SORT_DESC ){
            $this->holder = array_reverse($this->holder);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clear(){
        $this->holder = [];
        return $this;
    }

    /**
     * @param int $offset
     * @param null $length
     * @param bool $preserveKeys
     * @return $this
     */
    public function slice($offset = 0, $length = null, $preserveKeys = true ){
        $slicedList = clone $this;
        $slicedList->clear();

        $slicedHolder = array_slice($this->holder, $offset, $length, $preserveKeys);
        foreach( $slicedHolder as $key => $value ){
            $slicedList->offsetSet($key, $value);
        }
        return $slicedList;
    }

    /**
     * @param $value
     * @param null $offset
     * @return $this
     */
    public function unshift($value, $offset = null){
        if ($this->checkInstance($value)) {
            if (is_null($offset)) {
                array_unshift($this->holder, $value);
            } else {
                $this->holder = [$offset => $value] + $this->holder;
            }
        }
        return $this;
    }

    /**
     * @param $value
     * @param null $offset
     * @return $this
     */
    public function push($value, $offset = null){
        if ($this->checkInstance($value)) {
            if (is_null($offset)) {
                array_push($this->holder, $value);
            } else {
                $this->holder = $this->holder + [$offset => $value];
            }
        }
        return $this;
    }
}