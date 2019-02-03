<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 05.12.17
 * Time: 15:41
 */

namespace is\includes\Date;

use DateTimeZone;
use Exception;
use sql3db;
use \DateTimeInterface as DateTimeInterfaceNative;

class DateTimeFactory {

    const DATE_MYSQL = 'Y-m-d H:i:s';
    const USER_VIEW = 'Y-m-d H:i:s';

    /** @var sql3db $calendDBObj */
    protected static $calendarDBObj;

    /** @var DateTimeImmutable[] $DateTimeHolder */
    protected static $DateTimeHolder = [];
    /** @var DateTimeZone[] $DateTimeZoneHolder */
    protected static $DateTimeZoneHolder = [];

    /** @var string $defaultTimezone */
    private static $defaultTimezone = 'Europe/Moscow';

    /**
     * @param string|DateTimeInterface|DateTimeInterfaceNative $date
     * @param DateTimeZone|string|null $timezone
     * @return DateTimeInterface|null
     */
    public static function init($date, $timezone = null){
        try{
            if( empty($date) ){
                throw new Exception('Empty date');
            }

            if( is_string($date) ){
                $tzObj = self::initTimezone($timezone);
            } elseif ($date instanceof DateTimeInterface || $date instanceof DateTimeInterfaceNative){
                $tzObj = $date->getTimezone();
                $date = $date->format(self::DATE_MYSQL);
            } else {
                throw new Exception('Bad date');
            }

            if( is_null($tzObj) ){
                throw new Exception('Bad timezone');
            }

            $holderKey = $date.$tzObj->getName();
            if( !isset(self::$DateTimeHolder[$holderKey]) ){
                self::$DateTimeHolder[$holderKey] = new DateTimeImmutable($date, $tzObj);
            }

            return self::$DateTimeHolder[$holderKey];
        } catch( Exception $e ){
            return null;
        }
    }

    /**
     * @param DateTimeInterface|string|null $date
     * @param string $format
     * @return null|string
     */
    public static function format($date, $format = DateTimeFactory::DATE_MYSQL){
        if( is_string($date) && !empty($date) ){
            $date = self::init($date);
        }

        return ( $date instanceof DateTimeInterface || $date instanceof DateTimeInterval) ? $date->format($format) : null;
    }

    /**
     * Получение объекта/интервала в зависимости от того, что лежит в строке $date
     *  Из-за использования парсинга даты может быть довольно медленным, но понадёжней поиска слэшей
     * @param string $date
     * @param DateTimeZone|null $timezone
     * @return DateTime|DateTimeInterval|null
     */
    public static function initByParse($date, DateTimeZone $timezone = null){
        try {
            $parse = self::parse($date);
            $firstDate = self::parsedDateToObj($parse[0]);
            if( $timezone ){
                $firstDate->setTimezone($timezone);
            }
            if (isset($parse[1])) {
                $secondDate = self::parsedDateToObj($parse[1])->setTimezone($firstDate->getTimezone());
            }

            return (isset($secondDate)) ? new DateTimeInterval($firstDate, $secondDate, $firstDate->getTimezone()) : $firstDate;
        } catch( Exception $e ){
            return null;
        }
    }

    /**
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @return DateTimeInterval
     */
    public static function initInterval(DateTimeInterface $start, DateTimeInterface $end){
        return new DateTimeInterval($start, $end, $start->getTimezone());
    }

    /**
     * @param DateTimeZone|string|null $timezone
     * @return DateTimeZone|null
     */
    public static function initTimezone($timezone = null)
    {
        try{
            $timezone = $timezone ?? self::$defaultTimezone;

            //Класс таймзоны очень привередливый, поэтому при любом косяке отлетит экзепшн,
            // дополнительные проверки не нужны
            if( $timezone instanceof DateTimeZone ) {
                $timezone = $timezone->getName();
            }

            if( !isset(self::$DateTimeZoneHolder[$timezone]) ){
                self::$DateTimeZoneHolder[$timezone] = new DateTimeZone($timezone);
            }
            return self::$DateTimeZoneHolder[$timezone];
        } catch( Exception $e ){
            return null;
        }
    }

    /**
     * @param string|array|null $schedule
     * @return WeekSchedule
     */
    public static function initSchedule($schedule = null){
        $scheduleObj = new WeekSchedule();
        if( is_string($schedule) ){
            $scheduleObj->setScheduleFromJson($schedule);
        } elseif( is_array($schedule) ){
            $scheduleObj->setScheduleFromArray($schedule);
        }

        return $scheduleObj;
    }

