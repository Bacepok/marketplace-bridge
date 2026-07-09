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

                $url = $this->extractImageUrl($image);

                if ($url !== '') {
                    $images[] = $url;
                }

            }

        }

        if (!empty($item['primary_image'])) {

            $primaryImage = $this->extractImageUrl($item['primary_image']);

            if ($primaryImage !== '') {
                array_unshift($images, $primaryImage);
            }

        }

        return array_values(array_unique(array_filter($images)));
    }

    private function extractImageUrl($image): string
    {
        if (is_string($image)) {
            return $image;
        }

        if (!is_array($image)) {
            return '';
        }

        return (string) (
            $image['url']
            ?? $image['file_name']
            ?? $image['image_url']
            ?? ''
        );
    }
}
