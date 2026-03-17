<?php

namespace App\Controllers;

use PDO;
use Exception;

class ProfileController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getProfile(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
u.id,
u.name,
u.phone,
u.email,
p.display_name,
p.bio,
p.city,
p.country
FROM users u
LEFT JOIN profiles p ON u.id = p.user_id
WHERE u.id = ?
        ");

        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $photosStmt = $this->db->prepare("
            SELECT id,url,is_primary,order_index
            FROM photos
            WHERE user_id=?
            ORDER BY order_index ASC
        ");

        $photosStmt->execute([$userId]);
        $photos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "success"=>true,
            "data"=>$profile,
            "photos"=>$photos
        ];
    }

    public function setupProfile(array $data): array
    {
        try {

            $this->db->beginTransaction();

            $userId = $data['user_id'];

            $display_name = $data['display_name'] ?? null;
            $bio = $data['bio'] ?? null;
            $gender = $data['gender'] ?? null;
            $date_of_birth = $data['date_of_birth'] ?? null;
            $city = $data['city'] ?? null;
            $country = $data['country'] ?? "Kenya";
            $religion = $data['religion'] ?? null;
            $tribe = $data['tribe'] ?? null;
            $relationship_intent = $data['relationship_intent'] ?? null;

            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            $photos = $data['photos'] ?? [];

            // check profile
            $check = $this->db->prepare("SELECT user_id FROM profiles WHERE user_id=?");
            $check->execute([$userId]);

            if($check->rowCount() == 0){

                $stmt = $this->db->prepare("
                    INSERT INTO profiles
                    (user_id,display_name,date_of_birth,gender,bio,religion,tribe,
                    relationship_intent,latitude,longitude,city,country,is_premium,created_at,updated_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,NOW(),NOW())
                ");

                $stmt->execute([
                    $userId,
                    $display_name,
                    $date_of_birth,
                    $gender,
                    $bio,
                    $religion,
                    $tribe,
                    $relationship_intent,
                    $latitude,
                    $longitude,
                    $city,
                    $country
                ]);

            } else {

                $stmt = $this->db->prepare("
                    UPDATE profiles SET
                    display_name=?,
                    date_of_birth=?,
                    gender=?,
                    bio=?,
                    religion=?,
                    tribe=?,
                    relationship_intent=?,
                    latitude=?,
                    longitude=?,
                    city=?,
                    country=?,
                    updated_at=NOW()
                    WHERE user_id=?
                ");

                $stmt->execute([
                    $display_name,
                    $date_of_birth,
                    $gender,
                    $bio,
                    $religion,
                    $tribe,
                    $relationship_intent,
                    $latitude,
                    $longitude,
                    $city,
                    $country,
                    $userId
                ]);

            }

            // SAVE PHOTOS
            if(!empty($photos)){

                $order = 0;

                foreach($photos as $photo){
                    if(empty($photo)){
                        continue; // skip empty entries
                    }
                    $stmt = $this->db->prepare("
                        INSERT INTO photos
                        (user_id,url,is_primary,is_verified,order_index,created_at)
                        VALUES (?,?,?,?,?,NOW())
                    ");

                    $stmt->execute([
                        $userId,
                        $photo,
                        $order === 0 ? 1 : 0,
                        0,
                        $order
                    ]);

                    $order++;
                }
            }

            $this->db->commit();

            return [
                "success"=>true,
                "message"=>"Profile setup completed"
            ];

        } catch(Exception $e){

            $this->db->rollBack();

            return [
                "success"=>false,
                "message"=>$e->getMessage()
            ];
        }
    }
}