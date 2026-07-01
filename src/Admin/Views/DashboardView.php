<?php

namespace MarketplaceBridge\Admin\Views;

defined('ABSPATH') || exit;

class DashboardView
{
    public static function render(): void
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
}