<?php
// File: /home/tapsndrc/common_lib/core/ImageHandler.php

class ImageHandler
{
    private $uploadPath;

    public function __construct()
    {
        // Set the base upload path
        $this->uploadPath = '/tickets/';

        // Create upload directory if it doesn't exist
        if (! file_exists(storage_path('/app/public') . $this->uploadPath)) {
            mkdir(storage_path('/app/public') . $this->uploadPath, 0755, true);
        }
    }

    public function processImage($uploadedFile, $domain, $ticketId)
    {
        try {
            // Get file extension
            $fileInfo  = pathinfo($uploadedFile['name']);
            $extension = strtolower($fileInfo['extension']);

            // Generate unique filename
            $filename = $ticketId . '_' . time() . '.' . $extension;

            // Create domain-specific directory
            $domainPath = $this->uploadPath . str_replace('.', '_', $domain) . '/';
            if (! file_exists(storage_path('app/public') . $domainPath)) {
                mkdir(storage_path('app/public') . $domainPath, 0755, true);
            }

            // Full path for the file
            $fullPath = storage_path('app/public') . $domainPath . $filename;

            // Move uploaded file
            if (! move_uploaded_file($uploadedFile['tmp_name'], $fullPath)) {
                throw new Exception('Failed to move uploaded file');
            }

            // Return the relative path for database storage
            return $domainPath . $filename;

        } catch (Exception $e) {
            error_log('Error processing image: ' . $e->getMessage());
            throw new Exception('Failed to process image');
        }
    }
}
