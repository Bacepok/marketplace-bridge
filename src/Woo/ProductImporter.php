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
        if (
            !function_exists('wc_get_product_id_by_sku') ||
            !function_exists('wc_get_product') ||
            !class_exists('WC_Product_Simple')
        ) {

            return 0;

        }

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

        if ($product->oldPrice > $product->price) {

            $wcProduct->set_regular_price((string) $product->oldPrice);

            $wcProduct->set_sale_price((string) $product->price);

        } else {

            $wcProduct->set_regular_price((string) $product->price);

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