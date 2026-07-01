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

        if (!empty($item['barcodes'][0])) {
            $product->barcode = (string) $item['barcodes'][0];
        }

        if (!empty($item['sources'][0]['sku'])) {
            $product->sku = (string) $item['sources'][0]['sku'];
        }

        if (!empty($item['images']) && is_array($item['images'])) {
            $product->images = $item['images'];
        }

        if (!empty($item['attributes']) && is_array($item['attributes'])) {
            $product->attributes = $item['attributes'];
        }

        return $product;
    }
}