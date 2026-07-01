<?php

namespace MarketplaceBridge\Core;

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
    }

    public function dashboard(): void
    {
        ?>
        <div class="wrap">
            <h1>Marketplace Bridge</h1>

            <div class="card" style="max-width:900px;padding:20px;">

                <h2>Версия <?php echo esc_html(MB_VERSION); ?></h2>

                <p>Плагин успешно установлен.</p>

                <hr>

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

                            if (class_exists('WooCommerce')) {
                                echo esc_html(WC()->version);
                            } else {
                                echo 'Не установлен';
                            }

                            ?>

                        </td>

                    </tr>

                    </tbody>

                </table>

            </div>

        </div>
        <?php
    }
}