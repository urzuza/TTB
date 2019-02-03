<?php
/**
 * Created by IntelliJ IDEA.
 * User: ixapek
 * Date: 25.10.17
 * Time: 16:52
 */

namespace is\includes\Date;

use DateInterval;
use DateTimeZone;

class DateTimeInterval{

    /** @var DateTime $dateStart */
    protected $dateStart;
    /** @var DateTime $dateEnd */
    protected $dateEnd;

    public function __construct(DateTimeInterface $dateStart, DateTimeInterface $dateEnd, DateTimeZone $timeZone){
        $this->setDateStart($dateStart);
        $this->setDateEnd($dateEnd);

        $this->getDateStart()->setTimezone($timeZone);
        $this->getDateEnd()->setTimezone($timeZone);

        $this->checkNormalized();
    }

    /**
     * Получить объект интервала между датами начала и конца
     * @return DateInterval
     */
    public function getInterval():DateInterval{
        return $this->getDateStart()->diff($this->getDateEnd());
    }

    /**
     * Проверка нормальности периода (дата начала должна быть меньше конца)
     * @return DateTimeInterval
     */
    public function checkNormalized(){
        $currInterval = $this->getInterval();
        if( $currInterval->invert ){
            $this->getDateStart()->add($currInterval);
            $this->getDateEnd()->sub($currInterval);
        }
        return $this;
    }

    /**
     * Получение интервала в виде строки
     * @param string $format
     * @param string $delimiter
     * @param bool $asDateInterval Формат вывода с интервалом или без
     * @return string
     */
    public function format($format, $delimiter = '/', $asDateInterval = false) {
        return $this->getDateStart()->format($format).$delimiter.(
            ($asDateInterval) ? $this->getInterval()->format('P%yY%mM%dDT%hH%iM%sS') : $this->getDateEnd()->format($format));
    }

    /**
     * @return DateTimeInterface
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param DateTimeInterface $dateStart
     * @return DateTimeInterval
     */
    public function setDateStart(DateTimeInterface $dateStart): DateTimeInterval
    {
        $this->dateStart = $dateStart;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param DateTimeInterface $dateEnd
     * @return DateTimeInterval
     */
    public function setDateEnd(DateTimeInterface $dateEnd): DateTimeInterval
    {
        $this->dateEnd = $dateEnd;
        return $this;
    }
}