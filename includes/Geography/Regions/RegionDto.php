<?php


namespace is\includes\Geography\Regions;
use is\includes\AbstractDTO;

/**
 * Created by IntelliJ IDEA.
 * User: bimdeer
 * Date: 03.10.17
 * Time: 13:18
 */
class RegionDto extends AbstractDTO
{
    public function __toArray($ext = []): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'timezone' => $this->getTimezone()
        ];
    }

    public static function getProperties(): array
    {
        return get_class_vars(get_called_class());
    }
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var string $title
     */
    protected $title;

    /**
     * @var int $timezone
     */
    protected $timezone;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getTimezone(): int
    {
        return $this->timezone;
    }

    /**
     * @param int $timezone
     */
    public function setTimezone(int $timezone)
    {
        $this->timezone = $timezone;
    }

}