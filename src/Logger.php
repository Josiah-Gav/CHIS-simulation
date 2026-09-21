<?php

declare(strict_types=1);

/**
 * Records every inbound call Telemed makes into this app's API and every
 * outbound call this app makes into Telemed, so the Activity Log page can
 * show the two-way exchange actually happening, not just claim it does.
 */
final class Logger
{
    public function __construct(private readonly PDO $pdo) {}

    public function log(string $direction, string $method, string $path, ?int $statusCode, ?string $detail = null, ?string $remote = null): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO activity_log (direction, method, path, remote, status_code, detail) VALUES (:direction, :method, :path, :remote, :status_code, :detail)'
        );
        $stmt->execute([
            'direction' => $direction,
            'method' => $method,
            'path' => $path,
            'remote' => $remote,
            'status_code' => $statusCode,
            'detail' => $detail,
        ]);
    }

    public function recent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM activity_log ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
