<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 26.10.17
 * Time: 12:02
 */

namespace is\includes\Date;


use is\includes\AbstractList;

class DateTimeIntervalList extends AbstractList
{
    protected static $instanceName = 'is\includes\Date\DateTimeInterval';

    public function __toArray($ext = []){
        return $this->map(function(DateTimeInterval $interval) use ($ext){
            return $interval->format($ext['format'] ?? 'H:i', $ext['delimiter'] ?? '/');
        });
    }

    public function toString($ext = []){
        return implode($ext['betweenDelimiter'] ?? ',', $this->__toArray($ext));
    }
}