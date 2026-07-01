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
    $catalog = [
        'success' => false,
        'items' => [],
        'message' => ''
    ];

    if (isset($_POST['mb_load_catalog'])) {

        check_admin_referer('mb_catalog');

        $service = new ProductService();

        $catalog = $service->getProducts();
}

$details = null;

if (isset($_POST['mb_product_details'])) {

    check_admin_referer('mb_catalog_details');

    $service = new \MarketplaceBridge\Ozon\ProductDetailsService();

    $details = $service->getByProductId(
        (int) $_POST['product_id']
    );
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

        <?php if (!$catalog['success'] && !empty($catalog['message'])) : ?>

            <div class="notice notice-error">

                <p><?php echo esc_html($catalog['message']); ?></p>

            </div>

        <?php endif; ?>

        <?php if (!empty($catalog['items'])) : ?>

            <table class="widefat striped">

                <thead>

                <tr>

                    <th width="120">Offer ID</th>

                    <th width="120">Product ID</th>

                    <th width="80">FBO</th>

                    <th width="80">FBS</th>

                    <th width="120">Архив</th>

                    <th width="120">Действие</th>

                </tr>

                </thead>

                <tbody>

                <?php foreach ($catalog['items'] as $product) : ?>

                    <tr>

                        <td>

                            <?php echo esc_html($product['offer_id']); ?>

                        </td>

                        <td>

                            <?php echo esc_html($product['product_id']); ?>

                        </td>

                        <td>

                            <?php echo $product['has_fbo'] ? '✔' : '—'; ?>

                        </td>

                        <td>

                            <?php echo $product['has_fbs'] ? '✔' : '—'; ?>

                        </td>

                        <td>

                            <?php echo $product['archived'] ? 'Да' : 'Нет'; ?>

                        </td>

                        <td>

                            <form method="post" style="margin:0;">

    <?php wp_nonce_field('mb_catalog_details'); ?>

    <input
        type="hidden"
        name="product_id"
        value="<?php echo (int)$product['product_id']; ?>">

    <button
        class="button button-secondary"
        name="mb_product_details"
        value="1">

        Подробнее

    </button>

</form>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>
<?php if (!empty($details['item'])) : ?>

<hr>

<h2>Карточка товара</h2>

<table class="widefat striped">

    <tbody>

    <tr>

        <th width="250">Название</th>

        <td>

            <?php echo esc_html($details['item']['name']); ?>

        </td>

    </tr>

    <tr>

        <th>Offer ID</th>

        <td>

            <?php echo esc_html($details['item']['offer_id']); ?>

        </td>

    </tr>

    <tr>

        <th>SKU</th>

        <td>

            <?php echo esc_html(
                $details['item']['sources'][0]['sku'] ?? ''
            ); ?>

        </td>

    </tr>

    <tr>

        <th>Цена</th>

        <td>

            <?php echo esc_html($details['item']['price']); ?>

            <?php echo esc_html($details['item']['currency_code']); ?>

        </td>

    </tr>

    <tr>

        <th>Старая цена</th>

        <td>

            <?php echo esc_html($details['item']['old_price']); ?>

        </td>

    </tr>

    <tr>

        <th>Штрихкод</th>

        <td>

            <?php

            echo esc_html(
                $details['item']['barcodes'][0] ?? ''
            );

            ?>

        </td>

    </tr>

    <tr>

        <th>Описание</th>

        <td>

            <?php

            echo wp_kses_post(
                nl2br(
                    esc_html(
                        $details['item']['description']
                    )
                )
            );

            ?>

        </td>

    </tr>

    </tbody>

</table>

<?php endif; ?>
        <?php endif; ?>

    </div>

    <?php
}
}