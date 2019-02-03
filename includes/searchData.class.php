<?php


use is\includes\Geography\Cities\CityDto;
use is\includes\Geography\Cities\CityNewFactory;

class searchData{
	
	/**
	Методы осуществляют различную выборку информации из базы
	 **/

	public function getWorkStatusesTitles() {

		$data = Order::getOrderWorkStatuses();

		$i = 0;
		foreach($data as $key=>$val) {
			$result[$i]['id'] = $key;
			$result[$i++]['title'] = $val;
		}
		return $result;
	}

	public function getStatusesTitles() {

		$data = Order::getOrderStatuses();

		$i = 0;
		foreach($data as $key=>$val) {
			$result[$i]['id'] = $key;
			$result[$i++]['title'] = $val;
		}
		return $result;
	}


    /**
     * Не судите да не судимы будите:
     * метод очень плох , там где он вызывается все тоже очень плохо , как исправить пока идей нет
     * + к нему еще надо прикрепить типв услуг доступные в городе (КГТ, МГТ, Самовывоз ..)
     *
     * @param array $searchParams
     * @param array $sortParams
     * @return array
     * @throws Exception
     */
	public function getCitiesTitlesIds($searchParams = array(), $sortParams = array())
	{
        $dataFormated = [];
		if (isset($searchParams['zipcode']) && strlen($searchParams['zipcode']) > 0) {
			$_city = CityNewFactory::initCityByType('zip', 0, 0, $searchParams['zipcode'], null);

			$dataFormated[] = [
				'id' => $_city->getId(),
				'region_id' => $_city->getRegionId(),
				'zipcode' => $searchParams['zipcode'],
			];

		}else {
			$cityList = CityNewFactory::initByParams($searchParams, [], $sortParams);
			if ( $cityList->count() > 0 ) {
                $cityList->walk(function( CityDto $cityDto ) use (&$dataFormated){
                    $zipcodes = CityNewFactory::getService($cityDto)->getZipCodes();
                    if (count($zipcodes) > 0) {
                        foreach ($zipcodes as $zipcode) {
                            $dataFormated[] = [
                                'id' => $cityDto->getId(),
                                'zipcode' => $zipcode,
                                'city_id' => $cityDto->getId(),
                                'region_id' => $cityDto->getRegionId(),
                            ];
                        }
                    } else {
                        $dataFormated[] = [
                            'id' => $cityDto->getId(),
                            'zipcode' => '',
                            'city_id' => $cityDto->getId(),
                            'region_id' => $cityDto->getRegionId(),
                        ];
                    }
                });
			}
		}
        return $dataFormated;
	}
    
    public static function parseTableFieldKey($_parseKey){
        $result = ['table'=>'orders', 'field'=>null];
        $_fieldExploded = explode('*', $_parseKey);
        if( count($_fieldExploded) == 1 ){
            $result['field'] = $_fieldExploded[0];
        } elseif( isset($_fieldExploded[1]) ) {
            $result['table'] = $_fieldExploded[0];
            $result['field'] = $_fieldExploded[1];
        }
        return $result;
    }
    
    public static function parseJqOrdersFilters($_jqFilters){
	    
        $maybeZeroFields = ['is_problem','locked','check_by_cc','in_shipment','number','webshop_number','bar_code'];
        
        $searchParams = [];
        $filters = json_decode($_jqFilters);
        
        if( isset($filters->rules) && !empty($filters->rules) ){
            foreach( $filters->rules as $_ruleParam ){
                
                $keyParam = self::parseTableFieldKey($_ruleParam->field);
                
                if( $_ruleParam->data == 'jqgrid_select_value_all' || empty($keyParam['field']) || 
                    (($_ruleParam->data === '0' || $_ruleParam->data === 0 ) && !in_array($keyParam['field'], $maybeZeroFields)) ){
                    continue;
                }
                
                $_value = System::get('Db')->escape($_ruleParam->data);
                    
                $_operator = null;
                switch( $_ruleParam->op ){
                    case 'eq': $_operator = "= '".$_value."'"; break;
			        case 'ne': $_operator = "!= '".$_value."'"; break;
			        case 'bw': $_operator = "LIKE '".$_value."%'"; break;
			        case 'bn': $_operator = "NOT LIKE '".$_value."%'"; break;
			        case 'ew': $_operator = "LIKE '%".$_value."'"; break;
			        case 'en': $_operator = "NOT LIKE '%".$_value."'"; break;
			        case 'cn': $_operator = "LIKE '%".$_value."%'"; break;
			        case 'nc': $_operator = "NOT LIKE '%".$_value."%'"; break;
			        case 'nu': $_operator = "IS NULL"; break;
			        case 'nn': $_operator = "IS NOT NULL"; break;
			        case 'in': $_operator = "IN ('".str_replace(",", "','", $_value)."')"; break;
			        case 'ni': $_operator = "NOT IN ('".str_replace(",", "','", $_value)."')"; break;
			        case 'le': $_operator = "<= '".$_value."'"; break;
			        case 'ge': $_operator = ">= '".$_value."'"; break;
			        case 'lt': $_operator = "< '".$_value."'"; break;
			        case 'gt': $_operator = "> '".$_value."'"; break;
                }
                
                if( !empty($_operator) ){
                    $searchParams[] = "`".$keyParam['table']."`.`".$keyParam['field']."` ".$_operator;
                }
            }
        }
        
        return $searchParams;
    }
}
