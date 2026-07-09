<?php

namespace MarketplaceBridge\Admin\Views;

defined('ABSPATH') || exit;

class CatalogView
{
    /**
     * Отрисовка каталога Ozon.
     */
    public static function render(array $catalog, ?array $details = null): void
    {
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

                        <th width="140">Offer ID</th>

                        <th width="120">Product ID</th>

                        <th width="60">FBO</th>

                        <th width="60">FBS</th>

                        <th width="100">Статус</th>

                        <th width="80">Архив</th>

                        <th width="180">Действия</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($catalog['items'] as $product) : ?>

                        <?php

                        $detailsUrl = admin_url(
                            'admin.php?page=marketplace-bridge-ozon&product_id=' .
                            (int) $product['product_id']
                        );

                        $detailsUrl = wp_nonce_url(
                            $detailsUrl,
                            'mb_product_details'
                        );

                        ?>

                        <tr>

                            <td>

                                <?php echo esc_html($product['offer_id']); ?>

                            </td>

                            <td>

                                <?php echo (int) $product['product_id']; ?>

                            </td>

                            <td>

                                <?php echo $product['has_fbo'] ? '✔' : '—'; ?>

                            </td>

                            <td>

                                <?php echo $product['has_fbs'] ? '✔' : '—'; ?>

                            </td>

                            <td>

                                <?php echo esc_html($product['status'] ?? ''); ?>

                            </td>

                            <td>

                                <?php echo $product['archived'] ? 'Да' : 'Нет'; ?>

                            </td>

                            <td>

                                <a
                                    class="button button-secondary"
                                    href="<?php echo esc_url($detailsUrl); ?>">

                                    Подробнее

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <?php if (!empty($catalog['last_id'])) : ?>

                    <form method="post" style="margin-top:15px;">

                        <?php wp_nonce_field('mb_catalog'); ?>

                        <input
                            type="hidden"
                            name="last_id"
                            value="<?php echo esc_attr($catalog['last_id']); ?>">

                        <button
                            class="button"
                            name="mb_load_catalog"
                            value="1">

                            Следующая страница

                        </button>

                    </form>

                <?php endif; ?>

            <?php endif; ?>

        </div>

        <?php

    }
}
