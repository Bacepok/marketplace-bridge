<?php

namespace MarketplaceBridge\Ozon;

use MarketplaceBridge\Marketplace\Product;

defined('ABSPATH') || exit;

class ImportService
{
    private ProductService $productService;

    private ProductDetailsService $detailsService;

    private ProductMapper $mapper;

    public function __construct()
    {
        $this->productService = new ProductService();

        $this->detailsService = new ProductDetailsService();

        $this->mapper = new ProductMapper();
    }

    /**
     * Получить первый товар из каталога
     * и преобразовать его
     * во внутреннюю модель Marketplace\Product.
     */
    public function getFirstProduct(): ?Product
    {
        $catalog = $this->productService->getProducts(1);

        if (!$catalog['success']) {
            return null;
        }

        if (empty($catalog['items'])) {
            return null;
        }

        $productId = $catalog['items'][0]['product_id'];

        $details = $this->detailsService->getByProductId($productId);

        if (!$details['success']) {
            return null;
        }

        return $this->mapper->map($details['item']);
    }
}