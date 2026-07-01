<?php

namespace MarketplaceBridge\Admin;

use MarketplaceBridge\Core\Settings;

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
        if (!empty($_POST['mb_save'])) {

            check_admin_referer('mb_settings');

            Settings::set([
                'ozon_client_id' => sanitize_text_field($_POST['ozon_client_id'] ?? ''),
                'ozon_api_key'   => sanitize_text_field($_POST['ozon_api_key'] ?? '')
            ]);

            echo '<div class="notice notice-success is-dismissible"><p>Настройки сохранены.</p></div>';
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
                                type="text"
                                class="regular-text"
                                name="ozon_client_id"
                                value="<?php echo esc_attr(Settings::get('ozon_client_id')); ?>">

                        </td>

                    </tr>

                    <tr>

                        <th>API Key</th>

                        <td>

                            <input
                                type="password"
                                class="regular-text"
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

                </p>

            </form>

        </div>

        <?php
    }
}