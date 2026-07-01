<?php

namespace MarketplaceBridge\Admin;

use MarketplaceBridge\Core\Settings;
use MarketplaceBridge\Ozon\ConnectionService;

defined('ABSPATH') || exit;

class SettingsController
{
    /**
     * Обработка страницы настроек.
     *
     * @return array
     */
    public function handle(): array
    {
        $result = [
            'saved'   => false,
            'success' => null,
            'message' => '',
        ];

        if (!isset($_POST['mb_save'])) {
            return $result;
        }

        check_admin_referer('mb_settings');

        Settings::set([
            'ozon_client_id' => sanitize_text_field($_POST['ozon_client_id'] ?? ''),
            'ozon_api_key'   => sanitize_text_field($_POST['ozon_api_key'] ?? ''),
        ]);

        $result['saved'] = true;

        if (isset($_POST['mb_test'])) {

            $connection = new ConnectionService();

            $test = $connection->test();

            $result['success'] = $test['success'];

            $result['message'] = $test['message'];
        }

        return $result;
    }
}