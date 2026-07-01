<?php
/**
 * Plugin Name: Marketplace Bridge
 * Plugin URI: https://github.com/Bacepok/marketplace-bridge
 * Description: Integration of WooCommerce with Ozon and Wildberries.
 * Version: 0.0.1
 * Author: Ирина Барауля
 * License: GPL2+
 * Text Domain: marketplace-bridge
 */

defined('ABSPATH') || exit;

define('MB_VERSION', '0.0.1');
define('MB_PATH', plugin_dir_path(__FILE__));
define('MB_URL', plugin_dir_url(__FILE__));

require_once MB_PATH . 'src/Core/Autoloader.php';

$loader = new MarketplaceBridge\Core\Autoloader();
$loader->register();

$plugin = new MarketplaceBridge\Core\Plugin();
$plugin->boot();