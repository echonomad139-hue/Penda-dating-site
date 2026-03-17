<?php

namespace App\Services;

use PDO;

class LocationService
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate distance using Haversine formula (KM)
     */
    public function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));

        return $earthRadius * $c;
    }

    /**
     * Get nearby users based on profile filters
     */
    public function getNearbyUsers(int $userId): array
    {
        // Get current user's profile
        $stmt = $this->db->prepare("
            SELECT p.*, u.language
            FROM profiles p
            JOIN users u ON u.id = p.user_id
            WHERE p.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $me = $stmt->fetch();

        if (!$me) return [];

        $query = "
            SELECT 
                p.*,
                u.is_online,
                u.last_seen_at,
                (
                    6371 * acos(
                        cos(radians(:lat)) *
                        cos(radians(p.latitude)) *
                        cos(radians(p.longitude) - radians(:lon)) +
                        sin(radians(:lat)) *
                        sin(radians(p.latitude))
                    )
                ) AS distance
            FROM profiles p
            JOIN users u ON u.id = p.user_id
            WHERE p.user_id != :user_id
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'lat' => $me['latitude'],
            'lon' => $me['longitude'],
            'user_id' => $userId
        ]);

        $results = $stmt->fetchAll();

        return $this->applyFilters($results, $me);
    }

    /**
     * Apply match, block, age, gender and distance filters
     */
    private function applyFilters(array $users, array $me): array
    {
        $filtered = [];

        foreach ($users as $user) {

            // Distance filter
            if ($user['distance'] > $me['max_distance']) continue;

            // Gender preference
            if ($me['interested_in'] !== 'both' &&
                $user['gender'] !== $me['interested_in']) {
                continue;
            }

            // Age filter
            $age = $this->calculateAge($user['birthdate']);
            if ($age < $me['min_age'] || $age > $me['max_age']) {
                continue;
            }

            // Block check
            if ($this->isBlocked($me['user_id'], $user['user_id'])) {
                continue;
            }

            $filtered[] = $user;
        }

        // Sort by online first, then distance
        usort($filtered, function ($a, $b) {
            if ($a['is_online'] !== $b['is_online']) {
                return $b['is_online'] <=> $a['is_online'];
            }
            return $a['distance'] <=> $b['distance'];
        });

        return $filtered;
    }

    private function calculateAge(string $birthdate): int
    {
        return (int)date_diff(
            date_create($birthdate),
            date_create('today')
        )->y;
    }

    /**
     * Check if users blocked each other
     */
    private function isBlocked(int $userId, int $otherId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM blocks
            WHERE (blocker_id = :u1 AND blocked_id = :u2)
               OR (blocker_id = :u2 AND blocked_id = :u1)
            LIMIT 1
        ");
        $stmt->execute([
            'u1' => $userId,
            'u2' => $otherId
        ]);

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Premium location boost
     */
    public function boostLocation(int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT plan_type, status
            FROM subscriptions
            WHERE user_id = :user_id
            LIMIT 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $sub = $stmt->fetch();

        if (!$sub || $sub['status'] !== 'active') {
            return false;
        }

        // Increase visibility logic here
        return true;
    }

    /**
     * Travel mode (change location temporarily)
     */
    public function updateTravelLocation(
        int $userId,
        float $lat,
        float $lon,
        string $city,
        string $country
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE profiles
            SET latitude = :lat,
                longitude = :lon,
                city = :city,
                country = :country
            WHERE user_id = :user_id
        ");

        return $stmt->execute([
            'lat' => $lat,
            'lon' => $lon,
            'city' => $city,
            'country' => $country,
            'user_id' => $userId
        ]);
    }
}
