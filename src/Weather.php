<?php

namespace Cold\Weather;


use Cold\Weather\Exceptions\HttpException;
use Cold\Weather\Exceptions\InvalidArgumentException;
use GuzzleHttp\Client;

class Weather
{
    protected $key;
    protected $guzzleOptions = [];
    const WEATHER_REQUEST_URL = 'https://restapi.amap.com/v3/weather/weatherInfo';

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    /**
     * @param string $city
     * @param string $format
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Cold\Weather\Exceptions\HttpException
     * @throws \Cold\Weather\Exceptions\InvalidArgumentException
     */
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    /**
     * @param string $city
     * @param string $format
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Cold\Weather\Exceptions\HttpException
     * @throws \COld\Weather\Exceptions\InvalidArgumentException
     */
    public function getForcastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }
    /**
     * @param string $city
     * @param string $type
     * @param string $format
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Cold\Weather\Exceptions\HttpException
     * @throws \Cold\Weather\Exceptions\InvalidArgumentException
     */
    public function getWeather($city, string $type = 'base', string $format = 'json')
    {
        if(!\in_array(\strtolower($format), ['xml', 'json'])){
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if(!\in_array(\strtolower($type), ['all', 'base'])){
            throw new InvalidArgumentException('Invalid type value(base/all): '.$type);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);

        try{
            $response = $this->getHttpClient()->get(self::WEATHER_REQUEST_URL, [
                'query' => $query,
            ])->getBody()->getContents();

            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e){
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}