<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace is\includes\Date;

use Exception;

class WeekSchedule{

    protected static $translitWeekDays = [
        'Mon' => 'Пн',
        'Tue' => 'Вт',
        'Wed' => 'Ср',
        'Thu' => 'Чт',
        'Fri' => 'Пт',
        'Sat' => 'Сб',
        'Sun' => 'Вс',
    ];

    /** @var DateTimeIntervalList $Mon */
    protected $Mon;
    /** @var DateTimeIntervalList $Tue */
    protected $Tue;
    /** @var DateTimeIntervalList $Wed */
    protected $Wed;
    /** @var DateTimeIntervalList $Thu */
    protected $Thu;
    /** @var DateTimeIntervalList $Fri */
    protected $Fri;
    /** @var DateTimeIntervalList $Sat */
    protected $Sat;
    /** @var DateTimeIntervalList $Sun */
    protected $Sun;

    public function __construct(){
        $this
            ->setMon(new DateTimeIntervalList())
            ->setTue(new DateTimeIntervalList())
            ->setWed(new DateTimeIntervalList())
            ->setThu(new DateTimeIntervalList())
            ->setFri(new DateTimeIntervalList())
            ->setSat(new DateTimeIntervalList())
            ->setSun(new DateTimeIntervalList());
    }

    /**
     * @param array|null $parsedSchedule
     */
    public function setScheduleFromArray($parsedSchedule){
        if( $parsedSchedule && is_array($parsedSchedule) ){
            foreach( $parsedSchedule as $day => $intervals ){
                if( !empty($intervals) && is_array($intervals) ){
                    foreach( $intervals as $interval ){
                        try {
                            $dateInterval = DateTimeFactory::initByParse(str_replace('-', '/', $interval));
                            if ( !is_null($dateInterval) && $dateInterval instanceof DateTimeInterval ) {
                                $dateInterval->getDateStart()->setDate(0, 0, 0);
                                $dateInterval->getDateEnd()->setDate(0, 0, 0);
                                $this->getDay($day)->attach($dateInterval);
                            }
                        } catch( Exception $e ){
                            continue;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $scheduleJson
     */
    public function setScheduleFromJson($scheduleJson){
        $parsedSchedule = json_decode($scheduleJson, true);

        $this->setScheduleFromArray($parsedSchedule);
    }

    /**
     * @param string $day
     * @return DateTimeIntervalList
     * @throws Exception
     */
    public function getDay($day){
        if( property_exists($this, $day) && in_array($day, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ){
            return $this->$day;
        } else {
            throw new Exception("День ".$day." не существует");
        }
    }

    /**
     * @param string $timeFormat
     * @param string $delimiter
     * @return array
     */
    public function __toArray($timeFormat = 'H:i', $delimiter = '-'){
        return [
            'Mon' => $this->getMon()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter]),
            'Tue' => $this->getTue()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter]),
            'Wed' => $this->getWed()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter]),
            'Thu' => $this->getThu()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter]),
            'Fri' => $this->getFri()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter]),
            'Sat' => $this->getSat()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter]),
            'Sun' => $this->getSun()->__toArray(['format'=>$timeFormat, 'delimiter'=>$delimiter])
        ];
    }

    /**
     * @return string
     */
    public function __toString(){
        return json_encode($this->__toArray());
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getMon(): DateTimeIntervalList
    {
        return $this->Mon;
    }

    /**
     * @param DateTimeIntervalList $Mon
     * @return WeekSchedule
     */
    public function setMon(DateTimeIntervalList $Mon): WeekSchedule
    {
        $this->Mon = $Mon;
        return $this;
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getTue(): DateTimeIntervalList
    {
        return $this->Tue;
    }

    /**
     * @param DateTimeIntervalList $Tue
     * @return WeekSchedule
     */
    public function setTue(DateTimeIntervalList $Tue): WeekSchedule
    {
        $this->Tue = $Tue;
        return $this;
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getWed(): DateTimeIntervalList
    {
        return $this->Wed;
    }

    /**
     * @param DateTimeIntervalList $Wed
     * @return WeekSchedule
     */
    public function setWed(DateTimeIntervalList $Wed): WeekSchedule
    {
        $this->Wed = $Wed;
        return $this;
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getThu(): DateTimeIntervalList
    {
        return $this->Thu;
    }

    /**
     * @param DateTimeIntervalList $Thu
     * @return WeekSchedule
     */
    public function setThu(DateTimeIntervalList $Thu): WeekSchedule
    {
        $this->Thu = $Thu;
        return $this;
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getFri(): DateTimeIntervalList
    {
        return $this->Fri;
    }

    /**
     * @param DateTimeIntervalList $Fri
     * @return WeekSchedule
     */
    public function setFri(DateTimeIntervalList $Fri): WeekSchedule
    {
        $this->Fri = $Fri;
        return $this;
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getSat(): DateTimeIntervalList
    {
        return $this->Sat;
    }

    /**
     * @param DateTimeIntervalList $Sat
     * @return WeekSchedule
     */
    public function setSat(DateTimeIntervalList $Sat): WeekSchedule
    {
        $this->Sat = $Sat;
        return $this;
    }

    /**
     * @return DateTimeIntervalList
     */
    public function getSun(): DateTimeIntervalList
    {
        return $this->Sun;
    }

    /**
     * @param DateTimeIntervalList $Sun
     * @return WeekSchedule
     */
    public function setSun(DateTimeIntervalList $Sun): WeekSchedule
    {
        $this->Sun = $Sun;
        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toStringFormattedLegacy(){
        $daysByTime = [];
        $daysArray = array_keys(self::$translitWeekDays);
        foreach( $daysArray as $day ){
            $stringInterval = $this->getDay($day)->toString();
            if( isset($daysByTime[$stringInterval]) ){
                $daysByTime[$stringInterval][] = self::$translitWeekDays[$day];
            } else {
                $daysByTime[$stringInterval] = [self::$translitWeekDays[$day]];
            }
        }

        $workSchedule = [];
        foreach( $daysByTime as $time=>$days ){
            if( !empty($time) ) {
                $workSchedule[] = implode(',', $days) . ": " . str_replace('/','-', $time);
            }
        }

        return implode(' ', $workSchedule);
    }
}