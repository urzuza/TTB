<?php

use GuzzleHttp\Client;

/**
 * Created by IntelliJ IDEA.
 * User: ruslanmukhtarov
 * Date: 2019-02-03
 * Time: 04:39
 */
class telegramBot
{
    protected $token = "728271207:AAFgm7PuuDqseJI65ft5UzKyRtsDEW01kmc";
    protected $update_id;

    /**
     * @param $method
     * @param array $params
     * @return mixed
     */
    protected function query($method, $params = [])
    {
        $url = "https://api.telegram.org/bot";
        $url .= $this->token;
        $url .= "/" . $method;

        if (!empty($params)) {
            $url .= "?" . http_build_query($params);
        }


        $client = new Client([
            'base_uri' => $url
        ]);

        try {
            $result = $client->request('GET');
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        }

        if (!empty($result)) {
            return json_decode($result->getBody());
        }

    }


    /**
     * Тащим апдэйты с Телеграмма и ставим им "Прочитанно"
     *
     * @return mixed
     */
    public function getUpdates()
    {
        $response = $this->query('getUpdates', ['offset' => $this->update_id + 1]);
        if (!empty($response->result)) {
            $this->update_id = $response->result[count($response->result) - 1]->update_id;

        }
        return $response->result;

    }


    /**
     * @param $chat_id
     * @param $text
     * @return mixed
     */
    public function sendMessage($chat_id, $text)
    {
        $response = $this->query('sendMessage', [
            'text' => $text,
            'chat_id' => $chat_id,
        ]);

        return $response;
    }

}

