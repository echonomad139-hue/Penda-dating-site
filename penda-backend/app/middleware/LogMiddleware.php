<?php

namespace App\Middleware;

use PDO;
use Throwable;

class LogMiddleware
{
    protected PDO $db;
    protected string $logDir;

    public function __construct(PDO $db, string $logDir = __DIR__ . '/../../storage/logs/')
    {
        $this->db = $db;
        $this->logDir = rtrim($logDir, '/') . '/';
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    /**
     * Log request and response
     */
    public function logRequest(array $request, array $response = [], ?int $userId = null): void
    {
        $entry = [
            'timestamp' => date('c'),
            'user_id'   => $userId,
            'endpoint'  => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method'    => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'request'   => $request,
            'response'  => $response,
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        // Save to DB
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (user_id, endpoint, method, request, response, created_at)
            VALUES (:user_id, :endpoint, :method, :request, :response, NOW())
        ");
        $stmt->execute([
            'user_id'  => $entry['user_id'],
            'endpoint' => $entry['endpoint'],
            'method'   => $entry['method'],
            'request'  => json_encode($entry['request'], JSON_UNESCAPED_UNICODE),
            'response' => json_encode($entry['response'], JSON_UNESCAPED_UNICODE),
        ]);

        // Write to file (structured JSON)
        $this->writeToFile($entry);
    }

    /**
     * Log exceptions
     */
    public function logError(Throwable $e, ?int $userId = null): void
    {
        $entry = [
            'timestamp' => date('c'),
            'user_id'   => $userId,
            'endpoint'  => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'method'    => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'request'   => json_decode(file_get_contents('php://input'), true) ?: [],
            'error'     => [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString()
            ],
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        // Save to DB
        $stmt = $this->db->prepare("
            INSERT INTO audit_logs (user_id, endpoint, method, request, response, created_at)
            VALUES (:user_id, :endpoint, :method, :request, :response, NOW())
        ");
        $stmt->execute([
            'user_id'  => $entry['user_id'],
            'endpoint' => $entry['endpoint'],
            'method'   => $entry['method'],
            'request'  => json_encode($entry['request'], JSON_UNESCAPED_UNICODE),
            'response' => json_encode($entry['error'], JSON_UNESCAPED_UNICODE),
        ]);

        $this->writeToFile($entry);
    }

    /**
     * Write structured JSON logs to daily rotating file
     */
    private function writeToFile(array $entry): void
    {
        $filename = $this->logDir . 'app-' . date('Y-m-d') . '.log';
        $jsonLine = json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents($filename, $jsonLine, FILE_APPEND);
    }

    /**
     * Cleanup old log files beyond CRON_LOG_PRUNE_DAYS
     */
    public function pruneOldLogs(): void
    {
        $days = (int)($_ENV['CRON_LOG_PRUNE_DAYS'] ?? 30);
        $files = glob($this->logDir . 'app-*.log');

        $threshold = time() - ($days * 86400);

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
            }
        }

        // Optional: cleanup old DB logs
        $stmt = $this->db->prepare("
            DELETE FROM audit_logs
            WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        $stmt->execute(['days' => $days]);
    }
}
