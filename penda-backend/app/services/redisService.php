<?php

namespace App\Services;

use Redis;
use Exception;

class RedisService
{
    private ?Redis $redis = null;

    public function __construct()
    {
        $host = $_ENV['REDIS_HOST'] ?? '127.0.0.1';
        $port = (int)($_ENV['REDIS_PORT'] ?? 6379);
        $auth = $_ENV['REDIS_PASSWORD'] ?? null;

        try {
            if (class_exists('Redis')) {
                $this->redis = new Redis();
                $this->redis->connect($host, $port, 2);
                if ($auth) $this->redis->auth($auth);
            } else {
                // Local fallback
                $this->redis = null;
            }
        } catch (Exception $e) {
            // Allow local dev without Redis
            $this->redis = null;
        }
    }

    public function set(string $key, $value, int $ttl = 300): bool
    {
        if (!$this->redis) return false;
        return $this->redis->set($key, serialize($value), $ttl);
    }

    public function get(string $key)
    {
        if (!$this->redis) return null;
        $val = $this->redis->get($key);
        return $val !== false ? unserialize($val) : null;
    }

    public function delete(string $key): bool
    {
        if (!$this->redis) return false;
        return (bool) $this->redis->del($key);
    }

    public function exists(string $key): bool
    {
        if (!$this->redis) return false;
        return $this->redis->exists($key) > 0;
    }
}
