<?php

namespace App\Controllers;

use PDO;

class DiscoverController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // --- Feed method ---
   public function getFeed(int $userId, int $limit = 20): array
{
    $limit = (int) $limit; // always cast to int for safety

    $sql = "
SELECT 
    u.id,
    u.display_name AS name,
    p.bio,
    p.gender,
    p.date_of_birth,
    p.country AS location,
    ph.url AS primary_photo
FROM users u
JOIN profiles p ON u.id = p.user_id
LEFT JOIN photos ph 
    ON ph.user_id = u.id 
    AND ph.is_primary = 1
WHERE u.id != :user_id
AND u.is_active = 1

AND u.id NOT IN (
    SELECT liked_id
    FROM interactions
    WHERE liker_id = :interact_user
)

AND u.id NOT IN (
    SELECT user2_id
    FROM matches
    WHERE user1_id = :match_user1

    UNION

    SELECT user1_id
    FROM matches
    WHERE user2_id = :match_user2
)

AND u.id NOT IN (
    SELECT blocked_id
    FROM blocks
    WHERE blocker_id = :block_user
)

ORDER BY u.created_at DESC
LIMIT $limit
";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':interact_user', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':match_user1', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':match_user2', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':block_user', $userId, PDO::PARAM_INT);

    $stmt->execute();

    return [
        'status' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ];
}

    // --- Nearby method ---
    public function getNearby(int $userId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        try {
            $locationService = new \App\Services\LocationService($this->db);
            $nearbyUsers = $locationService->getNearbyUsers($userId);

            // Apply frontend filters if any
            if (!empty($filters)) {
                $ageRange = $filters['ageRange'] ?? [18, 50];
                $distanceLimit = $filters['distance'] ?? 50;
                $gender = $filters['gender'] ?? 'All';

                $nearbyUsers = array_filter($nearbyUsers, function($user) use ($ageRange, $distanceLimit, $gender) {
                    if ($user['distance'] > $distanceLimit) return false;
                    if ($gender !== 'All' && $user['gender'] !== $gender) return false;
                    return true;
                });
            }

            // Pagination
            $offset = ($page - 1) * $perPage;
            $paginated = array_slice($nearbyUsers, $offset, $perPage);

            return [
                'success' => true,
                'users' => array_values($paginated),
                'page' => $page,
                'perPage' => $perPage,
                'total' => count($nearbyUsers)
            ];

        } catch (\Exception $e) {
            error_log('Nearby API error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Server error fetching nearby users',
                'error' => $e->getMessage()
            ];
        }
    }
}