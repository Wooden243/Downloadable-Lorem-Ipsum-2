<?php
declare(strict_types=1);

spl_autoload_register(static function (string $className): void {
    $prefix = 'RestaurantMockup\\';
    if (!str_starts_with($className, $prefix)) {
        return;
    }
    $relative = substr($className, strlen($prefix));
    $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
