<?php

namespace App\Services;

use PDO;

class TranslationService
{
    protected PDO $db;

    protected array $translations = [
        'en' => [
            'match' => "It's a Match!",
            'new_message' => "You received a new message.",
            'welcome' => "Welcome to Penda",
        ],
        'sw' => [
            'match' => "Mmefanana!",
            'new_message' => "Umepokea ujumbe mpya.",
            'welcome' => "Karibu Penda",
        ]
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get user language
     */
    public function getUserLanguage(int $userId): string
    {
        $stmt = $this->db->prepare("
            SELECT language
            FROM users
            WHERE id = :id
        ");
        $stmt->execute(['id' => $userId]);

        $lang = $stmt->fetchColumn();

        return $lang ?: 'en';
    }

    /**
     * Translate key based on user language
     */
    public function translate(int $userId, string $key): string
    {
        $language = $this->getUserLanguage($userId);

        return $this->translations[$language][$key]
            ?? $this->translations['en'][$key]
            ?? $key;
    }

    /**
     * Translate custom text (basic demo AI-style placeholder)
     */
    public function translateText(
        string $text,
        string $from = 'en',
        string $to = 'sw'
    ): string {

        // Here you'd integrate:
        // Google Translate API / DeepL etc

        return "[{$to}] " . $text;
    }

    /**
     * Change user language preference
     */
    public function updateLanguage(int $userId, string $language): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET language = :language
            WHERE id = :id
        ");

        return $stmt->execute([
            'language' => $language,
            'id' => $userId
        ]);
    }

    /**
     * Translate message automatically before saving
     */
    public function autoTranslateMessage(
        int $senderId,
        int $receiverId,
        string $message
    ): string {

        $senderLang = $this->getUserLanguage($senderId);
        $receiverLang = $this->getUserLanguage($receiverId);

        if ($senderLang === $receiverLang) {
            return $message;
        }

        return $this->translateText($message, $senderLang, $receiverLang);
    }
}
