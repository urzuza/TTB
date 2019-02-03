<?php

namespace is\includes\Helpers\Search\Rules;
use is\includes\AbstractDTO;

/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 13.03.18
 * Time: 15:47
 */

class RuleDto extends AbstractDTO
{

    private $field;
    private $op;
    private $data;

    public function __toArray($ext = []): array
    {
        return [
            'field' => $this->getField(),
            'op' => $this->getOp(),
            'data' => $this->getData(),
        ];
    }

    public static function getProperties(): array
    {
        return get_class_vars(get_called_class());
    }


    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return mixed
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @param mixed $op
     */
    public function setOp($op)
    {
        $this->op = $op;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


}