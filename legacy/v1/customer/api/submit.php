<?php
    // File: /home/tapsndrc/[subdomain].tapsndr.com/api/submit.php

    // Set path to common library

    use Illuminate\Support\Facades\Storage;

    define('COMMON_LIB_PATH', __DIR__ . '/../../common_lib');

    // Start session before any output
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Include necessary files
    require_once COMMON_LIB_PATH . '/core/GroupDetector.php';
    require_once COMMON_LIB_PATH . '/core/TicketProcessor.php';
    require_once COMMON_LIB_PATH . '/core/ImageHandler.php';
    require_once COMMON_LIB_PATH . '/core/FormOptionsManager.php';
    require_once COMMON_LIB_PATH . '/config/db_config.php';

    // Initialize response
    $success       = false;
    $message       = '';
    $ticketId      = null;
    $submittedData = null;

    try {
        // Verify request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Invalid request method');
        }

        // Debug CSRF token (remove in production)
        error_log('Session CSRF Token: ' . ($_SESSION['csrf_token'] ?? 'not set'));
        error_log('POST CSRF Token: ' . ($_POST['csrf_token'] ?? 'not set'));

        // Verify CSRF token with detailed error message
        if (! isset($_SESSION['csrf_token'])) {
            throw new Exception('Session expired. Please refresh the page and try again.');
        }
        if (! isset($_POST['csrf_token'])) {
            throw new Exception('CSRF token missing from form submission.');
        }
        if (! hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            throw new Exception('Invalid security token. Please refresh the page and try again.');
        }

        // Get domain information
        $domainId      = $_POST['domain_id'] ?? '';
        $groupDetector = new GroupDetector();
        $db            = $groupDetector->getConnection();

        // Get domain from database using ID
        $stmt = $db->prepare('SELECT * FROM form_domains WHERE id = ? AND active = 1 LIMIT 1');
        $stmt->execute([$domainId]);
        $domain = $stmt->fetch();

        if (! $domain) {
            throw new Exception('Invalid domain');
        }

        // Add this check before the database insert
        if (empty($domain['telegram_chat_id'])) {
            throw new Exception('No Telegram chat ID configured for this domain');
        }

        // Initialize processors
        $ticketProcessor = new TicketProcessor();
        $imageHandler    = new ImageHandler();
        $optionsManager  = new FormOptionsManager();

        // Check operating hours
        if (! $ticketProcessor->isWithinOperatingHours()) {
            throw new Exception('Submissions are only accepted during our operating hours: ' . $ticketProcessor->getOperatingHours() . '. Please contact your host for assistance.');
        }

        // Validate required fields
        $requiredFields = [
            'facebook_name', 'amount', 'game', 'game_id',
            'payment_method', 'payment_tag', 'account_name',
        ];

        foreach ($requiredFields as $field) {
            if (! isset($_POST[$field]) || empty($_POST[$field])) {
                throw new Exception('Missing required field: ' . $field);
            }
        }

        // Process screenshot
        $uploadedFile = $_FILES['screenshot'];
        $fileName     = $uploadedFile['name'];
        $fileType     = $uploadedFile['type'];
        $fileTmpPath  = $uploadedFile['tmp_name'];
        $fileError    = $uploadedFile['error'];
        $fileSize     = $uploadedFile['size'];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        if (! in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed');
        }

        // Validate file size (max 5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
        if ($fileSize > $maxFileSize) {
            throw new Exception('File size exceeds the limit (5MB)');
        }

        // Generate unique ticket ID
        $prefix    = strtoupper(substr($domain['domain'], 0, 2));
        $timestamp = time();
        $randomStr = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4);
        $ticketId  = "{$prefix}-{$timestamp}-{$randomStr}";

        // Process and save image
        $imagePath = $imageHandler->processImage($uploadedFile, $domain['domain'], $ticketId);

        // Set default user ID for anonymous submissions
        $userId = 0;

        // Save to database with 'pending' status
        $query = "INSERT INTO tickets (
        domain_id,
        facebook_name,
        ticket_id,
        user_id,
        payment_method,
        payment_tag,
        account_name,
        amount,
        game,
        game_id,
        image_path,
        status,
        chat_group_id
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?
    )";

        $stmt = $db->prepare($query);
        $stmt->execute([
            $domain['id'],
            $_POST['facebook_name'],
            $ticketId,
            $userId,
            $_POST['payment_method'],
            $_POST['payment_tag'],
            $_POST['account_name'],
            $_POST['amount'],
            $_POST['game'],
            $_POST['game_id'],
            url(Storage::url($imagePath)),
            $domain['telegram_chat_id'],
        ]);

        $result = true; // PDO will throw an exception if the execute fails

        // Store submitted data for display
        $submittedData = [
            'facebook_name'  => $_POST['facebook_name'],
            'amount'         => $_POST['amount'],
            'game'           => $_POST['game'],
            'game_id'        => $_POST['game_id'],
            'payment_method' => $_POST['payment_method'],
            'payment_tag'    => $_POST['payment_tag'],
            'account_name'   => $_POST['account_name'],
        ];

        $success = true;
        $message = 'Ticket submitted successfully!';

    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    $commissionPercentage = $optionsManager->getCommissionPercentage($domainId);

    // Return HTML response
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Submission Result</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        padding: 20px;
    }

    .result-box {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .ticket-details {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-top: 15px;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: none;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .warning-text {
        color: #dc3545;
        font-weight: bold;
        text-align: center;
        margin: 20px 0;
        padding: 10px;
        border: 1px solid #dc3545;
        border-radius: 5px;
        background-color: rgba(220, 53, 69, 0.1);
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="result-box">
            <div class="warning-text">
                ⚠️DO NOT SEND BACK TO WHERE YOU RECEIVE FROM! WE WILL NOT BE HELD RESPONSIBLE FOR ANY PAYMENTS SENT TO
                ACCOUNTS FROM TAP!⚠️
            </div>
            <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <h4 class="alert-heading">Submitted Successfully!</h4>
            </div>
            <div class="text-center mb-4">
                <h5>Transaction ID: <?php echo htmlspecialchars($ticketId); ?></h5>
                <h5>Amount to receive: $<?php echo round($submittedData['amount'] * (100 - (float) $commissionPercentage)) / 100; ?></h5>
            </div>
            <div class="ticket-details">
                <h5>Submission Details:</h5>
                <ul class="list-unstyled">
                    <li><strong>Facebook Name:</strong> <?php echo htmlspecialchars($submittedData['facebook_name']); ?></li>
                    <li><strong>Amount:</strong> $<?php echo htmlspecialchars($submittedData['amount']); ?></li>
                    <li><strong>Game:</strong> <?php echo htmlspecialchars($submittedData['game']); ?></li>
                    <li><strong>Game ID:</strong> <?php echo htmlspecialchars($submittedData['game_id']); ?></li>
                    <li><strong>Payment Method:</strong> <?php echo htmlspecialchars($submittedData['payment_method']); ?></li>
                    <li><strong>Payment Tag:</strong> <?php echo htmlspecialchars($submittedData['payment_tag']); ?></li>
                    <li><strong>Account Name:</strong> <?php echo htmlspecialchars($submittedData['account_name']); ?></li>
                </ul>
            </div>
            <?php else: ?>
            <div class="alert alert-danger text-center">
                <h4 class="alert-heading">Ticket was not submitted!</h4>
                <p><?php echo htmlspecialchars($message); ?></p>
                <?php if (strpos($message, 'operating hours') !== false): ?>
                <p>Please contact your host for assistance.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="text-center mt-4">
                <a href="javascript:window.location.href=document.referrer" class="btn btn-primary">Submit Another Ticket</a>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.querySelector('input[name="amount"]');
        if (amountInput) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = 'Amount must be $100 or higher';
            amountInput.parentNode.appendChild(errorDiv);

            amountInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (value < 100) {
                    this.classList.add('is-invalid');
                    errorDiv.style.display = 'block';
                } else {
                    this.classList.remove('is-invalid');
                    errorDiv.style.display = 'none';
                }
            });

            // Also validate on form submission
            const form = amountInput.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const value = parseFloat(amountInput.value);
                    if (value < 100) {
                        e.preventDefault();
                        amountInput.classList.add('is-invalid');
                        errorDiv.style.display = 'block';
                    }
                });
            }
        }
    });
    </script>
</body>

</html>
