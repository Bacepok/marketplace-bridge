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

        if (!empty($product->images)) {

            $imageId = $this->importImage($product->images[0]);

            if ($imageId > 0) {
                $wcProduct->set_image_id($imageId);
            }

        }

        /*
         * Сохранение
         */

        return $wcProduct->save();
    }

    private function importImage(string $imageUrl): int
    {
        if (
            !function_exists('media_sideload_image') ||
            !function_exists('attachment_url_to_postid')
        ) {

            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

        }

        $existingId = attachment_url_to_postid($imageUrl);

        if ($existingId) {
            return (int) $existingId;
        }

        $attachmentId = media_sideload_image(
            $imageUrl,
            0,
            null,
            'id'
        );

        if (is_wp_error($attachmentId)) {
            return 0;
        }

        return (int) $attachmentId;
    }
}
