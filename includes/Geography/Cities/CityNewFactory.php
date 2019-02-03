<?php

namespace is\includes\Geography\Cities;
use Exception;
use is\includes\AbstractFactory;
use is\includes\Geography\Regions\RegionNewFactory;
use is\includes\PartnerPickupAddress\PartnerPickupAddressFactory;
use System;

/**
 * Created by IntelliJ IDEA.
 * User: Solver
 * Date: 03.10.17
 * Time: 13:18
 */
class CityNewFactory extends AbstractFactory
{

    protected static $holder;
    protected static $servicesHolder = [];
    /**
     * @return CityList
     */
    protected static function getHolder()
    {
        if (!(static::$holder instanceof CityList)) {
            static::$holder = new CityList();
        }
        return static::$holder;
    }

    /**
     * @param int $id
     * @return CityDto|null
     */
    public static function init(int $id){
        return static::abstractInit($id);
    }

    /**
     * @param CityDto|int $cityDto
     * @return CityService|null
     */
    public static function getService($cityDto)
    {
        if ( !($cityDto instanceof CityDto) ) {
            $cityDto = static::init($cityDto);
        }

        if( !is_null($cityDto) ) {
            if (!isset(static::$servicesHolder[$cityDto->getId()])) {
                static::$servicesHolder[$cityDto->getId()] = new CityService($cityDto);
            }
            return static::$servicesHolder[$cityDto->getId()];
        } else {
            return null;
        }
    }


    /**
     * @param array $search
     * @param array $limit
     * @param array $sort
     * @return CityList|CityDto[]
     */
    public static function initByParams($search = [], $limit = [], $sort = [])
    {
        $_dtoFields = CityDto::getFields();
        //Выборка по непустым параметрам (пустые отсекаются через array_filter)
        $select = System::get('Db')->select(
            static::prepareFields($_dtoFields),
            'cities',
            $search,
            $limit,
            array_intersect_key($sort, array_flip($_dtoFields))
        );

        $cityList = new CityList();
        if( !empty($select) ){
            //Генерация ДТО для возврата
            foreach( $select as $_row ){
                if (!static::getHolder()->offsetExists($_row['id'])) {
                    $cityDto = new CityDto();
                    $cityDto->setId($_row['id']);
                    $cityDto->setTitle($_row['title']);
                    $cityDto->setRegionId($_row['region_id']);
                    static::getHolder()->offsetSet($cityDto->getId(), $cityDto);
                }
                $cityList[$_row['id']] = static::getHolder()->offsetGet($_row['id']);
            }
        }
        return $cityList;
    }





    /**
     * @param CityDto $cityDto
     * @return CityDto
     * @throws Exception
     */
    public static function add(CityDto $cityDto){
        try{
            $cityId = System::get('Db')->insert([
                'title' => $cityDto->getTitle(),
                'region_id' => $cityDto->getRegionId()
            ],'cities');

            if ( !$cityId ) {
                throw new Exception("Ошибка добавления записи.");
            }
            $cityDto->setId($cityId);
            static::getHolder()->offsetSet($cityDto->getId(),$cityDto);
        }catch (Exception $exception){
            throw new Exception(" Ошибка добавления города: " .PHP_EOL. $exception->getMessage());
        }
        return static::getHolder()->offsetGet($cityDto->getId());
    }

    /**
     * Метод получения ИД города и региона по различному типу параметров
     *      Активно используется в апи, поэтому решил оставить его, несколько переработав
     *      Возвращает объект города (а в нем уже есть объект региона, что позволит делать все необходимые операции и проверки)
     * @param string $type
     * @param integer | string $city
     * @param integer | string $region
     * @param integer $zipcode
     * @param integer $pickupAddressId
     * @return CityDto
     * @throws Exception
     */
    public static function initCityByType($type, $city, $region, $zipcode = null, $pickupAddressId = null){

        $cityDto = null;
        $errors = [];

        switch ($type) {
            // Определение города и региона по индексу
            case 'zip':
                $zipcodes = System::get('Db')->select(['city_id'], 'zipcodes', ['zipcode'=>$zipcode], ['count'=>1]);
                if( !empty($zipcodes) ){
                    $cityDto = self::init($zipcodes[0]['city_id']);
                } else {
                    $errors[] = 'Индекс='.$zipcode;
                }
                break;
            // Определение города и региона по строкам
            case 'string':
                $unvalidRegNames = [
                    'Санкт-Петербург',
                    'московская область',
                    'Московская область',
                ];
                $validRegNames = [
                    'Ленинградская область',
                    'Москва',
                    'Москва',
                ];
                if( in_array($region, $unvalidRegNames) ) {
                    $region = str_replace($unvalidRegNames, $validRegNames, $region);
                }

                $regionObj = RegionNewFactory::initByParams(['title'=>trim($region)],['count' =>1])->shift();
                if( !is_null($regionObj) ) {
                    $citiesList = self::initByParams(['title' => trim($city), 'region_id' => $regionObj->getId()],['count' =>1]);
                    if ($citiesList->count() > 0) {
                        $cityDto = $citiesList->shift();
                    }
                }
                if( empty($cityDto) ){
                    $errors[] = 'Регион='.$region;
                    $errors[] = 'Город='.$city;
                }
                break;
            // Определение города и региона по внутренним ИД ТД
            case 'id':
                $cityDto = self::init($city);
                if( empty($cityDto) ){
                    $errors[] = 'Город='.$city;
                }
                break;
            case 'pickup':
                $pickupDto = PartnerPickupAddressFactory::init($pickupAddressId);
                if( !is_null($pickupDto) ){
                    $cityDto = $pickupDto->getCity();
                } else {
                    $errors[] = 'ПВЗ='.$pickupAddressId;
                }
                break;

            default:
                throw new Exception("Не задан тип передаваемого адреса");
        }

        if( !($cityDto instanceof CityDto) ){
            $errorMsg = 'Ошибка определения города доставки по параметрам';
            if( !empty($errors) ) {
                $errorMsg .= ': [' . implode(', ', $errors) . ']';
            }
            throw new Exception($errorMsg);
        }

        return $cityDto;
    }

    /**
     * @param CityDto|int $cityDto
     * @return string
     */
    public static function getStringLocation($cityDto){
        if( !($cityDto instanceof CityDto) ){
            $cityDto = self::init($cityDto);
        }

        $location = [];
        if( !is_null($cityDto) ){
            $regionDto = $cityDto->getRegion();
            if( !is_null($regionDto) ){
                $location[] = $regionDto->getTitle();
            }
            $location[] = $cityDto->getTitle();
        }

        return ( !empty($location) ) ? implode(', ', $location) : '';
    }

}