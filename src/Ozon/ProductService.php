<?php

namespace MarketplaceBridge\Ozon;

defined('ABSPATH') || exit;

class ProductService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Получить список товаров Ozon.
     */
    public function getProducts(
        int $limit = 50,
        string $lastId = ''
    ): array {

        $response = $this->client->post(
            '/v3/product/list',
            [
                'filter' => new \stdClass(),
                'last_id' => $lastId,
                'limit' => $limit
            ]
        );

        if (
            !$response['success']
            || $response['code'] !== 200
            || empty($response['body']['result']['items'])
        ) {
            return [];
        }

        $products = [];

        foreach ($response['body']['result']['items'] as $item) {

            $products[] = [

                'product_id' => $item['product_id'] ?? '',

                'offer_id' => $item['offer_id'] ?? '',

                'name' => $item['name'] ?? '',

                'status' => $item['visibility_details']['has_price']
                    ? 'Visible'
                    : 'Hidden'

            ];

        }

        return $products;

    }
}