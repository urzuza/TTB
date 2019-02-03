<?php

namespace is\includes\Geography\Regions;
use Exception;
use is\includes\AbstractFactory;
use staticInfo;
use System;

/**
 * Created by IntelliJ IDEA.
 * User: bimdeer
 * Date: 03.10.17
 * Time: 13:18
 */
class RegionNewFactory extends AbstractFactory
{

    protected static $holder;
    /**
     * @return RegionList
     */
    protected static function getHolder()
    {
        if (!(static::$holder instanceof RegionList)) {
            static::$holder = new RegionList();
        }
        return static::$holder;
    }

    /**
     * @param int $id
     * @return RegionDto|null
     */
    public static function init(int $id){
        return static::abstractInit($id);
    }

    /**
     * @param RegionDto $regionDto
     * @return RegionService
     */
    public static function getService(RegionDto $regionDto): RegionService
    {
        return new RegionService($regionDto);
    }


    /**
     * @param array $search
     * @param array $limit
     * @param array $sort
     * @return RegionList|RegionDto[]
     */
    public static function initByParams($search = [], $limit = [], $sort = [])
    {
        $_dtoFields = RegionDto::getFields();
        //Выборка по непустым параметрам (пустые отсекаются через array_filter)
        $select = System::get('Db')->select(
            static::prepareFields($_dtoFields),
            'regions',
            $search,
            $limit,
            array_intersect_key($sort, array_flip($_dtoFields))
        );

        $regionList = new RegionList();
        if( !empty($select) ){
            //Генерация ДТО для возврата
            foreach( $select as $_row ){
                if (!static::getHolder()->offsetExists($_row['id'])) {
                    $regionDto = new RegionDto();
                    $regionDto->setId($_row['id']);
                    $regionDto->setTitle($_row['title']);
                    $regionDto->setTimezone($_row['timezone']);
                    static::getHolder()->offsetSet($regionDto->getId(), $regionDto);
                }
                if ($_row['id'] == 51 || $_row['id'] == 53){
                    $regionList->unshift(static::getHolder()->offsetGet($_row['id']),$_row['id']);
                }else{
                    $regionList->push(static::getHolder()->offsetGet($_row['id']),$_row['id']);
                }
            }
        }
        return $regionList;
    }


    /**
     * @param RegionDto $regionDto
     * @return RegionDto
     * @throws Exception
     */
    public static function add(RegionDto $regionDto){
        {
            try{
                $regionId = System::get('Db')->insert(
                    [
                        'title' => $regionDto->getTitle(),
                        'timezone' => $regionDto->getTimezone(),
                    ],'regions'
                );
                if (! ($regionId) ) {
                    throw new Exception("Ошибка добавления записи.");
                }
                $regionDto->setId($regionId);
                staticInfo::getRegionsCitiesList(false);
                static::getHolder()->offsetSet($regionDto->getId(),$regionDto);
            }catch (Exception $exception){
                throw new Exception(" Ошибка добавления юридического лица: " .PHP_EOL. $exception->getMessage());
            }
            return static::getHolder()->offsetGet($regionDto->getId());
        }
    }
}