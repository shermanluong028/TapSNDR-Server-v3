<?php
// File: /home/tapsndrc/[subdomain].tapsndr.com/index.php

// Set path to common library
define('COMMON_LIB_PATH', __DIR__ . '/../common_lib');

// Include GroupDetector
require_once COMMON_LIB_PATH . '/core/GroupDetector.php';

// Get current domain info
$uri           = $_SERVER['REQUEST_URI'];
$groupDetector = new GroupDetector();
$domainInfo    = $groupDetector->getDomainInfo($uri);

// If domain not found, show error
if (! $domainInfo) {
    die('This submission portal is currently unavailable. Please check the URL and try again.');
}

if ($domainInfo['original_form_enabled']) {
    // Include the universal form template
    require_once COMMON_LIB_PATH . '/templates/form.php';
} else {
    require_once COMMON_LIB_PATH . '/templates/unavailable.php';
}
