<?php

declare(strict_types=1);

/**
 * Front controller for PHP's built-in server (`php -S ... public/router.php`).
 * No framework, so routing is a hand-rolled regex table. Real files under
 * public/ (assets/style.css etc.) are served directly by returning false,
 * the documented way to fall through to the built-in server for a static
 * asset.
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$root = __DIR__;

if ($uri !== '/' && file_exists($root.$uri) && ! is_dir($root.$uri)) {
    return false;
}

$routes = [
    '#^/$#' => 'index.php',
];

foreach ($routes as $pattern => $script) {
    if (preg_match($pattern, $uri, $matches)) {
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $_GET[$key] = $value;
            }
        }
        require $root.'/'.$script;

        return true;
    }
}

http_response_code(404);
echo '404 Not Found';

return true;
