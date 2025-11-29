<?php

namespace MoveMoveIo\DaData;

use Cache;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class DadataEntity
{
    public static function getToken()
    {
        $token = Cache::get('dadata_token');

        if ($token) {
            return $token;
        }

        return self::getTokenByServer();
    }

    public static function getTokenByServer()
    {
        foreach (config('dadata.tokens') as $data) {
            $client = (new Client([
                RequestOptions::HTTP_ERRORS => false,
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Token ' . $data['token'],
                    'X-Secret' => $data['secret'],
                ],
            ]));

            $stat = $client->get('https://dadata.ru/api/v2/stat/daily');
            $result = json_decode($stat->getBody()->getContents(), true);
            $remain = data_get($result, 'remaining.suggestions', 0);

            if ($remain > 300) {
                Cache::put('dadata_token', $data['token'], now()->addMinutes(5));
                return $data['token'];
            }
        }

        return null;
    }
}
