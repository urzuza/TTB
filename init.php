<?php
/**
 * Created by IntelliJ IDEA.
 * User: ruslanmukhtarov
 * Date: 2019-02-03
 * Time: 04:32
 */

include('vendor/autoload.php');
include('telegramBot.php');


// Спрашиваем непрочитанные ботом сообщения.
$telegramApi = new telegramBot();

while (true) {
    sleep(3);


    $updates = $telegramApi->getUpdates();

    // Работаем с каждым непрочитанным сообщением:
    // - на каждое отвечаем "Я спасу тебя, Леха";
    // - помечаем сообщение прочитанным;

    foreach ($updates as $update) {
        $telegramApi->sendMessage($update->message->chat->id, 'Я тебе помогу!');
    }
}


