<?php

namespace App\Routes;

use PDO;

class RouteDispatcher
{
    public static function dispatch(string $method, string $uri, array $data, PDO $db): array
    {

        $GLOBALS['db'] = $db;
        $GLOBALS['data'] = $data;

        ob_start();

        require BASE_PATH . '/app/routes/auth.routes.php';
        require BASE_PATH . '/app/routes/profile.routes.php';
        require BASE_PATH . '/app/routes/photo.routes.php';
        require BASE_PATH . '/app/routes/swipe.routes.php';
        require BASE_PATH . '/app/routes/match.routes.php';
        require BASE_PATH . '/app/routes/message.routes.php';

        $output = ob_get_clean();

        if ($output) {
            return json_decode($output, true);
        }

        return [
            "success" => false,
            "message" => "Route $uri not found"
        ];
    }
}