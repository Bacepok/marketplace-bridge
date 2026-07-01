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

        if (isset($_GET['product_id'])) {

            $detailsService = new ProductDetailsService();

            $details = $detailsService->getByProductId(
                (int) $_GET['product_id']
            );

        }

        return [

            'catalog' => $catalog,

            'details' => $details,

        ];
    }
}