<?php

namespace MarketplaceBridge\Admin;

use MarketplaceBridge\Ozon\ProductDetailsService;
use MarketplaceBridge\Ozon\ProductMapper;
use MarketplaceBridge\Ozon\ProductService;
use MarketplaceBridge\Woo\ProductImporter;

defined('ABSPATH') || exit;

class CatalogController
{
    /**
     * Подготовить данные для страницы каталога.
     */
    public function handle(): array
    {
        $catalog = [
            'success' => false,
            'items'   => [],
            'message' => '',
            'last_id' => '',
        ];

        $details = null;

        $importResult = null;

        if (!current_user_can('manage_options')) {

            return [

                'catalog' => $catalog,

                'details' => $details,

                'import_result' => $importResult,

            ];

        }

        $productId = 0;

        if (isset($_GET['product_id'])) {

            check_admin_referer('mb_product_details', 'mb_product_details_nonce');
            check_admin_referer('mb_product_details');

            $productId = absint($_GET['product_id']);

        }

        if (isset($_POST['mb_import_product'])) {

            check_admin_referer('mb_import_product', 'mb_import_product_nonce');

            $productId = absint($_POST['product_id'] ?? 0);

        }

        $service = new ProductService();

        /*
         * Загрузка каталога
         */

        if (
            isset($_POST['mb_load_catalog']) ||
            isset($_GET['product_id']) ||
            isset($_POST['mb_import_product'])
        ) {

            if (isset($_POST['mb_load_catalog'])) {

                check_admin_referer('mb_catalog');

            }

            $catalog = $service->getProducts();

        }

        /*
         * Карточка товара
         */

        if ($productId > 0) {

            $detailsService = new ProductDetailsService();

            $details = $detailsService->getByProductId(
                $productId
            );

            if (
                isset($_POST['mb_import_product']) &&
                !empty($details['success']) &&
                !empty($details['item'])
            ) {

                $mapper = new ProductMapper();

                $importer = new ProductImporter();

                $wcProductId = $importer->import(
                    $mapper->map($details['item'])
                );

                $importResult = [

                    'success' => $wcProductId > 0,

                    'product_id' => $wcProductId,

                    'message' => $wcProductId > 0
                        ? 'Товар импортирован в WooCommerce.'
                        : 'Не удалось импортировать товар в WooCommerce.',

                ];

            }

        }

        return [

            'catalog' => $catalog,

            'details' => $details,

            'import_result' => $importResult,

        ];
    }
}
