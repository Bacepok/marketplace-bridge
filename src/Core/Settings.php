<?php

namespace MarketplaceBridge\Core;

defined('ABSPATH') || exit;

class Settings
{
    private const OPTION_NAME = 'mb_settings';

    public static function get(string $key, $default = '')
    {
        $settings = get_option(self::OPTION_NAME, []);

        return $settings[$key] ?? $default;
    }

    public static function set(array $values): void
    {
        $settings = get_option(self::OPTION_NAME, []);

        foreach ($values as $key => $value) {
            $settings[$key] = trim($value);
        }

        update_option(self::OPTION_NAME, $settings);
    }
}