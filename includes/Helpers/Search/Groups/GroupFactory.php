<?php
/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 13.03.18
 * Time: 16:41
 */

namespace is\includes\Helpers\Search\Groups;


use is\includes\Helpers\Search\Rules\RuleFactory;
use is\includes\Helpers\Search\Rules\RuleList;

class GroupFactory
{

    private static $allowGroupOperations = ['OR','AND'];

    /**
     * @param $filter
     * @param $typeHinting
     * @return GroupDto|null
     */
    public static function create($filter, $typeHinting){

        if (!isset($filter->groupOp) ||  !in_array($filter->groupOp,static::$allowGroupOperations)){
            return null;
        }

        if (!isset($filter->rules) && !isset($filter->groups)){
            return null;
        }

        $result = new GroupDto();
        $result->setGroupOp($filter->groupOp);


        $result->setRules(new RuleList());
        if (isset($filter->rules) && count($filter->rules) > 0 ){
            foreach ($filter->rules as $rule){
                $_rule = RuleFactory::create($rule,$typeHinting);
                if (!is_null($_rule)){
                    $result->getRules()->attach($_rule);
                }
            }
        }

        $result->setGroups(new GroupList());
        if (isset($filter->groups) && count($filter->groups) > 0){
            foreach ($filter->groups as $group){
                $_group = static::create($group,$typeHinting);
                if(!is_null($_group)){
                    $result->getGroups()->attach($_group);
                }
            }
        }


        return ($result->getRules()->count() === 0 &&  $result->getGroups()->count() <= 1) ? $result->getGroups()->shift() : $result;
    }

    public static function initDefault(RuleList $ruleList = null, GroupList $groupList = null, string $groupOp = 'AND'){
        $groupDto = new GroupDto();
        $groupDto->setGroupOp($groupOp);
        $groupDto->setRules($ruleList ?? new RuleList());
        $groupDto->setGroups($groupList ?? new GroupList());
        return $groupDto;
    }

    public static function getGroupList($groups){
        $groupList = new GroupList();
        foreach ($groups as $group){
            $groupList->attach($group);
        }
        return $groupList;
    }
}