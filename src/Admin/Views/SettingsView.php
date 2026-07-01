<?php

namespace MarketplaceBridge\Admin\Views;

use MarketplaceBridge\Core\Settings;

defined('ABSPATH') || exit;

class SettingsView
{
    public static function render(): void
    {
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
}