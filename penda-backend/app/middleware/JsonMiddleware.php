<?php

namespace App\Middleware;

use PDO;

class JsonMiddleware
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Validate JSON request
     */
    public function handle(): array
    {
        $input = file_get_contents('php://input');

        // Allow empty body for GET
        if (empty($input)) {
            return [];
        }

        // Ensure Content-Type is application/json
        if (
            isset($_SERVER['CONTENT_TYPE']) &&
            !str_contains($_SERVER['CONTENT_TYPE'], 'application/json')
        ) {
            throw new \Exception("Content-Type must be application/json");
        }

        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON: " . json_last_error_msg());
        }

        return $this->sanitize($data);
    }

    /**
     * Sanitize input
     */
    public function sanitize(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            return $value;
        }, $data);
    }

    /**
     * Validate required fields
     */
    public function validateRequiredFields(array $data, array $requiredColumns): bool
    {
        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $data)) {
                throw new \Exception("Missing required field: {$column}");
            }
        }

        return true;
    }

    /**
     * Example: registration validation
     */
    public function validateUserRegistration(array $data): bool
    {
        $this->validateRequiredFields($data, ['phone', 'password_hash']);

        if (!preg_match('/^\+?[0-9]{10,15}$/', $data['phone'])) {
            throw new \Exception("Invalid phone number format");
        }

        if (strlen($data['password_hash']) < 60) {
            throw new \Exception("Invalid password hash");
        }

        return true;
    }
}
