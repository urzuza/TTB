<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 11.09.17
 * Time: 17:51
 */

namespace is\includes\Date;

class Workday extends DateTime {

    /**
     * @param array $search
     * @return array
     */
    private function productionCalendarQuery($search = []){
        $queryStr = "SELECT 
                        calendar_holidays.timestamp,
                        calendar_holidays.date, 
                        calendar_holidays_types.typename AS type 
                    FROM calendar_holidays 
                    LEFT JOIN calendar_holidays_types ON calendar_holidays_types.id = calendar_holidays.type";

        if( !empty($search) ){
            $queryStr .= " WHERE ".implode(' AND ', $search);
        }
        return DateTimeFactory::getProductionCalendarDB()->query($queryStr);
    }

    /**
     * @return bool
     */
    public function isWorkDay(){
        return ( !$this->isHoliday() && !$this->isWeekend() || $this->isTransferWorkDay() );
    }

    /**
     * @return bool
     */
    public function isWeekend(){
        $dates = $this->productionCalendarQuery([
            "calendar_holidays_types.typename='WEEKEND'",
            "(calendar_holidays.timestamp='".$this->getTimestamp()."' OR calendar_holidays.date='".$this->format('Y-m-d')."')"
        ]);
        return ( count($dates) > 0 );
    }

    /**
     * @return bool
     */
    public function isHoliday(){
        $dates = $this->productionCalendarQuery([
            "calendar_holidays_types.typename='HOLIDAY'",
            "(calendar_holidays.timestamp='".$this->getTimestamp()."' OR calendar_holidays.date='".$this->format('Y-m-d')."')"
        ]);
        return ( count($dates) > 0 );
    }

    /**
     * @return bool
     */
    public function isTransferWorkDay(){
        $dates = $this->productionCalendarQuery([
            "calendar_holidays_types.typename='WORKDAY'",
            "(calendar_holidays.timestamp='".$this->getTimestamp()."' OR calendar_holidays.date='".$this->format('Y-m-d')."')"
        ]);
        return ( count($dates) > 0 );
    }

    /**
     * Выставляет объекту дату дату ближайшего рабочего дня
     * @return Workday
     */
    public function nextWorkDay(){
        $this->modify('+1 day');
        while( !$this->isWorkDay() ){
            $this->modify('+1 day');
        }
        return $this;
    }
}