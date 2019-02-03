<?php

use is\includes\Callcenter\CallcenterDto;
use is\includes\Callcenter\CallcenterFactory;
use is\includes\Partner\PartnerDto;
use is\includes\Partner\PartnerNewFactory;
use is\includes\PartnerPickupAddress\PartnerPickupAddressFactory;
use is\includes\Webshop\WebshopDto;
use is\includes\Webshop\WebshopFactory;

/**
 * Класс текущего пользователя: включает проверку авторизации, выборку информации о текущем пользователе.
 * Требует инициализированной переменной Db в System
 * 
 * 
 * @author syamka
 * @copyright 2011
 */


class CURR_USER {
    /**
     * @var Db $db
     */
	private $db = null;
	//Авторизован или нет?
	private $isAuth = false;
	//ID пользователя в таблице users
	private $userID = 0;

    /**
     * @var string пользователя (WEBSHOPS, PARTNERS, ADMINS)
     */
    private $userAccessGroup;

	//Тип пользователя: ADMIN, WEBSHOP, PARTNER, MANAGER_WEBSHOP 
	private $userType = null;

	//Информация о пользователе
	private $userInfo = array();
	
	//Ссылка на объект "Роли" - у соответствующей роли - соответствующий объект, остальные null
    /** @var WebshopDto $webshop */
	private $webshop = null;
    /**
     * Получение объекта ИМ
     * @return WebshopDto
     */
	public function getWebshop(){
		return $this->webshop;
	}
	
	private $partner = null;
	/**
     * Получение объекта партнера
     * @return PartnerDto
     */
    public function getPartner(){
		return $this->partner;
	}
        
    private $callCenter = null;
	/**
     * Получение объекта КЦ
     * @return CallcenterDto
     */
    public function getCallCenter(){
		return $this->callCenter;
	}

	public function __construct($db){
		$this->db = $db;
		
		if(isset($_SESSION['user_id']) && intval($_SESSION['user_id']) > 0){
			$this->userID = intval($_SESSION['user_id']);
			$this->userType = $_SESSION['user_type'];
			$this->isAuth = true;		
			$this->setRoleObject();	
		}
	}
	
	//Инициализируем объект соответствующей роли (ИМ, Партнер..)
	protected function setRoleObject(){
		if($this->userType == 'WEBSHOP'){
			$this->getUserInfo();
			$this->webshop = WebshopFactory::init($this->userInfo['id']);
		};
		
		if(($this->userType == 'PARTNER') || ($this->userType == 'STORAGE')){
			$this->getUserInfo();
			if (isset($this->userInfo['id'])) {
                $this->partner = PartnerNewFactory::init($this->userInfo['id']);
            }
		};
        if($this->userType == 'CALL_CENTER'){
			$this->getUserInfo();
			$this->callCenter = CallcenterFactory::init($this->userInfo['call_center_id']);
		} 


		//подмена понятий. Переопределение роли пользователя (ПВЗ действует от имени партнера с его правами)
        if($this->userType == 'PICKUP') {
            $this->getUserInfo();
            $this->userType = 'PARTNER';
            $this->userID = $this->userInfo['user_id'];

            $ppaDto = PartnerPickupAddressFactory::init($this->userInfo['pickup_id'] ?? null);
            if( !is_null($ppaDto) ){
                $this->partner = PartnerNewFactory::init($ppaDto->getPartnerId());
            }
		}
         
		
	}

    public function checkAuth($userId,$userType){
        $this->userID = $userId;
        $this->userType = $userType;
        $this->isAuth = true;

        $this->setRoleObject();
        $this->setSession();
        return true;
    }

	public function setSession(){
		$_SESSION['user_id'] = $this->userID;
		$_SESSION['user_type'] = $this->userType;
		//флаг для новостей
		if(in_array($this->userType, array('PARTNER', 'WEBSHOP', 'STORAGE')))
			$_SESSION['show_unseen_news'] = 1;
	}

	public function getUserId(){
		return $this->userID;
	}


    /**
     * Получить группу текущего пользователя
     * @return string
     * @throws Exception
     */
    public function getUserAccessGroup() {
        $result = $this->db->select(['access_group'], 'users', ['user_id'=>$this->getUserId()]);
        if(count($result) == 0){
            throw new Exception("Не задана группа для пользователя");
        }

        $this->userAccessGroup = $result[0]['group'];
	    return $this->userAccessGroup;
    }

