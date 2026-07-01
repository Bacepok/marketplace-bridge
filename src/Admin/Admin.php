<?php

namespace MarketplaceBridge\Admin;

use MarketplaceBridge\Core\Settings;
use MarketplaceBridge\Ozon\ConnectionService;
use MarketplaceBridge\Ozon\ProductService;

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
        ?>

        <div class="wrap">

            <h1>Marketplace Bridge</h1>

            <div class="card" style="max-width:900px;padding:20px;">

                <h2>Версия <?php echo esc_html(MB_VERSION); ?></h2>

                <p>Плагин успешно установлен.</p>

                <table class="widefat striped">

                    <tbody>

                    <tr>
                        <td><strong>WordPress</strong></td>
                        <td><?php echo esc_html(get_bloginfo('version')); ?></td>
                    </tr>

                    <tr>
                        <td><strong>PHP</strong></td>
                        <td><?php echo esc_html(PHP_VERSION); ?></td>
                    </tr>

                    <tr>
                        <td><strong>WooCommerce</strong></td>
                        <td>
                            <?php
                            echo class_exists('WooCommerce')
                                ? esc_html(WC()->version)
                                : 'Не установлен';
                            ?>
                        </td>
                    </tr>

                    </tbody>

                </table>

            </div>

        </div>

        <?php
    }

    public function settings(): void
    {
        if (isset($_POST['mb_save'])) {

            check_admin_referer('mb_settings');

            Settings::set([
                'ozon_client_id' => sanitize_text_field($_POST['ozon_client_id'] ?? ''),
                'ozon_api_key'   => sanitize_text_field($_POST['ozon_api_key'] ?? '')
            ]);

            echo '<div class="notice notice-success"><p>Настройки сохранены.</p></div>';

            if (isset($_POST['mb_test'])) {

                $service = new ConnectionService();

                $result = $service->test();

                printf(
                    '<div class="notice notice-%s"><p>%s</p></div>',
                    $result['success'] ? 'success' : 'error',
                    esc_html($result['message'])
                );
            }
        }

        ?>

        <div class="wrap">

            <h1>Настройки Ozon</h1>

            <form method="post">

                <?php wp_nonce_field('mb_settings'); ?>

                <table class="form-table">

                    <tr>

                        <th>Client ID</th>

                        <td>

                            <input
                                class="regular-text"
                                type="text"
                                name="ozon_client_id"
                                value="<?php echo esc_attr(Settings::get('ozon_client_id')); ?>">

                        </td>

                    </tr>

                    <tr>

                        <th>API Key</th>

                        <td>

                            <input
                                class="regular-text"
                                type="password"
                                name="ozon_api_key"
                                value="<?php echo esc_attr(Settings::get('ozon_api_key')); ?>">

                        </td>

                    </tr>

                </table>

                <p>

                    <button
                        class="button button-primary"
                        name="mb_save"
                        value="1">

                        Сохранить

                    </button>

                    <button
                        class="button"
                        name="mb_test"
                        value="1">

                        Сохранить и проверить

                    </button>

                </p>

            </form>

        </div>

        <?php
    }

    public function catalog(): void
    {
        $products = [];

        if (isset($_POST['mb_load_catalog'])) {

            check_admin_referer('mb_catalog');

            $service = new ProductService();

            $products = $service->getProducts();
        }

        ?>

        <div class="wrap">

            <h1>Каталог Ozon</h1>

            <form method="post">

                <?php wp_nonce_field('mb_catalog'); ?>

                <p>

                    <button
                        class="button button-primary"
                        name="mb_load_catalog"
                        value="1">

                        Получить каталог

                    </button>

                </p>

            </form>

            <?php if (!empty($products)) : ?>

                <table class="widefat striped">

                    <thead>

                    <tr>

                        <th>Offer ID</th>

                        <th>Product ID</th>

                        <th>Название</th>

                        <th>Статус</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($products as $product) : ?>

                        <tr>

                            <td><?php echo esc_html($product['offer_id']); ?></td>

                            <td><?php echo esc_html($product['product_id']); ?></td>

                            <td><?php echo esc_html($product['name']); ?></td>

                            <td><?php echo esc_html($product['status']); ?></td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

        <?php
    }
}