<?php
/**
 * Created by IntelliJ IDEA.
 * User: solver
 * Date: 13.03.18
 * Time: 15:54
 */

namespace is\includes\Helpers\Search\Rules;



use Common;
use is\includes\AbstractList;
use stdClass;

class RuleList extends AbstractList
{
    protected static $instanceName = 'is\includes\Helpers\Search\Rules\RuleDto';


    /**
     * @return stdClass|RuleDto[]
     */
    public function convertKeysToFieldName (){
        $this->rewind();
        $result = new stdClass();
        while ($this->valid()){
            $result->{Common::us2cc($this->current()->getField())} = $this->current();
            $this->next();
        }
        return $result;
    }

    public function removeRulesByFieldName (array $fieldNames){
        $this->rewind();
        $result = new RuleList();
        while ($this->valid()){
            if(!in_array($this->current()->getField(),$fieldNames)){
                $result->attach($this->current());
            }
            $this->next();
        }
        return $result;
    }
}