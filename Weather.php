<?php
/**
 * Created by IntelliJ IDEA.
 * User: ruslanmukhtarov
 * Date: 2019-02-03
 * Time: 06:19
 */

class Weather
{

    protected $token = "a68ff5dac8766a565daf89c016609c93";

    /**
     * @param $lat
     * @param $lon
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getWeather($lat, $lon)
    {


        $url = "api.openweathermap.org/data/2.5/weather";
        $params = [];
        $params['lat'] = $lat;
        $params['lon'] = $lon;
        $params['APPID'] = $this->token;

        $url .= "?" . http_build_query($params);

        $client = new \GuzzleHttp\Client(
            [
                'base_uri' => $url
            ]);
        $result = $client->request('GET');
        return json_decode($result->getBody());
    }

}




