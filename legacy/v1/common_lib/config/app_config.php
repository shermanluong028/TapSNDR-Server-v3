<?php
/**
 * Application Configuration
 * 
 * General application settings.
 */

// Timezone settings
define('APP_TIMEZONE', 'America/New_York'); // Set to US Eastern Time
date_default_timezone_set(APP_TIMEZONE);

// Form operating hours (in 24-hour format)
define('FORM_OPEN_HOUR', 8);  // 8:51 AM
define('FORM_OPEN_MINUTE', 51);
define('FORM_CLOSE_HOUR', 25); // 1:09 AM (next day, represented as hour 25)
define('FORM_CLOSE_MINUTE', 9);

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB max file size
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'image/jpg']);
define('UPLOAD_PATH', '/images');

// Telegram settings
define('TELEGRAM_BOT_TOKEN', '7958920210:AAFjG8oiAC6wJN7I9fx2ldeoOXHLKnpDwbU'); // Replace with your actual bot token
define('TELEGRAM_WEBHOOK_URL', 'https://tapsndr.com/api/telegram_webhook.php');

// Security settings
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('CSRF_TOKEN_NAME', 'csrf_token');

// Site settings
define('SITE_NAME', 'TapSNDR');
define('SITE_URL', 'https://tapsndr.com');