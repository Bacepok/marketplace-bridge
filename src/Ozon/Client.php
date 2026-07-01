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
                'code'    => 0,
                'message' => $response->get_error_message(),
                'body'    => []
            ];

        }

        return [

            'success' => true,

            'code' => wp_remote_retrieve_response_code($response),

            'body' => json_decode(
                wp_remote_retrieve_body($response),
                true
            )

        ];
    }
}