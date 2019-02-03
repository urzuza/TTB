<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 06.12.17
 * Time: 14:29
 */

namespace is\includes\Date;

use DateInterval;
use DateTimeZone;
use DateTimeInterface as DateTimeInterfaceNative;

interface DateTimeInterface extends DateTimeInterfaceNative{
    /**
     * @param DateInterval $interval
     * @return DateTimeInterface
     */
    public function add( $interval );

    /**
     * @param string $format
     * @param string $time
     * @param DateTimeZone|null $timezone
     * @return DateTimeInterface
     */
    public static function createFromFormat( $format , $time, $timezone = null );

    /**
     * @return array
     */
    public static function getLastErrors();

    /**
     * @param string $modify
     * @return DateTimeInterface
     */
    public function modify( $modify );

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @return DateTimeInterface
     */
    public function setDate( $year, $month, $day );

    /**
     * @param int $year
     * @param int $week
     * @param int $day
     * @return DateTimeInterface
     */
    public function setISODate( $year, $week, $day = 1 );

    /**
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return DateTimeInterface
     */
    public function setTime( $hour, $minute, $second = NULL );

    /**
     * @param int $unixtimestamp
     * @return DateTimeInterface
     */
    public function setTimestamp( $unixtimestamp );

    /**
     * @param DateTimeZone $timezone
     * @return DateTimeInterface
     */
    public function setTimezone( $timezone );

    /**
     * @param DateInterval $interval
     * @return DateTimeInterface
     */
    public function sub( $interval );
}