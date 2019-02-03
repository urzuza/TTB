<?php


namespace is\includes\Geography\Cities;
use is\includes\AbstractDTO;
use is\includes\Geography\Regions\RegionDto;
use is\includes\Geography\Regions\RegionNewFactory;

/**
 * Created by IntelliJ IDEA.
 * User: Solver
 * Date: 03.10.17
 * Time: 13:18
 */
class CityDto extends AbstractDTO
{
    public function __toArray($ext = []): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'region_id' => $this->getRegionId(),
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
     * @var int $regionId
     */
    protected $regionId;

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
    public function getRegionId(): int
    {
        return $this->regionId;
    }

    /**
     * @param int $regionId
     */
    public function setRegionId(int $regionId)
    {
        $this->regionId = $regionId;
    }

    /**
     * @return RegionDto|null
     */
    public function getRegion(){
        return RegionNewFactory::init($this->getRegionId());
    }
}