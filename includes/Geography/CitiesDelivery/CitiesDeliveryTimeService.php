<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 11.09.17
 * Time: 12:34
 */

namespace is\includes\Geography\CitiesDelivery;

use Exception;
use is\includes\Date\DateTimeFactory;
use is\includes\Date\DateTimeInterface;
use is\includes\Date\Workday;
use is\includes\Geography\Cities\CityDto;
use System;

class CitiesDeliveryTimeService{

    /** @var  CitiesDeliveryTimeDto $deliveryTimeDto */
    private $deliveryTimeDto;

    /**
     * CitiesDeliveryTimeService constructor.
     * @param CitiesDeliveryTimeDto $citiesDeliveryTimeDto
     */
    public function __construct(CitiesDeliveryTimeDto $citiesDeliveryTimeDto){
        $this->setDto($citiesDeliveryTimeDto);
    }

    /**
     * @return CitiesDeliveryTimeDto
     */
    public function getDto(): CitiesDeliveryTimeDto{
        return $this->deliveryTimeDto;
    }

    /**
     * @param CitiesDeliveryTimeDto $deliveryTimeDto
     * @return CitiesDeliveryTimeService
     */
    public function setDto(CitiesDeliveryTimeDto $deliveryTimeDto): CitiesDeliveryTimeService{
        $this->deliveryTimeDto = $deliveryTimeDto;
        return $this;
    }

    /**
     * @param DateTimeInterface|null $departDateTime
     * @return Workday
     */
    public function calcEstimatedArrivalDate(DateTimeInterface $departDateTime = null){

        //Если дата не передана, то считается, что поедем райт хир райт мяу
        if( empty($departDateTime) ){
            $departDateTime = DateTimeFactory::init('now');
        }

        //Получаем дни отправки из пункта А в пункт Нахуй (0 - ПН .... 6 - ВС)
        $departCityToCityDays = $this->getDto()->getDaysDelivery();

        //Форматируем дату в рабочий день
        // (в базе календаря лежат таймстампы на даты dd-mm-yyyy 00:00:00, поэтому тут на Верочку это дело чекается)
        //Пусть никого не смутит название переменной, ибо данный объект будет наращиваться по логике метода и плавно
        // превратится из дня отправки в день прибытия
        $arrivalDay = DateTimeFactory::initWorkday($departDateTime, $departDateTime->getTimezone());

        //Определяем день отправки на основе дней отправки и праздничных дней (в такие дни никто никуда не поедет)
        while( $departCityToCityDays[$arrivalDay->format('N') - 1] == 0 || $arrivalDay->isHoliday() ){
            $arrivalDay = $arrivalDay->modify('+1 day');
        }

        //Время в пути в РАБОЧИХ днях
        // (яхз почему так, логистам норм, что поезда свежим субботним утром встают посреди уральских лесов)
        $wayTime = $this->getDto()->getWaytime();

        //Если отправка осуществляется в субботу или воскресенье, сроки бьудут на 1 рабочий день меньше,
        // т.к. предполагается, что в выходной отправка физически уйдет, а в следующий рабочий день уже будет в пути
        if( in_array($arrivalDay->format('D'), ['Sat','Sun']) ){
            $wayTime--;
        }

        //Время будет уменьшаться на единицу итеративно за каждый невыходной день
        while( $wayTime > 0 ){
            //Если это не праздник и не сб-вс, то день считается рабочим и паравозик пых-пых-пых
            if( !$arrivalDay->isHoliday() && !$arrivalDay->isWeekend() ){
                $wayTime--;
            }
            $arrivalDay = $arrivalDay->modify('+1 day');
        }

        //Результат - день Х, когда коробочку с вибродымопарогенератором встречают на платформе в пуховом платочке
        return $arrivalDay;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function save()
    {
        if (!System::get('Db')->update([
            'depart_city_id' => $this->getDto()->getDepartCityId(),
            'delivery_city_id' => $this->getDto()->getDeliveryCityId(),
            'days_delivery' => implode('', $this->getDto()->getDaysDelivery()),
            'waytime' => $this->getDto()->getWaytime()
        ], 'cities_delivery', ['id' => $this->getDto()->getId()])
        ) {
            throw new Exception("Ошибка обновления сроков доставки");
        }
        return $this;
    }
}