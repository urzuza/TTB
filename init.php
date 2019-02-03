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


// Спрашиваем непрочитанные ботом сообщения.
$telegramApi = new telegramBot();
$weatherApi = new Weather();
$response ="";


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
                    switch ($result->weather[0]->main) {


                        case "Clear":
                            $response = "Все чисто";
                            break;
                        case "Clouds":
                            $response = "Дым";
                            break;
                        case "Rain":
                            $response = "Мокруха";
                            break;
                        case "default":
                            $response = "Я тебе не оракул, Вася";
                    }
                }
                $telegramApi->sendMessage($update->message->chat->id, "$response");

            } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            }
        } else {
            $telegramApi->sendMessage($update->message->chat->id, 'Я тебе помогу!');
        }
    }

    var_dump($result);
}




