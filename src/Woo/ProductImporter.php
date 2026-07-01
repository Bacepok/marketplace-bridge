<?php

namespace MarketplaceBridge\Woo;

use MarketplaceBridge\Marketplace\Product;

defined('ABSPATH') || exit;

class ProductImporter
{
    /**
     * Создать или обновить товар WooCommerce.
     *
     * @return int ID товара WooCommerce
     */
    public function import(Product $product): int
    {
        $existingId = wc_get_product_id_by_sku($product->offerId);

        if ($existingId) {

            $wcProduct = wc_get_product($existingId);

            if (!$wcProduct) {
                return 0;
            }

        } else {

            $wcProduct = new \WC_Product_Simple();

        }

        /*
         * Основные данные
         */

        $wcProduct->set_name($product->name);

        $wcProduct->set_sku($product->offerId);

        $wcProduct->set_description($product->description);

        $wcProduct->set_regular_price((string)$product->price);

        if ($product->oldPrice > 0) {

            $wcProduct->set_sale_price('');

        }

        $wcProduct->set_catalog_visibility('visible');

        $wcProduct->set_status(
            $product->archived
                ? 'draft'
                : 'publish'
        );

        /*
         * Остатки
         */

        $wcProduct->set_manage_stock(false);

        $wcProduct->set_stock_status(
            $product->archived
                ? 'outofstock'
                : 'instock'
        );

        /*
         * Сохранение
         */

        return $wcProduct->save();
    }
}