<?php
// File: /home/tapsndrc/common_lib/core/GroupDetector.php

class GroupDetector
{
    private $pdo;

    public function __construct()
    {
        // Load configurations
        require_once __DIR__ . '/../config/db_config.php';
        require_once __DIR__ . '/../config/app_config.php';

        // Set timezone
        date_default_timezone_set(APP_TIMEZONE);

        // Connect to database using PDO
        try {
            $dsn       = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection error: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function getDomainInfo($uri)
    {
        try {
            $slug = ltrim(parse_url($uri, PHP_URL_PATH), '/');

            $stmt = $this->pdo->prepare('
                SELECT * FROM form_domains
                WHERE vendor_code = ? OR domain = ?
                LIMIT 1
            ');

            $stmt->execute([$slug, $slug . '.tapsndr.com']);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log('Error in getDomainInfo: ' . $e->getMessage());
            return null;
        }
    }

    public function getAllActiveDomains()
    {
        try {
            $stmt = $this->pdo->query('
                SELECT * FROM form_domains
                ORDER BY domain
            ');

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log('Error in getAllActiveDomains: ' . $e->getMessage());
            return [];
        }
    }

    public function createDomain($domain, $groupName, $telegramChatId)
    {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO form_domains (domain, group_name, telegram_chat_id, active, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ');

            return $stmt->execute([$domain, $groupName, $telegramChatId]);

        } catch (PDOException $e) {
            error_log('Error in createDomain: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDomain($domainId, $data)
    {
        try {
            $sets   = [];
            $params = [];

            foreach ($data as $key => $value) {
                $sets[]   = "{$key} = ?";
                $params[] = $value;
            }

            // Add domain_id to params
            $params[] = $domainId;

            $sql  = "UPDATE form_domains SET " . implode(', ', $sets) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log('Error in updateDomain: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDomain($domainId)
    {
        try {
            $stmt = $this->pdo->prepare('DELETE FROM form_domains WHERE id = ?');
            return $stmt->execute([$domainId]);

        } catch (PDOException $e) {
            error_log('Error in deleteDomain: ' . $e->getMessage());
            return false;
        }
    }
}