<?php
namespace App\Middleware;

use PDO;

class RateLimitMiddleware
{
    protected PDO $db;
    protected int $limit;
    protected int $window; // seconds

    public function __construct(PDO $db, int $limit = 60, int $window = 60)
    {
        $this->db = $db;
        $this->limit = $limit; // max requests
        $this->window = $window; // per window (e.g., 60s)
    }

    public function handle(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();

        // Create table if not exists
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                ip VARCHAR(45) PRIMARY KEY,
                last_request INT NOT NULL,
                count INT NOT NULL
            )
        ");

        $stmt = $this->db->prepare("SELECT last_request, count FROM rate_limits WHERE ip = :ip");
        $stmt->execute(['ip' => $ip]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $stmt = $this->db->prepare("INSERT INTO rate_limits (ip, last_request, count) VALUES (:ip, :time, 1)");
            $stmt->execute(['ip' => $ip, 'time' => $now]);
            return;
        }

        // Check window
        if ($now - $row['last_request'] > $this->window) {
            // reset counter
            $stmt = $this->db->prepare("UPDATE rate_limits SET count = 1, last_request = :time WHERE ip = :ip");
            $stmt->execute(['ip' => $ip, 'time' => $now]);
            return;
        }

        if ($row['count'] >= $this->limit) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => 'Rate limit exceeded. Try again later.'
            ]);
            exit;
        }

        // Increment count
        $stmt = $this->db->prepare("UPDATE rate_limits SET count = count + 1 WHERE ip = :ip");
        $stmt->execute(['ip' => $ip]);
    }
}
