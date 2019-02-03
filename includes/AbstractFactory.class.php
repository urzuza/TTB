<?php

/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 13.07.17
 * Time: 19:03
 */
namespace is\includes;
use Common;
use is\includes\Date\DateTimeFactory;
use is\includes\Date\DateTimeInterface;
use is\includes\Date\DateTimeInterval;
use System;

abstract class AbstractFactory
{

    /**
     * @return AbstractList
     */
    abstract protected static function getHolder();
    abstract protected static function init(int $id);
    abstract protected static function initByParams($search=[],$limit=[],$sort=[]);


    public static function getLastSelectRowCount(){
        return System::get('Db')->getLastFoundRows();
    }

    public static function getLastSelectRowCountByGroupDto(){
        return System::get('Db')->getLastFoundRowsByGroupDto();
    }

    protected static $searchHelpers = [
        'both_like' => [],
        'right_like' => [],
        'date' => [],
    ];

    /**
     * @param int $id
     * @return mixed|null
     */
    protected static function abstractInit(int $id){
        if (!static::getHolder()->offsetExists($id)) {
            static::initByParams(['id' => $id]);
        }

        return ( static::getHolder()->offsetExists($id) ) ? static::getHolder()->offsetGet($id) : null;
    }

    /**
     * @var array
     */
    protected static $mappingRules;

    protected static function prepareSearch($search =[]){
        if (!empty($search)){
            foreach ($search as $key => $value) {
                if (is_string($key)) {
                    if (isset(static::$searchHelpers['both_like']) && in_array($key, static::$searchHelpers['both_like'])) {
                        $search[] = "`" . $key . "` LIKE '%" . $value . "%'";
                        unset($search[$key]);
                    } elseif (isset(static::$searchHelpers['right_like']) && in_array($key, static::$searchHelpers['right_like'])) {
                        $search[] = "`" . $key . "` LIKE '" . $value . "%'";
                        unset($search[$key]);
                    } elseif (isset(static::$searchHelpers['date']) && in_array($key, static::$searchHelpers['date'])) {
                        $search[] = "`" . $key . "` " . static::prepareSearchDate($value);
                        unset($search[$key]);
                    }
                }
            }
        }
        return $search;
    }

    private static function prepareSearchDate($value){
        if( $value instanceof DateTimeInterval ){
            $value = "BETWEEN '".$value->getDateStart()->format(DateTimeFactory::DATE_MYSQL)."' AND '".$value->getDateEnd()->format(DateTimeFactory::DATE_MYSQL)."'";
        }elseif( $value instanceof \DateTime || $value instanceof DateTimeInterface ){
            $value = "= '".$value->format(DateTimeFactory::DATE_MYSQL)."'";
        } elseif (is_null($value)){
            $value = ' IS NULL';
        }
        return $value;
    }


    protected static function prepareFields($fields){
        $result = [];
        foreach ($fields as $field){
            $result[] =Common::cc2us($field);
        }
        return $result;
    }


}