	public function getUserType(){
		return $this->userType;
	}

	public function isAuth(){
		return $this->isAuth;	
	}

    
    public function isWebshop(): bool{
        return ($this->userType == 'WEBSHOP');
    }
    
    public function isPartner(): bool{
       return ($this->userType == 'PARTNER'); 
    }
    
    public function isManager(): bool{
       return ($this->userType == 'MANAGER_WEBSHOP'); 
    }
    
    public function isStorage(): bool{
       return ($this->userType == 'STORAGE'); 
    }

       
	/**
	 * 	Добавление параметров в сессию (Должно производиться только здесь!)
	 * 	$params - массив параметров
	 *  !если ключ повторяется - он перезаписывается
	 **/
	 //Имя ключа в сессии, под которым хранятся параметры
	 private $session_key = 'params';
	 
	 public function writeSessionParam($params){
	 	if(!isset($_SESSION[$this->session_key]))
	 		$_SESSION[$this->session_key] = array();
 		$_SESSION[$this->session_key] = array_merge($_SESSION[$this->session_key],$params);
	 }
	 
	 /**
	  * Получить значение параметра $name из сессии
	  **/
	  public function getSessionParam($name){
 			if(!isset($_SESSION[$this->session_key]) || !isset($_SESSION[$this->session_key][$name]))
 				return false;
			return $_SESSION[$this->session_key][$name];
	  }
	 

	//Полная информация о текущем пользователе//
	public function getUserInfo(){
        
		if(is_null($this->userType))
			return false;

		if(count($this->userInfo) == 0){

			$info = users::getUserInfo($this->userID);
			if(!$info || (count($info) == 0)){
				$this->logout();
				return false;
			}
			$this->userInfo = $info;
			
			$_SESSION['user_info'] = $this->userInfo;
		}
		
		return $this->userInfo;
	}
	
	public function logout(){
		foreach( $_SESSION as $k=>$v ){
			unset($_SESSION[$k]);
		}
		$this->isAuth = false;
		$this->userID = false;
		$this->userType = false;
        
	}
    
    public function forcedLogout(){
		$this->logout();
        
        @setcookie(session_name(), '', time() - 42000);
        @session_regenerate_id(true);
        @session_destroy();
	}

	public function getRoleExtraPages(){

		$resultPagesArr = array();
		/*
		 * массив дополнительных страниц для разных ролей
		 * */
		$pages = array(
			'WEBSHOP' => array(
				
				'webshop_addOrderDelivery' => array(
													'url'=>'/pages/webshop/addOrderDelivery.php',
													'title'=>'Создание заказа на доставку',
													'page'=>'webshop_addOrderDelivery',
													'parent'=>'webshop_addOrder'
				),
				'webshop_addOrderChange' => array(
													'url'=>'/pages/webshop/addOrderChange.php',
													'title'=>'Создание заказа на обмен',
													'page'=>'webshop_addOrderChange',
													'parent'=>'webshop_addOrder'
				),
				'webshop_addOrderIntake' => array(
													'url'=>'/pages/webshop/addOrderIntake.php',
													'title'=>'Создание заказа на забор товара',
													'page'=>'webshop_addOrderIntake',
													'parent'=>'webshop_addOrder'
				)
			)
		);

		if($this->userType == 'WEBSHOP'){
			$orderTypesAllowedStr = WebshopFactory::initByUserId($this->getUserId())->getOrderTypesAllowed();
            $orderTypesAllowed = explode(',', $orderTypesAllowedStr);
			foreach($orderTypesAllowed as $orderType){
				switch($orderType){
					case 'DELIVERY': {
						$resultPagesArr['webshop_addOrderDelivery'] = $pages['WEBSHOP']['webshop_addOrderDelivery'];
					}
					break;
					case 'CHANGE': {
						$resultPagesArr['webshop_addOrderChange'] = $pages['WEBSHOP']['webshop_addOrderChange'];
					}
					break;
					case 'INTAKE': {
						$resultPagesArr['webshop_addOrderIntake'] = $pages['WEBSHOP']['webshop_addOrderIntake'];
					}
					break;
				}
			}
		} elseif($this->userType == 'PARTNER'){
		} elseif($this->userType == 'MANAGER_WEBSHOP'){
		}
		return $resultPagesArr;
	}
}
