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

        $productId = $wcProduct->save();

        if ($productId <= 0 || empty($product->images[0])) {
            return $productId;
        }

        $imageId = $this->importImage(
            (string) $product->images[0],
            $productId
        );

        if ($imageId <= 0) {
            return $productId;
        }

        $wcProduct = wc_get_product($productId);

        if (!$wcProduct) {
            return $productId;
        }

        $wcProduct->set_image_id($imageId);

        return $wcProduct->save();
    }

    private function importImage(string $imageUrl, int $productId): int
    {
        $existingId = $this->findExistingImage($imageUrl);

        if ($existingId > 0) {
            return $existingId;
        }

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($imageUrl, 30);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $file = [
            'name' => $this->getImageFileName($imageUrl),
            'tmp_name' => $tmp,
        ];

        $attachmentId = media_handle_sideload(
            $file,
            $productId
        );

        if (is_wp_error($attachmentId)) {
            @unlink($tmp);
            return 0;
        }

        update_post_meta(
            $attachmentId,
            '_mb_source_image_url',
            esc_url_raw($imageUrl)
        );

        return (int) $attachmentId;
    }

    private function findExistingImage(string $imageUrl): int
    {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_mb_source_image_url',
            'meta_value' => esc_url_raw($imageUrl),
        ]);

        if (empty($attachments[0])) {
            return 0;
        }

        return (int) $attachments[0];
    }

    private function getImageFileName(string $imageUrl): string
    {
        $path = (string) parse_url($imageUrl, PHP_URL_PATH);

        $fileName = basename($path);

        if ($fileName === '' || $fileName === '.' || $fileName === '/') {
            $fileName = 'ozon-product-image-' . md5($imageUrl) . '.jpg';
        }

        if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $fileName)) {
            $fileName .= '.jpg';
        }

        return sanitize_file_name($fileName);
    }
}
