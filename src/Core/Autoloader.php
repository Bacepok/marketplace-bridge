<?php

namespace MarketplaceBridge\Core;

defined('ABSPATH') || exit;

class Autoloader
{
    public function register(): void
    {
        spl_autoload_register([$this, 'autoload']);
    }

    private function autoload(string $class): void
    {
        $prefix = 'MarketplaceBridge\\';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));

        $file = MB_PATH . 'src/' . str_replace('\\', '/', $relative) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}