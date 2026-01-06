<?php

use App\Models\CryptoTransaction;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('COMMON_LIB_PATH', __DIR__ . '/common_lib');

// Include Telegram configuration
require_once COMMON_LIB_PATH . '/config/telegram_config.php';
// require_once COMMON_LIB_PATH . '/config/coinflow.php';

// Define Telegram API URL using token from config
define('TELEGRAM_API', 'https://api.telegram.org/bot' . BOT_TOKEN);

// Database configuration
$db_config = [
    'host' => env('DB_HOST', '127.0.0.1'),
    'user' => env('DB_USERNAME', 'root'),
    'pass' => env('DB_PASSWORD', ''),
    'db'   => env('DB_DATABASE', 'tapsndr'),
];

// Function to safely return error messages
function return_error($message, $details = null)
{
    return json_encode([
        'status'  => 'error',
        'message' => $message,
        'details' => $details,
    ]);
}

// function notifyNodeServer($type, $data)
// {
//     // Get the server's hostname dynamically
//     $host = $_SERVER['HTTP_HOST'] ?? 'v1.tapsndr.com';
//     $url  = 'https://' . $host . '/nodeapi/api/notify';
//     Log::channel('proxy')->info("Notifying Node.js server at: " . $url);
//     Log::channel('proxy')->info("Notification type: " . $type);
//     Log::channel('proxy')->info("Notification data: " . json_encode($data));

//     $payload = json_encode([
//         'type' => $type,
//         'data' => $data,
//     ]);

//     $ch = curl_init($url);
//     curl_setopt($ch, CURLOPT_POST, 1);
//     curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//     curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 30);           // Increase timeout to 30 seconds
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for internal calls

//     $response = curl_exec($ch);
//     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//     $error    = curl_error($ch);
//     curl_close($ch);

//     if ($error) {
//         Log::channel('proxy')->error("Error notifying Node.js server: " . $error);
//         return false;
//     }

//     if ($httpCode >= 400) {
//         Log::channel('proxy')->info("Node.js server returned error HTTP code: " . $httpCode);
//         Log::channel('proxy')->info("Response: " . $response);
//         return false;
//     }

//     Log::channel('proxy')->info("Node.js server notification successful. Response: " . $response);
//     return json_decode($response, true);
// }

