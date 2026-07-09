<?php

namespace MarketplaceBridge\Ozon;

defined('ABSPATH') || exit;

class Client
{
    private const API_URL = 'https://api-seller.ozon.ru';

    public function post(string $endpoint, array $body = []): array
    {
        $response = wp_remote_post(
            self::API_URL . $endpoint,
            [
                'timeout' => 30,
                'headers' => [
                    'Client-Id'    => Config::getClientId(),
                    'Api-Key'      => Config::getApiKey(),
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode($body)
            ]
        );

        if (is_wp_error($response)) {

            return [
                'success' => false,
                'code' => 0,
                'message' => $response->get_error_message(),
                'body' => []
            ];

        }

        $code = wp_remote_retrieve_response_code($response);

        $rawBody = wp_remote_retrieve_body($response);

        $body = json_decode(
            $rawBody,
            true
        );

        if (!is_array($body)) {

            return [

                'success' => false,

                'code' => $code,

                'message' => 'Некорректный JSON-ответ Ozon.',

                'body' => []

            ];

        }

        return [

            'success' => $code >= 200 && $code < 300,

            'code' => $code,

            'message' => (string) ($body['message']
                ?? $body['error']['message']
                ?? ''),

            'body' => $body

        ];
    }
}