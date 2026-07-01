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
     *
     * Возвращает:
     * [
     *     'items' => [],
     *     'last_id' => ''
     * ]
     */
    public function getProducts(
        int $limit = 50,
        string $lastId = ''
    ): array {

        $response = $this->client->post(
            '/v3/product/list',
            [
                'filter'  => new \stdClass(),
                'last_id' => $lastId,
                'limit'   => $limit,
            ]
        );

        if (!$response['success']) {

            return [
                'success' => false,
                'message' => $response['message'],
                'items'   => [],
                'last_id' => '',
            ];

        }

        if (
            empty($response['body']['result']) ||
            !isset($response['body']['result']['items'])
        ) {

            return [
                'success' => false,
                'message' => 'Некорректный ответ Ozon.',
                'items'   => [],
                'last_id' => '',
            ];

        }

        $items = [];

        foreach ($response['body']['result']['items'] as $item) {

            $items[] = [

                'product_id' => (int) ($item['product_id'] ?? 0),

                'offer_id' => (string) ($item['offer_id'] ?? ''),

                'archived' => (bool) ($item['archived'] ?? false),

                'has_fbo' => (bool) ($item['has_fbo_stocks'] ?? false),

                'has_fbs' => (bool) ($item['has_fbs_stocks'] ?? false),

            ];

        }

        return [

            'success' => true,

            'message' => '',

            'items' => $items,

            'last_id' => (string) ($response['body']['result']['last_id'] ?? ''),

        ];
    }
}