try {
    // Connect to database
    $mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['pass'], $db_config['db']);

    if ($mysqli->connect_error) {
        die(return_error('Database connection failed', $mysqli->connect_error));
    }

    // Function to check if column exists and add it if it doesn't
    function addColumnIfNotExists($mysqli, $table, $column, $definition)
    {
        $check = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if ($check->num_rows == 0) {
            $query = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
            return $mysqli->query($query);
        }
        return true;
    }

    // Add columns if they don't exist
    try {
        // Add each column separately with proper error handling
        $columns = [
            'error_type'          => 'VARCHAR(50) DEFAULT NULL',
            'error_details'       => 'TEXT DEFAULT NULL',
            'error_reported_at'   => 'DATETIME DEFAULT NULL',
            'error_reported_by'   => 'VARCHAR(50) DEFAULT NULL',
            'telegram_message_id' => 'BIGINT DEFAULT NULL',
            'telegram_chat_id'    => 'BIGINT DEFAULT NULL',
        ];

        foreach ($columns as $column => $definition) {
            if (! addColumnIfNotExists($mysqli, 'tickets', $column, $definition)) {
                Log::channel('proxy')->error("Failed to add column $column: " . $mysqli->error);
            }
        }

        // Check and create indexes
        $check_index1 = $mysqli->query("SHOW INDEX FROM tickets WHERE Key_name = 'idx_error_reported_at'");
        if ($check_index1->num_rows == 0) {
            $mysqli->query("CREATE INDEX idx_error_reported_at ON tickets (error_reported_at)");
        }

        $check_index2 = $mysqli->query("SHOW INDEX FROM tickets WHERE Key_name = 'idx_telegram_message'");
        if ($check_index2->num_rows == 0) {
            $mysqli->query("CREATE INDEX idx_telegram_message ON tickets (telegram_message_id, telegram_chat_id)");
        }

    } catch (Exception $e) {
        Log::channel('proxy')->error("Error in table modification: " . $e->getMessage());
    }

    function escapeTelegramMarkdown($text)
    {
        $search  = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        $replace = [];
        foreach ($search as $c) {
            $replace[] = '\\' . $c;
        }
        return str_replace($search, $replace, $text);
    }

    // Function to get pending forms
    function getPendingForms($mysqli)
    {
        $stmt = $mysqli->prepare("
            SELECT
                t.id,
                t.ticket_id,
                t.status,
                t.created_at,
                t.game,
                t.game_id,
                t.amount,
                t.facebook_name,
                t.chat_group_id,
                fd.telegram_chat_id,
                fd.domain
            FROM tickets t
            LEFT JOIN form_domains fd ON t.domain_id = fd.id
            WHERE t.status = 'pending'
            ORDER BY t.created_at ASC
        ");

        if (! $stmt) {
            Log::channel('proxy')->error("Failed to prepare getPendingForms query: " . $mysqli->error);
            return ['status' => 'error', 'message' => 'Failed to prepare query'];
        }

        if (! $stmt->execute()) {
            Log::channel('proxy')->error("Failed to execute getPendingForms query: " . $stmt->error);
            return ['status' => 'error', 'message' => 'Failed to execute query'];
        }

        $result = $stmt->get_result();
        $forms  = [];

        while ($row = $result->fetch_assoc()) {
            // validate and replace "_" with "-"
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    $row[$key] = str_replace("_", "-", $value);
                }
            }
            $forms[] = $row;
        }

        return [
            'status' => 'success',
            'forms'  => $forms,
        ];
    }

    function getFulfilledTickets($mysqli)
    {
        try {
            // Query for validated tickets that need to be sent back
            $query = "SELECT
                        t.id,
                        t.ticket_id as transaction_number,
                        t.facebook_name,
                        t.amount,
                        t.game,
                        t.game_id,
                        t.status,
                        t.chat_group_id,
                        ci.image_path as fulfillment_image
                    FROM tickets t
                    LEFT JOIN completion_images ci ON t.id = ci.form_id
                    WHERE t.status = 'validated'
                    LIMIT 10";

            $result = $mysqli->query($query);

            if (! $result) {
                return [
                    'status'  => 'error',
                    'message' => 'Query failed',
                    'details' => $mysqli->error,
                ];
            }

            $tickets = [];
            while ($row = $result->fetch_assoc()) {
                // Format the data for the bot
                $tickets[] = [
                    'id'                 => $row['id'],
                    'transaction_number' => $row['transaction_number'],
                    'facebook_name'      => $row['facebook_name'],
                    'amount'             => $row['amount'],
                    'game'               => $row['game'],
                    'game_id'            => $row['game_id'],
                    'status'             => $row['status'],
                    'chat_group_id'      => $row['chat_group_id'],
                    'fulfillment_images' => $row['fulfillment_image'] ? [$row['fulfillment_image']] : [],
                ];
            }

            return [
                'status'  => 'success',
                'tickets' => $tickets,
                'count'   => count($tickets),
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Exception in getFulfilledTickets',
                'details' => $e->getMessage(),
            ];
        }
    }

    // Function to update form status
    function updateFormStatus($mysqli, $ticket_id, $new_status, $chat_group_id = null, $validation_image = null)
    {
        try {
            // $ticket_id  = $mysqli->real_escape_string($ticket_id);
            // $new_status = $mysqli->real_escape_string($new_status);

            // Start transaction
            // $mysqli->begin_transaction();
            DB::beginTransaction();

            try {

                // If we have a validation image, save it
                if ($validation_image) {
                    $image_query = "INSERT INTO completion_images (form_id, image_path, created_at)
                                  SELECT id, ?, NOW()
                                  FROM tickets
                                  WHERE ticket_id = ?";

                    // $image_stmt = $mysqli->prepare($image_query);
                    // if (! $image_stmt) {
                    //     throw new Exception("Failed to prepare image insert: " . $mysqli->error);
                    // }

                    // $image_stmt->bind_param('ss', $validation_image, $ticket_id);
                    // if (! $image_stmt->execute()) {
                    //     throw new Exception("Failed to save validation image: " . $image_stmt->error);
                    // }
                    DB::insert($image_query, [$validation_image, $ticket_id]);
                }
                if ($new_status == 'declined') {

                    $validated_query = "SELECT status FROM tickets WHERE ticket_id = ?";
                    // $validated_stmt  = $mysqli->prepare($validated_query);
                    // if (! $validated_stmt) {
                    //     throw new Exception("Failed to prepare validated query: " . $mysqli->error);
                    // }

                    // $validated_stmt->bind_param('s', $ticket_id);
                    // if (! $validated_stmt->execute()) {
                    //     throw new Exception("Failed to execute valided query: " . $validated_stmt->error);
                    // }

                    // $result = $validated_stmt->get_result();
                    // if ($result->num_rows === 0) {
                    //     throw new Exception("No ticket found for ticket ID: " . $ticket_id);
                    // }
                    // $ticket_status_data = $result->fetch_assoc();
                    // $ticket_status      = $ticket_status_data['status'];

                    $results = DB::select($validated_query, [$ticket_id]);

                    if (empty($results)) {
                        throw new Exception("No ticket found for ticket ID: " . $ticket_id);
                    }

                    $ticket_status = $results[0]->status;

                    if ($ticket_status != 'sent' && $ticket_status != 'validated') {
                        if ($ticket_status == 'declined') {
                            throw new Exception("Already Declined");
                        } else {
                            throw new Exception("Either being processed or already completed");
                        }
                    }
                }

                if ($new_status == 'validated' && $chat_group_id !== null) {
                    if (! $validation_image) {
                        throw new Exception("Failed to validate ticket: image upload failed");
                    }
                    $client_query = "SELECT client_id FROM form_domains WHERE telegram_chat_id = ?";
                    // $client_stmt  = $mysqli->prepare($client_query);
                    // if (! $client_stmt) {
                    //     throw new Exception("Failed to prepare client query: " . $mysqli->error);
                    // }

                    // $client_stmt->bind_param('s', $chat_group_id);
                    // if (! $client_stmt->execute()) {
                    //     throw new Exception("Failed to execute client query: " . $client_stmt->error);
                    // }

                    // $result = $client_stmt->get_result();
                    // if ($result->num_rows === 0) {
                    //     throw new Exception("No client found for chat group ID: " . $chat_group_id);
                    // }
                    // $client_data = $result->fetch_assoc();
                    // $client_id = $client_data['client_id'];

                    $result = DB::select($client_query, [$chat_group_id]);

                    if (empty($result)) {
                        throw new Exception("No client found for chat group ID: " . $chat_group_id);
                    }

                    $client_id = $result[0]->client_id;

                    $commission_query = "SELECT commission_percentages.* FROM commission_percentages LEFT JOIN form_domains ON commission_percentages.domain_id = form_domains.id WHERE form_domains.telegram_chat_id = ? AND commission_percentages.deleted_at IS NULL";
                    // $commission_stmt  = $mysqli->prepare($commission_query);
                    // if (! $commission_stmt) {
                    //     throw new Exception("Failed to prepare commission query: " . $mysqli->error);
                    // }
                    // $commission_stmt->bind_param('s', $chat_group_id);
                    // if (! $commission_stmt->execute()) {
                    //     throw new Exception("Failed to execute commission query: " . $commission_stmt->error);
                    // }
                    // $commission_result = $commission_stmt->get_result();
                    // if ($commission_result->num_rows === 0) {
                    //     // Fallback to safe defaults if missing
                    //     $commission_data = [];
                    // } else {
                    //     $commission_data = $commission_result->fetch_assoc();
                    // }
                    $result = DB::select($commission_query, [$chat_group_id]);
                    if (empty($result)) {
                        $commission_data = [];
                    } else {
                        $commission_data = (array) $result[0];
                    }

                    $commission_data['admin_client']         = $commission_data['admin_client'] ?? 1;
                    $commission_data['admin_customer']       = $commission_data['admin_customer'] ?? 4;
                    $commission_data['distributor_client']   = $commission_data['distributor_client'] ?? 0;
                    $commission_data['distributor_customer'] = $commission_data['distributor_customer'] ?? 0;

                    $admin_client       = $commission_data['admin_client'] ?? 1;
                    $distributor_client = $commission_data['distributor_client'] ?? 0;

                    $commission_percentage = $admin_client + $distributor_client;
                    $ticket_query          = "SELECT * FROM tickets WHERE ticket_id = ? for update";
                    // $ticket_stmt           = $mysqli->prepare($ticket_query);
                    // if (! $ticket_stmt) {
                    //     throw new Exception("Failed to prepare ticket query: " . $mysqli->error);
                    // }

                    // $ticket_stmt->bind_param('s', $ticket_id);
                    // if (! $ticket_stmt->execute()) {
                    //     throw new Exception("Failed to execute ticket query: " . $ticket_stmt->error);
                    // }

                    // $ticket_result = $ticket_stmt->get_result();
                    // if ($ticket_result->num_rows === 0) {
                    //     throw new Exception("No ticket found with ID: " . $ticket_id);
                    // }
                    // $ticket_data = $ticket_result->fetch_assoc();

                    $result = DB::select($ticket_query, [$ticket_id]);
                    if (empty($result)) {
                        throw new Exception("No ticket found with ID: " . $ticket_id);
                    }
                    $ticket_data = (array) $result[0];

                    if ($ticket_data['status'] != 'sent') {
                        if ($ticket_data['status'] === 'declined') {
                            throw new Exception("Already Declined");
                        } else {
                            throw new Exception("Already Validated");
                        }
                    }

                    $amount       = $ticket_data['amount'] * (1 + ($commission_percentage / 100)) + 0.2;
                    $timestamp    = time();
                    $randomString = bin2hex(random_bytes(5)); // Generates 10-character random string
                    $reference_id = "DEP-{$timestamp}-{$randomString}";
                    // compare amount to current client balance
                    $balance_available_query = "SELECT
                                                    CASE
                                                        WHEN (w.balance - ?) < 0 THEN false
                                                        ELSE true
                                                    END AS balance_check
                                                FROM
                                                    tickets t
                                                JOIN
                                                    form_domains fd ON fd.id = t.domain_id
                                                JOIN
                                                    wallets w ON w.user_id = fd.client_id
                                                WHERE
                                                    t.ticket_id = ?
                                                ";
                    // $balance_available_stmt = $mysqli->prepare($balance_available_query);
                    // if (! $balance_available_stmt) {
                    //     throw new Exception("Failed to prepare the balance check query");
                    // }
                    // $balance_available_stmt->bind_param('ds', $amount, $ticket_id);
                    // if (! $balance_available_stmt->execute()) {
                    //     throw new Exception("Failed to execute balance check query:" . $balance_available_stmt->error);
                    // }
                    // $balance_available_result = $balance_available_stmt->get_result();
                    // if ($balance_available_result->num_rows === 0) {
                    //     throw new Exception("No ticket found with ID: " . $ticket_id . " or Insufficient balance");
                    // }
                    // $balance_available_data = $balance_available_result->fetch_assoc();
                    // $isAvailable            = $balance_available_data['balance_check'];

                    $result = DB::select($balance_available_query, [$amount, $ticket_id]);
                    if (empty($result)) {
                        throw new Exception("No ticket found with ID: " . $ticket_id . " or Insufficient balance");
                    }
                    $isAvailable = $result[0]->balance_check;

                    if (! $isAvailable) {
                        throw new Exception("Balance is not sufficient, You balance should be greater than 0.2 + 101% of the ticket amount");
                    }

                    $balance_query = "SELECT balance FROM wallets WHERE user_id = ?";
                    // $balance_stmt = $mysqli->prepare($balance_query);
                    // if (! $balance_stmt) {
                    //     throw new Exception("Failed to prepare the balance query");
                    // }
                    // $balance_stmt->bind_param('i', $client_id);
                    // if (! $balance_stmt->execute()) {
                    //     throw new Exception("Failed to execute balance query:" . $balance_stmt->error);
                    // }
                    // $balance_result = $balance_stmt->get_result();
                    // if ($balance_result->num_rows === 0) {
                    //     throw new Exception("No client found with ID: " . $client_id . " or Insuffic1ient balance");
                    // }
                    // $balance_data = $balance_result->fetch_assoc();
                    // $balance      = $balance_data['balance'];

                    $result = DB::select($balance_query, [$client_id]);
                    if (empty($result)) {
                        throw new Exception("No client found with ID: " . $client_id . " or Insufficient balance");
                    }
                    $balance = $result[0]->balance;

                    // Insert into crypto_transactions
                    $crypto_query = "INSERT INTO crypto_transactions (
                        user_id,
                        amount,
                        description,
                        reference_id,
                        created_at,
                        wallet_id,
                        status,
                        user_id_from,
                        user_id_to,
                        address_from,
                        transaction_hash,
                        address_to,
                        token_type,
                        transaction_type,
                        balance_before,
                        balance_after
                    ) VALUES (
                        ?,
                        ?,
                        'Ticket validated',
                        ?,
                        NOW(),
                        1,
                        'debit',
                        ?,
                        ?,
                        'system',
                        ?,
                        'system',
                        'USDT',
                        'debit',
                        ?,
                        ?
                    )";

                    // $crypto_stmt = $mysqli->prepare($crypto_query);
                    // if (! $crypto_stmt) {
                    //     throw new Exception("Failed to prepare crypto transaction insert: " . $mysqli->error);
                    // }
                    $user_id_to    = 0;
                    $balance_after = $balance - $amount;
                    // $crypto_stmt->bind_param('idsiisdd',
                    //     $client_id,
                    //     $amount,
                    //     $reference_id,
                    //     $client_id,
                    //     $user_id_to,
                    //     $ticket_id,
                    //     $balance,
                    //     $balance_after
                    // );

                    // if (! $crypto_stmt->execute()) {
                    //     throw new Exception("Failed to insert crypto transaction: " . $crypto_stmt->error);
                    // }

                    DB::insert($crypto_query, [
                        $client_id,
                        $amount,
                        $reference_id,
                        $client_id,
                        $user_id_to,
                        $ticket_id,
                        $balance,
                        $balance_after,
                    ]);

                    $update_wallet = "
                        UPDATE wallets
                        SET balance = balance - ?
                        WHERE user_id = ?
                    ";
                    // $update_stmt = $mysqli->prepare($update_wallet);
                    // if (! $update_stmt) {
                    //     throw new Exception("Failed to prepare wallet update: " . $mysqli->error);
                    // }

                    // $update_stmt->bind_param('dddi', $amount_test, $amount_test, $amount, $client_id);
                    // if (! $update_stmt->execute()) {
                    //     throw new Exception("Failed to update wallet balance: " . $update_stmt->error);
                    // }

                    DB::update($update_wallet, [
                        $amount,
                        $client_id,
                    ]);

                    $remote_addr = $_SERVER['REMOTE_ADDR'] ?? null;
                    Log::channel('proxy')->info("ID: $ticket_id, IP: $remote_addr, Image: $validation_image");

                    if ($ticket_data['coinflow_user_id'] && $ticket_data['coinflow_account']) {
                        $response = Http::withHeaders([
                            'Content-Type'  => 'application/json',
                            'Accept'        => 'application/json',
                            'Authorization' => env('COINFLOW_API_KEY'),
                        ])->post("https://api-sandbox.coinflow.cash/api/merchant/withdraws/payout/delegated", [
                            "amount"         => [
                                "cents" => round((1 - ($commission_data['admin_customer'] + $commission_data['distributor_customer']) / 100) * $ticket_data['amount'] * 100),
                            ],
                            "speed"          => "asap",
                            "account"        => $ticket_data['coinflow_account'],
                            "userId"         => $ticket_data['coinflow_user_id'],
                            "idempotencyKey" => $ticket_data['id'] . "",
                        ]);
                        Log::channel('proxy')->info($response);

                        $response_data = $response->json();

                        if (! $response_data || ! isset($response_data['signature'])) {
                            throw new Exception("Internal Server Error");
                        }

                        Ticket::where('ticket_id', $ticket_id)->update([
                            'coinflow_signature' => $response_data['signature'],
                            'completed_at'       => now()->toDateTimeString(),
                        ]);

                        $admin = User::whereHas('roles', function ($query) {
                            $query->where('name', 'admin');
                        })->first();
                        $balanceBefore = $admin->wallet->balance;
                        $commission    = $commission_data['admin_client'] / 100 * $ticket_data['amount'];
                        $admin->wallet->increment('balance', $ticket_data['amount'] + $commission);

                        CryptoTransaction::create([
                            'user_id'          => $admin->id,
                            'amount'           => $ticket_data['amount'] + $commission,
                            'description'      => 'Admin Ticket ' . $ticket_id . ' Completion Profit',
                            'transaction_hash' => $ticket_id,
                            'transaction_type' => 'credit',
                            'balance_before'   => $balanceBefore,
                        ]);

                        $client = User::find($client_id);

                        if ($client->distributor) {
                            $balanceBefore = $client->distributor->wallet->balance;
                            $commission    = $commission_data['distributor_client'] / 100 * $ticket_data['amount'];
                            $client->distributor->wallet->increment('balance', $commission);

                            CryptoTransaction::create([
                                'user_id'          => $client->distributor->id,
                                'amount'           => $commission,
                                'description'      => 'Distributor Ticket ' . $ticket_id . ' Completion Profit',
                                'transaction_hash' => $ticket_id,
                                'transaction_type' => 'credit',
                                'balance_before'   => $balanceBefore,
                            ]);
                        }

                        $new_status = 'completed';
                    }
                }
                // Update the ticket status
                $query  = "UPDATE tickets SET status = ? ";
                $params = [$new_status];

                // Add chat_group_id if provided
                if ($chat_group_id !== null) {
                    $query .= ", chat_group_id = ? ";
                    $params[] = $chat_group_id;
                }

                $query .= "WHERE ticket_id = ?";
                $params[] = $ticket_id;

                // $stmt = $mysqli->prepare($query);
                // if (! $stmt) {
                //     throw new Exception("Failed to prepare status update: " . $mysqli->error);
                // }

                // Bind parameters dynamically
                // $types = str_repeat('s', count($params));
                // $stmt->bind_param($types, ...$params);

                // if (! $stmt->execute()) {
                //     throw new Exception("Failed to update ticket status: " . $stmt->error);
                // }

                DB::update($query, $params);

                // After successful update, notify Node.js server
                // notifyNodeServer('status_update', [
                //     'ticket_id' => $ticket_id,
                //     'new_status' => $new_status,
                //     'chat_group_id' => $chat_group_id,
                //     'validation_image' => $validation_image
                // ]);

                // If all operations successful, commit the transaction
                // $mysqli->commit();
                DB::commit();

                return [
                    'status'  => 'success',
                    'message' => 'Ticket updated successfully',
                ];

            } catch (Exception $e) {
                // If any operation fails, roll back the transaction
                // $mysqli->rollback();
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::channel('proxy')->error($e);
            return [
                'status'  => 'error',
                'message' => 'Failed to update ticket',
                'details' => $e->getMessage(),
            ];
        }
    }

    // Function to get bot group settings
    function getBotGroups($mysqli)
    {
        try {
            // First, ensure the table exists
            $create_table = "CREATE TABLE IF NOT EXISTS bot_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                validation_group_id VARCHAR(255),
                notification_group_id VARCHAR(255),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )";

            if (! $mysqli->query($create_table)) {
                return [
                    'status'  => 'error',
                    'message' => 'Failed to create bot_settings table',
                    'details' => $mysqli->error,
                ];
            }

            // Check if we have any settings, if not insert default row
            $check_query = "SELECT COUNT(*) as count FROM bot_settings";
            $result      = $mysqli->query($check_query);
            $row         = $result->fetch_assoc();

            if ($row['count'] == 0) {
                $mysqli->query("INSERT INTO bot_settings (validation_group_id, notification_group_id) VALUES (NULL, NULL)");
            }

            // Get the group settings
            $query  = "SELECT validation_group_id, notification_group_id FROM bot_settings LIMIT 1";
            $result = $mysqli->query($query);

            if (! $result) {
                return [
                    'status'  => 'error',
                    'message' => 'Failed to fetch group settings',
                    'details' => $mysqli->error,
                ];
            }

            $groups = $result->fetch_assoc();
            return [
                'status' => 'success',
                'groups' => $groups,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Exception in getBotGroups',
                'details' => $e->getMessage(),
            ];
        }
    }

    // Function to set bot group
    function setBotGroup($mysqli, $group_type, $group_id)
    {
        try {
            // Validate group type
            if (! in_array($group_type, ['validation', 'notification'])) {
                return [
                    'status'  => 'error',
                    'message' => 'Invalid group type',
                ];
            }

            $column   = $group_type . '_group_id';
            $group_id = $mysqli->real_escape_string($group_id);

            $query  = "UPDATE bot_settings SET $column = '$group_id'";
            $result = $mysqli->query($query);

            return [
                'status'  => $result ? 'success' : 'error',
                'message' => $result ? 'Group updated successfully' : 'Failed to update group',
                'details' => $result ? null : $mysqli->error,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => 'Exception in setBotGroup',
                'details' => $e->getMessage(),
            ];
        }
    }

    // Add this function before the switch statement
    function sendTelegramMessage($chat_id, $text, $parse_mode = 'HTML')
    {
        $url  = TELEGRAM_API . '/sendMessage';
        $data = [
            'chat_id'    => $chat_id,
            'text'       => $text,
            'parse_mode' => $parse_mode,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result   = curl_exec($ch);
        $response = json_decode($result, true);
        curl_close($ch);

        Log::channel('proxy')->info("Telegram API Response: " . print_r($response, true));

        return $response && isset($response['result']['message_id']) ?
        $response['result']['message_id'] : null;
    }

    function sendTelegramPhoto($chat_id, $photo_path, $caption = null, $parse_mode = 'HTML')
    {
        $url = TELEGRAM_API . '/sendPhoto';

        Log::channel('proxy')->info("sendTelegramPhoto called with chat_id: $chat_id, photo_path: $photo_path");

        // Clean up the path - remove any leading or trailing whitespace
        $photo_path = trim($photo_path);

        // Try multiple path resolution approaches
        $possible_paths = [
            // 1. Direct path as provided
            $photo_path,
            // 2. Assuming path is relative to public_html
            '/home/tapsndrc/public_html' . $photo_path,
            // 3. Remove any leading slash for public_html relative path
            '/home/tapsndrc/public_html/' . ltrim($photo_path, '/'),
            // 4. Try document root version
            $_SERVER['DOCUMENT_ROOT'] . $photo_path,
            // 5. Try document root with leading slash removed
            $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($photo_path, '/'),
        ];

        // Find the first path that exists
        $full_path = null;
        foreach ($possible_paths as $path) {
            Log::channel('proxy')->info("Checking path: $path");
            if (file_exists($path)) {
                Log::channel('proxy')->info("Found file at path: $path");
                $full_path = $path;
                break;
            }
        }

        // If we couldn't find the file, log error and return
        if (! $full_path) {
            Log::channel('proxy')->error("ERROR: Could not find image file at any path");
            Log::channel('proxy')->error("Original photo_path: " . $photo_path);
            Log::channel('proxy')->error("All attempted paths:");
            foreach ($possible_paths as $path) {
                Log::channel('proxy')->error(" - $path (exists: " . (file_exists($path) ? 'YES' : 'NO') . ")");
            }
            return false;
        }

        // Create CURLFile for the image
        try {
            Log::channel('proxy')->info("Creating CURLFile with path: " . $full_path);
            $image = new CURLFile($full_path);

            $data = [
                'chat_id' => $chat_id,
                'photo'   => $image,
            ];

            if ($caption) {
                $data['caption']    = $caption;
                $data['parse_mode'] = $parse_mode;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            Log::channel('proxy')->info("Curl exec completed");

            if (! $result) {
                Log::channel('proxy')->error("Curl request failed: " . curl_error($ch));
                curl_close($ch);
                return false;
            }

            $response = json_decode($result, true);
            Log::channel('proxy')->info("Telegram API response: " . print_r($response, true));

            if (! $response || ! isset($response['ok']) || ! $response['ok']) {
                Log::channel('proxy')->error("Failed to send photo. Response: " . print_r($response, true));
                Log::channel('proxy')->error("CURL error: " . curl_error($ch));
                Log::channel('proxy')->error("Full path: " . $full_path);
                Log::channel('proxy')->error("File exists: " . (file_exists($full_path) ? 'yes' : 'no'));
                Log::channel('proxy')->error("File size: " . filesize($full_path) . " bytes");
                Log::channel('proxy')->error("File permissions: " . substr(sprintf('%o', fileperms($full_path)), -4));
                curl_close($ch);
                return false;
            }

            curl_close($ch);
            Log::channel('proxy')->info("Photo sent successfully with message ID: " . ($response['result']['message_id'] ?? 'unknown'));
            // Return the full response so we can extract message_id and other details
            return $response;
        } catch (Exception $e) {
            Log::channel('proxy')->error("Exception in sendTelegramPhoto: " . $e->getMessage());
            return false;
        }
    }

    // Function to handle error reporting
    function reportError($mysqli, $ticket_id, $error_type, $error_details, $reporter_id)
    {
        Log::channel('proxy')->info("Starting reportError for ticket: " . $ticket_id);
        $error_image = isset($_POST['error_image']) ? $_POST['error_image'] : null;

        if ($error_image) {
            Log::channel('proxy')->error("Error report includes image: " . $error_image);
        }

        // Look up the ticket by ticket_id
        $stmt = $mysqli->prepare("
            SELECT t.*, t.id as ticket_db_id, fd.domain, fd.telegram_chat_id
            FROM tickets t
            LEFT JOIN form_domains fd ON t.domain_id = fd.id
            WHERE t.ticket_id = ?
        ");

        if (! $stmt) {
            Log::channel('proxy')->error("Failed to prepare ticket lookup statement: " . $mysqli->error);
            return [
                'status'  => 'error',
                'message' => 'Failed to prepare statement: ' . $mysqli->error,
                'found'   => false,
            ];
        }

        if (! $stmt->bind_param('s', $ticket_id)) {
            Log::channel('proxy')->error("Failed to bind parameter for ticket lookup: " . $stmt->error);
            return [
                'status'  => 'error',
                'message' => 'Failed to bind parameter: ' . $stmt->error,
                'found'   => false,
            ];
        }

        if (! $stmt->execute()) {
            Log::channel('proxy')->error("Failed to execute ticket lookup: " . $stmt->error);
            return [
                'status'  => 'error',
                'message' => 'Failed to execute query: ' . $stmt->error,
                'found'   => false,
            ];
        }

        $result = $stmt->get_result();
        $ticket = $result->fetch_assoc();

        if (! $ticket) {
            Log::channel('proxy')->error("Ticket not found: " . $ticket_id);
            return [
                'status'  => 'error',
                'message' => 'Ticket not found',
                'found'   => false,
            ];
        }

        Log::channel('proxy')->info("Found ticket: " . print_r($ticket, true));

        // Update ticket status to error
        $update_stmt = $mysqli->prepare("
            UPDATE tickets
            SET status = 'error',
                error_type = ?,
                error_details = ?,
                error_reported_at = NOW()
            WHERE ticket_id = ?
        ");

        if (! $update_stmt) {
            Log::channel('proxy')->error("Failed to prepare update statement: " . $mysqli->error);
            return [
                'status'  => 'error',
                'message' => 'Failed to prepare update statement: ' . $mysqli->error,
                'found'   => true,
                'ticket'  => $ticket,
            ];
        }

        if (! $update_stmt->bind_param('sss', $error_type, $error_details, $ticket_id)) {
            Log::channel('proxy')->error("Failed to bind parameters for update: " . $update_stmt->error);
            return [
                'status'  => 'error',
                'message' => 'Failed to bind update parameters: ' . $update_stmt->error,
                'found'   => true,
                'ticket'  => $ticket,
            ];
        }

        if (! $update_stmt->execute()) {
            Log::channel('proxy')->error("Failed to update ticket: " . $update_stmt->error);
            return [
                'status'  => 'error',
                'message' => 'Failed to update ticket: ' . $update_stmt->error,
                'found'   => true,
                'ticket'  => $ticket,
            ];
        }

        Log::channel('proxy')->info("Updated ticket status to error");

        // Send notification to Telegram
        $chat_id = $ticket['telegram_chat_id'];
        if (! $chat_id) {
            Log::channel('proxy')->error("No Telegram chat ID found for this ticket's domain");
            return [
                'status'  => 'warning',
                'message' => 'Ticket updated but no Telegram chat ID available for notification',
                'found'   => true,
                'ticket'  => $ticket,
            ];
        }

        // Create error message for Telegram
        $message = "⚠️ ERROR REPORTED ⚠️\n\n" .
            "🎫 ID: {$ticket['ticket_id']}\n" .
            "💰 Amount: {$ticket['amount']}\n" .
            "👤 Facebook: {$ticket['facebook_name']}\n";

        if (! empty($ticket['game'])) {
            $message .= "🎮 Game: {$ticket['game']}\n";
        }

        if (! empty($ticket['game_id'])) {
            $message .= "🆔 Game ID: {$ticket['game_id']}\n";
        }

        $message .= "\n🚫 Error Type: " . $error_type . "\n" .
            "🔍 Details: " . $error_details;

        // Try to send with image first if available
        $message_id = null;
        if ($error_image) {
            Log::channel('proxy')->info("Attempting to send error notification with image: " . $error_image);
            $photo_response = sendTelegramPhoto($chat_id, $error_image, $message);

            if ($photo_response && isset($photo_response['ok']) && $photo_response['ok']) {
                Log::channel('proxy')->info("Successfully sent error notification with image");
                $message_id = isset($photo_response['result']['message_id']) ?
                $photo_response['result']['message_id'] : null;
            } else {
                Log::channel('proxy')->error("Failed to send with image, falling back to text message");
            }
        }

        // If no image or image sending failed, send text message
        if (! $message_id) {
            // Send message
            $message_id = sendTelegramMessage($chat_id, $message);
        }

        if ($message_id) {
            Log::channel('proxy')->info("Successfully sent Telegram error notification, message ID: " . $message_id);

            // Update the ticket with Telegram message info
            $msg_stmt = $mysqli->prepare("
                UPDATE tickets
                SET telegram_message_id = ?,
                    telegram_chat_id = ?
                WHERE ticket_id = ?
            ");

            if ($msg_stmt) {
                $msg_stmt->bind_param('sss', $message_id, $chat_id, $ticket_id);
                $msg_stmt->execute();
            }

            // Notify NodeJS server about the error (for SSE updates)
            // notifyNodeServer('error_ticket_update', [
            //     'ticket_id'  => $ticket_id,
            //     'error_type' => $error_type,
            // ]);

            return [
                'status'     => 'success',
                'message'    => 'Error reported and notification sent',
                'found'      => true,
                'ticket'     => $ticket,
                'message_id' => $message_id,
            ];
        } else {
            Log::channel('proxy')->error("Failed to send Telegram notification");
            return [
                'status'  => 'warning',
                'message' => 'Ticket updated but failed to send notification',
                'found'   => true,
                'ticket'  => $ticket,
            ];
        }
    }

    // Function to handle error resolution
    function resolveError($mysqli, $ticket_id, $resolution_image, $resolver_id)
    {
        try {
            // Start transaction
            $mysqli->begin_transaction();
            Log::channel('proxy')->info("resolveError called for ticket_id: $ticket_id");

            try {
                // First check if the ticket exists with error_type = USER_ERROR (regardless of status)
                $stmt = $mysqli->prepare("
                    SELECT t.*, fd.telegram_chat_id, fd.domain, t.id as ticket_db_id
                    FROM tickets t
                    LEFT JOIN form_domains fd ON t.domain_id = fd.id
                    WHERE t.ticket_id = ? AND t.error_type = 'USER_ERROR'
                ");

                if (! $stmt->execute([$ticket_id])) {
                    throw new Exception("Failed to check ticket: " . $stmt->error);
                }

                $ticket = $stmt->get_result()->fetch_assoc();

                // If not found by ticket_id, try by database ID
                if (! $ticket && is_numeric($ticket_id)) {
                    Log::channel('proxy')->error("Ticket not found by ticket_id with USER_ERROR type, trying database ID: $ticket_id");

                    $stmt = $mysqli->prepare("
                        SELECT t.*, fd.telegram_chat_id, fd.domain, t.id as ticket_db_id
                        FROM tickets t
                        LEFT JOIN form_domains fd ON t.domain_id = fd.id
                        WHERE t.id = ? AND t.error_type = 'USER_ERROR'
                    ");

                    if (! $stmt->execute([$ticket_id])) {
                        throw new Exception("Failed to check ticket by ID: " . $stmt->error);
                    }

                    $ticket = $stmt->get_result()->fetch_assoc();
                }

                // Debug - show available USER_ERROR tickets
                if (! $ticket) {
                    Log::channel('proxy')->error("No matching USER_ERROR ticket found. Showing available USER_ERROR tickets:");
                    $debug_stmt    = $mysqli->query("SELECT id, ticket_id, status, error_type FROM tickets WHERE error_type = 'USER_ERROR' LIMIT 10");
                    $debug_tickets = [];
                    while ($row = $debug_stmt->fetch_assoc()) {
                        $debug_tickets[] = $row;
                    }
                    Log::channel('proxy')->info("Available USER_ERROR tickets: " . print_r($debug_tickets, true));
                    throw new Exception('Ticket not found with USER_ERROR type');
                }

                // Log the ticket found
                Log::channel('proxy')->info("Found USER_ERROR ticket: ID=" . $ticket['id'] . ", ticket_id=" . $ticket['ticket_id'] . ", status=" . $ticket['status'] . ", error_type=" . $ticket['error_type']);

                // Update the ticket status and resolution details
                $update_field = is_numeric($ticket_id) && $ticket_id == $ticket['id'] ? 'id' : 'ticket_id';
                $update_value = $update_field == 'id' ? $ticket['id'] : $ticket['ticket_id'];

                Log::channel('proxy')->info("Updating ticket with $update_field = $update_value");
                $stmt = $mysqli->prepare("
                    UPDATE tickets SET
                        status = 'resolved',
                        resolution_image = ?,
                        resolved_at = NOW(),
                        resolved_by = ?,
                        resolution_notified = 0
                    WHERE $update_field = ?
                ");

                if (! $stmt->execute([$resolution_image, $resolver_id, $update_value])) {
                    throw new Exception("Failed to update ticket: " . $stmt->error);
                }

                Log::channel('proxy')->info("Ticket status updated to resolved");

                // Also add the resolution image to completion_images table
                $stmt = $mysqli->prepare("
                    INSERT INTO completion_images
                        (form_id, image_path, created_at, uploaded_by)
                    VALUES (?, ?, NOW(), 'fulfiller')
                ");

                if (! $stmt->execute([$ticket['ticket_db_id'], $resolution_image])) {
                    Log::channel('proxy')->error("Failed to add resolution image to completion_images: " . $stmt->error);
                } else {
                    Log::channel('proxy')->info("Added resolution image to completion_images table");
                }

                // Send notification to Telegram if chat_id exists
                if ($ticket['telegram_chat_id']) {
                    $message = "✅ Error Resolved!\n\n" .
                        "🎫 ID: {$ticket['ticket_id']}\n" .
                        "💰 Amount: {$ticket['amount']}\n" .
                        "👤 Facebook: {$ticket['facebook_name']}\n" .
                        "🎮 Game: {$ticket['game']}\n" .
                        "🆔 Game ID: {$ticket['game_id']}";

                    // Send resolution image with caption
                    $photo_response = sendTelegramPhoto($ticket['telegram_chat_id'], $resolution_image, $message);

                    if ($photo_response && isset($photo_response['ok']) && $photo_response['ok']) {
                        // Mark as notified
                        $stmt = $mysqli->prepare("
                            UPDATE tickets SET
                                resolution_notified = 1
                            WHERE ticket_id = ?
                        ");
                        $stmt->execute([$ticket_id]);
                    }
                }

                // Commit transaction
                $mysqli->commit();

                return [
                    'status'  => 'success',
                    'message' => 'Error resolved successfully',
                    'ticket'  => $ticket,
                ];

            } catch (Exception $e) {
                // If any operation fails, roll back the transaction
                $mysqli->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            Log::channel('proxy')->error("Error in resolveError: " . $e->getMessage());
            return [
                'status'  => 'error',
                'message' => 'Failed to resolve error',
                'details' => $e->getMessage(),
            ];
        }
    }

    // Get action from request
    $action   = $_GET['action'] ?? '';
    $response = [];

    switch ($action) {
        case 'getPendingForms':
            $response = getPendingForms($mysqli);
            break;

        case 'update':
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            Log::channel('proxy')->info("========== Request from IP: " . $client_ip);

            ksort($_POST);
            $signature = $_POST['signature'] ?? '';
            unset($_POST['signature']);
            $original = base64_encode(json_encode($_POST, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $hash     = hash_hmac('sha256', $original, env('TELEGRAM_BOT_SECRET_KEY'));

            Log::channel('proxy')->info($signature);
            Log::channel('proxy')->info($hash);

            if ($signature !== $hash) {
                $message_id = sendTelegramMessage(\App\Constants\Telegram::CHAT_IDS['solidda'], '🚨 An unauthenticated request was detected.');
                if ($message_id) {
                    Log::channel('proxy')->info("Notification for unauthenticated request sent successfully");
                } else {
                    Log::channel('proxy')->error("Failed to send text notification for unauthenticated request");
                }
            }

            // Validate required parameters
            if (! isset($_POST['ticket_id']) || ! isset($_POST['status'])) {
                die(return_error('Missing required parameters'));
            }

            $result = updateFormStatus(
                $mysqli,
                $_POST['ticket_id'],
                $_POST['status'],
                $_POST['chat_group_id'] ?? null,
                $_POST['validation_image'] ?? null
            );

            echo json_encode($result);
            exit;

        case 'status_changes':
            try {
                // First check if the telegram columns exist
                $check_columns = $mysqli->query("SHOW COLUMNS FROM tickets LIKE 'telegram_message_id'");
                if ($check_columns->num_rows === 0) {
                    // Add the columns if they don't exist
                    $mysqli->query("ALTER TABLE tickets
                        ADD COLUMN telegram_message_id BIGINT DEFAULT NULL,
                        ADD COLUMN telegram_chat_id BIGINT DEFAULT NULL");

                    // Create the index
                    $mysqli->query("CREATE INDEX idx_telegram_message ON tickets (telegram_message_id, telegram_chat_id)");
                }

                // Now run the main query
                $query = "SELECT
                            t.*,
                            GROUP_CONCAT(DISTINCT ci.image_path) as completion_images,
                            fd.domain,
                            fd.telegram_chat_id
                        FROM tickets t
                        INNER JOIN form_domains fd ON t.domain_id = fd.id
                        LEFT JOIN completion_images ci ON t.id = ci.form_id
                        WHERE t.status = 'completed'  # Simplified condition for now
                        AND fd.telegram_chat_id IS NOT NULL
                        GROUP BY t.id
                        ORDER BY t.created_at ASC
                        LIMIT 10";

                $result = $mysqli->query($query);

                if (! $result) {
                    throw new Exception($mysqli->error);
                }

                $tickets = [];
                while ($row = $result->fetch_assoc()) {
                    $tickets[] = $row;
                }

                $response = [
                    'status'  => 'success',
                    'tickets' => $tickets,
                ];

            } catch (Exception $e) {
                Log::channel('proxy')->error("Error in status_changes: " . $e->getMessage());
                $response = [
                    'status'  => 'error',
                    'message' => 'Failed to process status changes',
                    'details' => $e->getMessage(),
                ];
            }
            break;

        case 'send_notification':
            Log::channel('proxy')->info("=== NOTIFICATION REQUEST RECEIVED ===");

            try {
                if (empty($_POST['ticket_id'])) {
                    Log::channel('proxy')->error("No ticket_id provided in request");
                    throw new Exception('No ticket_id provided');
                }

                $ticket_id   = $_POST['ticket_id'];
                $chat_id     = isset($_POST['chat_id']) ? $_POST['chat_id'] : null;
                $first_image = isset($_POST['first_image']) ? $_POST['first_image'] : null;
                $all_images  = isset($_POST['all_images']) ? $_POST['all_images'] : null;

                Log::channel('proxy')->info("Notification for ticket: " . $ticket_id);

                // Optimize the query - only select fields we actually need
                $stmt = $mysqli->prepare("
                    SELECT t.ticket_id, t.status, t.amount, t.facebook_name, t.game, t.game_id,
                           fd.telegram_chat_id, t.completion_images
                    FROM tickets t
                    LEFT JOIN form_domains fd ON t.domain_id = fd.id
                    WHERE t.ticket_id = ?
                ");

                if (! $stmt->bind_param('s', $ticket_id)) {
                    throw new Exception("Failed to bind parameter: " . $stmt->error);
                }

                if (! $stmt->execute()) {
                    throw new Exception("Failed to execute ticket lookup: " . $stmt->error);
                }

                $ticket = $stmt->get_result()->fetch_assoc();

                if (! $ticket) {
                    Log::channel('proxy')->error("No ticket found. Ticket ID: " . $ticket_id);
                    throw new Exception('Ticket not found');
                }

                // Use chat_id from request if provided, otherwise use the one from the ticket
                if (! $chat_id) {
                    $chat_id = $ticket['telegram_chat_id'];
                }

                if (! $chat_id) {
                    throw new Exception('No telegram_chat_id for ticket');
                }

                // Ensure we have an image to send
                if (! $first_image) {
                    if (isset($_POST['all_images']) && $_POST['all_images']) {
                        $images      = explode(',', $_POST['all_images']);
                        $first_image = trim($images[0]);
                    } elseif (isset($ticket['completion_images']) && $ticket['completion_images']) {
                        $images      = explode(',', $ticket['completion_images']);
                        $first_image = trim($images[0]);
                    }
                }

                if (! $first_image) {
                    Log::channel('proxy')->info("No image available for notification. Sending text only.");
                }

                // Create the message
                $message = "✅ Ticket completed!\n\n" .
                    "🎫 ID: {$ticket['ticket_id']}\n" .
                    "💰 Amount: {$ticket['amount']}\n" .
                    "👤 Facebook: {$ticket['facebook_name']}\n" .
                    "🎮 Game: {$ticket['game']}\n" .
                    "🆔 Game ID: {$ticket['game_id']}";

                $message_sent = false;
                $message_id   = null;

                // Send the notification
                if ($first_image) {
                    Log::channel('proxy')->info("Sending photo notification with image: " . $first_image);

                    // Send photo with caption
                    $photo_response = sendTelegramPhoto($chat_id, $first_image, $message);

                    if ($photo_response && isset($photo_response['ok']) && $photo_response['ok']) {
                        $message_sent = true;
                        $message_id   = isset($photo_response['result']['message_id']) ?
                        $photo_response['result']['message_id'] : 1;

                        Log::channel('proxy')->info("Photo notification sent successfully");
                    } else {
                        Log::channel('proxy')->error("Failed to send photo notification, falling back to text");
                    }
                }

                // If photo failed, send as text
                if (! $message_sent) {
                    Log::channel('proxy')->info("Sending text notification");
                    $message_id = sendTelegramMessage($chat_id, $message);
                    if ($message_id) {
                        $message_sent = true;
                        Log::channel('proxy')->info("Text notification sent successfully");
                    } else {
                        Log::channel('proxy')->error("Failed to send text notification");
                    }
                }

                // Update the ticket if message was sent
                if ($message_sent && $message_id) {
                    $update_stmt = $mysqli->prepare("
                        UPDATE tickets
                        SET telegram_message_id = ?,
                            telegram_chat_id = ?,
                            completed_notified = 1
                        WHERE ticket_id = ?
                    ");

                    if ($update_stmt) {
                        $update_stmt->bind_param('sss', $message_id, $chat_id, $ticket_id);
                        $update_stmt->execute();
                        Log::channel('proxy')->info("Updated ticket with notification info");
                    }
                }

                // Always return a success response to the client
                header('Content-Type: application/json');
                echo json_encode([
                    'status'     => $message_sent ? 'success' : 'error',
                    'message'    => $message_sent ? 'Notification sent' : 'Failed to send notification',
                    'message_id' => $message_id,
                ]);
                exit;

            } catch (Exception $e) {
                Log::channel('proxy')->error("Error in send_notification: " . $e->getMessage());

                header('Content-Type: application/json');
                echo json_encode([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ]);
                exit;
            }
            break;

        case 'get_groups':
            $response = getBotGroups($mysqli);
            break;

        case 'set_group':
            // Get JSON data from request body
            $json_data = json_decode(file_get_contents('php://input'), true);
            if ($json_data && isset($json_data['group_type']) && isset($json_data['group_id'])) {
                $response = setBotGroup($mysqli, $json_data['group_type'], $json_data['group_id']);
            } else {
                $response = [
                    'status'  => 'error',
                    'message' => 'Missing group_type or group_id in request',
                ];
            }
            break;

        case 'fulfilled':
            $response = getFulfilledTickets($mysqli);
            break;

        case 'decline':
            $json_data = json_decode(file_get_contents('php://input'), true);
            if ($json_data && isset($json_data['ticket_id'])) {
                $ticket_id = $mysqli->real_escape_string($json_data['ticket_id']);
                $query     = "UPDATE tickets SET status = 'declined' WHERE ticket_id = ?";
                $stmt      = $mysqli->prepare($query);
                $stmt->bind_param('s', $ticket_id);
                $result = $stmt->execute();

                $response = [
                    'status'  => $result ? 'success' : 'error',
                    'message' => $result ? 'Ticket declined successfully' : 'Failed to decline ticket',
                    'details' => $result ? null : $mysqli->error,
                ];
            } else {
                $response = [
                    'status'  => 'error',
                    'message' => 'Missing ticket_id in request',
                ];
            }
            break;

        case 'get_image':
            // Verify bot token for security
            $provided_token = $_GET['bot_token'] ?? '';
            if (! hash_equals('7958920210:AAFjG8oiAC6wJN7I9fx2ldeoOXHLKnpDwbU', $provided_token)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Unauthorized',
                ]);
                exit;
            }

            $image_path = $_GET['path'] ?? '';
            if (empty($image_path)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'No image path provided',
                ]);
                exit;
            }

            // Construct full path (ensure it's within allowed directory)
            $full_path = '/home/tapsndrc/public_html/' . ltrim($image_path, '/');

            // Validate path is within uploads directory
            if (strpos($full_path, '/home/tapsndrc/public_html/uploads/completions/') !== 0) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Invalid image path',
                ]);
                exit;
            }

            // Check if file exists
            if (! file_exists($full_path)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Image not found',
                ]);
                exit;
            }

            // Get image content and type
            $image_data = file_get_contents($full_path);
            $finfo      = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type  = finfo_file($finfo, $full_path);
            finfo_close($finfo);

            // Send image directly
            header('Content-Type: ' . $mime_type);
            echo $image_data;
            exit;

        // case 'report_error':
        //     try {
        //         // Validate required parameters
        //         $required = ['ticket_id', 'error_type', 'error_details', 'reporter_id'];
        //         $missing  = [];
        //         foreach ($required as $param) {
        //             if (! isset($_POST[$param])) {
        //                 $missing[] = $param;
        //             }
        //         }

        //         if (! empty($missing)) {
        //             Log::channel('proxy')->error("Missing parameters for report_error: " . implode(', ', $missing));
        //             $response = [
        //                 'status'  => 'error',
        //                 'message' => "Missing required parameters: " . implode(', ', $missing),
        //                 'found'   => false,
        //             ];
        //         } else {
        //             Log::channel('proxy')->info("Processing error report for ticket: " . $_POST['ticket_id']);
        //             Log::channel('proxy')->error("Error type: " . $_POST['error_type'] . ", Reporter: " . $_POST['reporter_id']);

        //             $result = reportError(
        //                 $mysqli,
        //                 $_POST['ticket_id'],
        //                 $_POST['error_type'],
        //                 $_POST['error_details'],
        //                 $_POST['reporter_id']
        //             );

        //             Log::channel('proxy')->error("Error report result: " . json_encode($result));

        //             // If ticket is found but Telegram message wasn't sent, try to send it with any submitted images
        //             if (isset($result['found']) && $result['found'] && isset($result['ticket']) &&
        //                 (! isset($result['message_id']) || ! $result['message_id'])) {

        //                 Log::channel('proxy')->info("Initial error notification might have failed, checking for submitted images...");

        //                 // Look up submitted images for this ticket
        //                 $imgStmt = $mysqli->prepare("
        //                     SELECT GROUP_CONCAT(ci.image_path) as images
        //                     FROM completion_images ci
        //                     WHERE ci.form_id = ?
        //                 ");

        //                 if ($imgStmt && $imgStmt->bind_param('i', $result['ticket']['ticket_db_id'])) {
        //                     $imgStmt->execute();
        //                     $imgResult = $imgStmt->get_result()->fetch_assoc();

        //                     if ($imgResult && ! empty($imgResult['images'])) {
        //                         Log::channel('proxy')->info("Found submitted images: " . $imgResult['images']);
        //                         $images     = explode(',', $imgResult['images']);
        //                         $firstImage = trim($images[0]);

        //                         // Create error message for Telegram
        //                         $message = "⚠️ ERROR REPORTED ⚠️\n\n" .
        //                             "🎫 ID: {$result['ticket']['ticket_id']}\n" .
        //                             "💰 Amount: {$result['ticket']['amount']}\n" .
        //                             "👤 Facebook: {$result['ticket']['facebook_name']}\n";

        //                         if (! empty($result['ticket']['game'])) {
        //                             $message .= "🎮 Game: {$result['ticket']['game']}\n";
        //                         }

        //                         if (! empty($result['ticket']['game_id'])) {
        //                             $message .= "🆔 Game ID: {$result['ticket']['game_id']}\n";
        //                         }

        //                         $message .= "\n🚫 Error Type: " . $_POST['error_type'] . "\n" .
        //                             "🔍 Details: " . $_POST['error_details'];

        //                         // Try sending with image
        //                         Log::channel('proxy')->info("Attempting to send error with first image: " . $firstImage);
        //                         $photo_response = sendTelegramPhoto(
        //                             $result['ticket']['telegram_chat_id'],
        //                             $firstImage,
        //                             $message
        //                         );

        //                         if ($photo_response && isset($photo_response['ok']) && $photo_response['ok']) {
        //                             Log::channel('proxy')->info("Successfully sent error notification with image");

        //                             // Update the ticket with Telegram message info
        //                             if (isset($photo_response['result']['message_id'])) {
        //                                 $message_id = $photo_response['result']['message_id'];
        //                                 $chat_id    = $result['ticket']['telegram_chat_id'];

        //                                 $msgStmt = $mysqli->prepare("
        //                                     UPDATE tickets
        //                                     SET telegram_message_id = ?,
        //                                         telegram_chat_id = ?
        //                                     WHERE ticket_id = ?
        //                                 ");

        //                                 if ($msgStmt) {
        //                                     $msgStmt->bind_param('sss', $message_id, $chat_id, $_POST['ticket_id']);
        //                                     $msgStmt->execute();
        //                                     Log::channel('proxy')->info("Updated ticket with Telegram message info");
        //                                 }
        //                             }
        //                         } else {
        //                             Log::channel('proxy')->error("Failed to send error notification with image. Falling back to text message.");
        //                             // Fall back to plain text message if photo fails
        //                             $message_id = sendTelegramMessage($result['ticket']['telegram_chat_id'], $message);
        //                             if ($message_id) {
        //                                 Log::channel('proxy')->info("Successfully sent fallback text notification, message ID: " . $message_id);
        //                             }
        //                         }
        //                     } else {
        //                         Log::channel('proxy')->info("No images found for ticket. Trying plain text notification.");
        //                         // No images found, try sending just the text message again
        //                         $message = "⚠️ ERROR REPORTED ⚠️\n\n" .
        //                             "🎫 ID: {$result['ticket']['ticket_id']}\n" .
        //                             "💰 Amount: {$result['ticket']['amount']}\n" .
        //                             "👤 Facebook: {$result['ticket']['facebook_name']}\n";

        //                         if (! empty($result['ticket']['game'])) {
        //                             $message .= "🎮 Game: {$result['ticket']['game']}\n";
        //                         }

        //                         if (! empty($result['ticket']['game_id'])) {
        //                             $message .= "🆔 Game ID: {$result['ticket']['game_id']}\n";
        //                         }

        //                         $message .= "\n🚫 Error Type: " . $_POST['error_type'] . "\n" .
        //                             "🔍 Details: " . $_POST['error_details'];

        //                         $message_id = sendTelegramMessage($result['ticket']['telegram_chat_id'], $message);
        //                         if ($message_id) {
        //                             Log::channel('proxy')->info("Successfully sent text notification on second attempt, message ID: " . $message_id);
        //                         }
        //                     }
        //                 }
        //             }

        //             // For security, don't expose chat ID in response
        //             if (isset($result['found']) && $result['found'] && isset($result['ticket'])) {
        //                 unset($result['ticket']['telegram_chat_id']);
        //             }

        //             $response = $result;
        //         }

        //         // Ensure proper JSON response is sent
        //         header('Content-Type: application/json');
        //         echo json_encode($response);
        //         exit;

        //     } catch (Exception $e) {
        //         Log::channel('proxy')->error("Exception in report_error: " . $e->getMessage());
        //         echo json_encode([
        //             'status'  => 'error',
        //             'message' => 'Exception occurred: ' . $e->getMessage(),
        //             'found'   => false,
        //         ]);
        //         exit;
        //     }
        //     break;

        case 'verify_message':
            try {
                $message_id = $_GET['message_id'] ?? null;
                $chat_id    = $_GET['chat_id'] ?? null;

                if (! $message_id && ! $chat_id) {
                    $response = [
                        'status'  => 'error',
                        'message' => 'Missing both message_id and chat_id',
                    ];
                    break;
                }

                // Build the query based on available parameters
                $query = "SELECT t.ticket_id, t.status, t.amount, t.facebook_name, t.game, t.game_id
                         FROM tickets t
                         WHERE ";

                $params = [];
                $types  = "";

                if ($message_id && $chat_id) {
                    // If we have both, use exact match
                    $query .= "t.telegram_message_id = ? AND t.telegram_chat_id = ?";
                    $params = [$message_id, $chat_id];
                    $types  = "ss";
                } else if ($chat_id) {
                    // If we only have chat_id, find most recent notification in that chat
                    $query .= "t.telegram_chat_id = ? AND t.status IN ('completed', 'completed_notified')
                              ORDER BY t.completed_at DESC LIMIT 1";
                    $params = [$chat_id];
                    $types  = "s";
                } else {
                    // If we only have message_id (unlikely), try to match it
                    $query .= "t.telegram_message_id = ? AND t.status IN ('completed', 'completed_notified')";
                    $params = [$message_id];
                    $types  = "s";
                }

                $stmt = $mysqli->prepare($query);

                if (! $stmt) {
                    throw new Exception("Failed to prepare statement: " . $mysqli->error);
                }

                if (! empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }

                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();

                $response = [
                    'status'         => 'success',
                    'ticket_id'      => $result ? $result['ticket_id'] : null,
                    'ticket_details' => $result ?: null,
                ];

            } catch (Exception $e) {
                Log::channel('proxy')->error("Error in verify_message: " . $e->getMessage());
                $response = [
                    'status'  => 'error',
                    'message' => 'Failed to verify message',
                    'details' => $e->getMessage(),
                ];
            }
            break;

        case 'resolve_error':
            try {
                // Validate required parameters
                if (! isset($_POST['ticket_id']) || ! isset($_POST['resolution_image']) || ! isset($_POST['resolver_id'])) {
                    throw new Exception('Missing required parameters');
                }

                $result = resolveError(
                    $mysqli,
                    $_POST['ticket_id'],
                    $_POST['resolution_image'],
                    $_POST['resolver_id']
                );

                echo json_encode($result);
                exit;

            } catch (Exception $e) {
                echo return_error('Failed to resolve error', $e->getMessage());
                exit;
            }
            break;

        default:
            $response = [
                'status'            => 'success',
                'message'           => 'API is running',
                'available_actions' => ['pending', 'update', 'get_groups', 'set_group'],
            ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo return_error('Unexpected error', $e->getMessage());
} finally {
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
