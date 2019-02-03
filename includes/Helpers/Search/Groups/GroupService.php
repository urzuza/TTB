<?php
/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 23.08.18
 * Time: 12:29
 */

namespace is\includes\Helpers\Search\Groups;


use is\includes\Helpers\Search\Rules\RuleList;

class GroupService
{
    /**
     * @param GroupDto|null $groupDto
     * @param RuleList|null $rulesList
     * @return GroupDto
     */
    public static function addRulesListToGroup(GroupDto $groupDto = null, RuleList $rulesList = null){
        if (!is_null($groupDto) || !is_null($rulesList)) {
            if (is_null($groupDto)) {
                $groupDto = GroupFactory::initDefault($rulesList);
            } elseif ($groupDto->getRules()->count() === 0) {
                $groupDto->setRules($rulesList);
            }elseif ($rulesList->count() > 0){
                $groupDto->getRules()->merge($rulesList);
            }
        }
        return $groupDto;
    }

    public static function mergeGroups( GroupDto $mainGroup, GroupDto $additionalGroup, $operand = 'AND'){
        if ($mainGroup->getGroups() === null && $operand === $mainGroup->getGroupOp()){
            $groupList = new GroupList();
            $groupList->attach($additionalGroup);
            $mainGroup->setGroups($groupList);
        }elseif ( $operand === $mainGroup->getGroupOp()){
            $mainGroup->getGroups()->attach($additionalGroup);
        }else{
            $mainGroup->getGroups()->attach(self::mergeGroups($mainGroup->getGroups()->shift(),$additionalGroup,$operand));
        }

        return $mainGroup;
    }


}