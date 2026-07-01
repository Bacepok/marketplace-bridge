<?php

namespace MarketplaceBridge\Ozon;

defined('ABSPATH') || exit;

class ProductDetailsService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Получить полную информацию о товаре по product_id.
     */
    public function getByProductId(int $productId): array
    {
        $response = $this->client->post(
            '/v3/product/info/list',
            [
                'product_id' => [
                    $productId
                ]
            ]
        );

        if (!$response['success']) {

            return [
                'success' => false,
                'message' => $response['message'],
                'item' => []
            ];

        }

        if (
            empty($response['body']['items']) ||
            !isset($response['body']['items'][0])
        ) {

            return [
                'success' => false,
                'message' => 'Товар не найден.',
                'item' => []
            ];

        }

        return [

            'success' => true,

            'message' => '',

            'item' => $response['body']['items'][0]

        ];
    }
}