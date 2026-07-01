<?php

namespace MarketplaceBridge\Ozon;

defined('ABSPATH') || exit;

class ConnectionService
{
    public function test(): array
    {
        if (!Config::isConfigured()) {

            return [

                'success' => false,

                'message' => 'Не заполнены Client ID и API Key.'

            ];

        }

        $client = new Client();

        $response = $client->post(

            '/v3/product/list',

            [

                'filter' => new \stdClass(),

                'last_id' => '',

                'limit' => 1

            ]

        );

        switch ($response['code']) {

            case 200:

                return [

                    'success' => true,

                    'message' => 'Подключение успешно.'

                ];

            case 401:

                return [

                    'success' => false,

                    'message' => 'Ошибка авторизации (401).'

                ];

            case 403:

                return [

                    'success' => false,

                    'message' => 'Доступ запрещён (403).'

                ];

            case 429:

                return [

                    'success' => true,

                    'message' => 'Соединение работает, превышен лимит запросов.'

                ];

            default:

                return [

                    'success' => false,

                    'message' => 'HTTP ' . $response['code']

                ];

        }

    }

}