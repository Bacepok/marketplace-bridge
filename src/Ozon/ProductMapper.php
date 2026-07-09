<?php

namespace MarketplaceBridge\Ozon;

use MarketplaceBridge\Marketplace\Product;

defined('ABSPATH') || exit;

class ProductMapper
{
    /**
     * Преобразовать ответ Ozon API
     * во внутреннюю модель Marketplace\Product.
     */
    public function map(array $item): Product
    {
        $product = new Product();

        $product->marketplaceId = (int) ($item['id'] ?? 0);

        $product->offerId = (string) ($item['offer_id'] ?? '');

        $product->name = (string) ($item['name'] ?? '');

        $product->description = (string) ($item['description'] ?? '');

        $product->price = (float) ($item['price'] ?? 0);

        $product->oldPrice = (float) ($item['old_price'] ?? 0);

        $product->currency = (string) ($item['currency_code'] ?? 'RUB');

        $product->archived = (bool) ($item['is_archived'] ?? false);

        $product->marketplaceUrl = (string) (
            $item['url']
            ?? $item['share_url']
            ?? ''
        );

        if ($product->marketplaceUrl === '' && $product->marketplaceId > 0) {
            $product->marketplaceUrl = 'https://www.ozon.ru/product/' . $product->marketplaceId . '/';
        }

        if (isset($item['stock_quantity'])) {
            $product->manageStock = true;
            $product->stockQuantity = max(0, (int) $item['stock_quantity']);
        }

        if (!empty($item['barcodes'][0])) {
            $product->barcode = (string) $item['barcodes'][0];
        }

        if (!empty($item['sources'][0]['sku'])) {
            $product->sku = (string) $item['sources'][0]['sku'];
        }

        $images = [];

        if (!empty($item['images']) && is_array($item['images'])) {
            $images = $item['images'];
        }

        if (!empty($item['primary_image'])) {
            array_unshift($images, $item['primary_image']);
        }

        $product->images = $this->normalizeImages($images);

        if (!empty($item['attributes']) && is_array($item['attributes'])) {
            $product->attributes = $item['attributes'];
        }

        return $product;
    }

    private function normalizeImages(array $images): array
    {
        $normalized = [];

        foreach ($images as $image) {

            if (is_string($image)) {
                $normalized[] = $image;
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
                $normalized[] = (string) $url;
            }

        }

        return array_values(array_unique(array_filter($normalized)));
    }
}
