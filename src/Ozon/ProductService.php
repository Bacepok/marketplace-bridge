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
     * Получить каталог Ozon
     */
    public function getProducts(
        int $limit = 50,
        string $lastId = ''
    ): array {

        // 1. Получаем список product_id

        $response = $this->client->post(
            '/v3/product/list',
            [
                'filter'  => new \stdClass(),
                'last_id' => $lastId,
                'limit'   => $limit
            ]
        );

        if (
            !$response['success']
            || empty($response['body']['result']['items'])
        ) {
            return [];
        }

        $productIds = [];

        foreach ($response['body']['result']['items'] as $item) {

            $productIds[] = $item['product_id'];

        }

        // 2. Получаем подробную информацию

        $info = $this->client->post(
            '/v2/product/info/list',
            [
                'product_id' => $productIds
            ]
        );

        if (
            !$info['success']
            || empty($info['body']['items'])
        ) {
            return [];
        }

        $products = [];

        foreach ($info['body']['items'] as $item) {

            $products[] = [

                'product_id' => $item['id'] ?? '',

                'offer_id' => $item['offer_id'] ?? '',

                'name' => $item['name'] ?? '',

                'status' => $item['visible']
                    ? 'Visible'
                    : 'Hidden'

            ];

        }

        return $products;

    }
}