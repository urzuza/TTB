<?php

/**
 * 	Полностью статический класс для хранения объектов глобального доступа
 * 	Классы объектов подгружаются автоматически из папки LIBS_DIR, либо, если соответствующего класса в этой папке нет, то в папке INC_DIR
 *
 * @author syamka
 * @copyright 2012
 */

/**
 * 	Основной класс "Системы": предполагается хранить в нем ссылки на объекты глобальной области видимости
 **/
class System{

    //Массив ссылок на объекты
    private static $objects = array();

    /**
     *
     * @param string $class_name
     * @param mixed $args
     * @param boolean $object_name
     * @return boolean
     */
    public static function registerObject($class_name,$args,$object_name = false){
        try{
            //Объект ReflectionClass для класса $class_name
            $class = new ReflectionClass($class_name);
            if(!$object_name)
                $object_name = $class_name;
            //Создание объекта
            self::$objects[$object_name] = $class->newInstance($args);
            return true;
        }
        catch(Exception $e){
            //Обработку исключения предполагается изменить каким-либо образом..
            echo $e->getMessage();
        }
        return false;
    }

    /**
     * Получить ссылку на ранее зарегистрированный объект
     * @param string $object_name
     * @return Db|CURR_USER|Pages
     */
    public static function get($object_name){
        return self::$objects[$object_name];
    }

}


