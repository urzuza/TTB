<?php

namespace is\includes\Helpers\Search\Groups;
use is\includes\AbstractDTO;
use is\includes\Helpers\Search\Rules\RuleList;

/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 13.03.18
 * Time: 15:47
 */

class GroupDto extends AbstractDTO
{
    /**
     * @return string
     */
    public function getGroupOp(): string
    {
        return $this->groupOp;
    }

    /**
     * @param string $groupOp
     */
    public function setGroupOp(string $groupOp)
    {
        $this->groupOp = $groupOp;
    }

    /**
     * @return RuleList
     */
    public function getRules(): RuleList
    {
        return $this->rules;
    }

    /**
     * @param RuleList $rules
     */
    public function setRules(RuleList $rules)
    {
        $this->rules = $rules;
    }

    /**
     * @return GroupList
     */
    public function getGroups(): GroupList
    {
        return $this->groups;
    }

    /**
     * @param GroupList $groups
     */
    public function setGroups(GroupList $groups)
    {
        $this->groups = $groups;
    }

    /** @var string $groupOp */
    private $groupOp;
    /** @var RuleList $rules */
    private $rules;
    /** @var GroupList $groups */
    private $groups;

    public function __toArray($ext = []): array
    {
        return [
            'groupOp' => $this->getGroupOp(),
            'rules' => $this->getRules(),
            'groups' => $this->getGroups(),
        ];
    }

    public static function getProperties(): array
    {
        return get_class_vars(get_called_class());
    }

}