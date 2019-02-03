<?php

/**
 * Created by IntelliJ IDEA.
 * User: bimdeer
 * Date: 03.10.17
 * Time: 13:19
 */

namespace is\includes\Geography\Regions;

use Exception;
use is\includes\Date\DateTimeFactory;
use is\includes\Date\DateTimeInterface;
use is\includes\Geography\Cities\CityDto;
use is\includes\Geography\Cities\CityList;
use is\includes\Geography\Cities\CityNewFactory;
use OrderFactory;
use ShipmentFactory;
use staticInfo;
use System;

class RegionService
{
    /**
     * @return RegionDto
     * @throws Exception
     */
    public function update() {
        $_updateStatus = System::get('Db')->update([
            'title' => $this->getRegionDto()->getTitle(),
            'timezone' => $this->getRegionDto()->getTimezone(),
        ],
            'regions',
            [
                'id' => $this->getRegionDto()->getId()
            ]
        );

        if (!$_updateStatus ){
            throw new Exception('Ошибка обновления региона');
        }
        staticInfo::getRegionsCitiesList(false);
        return $this->getRegionDto();
    }

    /**
     * @throws Exception
     */
    public function delete() {
        $this->checkDeletable();
        if( !System::get('Db')->delete('regions', ['id' => $this->getRegionDto()->getId()]) ){
            throw new Exception("Ошибка удаления записи");
        }
    }



    /**
     * Проверка на возможность удаления региона
     * @throws Exception
     */
    protected function checkDeletable(){
        //Таблицы, в которых проверяем наличие региона
        $tablesRegionId = [
            'partner_cities',
            'employees',
            'partner_storages',
            'News',
            'order_log',
            'partner_pickup_addresses',
        ];

        foreach($tablesRegionId as $_tbl) {
            $chk = System::get('Db')->select(['id'],$_tbl,['region_id' => $this->getRegionDto()->getId()], ['count' => 1]);
            if ( isset($chk[0]['id']) ){
                throw new Exception("Невозможно удалить регион из-за записей в таблице " .$_tbl);
            }
        }

        //Проверка Им и партнеров на предмет базирования в удаляемом регионе
        $tablesRegionLocation = ['webshops','partners'];
        foreach($tablesRegionLocation as $_tbl) {
            $chk = System::get('Db')->select(['id'],$_tbl,['location_region' => $this->getRegionDto()->getId()], ['count' => 1]);
            if ( isset($chk[0]['id']) ){
                throw new Exception("Невозможно удалить регион из-за записей в таблице " .$_tbl);
            }
        }

        //Проверка отправок
        $chkShipment = ShipmentFactory::initShipments(["`depart_region_id`=".$this->getRegionDto()->getId()." OR `delivery_region_id`=". $this->getRegionDto()->getId() ],['count' =>1 ]);
        if ( count($chkShipment)>0 ){
            throw new Exception("Невозможно удалить регион из-за записей в таблице shipments");
        }

        //Проверка заказов
        $chkOrder = OrderFactory::initOrders(["`region_webshop_id`=".$this->getRegionDto()->getId()." OR `region_current_id`=". $this->getRegionDto()->getId()." OR `region_delivery`=".$this->getRegionDto()->getId()],['count' =>1 ]);
        if ( count($chkOrder)>0 ) {
            throw new Exception("Невозможно удалить регион из-за записей в таблице orders");
        }
    }



    /**
     * @var RegionDto $regionDto
     */
    private $regionDto;

    public function __construct(RegionDto $regionDto)
    {
        $this->regionDto = $regionDto;
    }

    /**
     * @return RegionDto
     */
    public function getRegionDto(): RegionDto
    {
        return $this->regionDto;
    }

    /**
     * @param RegionDto $regionDto
     */
    public function setRegionDto(RegionDto $regionDto)
    {
        $this->regionDto = $regionDto;
    }



    /**
     * Возвращает список городов региона
     * @param int $locked -1: Все, 0: незалоченные, 1: залоченные
     * @return CityDto[]|CityList
     */
    public function getCities($locked=-1){
        $search = [
            'region_id' => $this->getRegionDto()->getId()
        ];
        if ($locked != -1)
            $search['locked'] = $locked;
        return CityNewFactory::initByParams($search, ['id' => "ASC"]);
    }

    /**
     * Получение таймстампа времени региона относительно времени сервера
     * @param int $serverTime
     * @return int
     */
    public function getRegionTimestamp($serverTime=null){
        $serverTimestamp = ( empty($serverTime) ) ? time() : strtotime($serverTime);
        return $this->getRegionDto()->getTimezone()*3600 - date("Z") + $serverTimestamp;
    }

    /**
     * @param DateTimeInterface|null $moscowTime
     * @return DateTimeInterface
     */
    public function getRegionTime(DateTimeInterface $moscowTime = null){
        if( is_null($moscowTime) ){
            $moscowTime = DateTimeFactory::init('now');
        }
        $regionTime = clone $moscowTime;
        return $regionTime->setTimezone(
            DateTimeFactory::initTimezone(gmdate('+hi', $this->getRegionDto()->getTimezone()*3600))
        );
    }



}