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

        if ($product->manageStock) {

            $wcProduct->set_manage_stock(true);

            $wcProduct->set_stock_quantity($product->stockQuantity);

            $wcProduct->set_stock_status(
                $product->stockQuantity > 0
                    ? 'instock'
                    : 'outofstock'
            );

        } else {

            $wcProduct->set_manage_stock(false);

            $wcProduct->set_stock_status(
                $product->archived
                    ? 'outofstock'
                    : 'instock'
            );

        }

        $attributes = $this->buildAttributes($product->attributes);

        if (!empty($attributes)) {
            $wcProduct->set_attributes($attributes);
        }

        /*
         * Сохранение
         */

        $productId = $wcProduct->save();

        if ($productId > 0 && $product->marketplaceUrl !== '') {
            update_post_meta(
                $productId,
                'url_ozon',
                esc_url_raw($product->marketplaceUrl)
            );
        }

        $imageUrls = $this->getImageUrls($product->images);

        if ($productId <= 0 || empty($imageUrls)) {
            return $productId;
        }

        $imageIds = [];

        foreach ($imageUrls as $imageUrl) {

            $imageId = $this->importImage(
                $imageUrl,
                $productId
            );

            if ($imageId > 0) {
                $imageIds[] = $imageId;
            }

        }

        if (empty($imageIds)) {
            return $productId;
        }

        $wcProduct = wc_get_product($productId);

        if (!$wcProduct) {
            return $productId;
        }

        $wcProduct->set_image_id($imageIds[0]);

        if (count($imageIds) > 1) {
            $wcProduct->set_gallery_image_ids(array_slice($imageIds, 1));
        }

        return $wcProduct->save();
    }

    private function getImageUrls(array $images): array
    {
        $urls = [];

        foreach ($images as $image) {

            if (is_string($image) && $image !== '') {
                $urls[] = $image;
            }

        }

        return array_values(array_unique($urls));
    }

    private function buildAttributes(array $attributes): array
    {
        $wcAttributes = [];

        $position = 0;

        foreach ($attributes as $attribute) {

            if (!is_array($attribute)) {
                continue;
            }

            $name = $this->getAttributeName($attribute);

            $values = $this->getAttributeValues($attribute);

            if ($name === '' || empty($values)) {
                continue;
            }

            $wcAttribute = new \WC_Product_Attribute();

            $wcAttribute->set_id(0);
            $wcAttribute->set_name($name);
            $wcAttribute->set_options($values);
            $wcAttribute->set_position($position);
            $wcAttribute->set_visible(true);
            $wcAttribute->set_variation(false);

            $wcAttributes[] = $wcAttribute;

            $position++;

        }

        return $wcAttributes;
    }

    private function getAttributeName(array $attribute): string
    {
        return trim((string) (
            $attribute['attribute_name']
            ?? $attribute['name']
            ?? $attribute['title']
            ?? $attribute['id']
            ?? ''
        ));
    }

    private function getAttributeValues(array $attribute): array
    {
        $values = [];

        if (!empty($attribute['values']) && is_array($attribute['values'])) {

            foreach ($attribute['values'] as $value) {

                $normalized = $this->normalizeAttributeValue($value);

                if ($normalized !== '') {
                    $values[] = $normalized;
                }

            }

        } else {

            $normalized = $this->normalizeAttributeValue(
                $attribute['value']
                ?? $attribute['value_name']
                ?? ''
            );

            if ($normalized !== '') {
                $values[] = $normalized;
            }

        }

        return array_values(array_unique($values));
    }

    private function normalizeAttributeValue($value): string
    {
        if (is_scalar($value)) {
            return trim((string) $value);
        }

        if (!is_array($value)) {
            return '';
        }

        return trim((string) (
            $value['value']
            ?? $value['value_name']
            ?? $value['name']
            ?? ''
        ));
    }

    private function importImage(string $imageUrl, int $productId = 0): int
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
