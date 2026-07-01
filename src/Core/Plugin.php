<?php

namespace MarketplaceBridge\Core;

defined('ABSPATH') || exit;

class Plugin
{
    public function boot(): void
    {
        $admin = new Admin();

        add_action('admin_menu', [$admin, 'registerMenu']);
    }
}