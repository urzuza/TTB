<?php
/**
 * Created by IntelliJ IDEA.
 * User: ruslanmukhtarov
 * Date: 2019-02-03
 * Time: 04:32
 */

include('vendor/autoload.php');
include('telegramBot.php');
include('Weather.php');

session_start();

defined('SYSTEM_ROOT') || define('SYSTEM_ROOT', dirname(dirname(__FILE__)));
require_once "/etc/topdelivery/is/config.php";
require_once LIBS_DIR. "System.php";
require_once SYSTEM_ROOT . "/vendor/autoload.php";

//Регистрируем объект глобального доступа Db для работы с БД
System::registerObject('Db',array(
    'host' => DB_HOST,
    'user' => DB_USER,
    'pass' => DB_PASS,
    'db' => DB_NAME
));
//Регистрируем объект глобального доступа CURR_USER для работы с пользовательской авторизацией
System::registerObject('CURR_USER',System::get('Db'));












// Спрашиваем непрочитанные ботом сообщения.
$telegramApi = new telegramBot();
$weatherApi = new Weather();
$response = "";


while (true) {
    sleep(0.6);

    $updates = $telegramApi->getUpdates();

    // Работаем с каждым непрочитанным сообщением:
    // - на каждое отвечаем "Я спасу тебя, Леха";
    // - помечаем сообщение прочитанным;

    foreach ($updates as $update) {

        if (isset($update->message->location)) {

            try {
                $result = $weatherApi->getWeather($update->message->location->latitude, $update->message->location->longitude);

                if (!empty($result)) {
                    $response = $result->weather[0]->main;


//                    switch ($result->weather[0]->main) {
//                        case "Snow":
//                            $response = "Cнеговато";
//                            break;
//                        case "Clouds":
//                            $response = "Дым";
//                            break;
//                        case "Rain":
//                            $response = "Мокруха";
//                            break;
//                        case "default":
//                            $response = "Я тебе не оракул, Вася";
//                    }


                }
                $telegramApi->sendMessage($update->message->chat->id, "$response");

            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            }
        } else {
            $telegramApi->sendMessage($update->message->chat->id, 'Отправь мне локацию и я скажу какая погода!');
        }
    }
}




