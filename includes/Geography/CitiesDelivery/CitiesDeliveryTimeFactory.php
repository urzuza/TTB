<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 11.09.17
 * Time: 13:08
 */

namespace is\includes\Geography\CitiesDelivery;


use \Exception;
use is\includes\AbstractFactory;
use is\includes\AbstractList;
use is\includes\Geography\Cities\CityDto;
use \System;

class CitiesDeliveryTimeFactory extends AbstractFactory{

    /** @var CitiesDeliveryTimeList $holder */
    protected static $holder;

    /**
     * @return AbstractList
     */
    protected static function getHolder(){
        if (!(static::$holder instanceof CitiesDeliveryTimeList)) {
            static::$holder = new CitiesDeliveryTimeList();
        }
        return static::$holder;
    }

    /**
     * @param int $id
     * @return CitiesDeliveryTimeDto|null
     */
    public static function init(int $id){
        return static::abstractInit($id);
    }

    /**
     * @param array $search
     * @param array $limit
     * @param array $sort
     * @return CitiesDeliveryTimeList
     */
    public static function initByParams($search = [], $limit = [], $sort = []){
        $_dtoFields = CitiesDeliveryTimeDto::getFields();
        //Выборка по непустым параметрам (пустые отсекаются через array_filter)
        $select = System::get('Db')->select(
            static::prepareFields($_dtoFields),
            'cities_delivery',
            $search,
            $limit,
            array_intersect_key($sort, array_flip($_dtoFields))
        );

        $citiesDeliveryTimeList = new CitiesDeliveryTimeList();
        if( !empty($select) ){
            foreach( $select as $_row ) {
                if( !static::getHolder()->offsetExists($_row['id']) ){
                    $CitiesDeliveryTimeDto = static::initDto()
                        ->setId(intval($_row['id']))
                        ->setDeliveryCityId($_row['delivery_city_id'])
                        ->setDepartCityId($_row['depart_city_id'])
                        ->setWaytime(intval($_row['waytime']))
                        ->setDaysDelivery($_row['days_delivery']);
                        static::getHolder()->offsetSet($CitiesDeliveryTimeDto->getId(), $CitiesDeliveryTimeDto);
                }
                $citiesDeliveryTimeList->offsetSet($_row['id'], static::getHolder()->offsetGet($_row['id']));
            }
        }
        return $citiesDeliveryTimeList;
    }

    /**
     * @param array $params
     * @return CitiesDeliveryTimeDto
     */
    public static function initDto($params = []){
        $citiesDeliveryTimeDto = new CitiesDeliveryTimeDto();
        return $citiesDeliveryTimeDto
            ->setId($params['id'] ?? null)
            ->setDepartCityId($params['departCityId'] ?? null)
            ->setDeliveryCityId($params['deliveryCityId'] ?? null)
            ->setWaytime($params['waytime'] ?? null)
            ->setDaysDelivery($params['daysDelivery'] ?? null);
    }

    /**
     * @param CitiesDeliveryTimeDto $citiesDeliveryTimeDto
     * @return CitiesDeliveryTimeService
     */
    public static function getService(CitiesDeliveryTimeDto $citiesDeliveryTimeDto){
        return new CitiesDeliveryTimeService($citiesDeliveryTimeDto);
    }

    /**
     * Инициализая ДТО с данными о сроках доставки между городами
     *  В отдельном методе, т.к. есть чекер и используется в нескольких местах
     * @param CityDto $departCity
     * @param CityDto $deliveryCity
     * @return CitiesDeliveryTimeDto
     */
    public static function initCityToCityDto(CityDto $departCity, CityDto $deliveryCity){
        $result = null;
        //Получение времени в пути и дней отправки в указанный город из локации ИМ
        $deliveryTimeList = self::initByParams([
            'depart_city_id' => $departCity->getId(),
            'delivery_city_id' => $deliveryCity->getId()
        ]);
        //Проверка на наличие таких данных
        if( $deliveryTimeList->count() > 0 ){
            $result = $deliveryTimeList->shift();
        }

        return $result;
    }

    /**
     * @param CitiesDeliveryTimeDto $dto
     * @return CitiesDeliveryTimeDto
     * @throws Exception
     */
    public static function add(CitiesDeliveryTimeDto $dto){
        try{
            $id = System::get('Db')->insert([
                'depart_city_id' => $dto->getDepartCityId(),
                'delivery_city_id' => $dto->getDeliveryCityId(),
                'days_delivery' => implode('', CitiesDeliveryTimeDto::formalizeDaysDeliveryToArray($dto->getDaysDelivery())),
                'waytime' => $dto->getWaytime()
            ],'cities_delivery');

            if ( !$id ) {
                throw new Exception("Ошибка добавления записи");
            }
            $dto->setId($id);
            static::getHolder()->offsetSet($dto->getId(),$dto);
        }catch (Exception $exception){
            throw new Exception(" Ошибка добавления сроков доставки: " .PHP_EOL. $exception->getMessage());
        }
        return static::getHolder()->offsetGet($dto->getId());
    }
}