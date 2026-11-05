<?php

declare(strict_types=1);

/**
 * Application bootstrap: autoloader + shared services.
 * Every entry point in public/ (and bin/) requires this file.
 */

use OJStats\App;

const OJ_ROOT = __DIR__ . '/..';

spl_autoload_register(static function (string $class): void {
    $prefix = 'OJStats\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

return App::boot(OJ_ROOT);
