<?php

namespace MarketplaceBridge\Admin;

use MarketplaceBridge\Admin\Views\CatalogView;
use MarketplaceBridge\Admin\Views\DashboardView;
use MarketplaceBridge\Admin\Views\ProductCardView;
use MarketplaceBridge\Admin\Views\SettingsView;

defined('ABSPATH') || exit;

class Admin
{
    public function registerMenu(): void
    {
        add_menu_page(
            'Marketplace Bridge',
            'Marketplace Bridge',
            'manage_options',
            'marketplace-bridge',
            [$this, 'dashboard'],
            'dashicons-store',
            56
        );

        add_submenu_page(
            'marketplace-bridge',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'marketplace-bridge',
            [$this, 'dashboard']
        );

        add_submenu_page(
            'marketplace-bridge',
            'Settings',
            'Settings',
            'manage_options',
            'marketplace-bridge-settings',
            [$this, 'settings']
        );

        add_submenu_page(
            'marketplace-bridge',
            'Каталог Ozon',
            'Каталог Ozon',
            'manage_options',
            'marketplace-bridge-ozon',
            [$this, 'catalog']
        );
    }
    public function dashboard(): void
{
    DashboardView::render();
}

public function settings(): void
{
    $controller = new SettingsController();

    $result = $controller->handle();

    SettingsView::render($result);
}