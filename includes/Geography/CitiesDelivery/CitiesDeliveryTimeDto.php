<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 11.09.17
 * Time: 12:33
 */

namespace is\includes\Geography\CitiesDelivery;

use is\includes\AbstractDTO;
use is\includes\Geography\Cities\CityDto;
use is\includes\Geography\Cities\CityNewFactory;

class CitiesDeliveryTimeDto extends AbstractDTO
{
    /** @var  int $id */
    private $id;
    /** @var  int $deliveryCityId */
    private $deliveryCityId;
    /** @var  int $departCityId */
    private $departCityId;
    /** @var  int $waytime */
    private $waytime;
    /** @var array $daysDelivery */
    private $daysDelivery;


    public function __toArray($ext = []): array{
        return [
            'id' => $this->getId(),
            'deliveryCityId' => $this->getDeliveryCityId(),
            'departCityId' => $this->getDepartCityId(),
            'waytime' => $this->getWaytime(),
            'daysDelivery' => $this->getDaysDelivery()
        ];
    }

    public static function getProperties(): array{
        return get_class_vars(get_called_class());
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return CitiesDeliveryTimeDto
     */
    public function setId(int $id = null){
        $this->id = $id;
        return $this;
    }

    /**
     * @return CityDto|null
     */
    public function getDeliveryCity(){
        return CityNewFactory::init($this->getDeliveryCityId());
    }

    /**
     * @param int|null $deliveryCityId
     * @return CitiesDeliveryTimeDto
     */
    public function setDeliveryCityId(int $deliveryCityId = null){
        $this->deliveryCityId = $deliveryCityId;
        return $this;
    }

    /**
     * @return CityDto|null
     */
    public function getDepartCity(){
        return CityNewFactory::init($this->getDepartCityId());
    }

    /**
     * @param int|null $departCityId
     * @return CitiesDeliveryTimeDto
     */
    public function setDepartCityId(int $departCityId = null){
        $this->departCityId = $departCityId;
        return $this;
    }

    /**
     * @return int
     */
    public function getWaytime(){
        return $this->waytime;
    }

    /**
     * @param int $waytime
     * @return CitiesDeliveryTimeDto
     */
    public function setWaytime(int $waytime = null){
        $this->waytime = $waytime;
        return $this;
    }

    /**
     * @return array
     */
    public function getDaysDelivery(){
        return $this->daysDelivery;
    }

    /**
     * @param mixed $daysDelivery
     * @return CitiesDeliveryTimeDto
     */
    public function setDaysDelivery($daysDelivery = null){
        $this->daysDelivery = CitiesDeliveryTimeDto::formalizeDaysDeliveryToArray($daysDelivery);
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryCityId(){
        return $this->deliveryCityId;
    }

    /**
     * @return int
     */
    public function getDepartCityId(){
        return $this->departCityId;
    }

    /**
     * @param mixed $daysDelivery
     * @return array
     */
    public static function formalizeDaysDeliveryToArray($daysDelivery = null){
        if( is_null($daysDelivery) ){
            $daysDelivery = [];
        } elseif( is_string($daysDelivery) ){
            $daysDelivery = str_split($daysDelivery);
        } elseif( is_object($daysDelivery) ) {
            $daysDelivery = (array) $daysDelivery;
        } elseif( is_array($daysDelivery) ){
            $daysDelivery = array_values($daysDelivery);
        } elseif( !is_array($daysDelivery) ){
            $daysDelivery = [$daysDelivery];
        }

        if( count($daysDelivery) > 7 ){
            $daysDelivery = array_slice($daysDelivery, 0 ,7);
        } else {
            $daysDelivery = array_pad($daysDelivery, 7, '0');
        }

        return array_map(function($day){
            return intval(boolval($day));
        }, $daysDelivery);
    }

}