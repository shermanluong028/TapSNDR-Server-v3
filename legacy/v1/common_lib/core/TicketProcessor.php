<?php
// File: /home/tapsndrc/common_lib/core/TicketProcessor.php

class TicketProcessor {
    private $pdo;
    private $devMode = false; // Set this to false in production
    
    public function __construct() {
        // Load configurations
        require_once __DIR__ . '/../config/db_config.php';
        
        // Set timezone
        date_default_timezone_set('America/New_York');
        
        // Connect to database using PDO
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log('Database connection error in TicketProcessor: ' . $e->getMessage());
            throw new Exception('Database connection failed');
        }
    }
    
    public function isWithinOperatingHours() {
        return true;
        // If in development mode, always return true
        // if ($this->devMode) {
        //     return true;
        // }
        
        // $currentTime = new DateTime('now', new DateTimeZone('America/New_York'));
        // $currentHour = (int)$currentTime->format('G');
        // $currentMinute = (int)$currentTime->format('i');
        
        // // Convert current time to minutes since midnight
        // $currentTimeInMinutes = ($currentHour * 60) + $currentMinute;
        
        // // Convert operating hours to minutes
        // $startTimeInMinutes = (8 * 60) + 51;  // 8:51 AM
        // $endTimeInMinutes = (25 * 60) + 9;    // 1:09 AM next day (25 hours format)
        
        // // Handle the case where end time is in the next day
        // if ($currentHour < 8) {
        //     // If it's before 8 AM, add 24 hours to current time for comparison
        //     $currentTimeInMinutes += 24 * 60;
        // }
        
        // return $currentTimeInMinutes >= $startTimeInMinutes && $currentTimeInMinutes <= $endTimeInMinutes;
    }
    
    public function getOperatingHours() {
        return "8:51 AM - 1:09 AM EST";
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function getPendingTickets() {
        try {
            $stmt = $this->pdo->query("
                SELECT * FROM tickets 
                WHERE status = 'new' 
                ORDER BY created_at ASC 
                LIMIT 10
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getting pending tickets: ' . $e->getMessage());
            return [];
        }
    }
    
    public function updateTicketStatus($ticketId, $status) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE tickets 
                SET status = ?, 
                    updated_at = NOW() 
                WHERE ticket_id = ?
            ");
            return $stmt->execute([$status, $ticketId]);
        } catch (PDOException $e) {
            error_log('Error updating ticket status: ' . $e->getMessage());
            return false;
        }
    }
}
