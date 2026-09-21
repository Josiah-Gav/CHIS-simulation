<?php

declare(strict_types=1);

/**
 * Checks the shared-secret Authorization: Bearer header on this app's own
 * API. Telemed's future RealChisClient must send CHIS_SIM_API_KEY here,
 * mirroring how Telemed itself requires a Sanctum ability token from CHIS
 * on its own encounter-summary endpoint — both directions of the exchange
 * are authenticated, not just one.
 */
final class Auth
{
    public static function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if ($header === '' && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $header = $headers['Authorization'] ?? '';
        }

        if (! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        return substr($header, 7);
    }

    public static function isAuthorized(array $config): bool
    {
        $token = self::bearerToken();

        return $token !== null && hash_equals($config['api_key'], $token);
    }
}
