<?php

/**
 * Created by IntelliJ IDEA.
 * User: Solver
 * Date: 03.10.17
 * Time: 13:19
 */

namespace is\includes\Geography\Cities;

use Exception;
use is\includes\Geography\Regions\RegionDto;
use is\includes\Geography\Regions\RegionNewFactory;
use is\includes\Order\Log\OrderEventFactory;
use is\includes\Order\OrderNewFactory;
use is\includes\Partner\Geography\PartnerCityAggregate\PartnerCityAggregateDto;
use is\includes\Partner\Geography\PartnerCityAggregate\PartnerCityAggregateFactory;
use is\includes\Partner\PartnerNewFactory;
use is\includes\Partner\Storages\StorageFactory;
use is\includes\PartnerPickupAddress\PartnerPickupAddressFactory;
use is\includes\Webshop\WebshopFactory;
use ShipmentFactory;
use staticInfo;
use System;

class CityService
{

    /**
     * @var CityDto $cityDto
     */
    private $cityDto;
    /**
     * @var $available bool
     */
    private $available;

    private $zipCodes = [];

    /**
     * @return array
     */
    public function getZipCodes(): array
    {
        if (empty($this->zipCodes)){
            $this->initZipCodes();
        }
        return $this->zipCodes;
    }


    /**
     * @param array $zipCodes
     */
    public function setZipCodes(array $zipCodes){
        $this->zipCodes = $zipCodes;
    }

    protected function initZipCodes(){
        $this->zipCodes = [];
        $zipcodes_sql = System::get('Db')->select(['zipcode'],'zipcodes', ['city_id' => $this->getCityDto()->getId()]);
        if( isset( $zipcodes_sql[0]['zipcode'] ) ){
            $this->setZipCodes(array_column($zipcodes_sql,'zipcode'));
        }
    }

