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

        $item = $response['body']['items'][0];

        $description = $this->getDescriptionByProductId($productId);

        if ($description !== '') {
            $item['description'] = $description;
        }

        $images = $this->getImagesByProductId($productId);

        if (!empty($images)) {
            $item['images'] = $images;
        }

        return [

            'success' => true,

            'message' => '',

            'item' => $item

        ];
    }

    private function getDescriptionByProductId(int $productId): string
    {
        $response = $this->client->post(
            '/v1/product/info/description',
            [
                'product_id' => $productId
            ]
        );

        if (!$response['success']) {
            return '';
        }

        return (string) (
            $response['body']['result']['description']
            ?? $response['body']['description']
            ?? ''
        );
    }

    private function getImagesByProductId(int $productId): array
    {
        $response = $this->client->post(
            '/v2/product/pictures/info',
            [
                'product_id' => [
                    $productId
                ]
            ]
        );

        if (!$response['success']) {
            return [];
        }

        $items = $response['body']['result']['items']
            ?? $response['body']['items']
            ?? [];

        if (empty($items[0]) || !is_array($items[0])) {
            return [];
        }

        return $this->extractImages($items[0]);
    }

    private function extractImages(array $item): array
    {
        $images = [];

        foreach (['images', 'pictures'] as $key) {

            if (empty($item[$key]) || !is_array($item[$key])) {
                continue;
            }

            foreach ($item[$key] as $image) {

                if (is_string($image)) {
                    $images[] = $image;
                    continue;
                }

                if (!is_array($image)) {
                    continue;
                }

                $url = $image['url']
                    ?? $image['file_name']
                    ?? $image['image_url']
                    ?? '';

                if ($url !== '') {
                    $images[] = (string) $url;
                }

            }

        }

        if (!empty($item['primary_image'])) {
            array_unshift($images, (string) $item['primary_image']);
        }

        return array_values(array_unique(array_filter($images)));
    }
}
