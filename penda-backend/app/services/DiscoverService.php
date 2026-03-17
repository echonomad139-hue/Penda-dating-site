<?php

namespace App\Services;

use PDO;

class DiscoverService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | GET DISCOVERY FEED
    |--------------------------------------------------------------------------
    */
    public function getFeed(int $userId, int $limit = 20): array
    {
        /*
         users:
            id, phone, email, password_hash, is_verified, is_active, role, created_at

         profiles:
            user_id, first_name, last_name, bio, gender, date_of_birth, location

         photos:
            id, user_id, url, is_primary

         interactions:
            from_user_id, to_user_id, type

         matches:
            user_one_id, user_two_id, is_active

         blocks:
            blocker_id, blocked_id

         subscriptions:
            user_id, plan_type, start_date, end_date, status
        */

        $sql = "
            SELECT 
                u.id,
                p.first_name,
                p.last_name,
                p.bio,
                p.gender,
                p.date_of_birth,
                p.location,
                ph.url AS primary_photo,
                s.plan_type,
                s.status AS subscription_status
            FROM users u

            JOIN profiles p ON p.user_id = u.id

            LEFT JOIN photos ph 
                ON ph.user_id = u.id AND ph.is_primary = 1

            LEFT JOIN subscriptions s
                ON s.user_id = u.id AND s.status = 'active'

            WHERE u.id != :user_id
            AND u.is_active = 1
            AND u.is_verified = 1

            -- Not already interacted
            AND u.id NOT IN (
                SELECT to_user_id FROM interactions
                WHERE from_user_id = :user_id
            )

            -- Not already matched
            AND u.id NOT IN (
                SELECT 
                    CASE
                        WHEN user_one_id = :user_id THEN user_two_id
                        ELSE user_one_id
                    END
                FROM matches
                WHERE (user_one_id = :user_id OR user_two_id = :user_id)
                AND is_active = 1
            )

            -- Not blocked either way
            AND u.id NOT IN (
                SELECT blocked_id FROM blocks WHERE blocker_id = :user_id
            )
            AND u.id NOT IN (
                SELECT blocker_id FROM blocks WHERE blocked_id = :user_id
            )

            ORDER BY 
                CASE WHEN s.plan_type = 'premium' THEN 1 ELSE 2 END,
                u.created_at DESC

            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
