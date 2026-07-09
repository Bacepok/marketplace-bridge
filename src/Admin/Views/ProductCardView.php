<?php

namespace MarketplaceBridge\Admin\Views;

defined('ABSPATH') || exit;

class ProductCardView
{
    /**
     * Отрисовка карточки товара Ozon.
     */
    public static function render(array $item, ?array $importResult = null): void
    {
        ?>

        <hr style="margin:30px 0;">

        <h2>Карточка товара</h2>

        <?php if ($importResult !== null) : ?>

            <div class="notice notice-<?php echo $importResult['success'] ? 'success' : 'error'; ?>">

                <p><?php echo esc_html($importResult['message']); ?></p>

                <?php if (!empty($importResult['product_id'])) : ?>

                    <p>WooCommerce Product ID: <?php echo (int) $importResult['product_id']; ?></p>

                <?php endif; ?>

            </div>

        <?php endif; ?>

        <form method="post" style="margin:20px 0;">

            <?php wp_nonce_field('mb_import_product', 'mb_import_product_nonce'); ?>

            <input
                type="hidden"
                name="product_id"
                value="<?php echo (int) ($item['id'] ?? 0); ?>">

            <button
                class="button button-primary"
                name="mb_import_product"
                value="1">

                Импортировать в WooCommerce

            </button>

        </form>

        <table class="widefat striped">

            <tbody>

            <tr>

                <th width="220">Название</th>

                <td><?php echo esc_html($item['name'] ?? ''); ?></td>

            </tr>

            <tr>

                <th>Offer ID</th>

                <td><?php echo esc_html($item['offer_id'] ?? ''); ?></td>

            </tr>

            <tr>

                <th>Product ID</th>

                <td><?php echo (int)($item['id'] ?? 0); ?></td>

            </tr>

            <tr>

                <th>SKU</th>

                <td>

                    <?php
                    echo esc_html(
                        $item['sources'][0]['sku'] ?? ''
                    );
                    ?>

                </td>

            </tr>

            <tr>

                <th>Штрихкод</th>

                <td>

                    <?php
                    echo esc_html(
                        $item['barcodes'][0] ?? ''
                    );
                    ?>

                </td>

            </tr>

            <tr>

                <th>Цена</th>

                <td>

                    <?php echo esc_html($item['price'] ?? ''); ?>

                    <?php echo esc_html($item['currency_code'] ?? ''); ?>

                </td>

            </tr>

            <tr>

                <th>Старая цена</th>

                <td>

                    <?php echo esc_html($item['old_price'] ?? ''); ?>

                </td>

            </tr>

            <tr>

                <th>Создан</th>

                <td>

                    <?php echo esc_html($item['created_at'] ?? ''); ?>

                </td>

            </tr>

            <tr>

                <th>Описание</th>

                <td>

                    <?php

                    echo wp_kses_post(
                        nl2br(
                            esc_html(
                                $item['description'] ?? ''
                            )
                        )
                    );

                    ?>

                </td>

            </tr>

            </tbody>

        </table>

        <?php if (!empty($item['images'])) : ?>

            <h3 style="margin-top:30px;">Изображения</h3>

            <div style="display:flex;gap:15px;flex-wrap:wrap;">

                <?php foreach ($item['images'] as $image) : ?>

                    <img
                        src="<?php echo esc_url($image); ?>"
                        alt=""
                        style="
                            width:120px;
                            height:120px;
                            object-fit:contain;
                            border:1px solid #ccd0d4;
                            background:#fff;
                            padding:5px;
                        ">

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <?php
    }
}
