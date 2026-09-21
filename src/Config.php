<?php

declare(strict_types=1);

/**
 * Loads .env (a plain KEY=VALUE file, no vendor dependency) plus fixed
 * defaults. Values here are the seam between this app and Telemed: api_key
 * must match Telemed's CHIS_SIM_API_KEY, telemed_token must be a Sanctum
 * token Telemed issued via `php artisan chis:issue-token`.
 */
final class Config
{
    private static ?array $values = null;

    public static function load(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        $env = [];
        $envFile = dirname(__DIR__).'/.env';

        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }
                [$key, $value] = explode('=', $line, 2);
                $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"");
            }
        }

        $get = static function (string $key, string $default = '') use ($env): string {
            if (array_key_exists($key, $env)) {
                return $env[$key];
            }

            $fromProcess = getenv($key);

            return $fromProcess !== false ? $fromProcess : $default;
        };

        self::$values = [
            'app_name' => $get('APP_NAME', 'CHIS Simulation'),
            'db_host' => $get('CHIS_DB_HOST', '127.0.0.1'),
            'db_port' => $get('CHIS_DB_PORT', '3306'),
            'db_name' => $get('CHIS_DB_NAME', 'chis_simulation'),
            'db_user' => $get('CHIS_DB_USER', 'root'),
            'db_pass' => $get('CHIS_DB_PASS', ''),
            'api_key' => $get('CHIS_SIM_API_KEY', 'demo-chis-sim-key-change-me'),
            'telemed_base_url' => rtrim($get('TELEMED_BASE_URL', 'http://localhost:8000'), '/'),
            'telemed_token' => $get('TELEMED_TOKEN', ''),
        ];

        return self::$values;
    }
}
