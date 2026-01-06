<?php
    // File: /home/tapsndrc/common_lib/templates/form.php

    /**
     * Universal form template with custom fields
     *
     * @param array $domainInfo Information about the current domain
     */

    // Start session if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Generate CSRF token
    if (! isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Get form options from database
    require_once COMMON_LIB_PATH . '/core/FormOptionsManager.php';
    $optionsManager = new FormOptionsManager();
    $gameOptions    = $optionsManager->getGameOptions($domainInfo['id']);
    $paymentMethods = $optionsManager->getPaymentMethods($domainInfo['id']);

    // Get form configuration
    $formConfig = $optionsManager->getFormConfig($domainInfo['id']);
    $headerText = $formConfig['header_text'] ?? '';
    $footerText = $formConfig['footer_text'] ?? '';

    $commissionPercentage = $optionsManager->getCommissionPercentage($domainInfo['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Ticket -                           <?php echo htmlspecialchars($domainInfo['group_name']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Common stylesheet -->
    <link href="/common/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="card-title mb-0">Submit Ticket -
                            <?php echo htmlspecialchars($domainInfo['group_name']); ?></h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Warning Text -->
                        <div class="alert alert-danger mb-4">
                            TapSNDR redeems cost <?php echo $commissionPercentage; ?>%! DO NOT SEND BACK TO WHERE YOU RECEIVE FROM! WE WILL NOT BE HELD
                            RESPONSIBLE FOR ANY PAYMENTS SENT TO ACCOUNTS FROM TAP! Payments will be sent within 1-3
                            hours.
                        </div>

                        <!-- Header Text -->
                        <?php if (! empty($headerText)): ?>
                        <div class="alert alert-info mb-4">
                            <?php echo nl2br(htmlspecialchars($headerText)); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Alert for messages -->
                        <div id="formAlert" class="alert d-none" role="alert"></div>

                        <!-- Ticket Submission Form -->
                        <form id="ticketForm" method="POST" action="/api/submit.php" enctype="multipart/form-data">
                            <!-- CSRF Token and Domain ID -->
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="domain_id" value="<?php echo $domainInfo['id']; ?>">

                            <!-- Facebook Name -->
                            <div class="mb-4">
                                <label for="facebookName" class="form-label">Facebook Name</label>
                                <input type="text" class="form-control" id="facebookName" name="facebook_name" required>
                            </div>

                            <!-- Amount -->
                            <div class="mb-4">
                                <label for="amount" class="form-label">Amount (Min: 100)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="amount" name="amount" min="100"
                                        step="1" required>
                                </div>
                                <div class="invalid-feedback">
                                    Minimum amount is $100
                                </div>
                            </div>

                            <!-- Game -->
                            <div class="mb-4">
                                <label for="game" class="form-label">Game</label>
                                <select class="form-select" id="game" name="game" required>
                                    <option value="" selected disabled>Select Game</option>
                                    <?php foreach ($gameOptions as $game): ?>
                                    <option value="<?php echo htmlspecialchars($game); ?>">
                                        <?php echo htmlspecialchars($game); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Game ID -->
                            <div class="mb-4">
                                <label for="gameId" class="form-label">Game ID</label>
                                <input type="text" class="form-control" id="gameId" name="game_id" required>
                            </div>

                            <!-- Method of Payment -->
                            <div class="mb-4">
                                <label for="paymentMethod" class="form-label">Method of Payment</label>
                                <select class="form-select" id="paymentMethod" name="payment_method" required>
                                    <option value="" selected disabled>Select Payment Method</option>
                                    <?php foreach ($paymentMethods as $method): ?>
                                    <option value="<?php echo htmlspecialchars($method); ?>">
                                        <?php echo htmlspecialchars($method); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Method of Payment Tag -->
                            <div class="mb-4">
                                <label for="paymentTag" class="form-label">Method of Payment Tag</label>
                                <input type="text" class="form-control" id="paymentTag" name="payment_tag" required>
                            </div>

                            <!-- Name on Account -->
                            <div class="mb-4">
                                <label for="accountName" class="form-label">Name on the Account</label>
                                <input type="text" class="form-control" id="accountName" name="account_name" required>
                            </div>

                            <!-- Screenshot Upload -->
                            <div class="mb-4">
                                <label for="screenshot" class="form-label">Payment QR Code Screenshot</label>
                                <input type="file" class="form-control" id="screenshot" name="screenshot"
                                    accept="image/*" required>
                                <div class="form-text">
                                    Please upload a clear screenshot of your QR code.
                                </div>
                            </div>

                            <!-- Image Preview -->
                            <div class="mb-4 d-none" id="imagePreviewContainer">
                                <label class="form-label">Image Preview</label>
                                <div class="border rounded p-2 text-center">
                                    <img id="imagePreview" class="img-fluid" style="max-height: 300px;"
                                        alt="Screenshot preview">
                                </div>
                            </div>

                            <!-- Footer Text -->
                            <?php if (! empty($footerText)): ?>
                            <div class="alert alert-info mb-4">
                                <?php echo nl2br(htmlspecialchars($footerText)); ?>
                            </div>
                            <?php endif; ?>

                            <!-- Submit Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="submitBtn">Submit Ticket</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Form Handling Script -->
    <script src="/common/assets/js/form-handler.js"></script>

    <!-- Amount Validation Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('amount');
        const form = document.getElementById('ticketForm');

        function validateAmount() {
            const value = parseFloat(amountInput.value);
            if (value < 100) {
                amountInput.classList.add('is-invalid');
                return false;
            } else {
                amountInput.classList.remove('is-invalid');
                return true;
            }
        }

        amountInput.addEventListener('input', validateAmount);

        form.addEventListener('submit', function(e) {
            if (!validateAmount()) {
                e.preventDefault();
                return;
            }

            const submitButton = this.querySelector('button[type=submit]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerText = 'Submitting...';
            }
        });
    });
    </script>
</body>

</html>
