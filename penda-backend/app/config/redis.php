<?php

namespace App\Config;

use Redis as NativeRedis;

class Redis
{
    private static ?NativeRedis $connection = null;

    private function __construct() {}

    public static function getConnection(): NativeRedis
    {
        if (self::$connection === null) {

            $redis = new NativeRedis();

            $redis->connect(
                $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                (int) ($_ENV['REDIS_PORT'] ?? 6379)
            );

            if (!empty($_ENV['REDIS_PASSWORD'])) {
                $redis->auth($_ENV['REDIS_PASSWORD']);
            }

            self::$connection = $redis;
        }

        return self::$connection;
    }
}
