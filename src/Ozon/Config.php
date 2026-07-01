<?php

namespace MarketplaceBridge\Ozon;

use MarketplaceBridge\Core\Settings;

defined('ABSPATH') || exit;

class Config
{
    public static function getClientId(): string
    {
        return (string) Settings::get('ozon_client_id');
    }

    public static function getApiKey(): string
    {
        return (string) Settings::get('ozon_api_key');
    }

    public static function isConfigured(): bool
    {
        return self::getClientId() !== ''
            && self::getApiKey() !== '';
    }
}