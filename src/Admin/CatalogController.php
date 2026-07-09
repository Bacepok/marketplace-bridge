<?php

namespace MarketplaceBridge\Admin;

use MarketplaceBridge\Ozon\ProductDetailsService;
use MarketplaceBridge\Ozon\ProductService;

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

        if (!current_user_can('manage_options')) {

            return [

                'catalog' => $catalog,

                'details' => $details,

            ];

        }

        $productId = 0;

        if (isset($_GET['product_id'])) {

            check_admin_referer('mb_product_details');

            $productId = absint($_GET['product_id']);

        }

        $service = new ProductService();

        /*
         * Загрузка каталога
         */

        if (
            isset($_POST['mb_load_catalog']) ||
            isset($_GET['product_id'])
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

        }

        return [

            'catalog' => $catalog,

            'details' => $details,

        ];
    }
}