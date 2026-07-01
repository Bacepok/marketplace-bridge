<?php

namespace MarketplaceBridge\Marketplace;

defined('ABSPATH') || exit;

class Product
{
    public int $marketplaceId = 0;

    public string $offerId = '';

    public string $sku = '';

    public string $barcode = '';

    public string $name = '';

    public string $description = '';

    public float $price = 0;

    public float $oldPrice = 0;

    public string $currency = 'RUB';

    public array $images = [];

    public array $attributes = [];

    public bool $archived = false;
}