    /**
     * TODO: При переводе на эту фабрику прочекать вызовы метода
     * @param DateTimeInterface|string|null $date
     * @param DateTimeZone|string|null $timezone
     * @return Workday|null
     */
    public static function initWorkday($date = null, $timezone = null){
        try {
            $tzObj = self::initTimezone($timezone);
            if (is_null($tzObj)) {
                throw new Exception('Bad timezone');
            }

            $dateStr = 'now';
            if ($date instanceof DateTimeInterface) {
                $dateStr = $date->format(self::DATE_MYSQL);
            } elseif (is_string($date)) {
                $dateStr = $date;
            }

            return new Workday($dateStr, $tzObj);
        } catch( Exception $e ){
            return null;
        }
    }

    /**
     * Парсит дату и, в случае если в строке находится 2 дата, то докидывает её
     * @param string $date
     * @return array
     */
    private static function parse($date){
        $parseResult = [date_parse($date)];
        if( $parseResult[0]['error_count'] > 0 ){
            $doubleDateKey = array_search('Double date specification', $parseResult[0]['errors']);
            if( $doubleDateKey === false ){
                $doubleDateKey = array_search('Double time specification', $parseResult[0]['errors']);
            }
            $prevSymbolKey = $doubleDateKey-1;
            if( $doubleDateKey > 0 && $date[$prevSymbolKey] == '/' ){
                $parseResult[] = date_parse(substr($date, $doubleDateKey));
            }
        }
        return $parseResult;
    }

    /**
     * Укладка результата, полученного из date_parse в объект DateTime
     *  Если парсер чего-то не нашел, то подставляется параметр текущей даты
     * @param array $dateParseResult
     * @return DateTime
     */
    private static function parsedDateToObj($dateParseResult){
        $currDate = new DateTime('now', self::initTimezone());

        $currDate->setDate(
            (isset($dateParseResult['year']) && $dateParseResult['year'] > 0) ? $dateParseResult['year'] : $currDate->format('Y'),
            (isset($dateParseResult['month']) && $dateParseResult['month'] > 0) ? $dateParseResult['month'] : $currDate->format('m'),
            (isset($dateParseResult['day']) && $dateParseResult['day'] > 0) ? $dateParseResult['day'] : $currDate->format('d')
        );

        $currDate->setTime(
            (isset($dateParseResult['hour']) && ($dateParseResult['hour'] > 0 || $dateParseResult['hour'] === 0)) ?
                $dateParseResult['hour'] : $currDate->format('H'),
            (isset($dateParseResult['minute']) && ($dateParseResult['minute'] > 0 || $dateParseResult['minute'] === 0)) ?
                $dateParseResult['minute'] : $currDate->format('i'),
            (isset($dateParseResult['second']) && ($dateParseResult['second'] > 0 || $dateParseResult['second'] === 0)) ?
                $dateParseResult['second'] : $currDate->format('s')
        );

        if( isset($dateParseResult['zone']) ){
            $currDate->setTimezone(
                self::initTimezone(
                    (($dateParseResult['zone'] > 0) ? '-' : '+').
                    gmdate("h:i", abs($dateParseResult['zone'])*60)
                )
            );
        }

        return $currDate;
    }

    /**
     * @return sql3db
     */
    public static function getProductionCalendarDB(){
        if( empty(self::$calendarDBObj) ) {
            self::$calendarDBObj = new sql3db(CALENDAR_DB);
        }
        return self::$calendarDBObj;
    }

    /**
     * @param DateTimeIntervalList $intervalList
     * @return DateTimeInterval|null
     */
    public static function mergeIntervals(DateTimeIntervalList $intervalList){
        $intervalList->rewind();

        /** @var DateTimeInterval $resultInterval */
        $resultInterval = $intervalList->shift();
        $intervalList->next();
        while( $intervalList->valid() ){
            /** @var DateTimeInterval $_interval */
            $_interval = $intervalList->current();

            if ($_interval->getDateStart() < $resultInterval->getDateStart()) {
                $resultInterval = $resultInterval->setDateStart($resultInterval->getDateStart());
            }
            if ($_interval->getDateEnd() > $resultInterval->getDateEnd()) {
                $resultInterval = $resultInterval->setDateEnd($_interval->getDateEnd());
            }

            $intervalList->next();
        }

        return $resultInterval;
    }
}