    /**
     * @param array $zipCodes
     * @return $this
     * @throws Exception
     */
    public function editZipCodes(array $zipCodes = []){
        $deleteZipCodes = array_merge($this->getZipCodes(), $zipCodes);
        try{
            System::get('Db')->startTransaction();

            if( !empty($deleteZipCodes) ){
                if( !System::get('Db')->delete('zipcodes', ['zipcode'=>$deleteZipCodes]) ){
                    throw new Exception("Ошибка удаления индексов");
                }
            }

            if( !empty($zipCodes) ){
                $cityId = $this->getCityDto()->getId();
                $insertZipCodes = array_map(function($_zipcode) use ($cityId){
                    return [
                        'city_id' => $cityId,
                        'zipcode' => $_zipcode
                    ];
                }, $zipCodes);

                if( !System::get('Db')->insertMulti($insertZipCodes, 'zipcodes') ){
                    throw new Exception("Ошибка записи индексов");
                }
            }

            System::get('Db')->commit();
        } catch ( Exception $e ){
            System::get('Db')->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (is_null($this->available)){
            $this->available = false;
            $partnerCityAggregateList = PartnerCityAggregateFactory::initByParams(['city_id' => $this->getCityDto()->getId()]);
            if ($partnerCityAggregateList->count() > 0){
                /** @var PartnerCityAggregateDto $partnerCityAggregateDto */
                $partnerCityAggregateDto = $partnerCityAggregateList->shift();
                $this->available  = $partnerCityAggregateDto->isAvailable();
            }
        }
        return $this->available;
    }

    /**
     * @param bool $available
     */
    public function setAvailable(bool $available)
    {
        $this->available = $available;
    }

    public function __construct(CityDto $cityDto)
    {
        $this->cityDto = $cityDto;
    }

    /**
     * @return CityDto
     */
    public function getCityDto(): CityDto
    {
        return $this->cityDto;
    }

    /**
     * @param CityDto $cityDto
     */
    public function setCityDto(CityDto $cityDto)
    {
        $this->cityDto = $cityDto;
    }

    /**
     * Получение объекта родительского региона. Если он не был установлен - инициализируется из region_id
     * @return RegionDto
     */
    public function getParentRegion() {
        return RegionNewFactory::init($this->getCityDto()->getRegionId());
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function update(){
        if( !System::get('Db')->update([
            'region_id' => $this->getCityDto()->getRegionId(),
            'title' => $this->getCityDto()->getTitle()
        ], 'cities', ['id'=>$this->getCityDto()->getId()]) ){
            throw new Exception('Ошибка обновления города '.$this->getCityDto()->getId());
        }
        staticInfo::getRegionsCitiesList(false);
        return $this;
    }

    /**
     * Удаление города
     * @throws Exception
     */
    public function delete(){
        $this->checkDeletable();

        try{
            System::get('Db')->startTransaction();

            // Удаление записей из таблцы сроков доставки //
            if( !System::get('Db')->delete('cities_delivery', [
                "`depart_city_id`='".$this->getCityDto()->getId()."' OR `delivery_city_id`='".$this->getCityDto()->getId()."'"
            ]) ){
                throw new Exception('Произошла ошибка при удалении сроков доставки до города произошла');
            }

            if( !System::get('Db')->delete('partner_cities', ['city_id' => $this->getCityDto()->getId()]) ){
                throw new Exception("При удалении привязки партнера к городу произошла ошибка");
            }

            if( !System::get('Db')->delete('zipcodes', ['city_id'=>$this->getCityDto()->getId()]) ){
                throw new Exception("При удалении привязки индексов к городу произошла ошибка");
            }

            if( !System::get('Db')->delete('tariff_zones_cities', ['city_id'=>$this->getCityDto()->getId()]) ){
                throw new Exception("При удалении привязки города к тарифным зонам произошла ошибка");
            }

            if( !System::get('Db')->delete('cities',['id'=>$this->getCityDto()->getId()]) ){
                throw new Exception("При удалении привязки города произошла ошибка");
            }

            System::get('Db')->commit();
        } catch(Exception $e){
            System::get('Db')->rollback();
            throw $e;
        }
    }

    /**
     * Проверка на возможность удаления города
     * @throws Exception
     */
    protected function checkDeletable(){
        if (PartnerPickupAddressFactory::initByParams(['city_id'=>$this->getCityDto()->getId()], ['count'=>1])->count() > 0){
            throw new Exception("Невозможно удалить город из-за наличия пвз в данном городе");
        }

        if( OrderNewFactory::initByParams(['city_delivery_id' => $this->getCityDto()->getId()], ['count'=>1])->count() > 0){
            throw new Exception("Невозможно удалить город из-за наличия заказов в данный город");
        }

        if(count(ShipmentFactory::initShipments(['depart_city_id='. $this->getCityDto()->getId() . ' OR  delivery_city_id=' .$this->getCityDto()->getId()], ['count'=>1])) > 0){
            throw new Exception("Невозможно удалить город из-за наличия заказов в данный город");
        }

        if(WebshopFactory::initByParams(['location_city' => $this->getCityDto()->getId()], ['count'=>1])->count() > 0){
            throw new Exception("Невозможно удалить город из-за наличия магазинов в данном городе");
        }

        if(PartnerNewFactory::initByParams(['location_city' => $this->getCityDto()->getId()], ['count'=>1])->count() > 0){
            throw new Exception("Невозможно удалить город из-за наличия партнеров в данном городе");
        }

        if(StorageFactory::initByParams(['city_id'=>$this->getCityDto()->getId()], ['count'=>1])->count() > 0){
            throw new Exception("Невозможно удалить город из-за наличия складов в данном городе");
        }

        if(OrderEventFactory::initByParams(['city_id'=>$this->getCityDto()->getId()], ['count'=>1])->count() > 0){
            throw new Exception("Невозможно удалить город из-за наличия записей в логе заказов");
        }
    }
}
