<?php
/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 13.03.18
 * Time: 16:41
 */

namespace is\includes\Helpers\Search\Rules;


use Common;
use Filter;
use is\includes\Date\DateTimeFactory;
use stdClass;

class RuleFactory
{
    private static $allowSearchOperations = ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni', 'le', 'ge', 'lt', 'gt','dbw'];

    /**
     * @param $rawRule
     * @param $typeHinting
     * @return RuleDto|null
     */
    public static function create($rawRule, $typeHinting){
        $rule = null;
        $preparedRule = static::prepareRule($rawRule, $typeHinting);
        if (!is_null($preparedRule)){
            $rule = new RuleDto();
            $rule->setField($preparedRule->field);
            $rule->setOp($preparedRule->op);
            $rule->setData($preparedRule->data);
        }
        return $rule;
    }

    public static function createFromCleanParams(string $field, string $op, $data)
    {
        $rule = new RuleDto();
        $rule->setField($field);
        $rule->setOp($op);
        $rule->setData($data);
        return $rule;
    }


    /**
     * @param RuleDto[] $rules
     * @return
     */
    public static function createList($rules)
    {
        $rulesList = new RuleList();
        foreach ($rules as $rule){
            $rulesList->attach($rule);
        }
        return $rulesList;
    }

    /**
     * @param $rule
     * @param $typeHinting
     * @return null|stdClass
     */
    private static function prepareRule($rule, $typeHinting){

        // Если нам приехал плохой рул сразу вернем null
        if (!isset($rule->field) || empty($rule->field) || !isset($rule->op) || !in_array($rule->op,self::$allowSearchOperations) ||
            !isset($rule->data) || $rule->data === 'jqgrid_select_value_all') {
            return null;
        }

        if (isset($typeHinting['nillable'])
            && in_array($rule->field, $typeHinting['nillable']) && in_array($rule->data,['null',null])){
            $rule->data = null;
        } elseif( isset($typeHinting['multiplyInt'])
            && in_array($rule->field, $typeHinting['multiplyInt']) && is_array($rule->data) && count($rule->data) > 0 ){
            foreach ($rule->data as $key=>$item){
                $rule->data[$key] = Filter::positiveInt($item);
            }
        } elseif (isset($typeHinting['int']) && in_array($rule->field, $typeHinting['int'])) {
            $rule->data = Filter::nonNegativeInt($rule->data);
        } elseif (isset($typeHinting['date']) && in_array($rule->field, $typeHinting['date'])) {
            $rule->data = DateTimeFactory::initByParse($rule->data);
        } elseif (isset($typeHinting['float']) && in_array($rule->field, $typeHinting['float'])) {
            $rule->data = Filter::positiveFloat($rule->data);
        } elseif (isset($typeHinting['num_string']) && in_array($rule->field, $typeHinting['num_string'])) {
            $rule->data = Filter::numString($rule->data);
        } elseif (isset($typeHinting['safe_string']) && in_array($rule->field, $typeHinting['safe_string'])) {
            $rule->data =Filter::safeString($rule->data);
        } elseif (isset($typeHinting['unsafe_string']) && in_array($rule->field, $typeHinting['unsafe_string'])) {
            $rule->data =Filter::unsafeString($rule->data);
        } elseif (isset($typeHinting['barcode']) && in_array($rule->field, $typeHinting['barcode'])) {
            $rule->data =Filter::barcode($rule->data);
        } elseif (isset($typeHinting['boolean']) && in_array($rule->field, $typeHinting['boolean'])) {
            $rule->data = Filter::boolean($rule->data);
        } elseif (isset($typeHinting['phone']) && in_array($rule->field, $typeHinting['phone'])) {
            $rule->data = Filter::phone($rule->data);
        } elseif (isset($typeHinting['email']) && in_array($rule->field, $typeHinting['email'])) {
            $rule->data = Filter::phone($rule->data);
        }else{
            $rule = null;
        }

        // Если в процессе проверки какое-то поле коорое не должно было быть null`ом встало в null отбрасываем все правило целиком
        if (!is_null($rule) && isset($typeHinting['nillable'])
            && !in_array($rule->field, $typeHinting['nillable']) && is_null($rule->data)){
            $rule = null;
        }

        // Если к этому моменту есть что искать то андерскорим поле, для того чо бы MySql не ругался
        if(!is_null($rule)){
            $rule->field = Common::cc2us($rule->field);
        }

        return $rule;

    }
}