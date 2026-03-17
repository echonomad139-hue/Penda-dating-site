<?php

namespace App\Middleware;

use PDO;

class ValidationMiddleware
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Validate request payload against DB table columns
     */
    public function validateTable(array $data, string $table, array $requiredColumns = []): bool
    {
        if (empty($requiredColumns)) {
            $stmt = $this->db->prepare("DESCRIBE {$table}");
            $stmt->execute();
            $columns = array_map(fn($col) => $col['Field'], $stmt->fetchAll(PDO::FETCH_ASSOC));
            $requiredColumns = $columns;
        }

        foreach ($requiredColumns as $column) {
            if (!array_key_exists($column, $data)) {
                throw new \Exception("Validation failed: missing {$column} for table {$table}");
            }
        }

        return true;
    }

    /**
 * Validate register request
 */
public function validateRegister(array $data): bool
{
    if (empty($data['phone'])) {
        throw new \Exception("Phone is required");
    }

    if (!preg_match('/^(?:\+254|254|0)?7[0-9]{8}$/', $data['phone'])) {
        throw new \Exception("Invalid Kenyan phone number");
    }

    if (empty($data['password'])) {
        throw new \Exception("Password is required");
    }

    if (strlen($data['password']) < 6) {
        throw new \Exception("Password must be at least 6 characters");
    }

    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new \Exception("Invalid email address");
    }

    return true;
}
    /**
     * Specific validation for user registration
     */
    public function validateUser(array $data): bool
    {
        return $this->validateTable($data, 'users', [
            'phone', 'password_hash', 'email'
        ]);
    }

    /**
     * Specific validation for profile
     */
    public function validateProfile(array $data): bool
    {
        return $this->validateTable($data, 'profiles', [
            'user_id', 'display_name', 'date_of_birth', 'gender'
        ]);
    }

    /**
     * Validate interaction payload
     */
    public function validateInteraction(array $data): bool
    {
        return $this->validateTable($data, 'interactions', [
            'liker_id', 'liked_id', 'type'
        ]);
    }

    /**
     * Validate message payload
     */
    public function validateMessage(array $data): bool
    {
        return $this->validateTable($data, 'messages', [
            'match_id', 'sender_id', 'body'
        ]);
    }

    /**
     * Validate payment payload
     */
    public function validatePayment(array $data): bool
    {
        return $this->validateTable($data, 'payments', [
            'user_id', 'provider', 'transaction_id', 'amount'
        ]);
    }

    /**
     * Validate report/block payload
     */
    public function validateReport(array $data): bool
    {
        return $this->validateTable($data, 'reports', [
            'reporter_id', 'reported_id', 'reason'
        ]);
    }

    /**
     * Validate subscription payload
     */
    public function validateSubscription(array $data): bool
    {
        return $this->validateTable($data, 'subscriptions', [
            'user_id', 'plan', 'start_date', 'end_date', 'status'
        ]);
    }

    /**
     * Validate user settings payload
     */
    public function validateUserSettings(array $data): bool
    {
        return $this->validateTable($data, 'user_settings', [
            'user_id', 'language', 'notifications_enabled', 'show_online_status', 'show_distance'
        ]);
    }
}
