<?php
// File: /home/tapsndrc/common_lib/core/FormOptionsManager.php

/**
 * Form Options Manager
 *
 * Manages dynamic form options like games and payment methods
 */
class FormOptionsManager
{
    private $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        require_once __DIR__ . '/../config/db_config.php';

        $this->db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->db->connect_error) {
            die('Database connection failed: ' . $this->db->connect_error);
        }
    }

    /**
     * Get game options for a domain
     *
     * @param int $domainId Domain ID
     * @return array Game options
     */
    public function getGameOptions($domainId)
    {
        // Check if domain has specific options
        $stmt = $this->db->prepare('
            SELECT game_name FROM form_game_options
            WHERE domain_id = ?
            ORDER BY display_order, game_name
        ');

        $stmt->bind_param('i', $domainId);
        $stmt->execute();
        $result = $stmt->get_result();

        $options = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $options[] = $row['game_name'];
            }
        } else {
            // If no domain-specific options, get global options
            $result = $this->db->query('
                SELECT game_name FROM form_game_options
                WHERE domain_id IS NULL
                ORDER BY display_order, game_name
            ');

            while ($row = $result->fetch_assoc()) {
                $options[] = $row['game_name'];
            }
        }

        // Default games if none configured
        if (empty($options)) {
            $options = [
                'Golden Dragon',
                'Magic City',
                'V-Blink',
                'Ultra Panda',
                'Orion Star',
                'Fire Kirin',
                'Milky Way',
                'Panda Master',
                'River Sweeps',
                'Juwa',
                'Fire Phoenix',
            ];
        }

        return $options;
    }

    /**
     * Get payment methods for a domain
     *
     * @param int $domainId Domain ID
     * @return array Payment methods
     */
    public function getPaymentMethods($domainId)
    {
        // Check if domain has specific methods
        $stmt = $this->db->prepare('
            SELECT method_name FROM form_payment_methods
            WHERE domain_id = ? AND active = 1
            ORDER BY display_order, method_name
        ');

        $stmt->bind_param('i', $domainId);
        $stmt->execute();
        $result = $stmt->get_result();

        $methods = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $methods[] = $row['method_name'];
            }
        } else {
            // If no domain-specific methods, get global methods
            $result = $this->db->query('
                SELECT method_name FROM form_payment_methods
                WHERE domain_id IS NULL AND active = 1
                ORDER BY display_order, method_name
            ');

            while ($row = $result->fetch_assoc()) {
                $methods[] = $row['method_name'];
            }
        }

        // Default methods if none configured
        if (empty($methods)) {
            $methods = ['CashApp', 'Venmo', 'Zelle', 'PayPal'];
        }

        return $methods;
    }

    /**
     * Get form configuration for a domain
     *
     * @param int $domainId Domain ID
     * @return array Form configuration
     */
    public function getFormConfig($domainId)
    {
        // Check if domain has specific configuration
        $stmt = $this->db->prepare('
            SELECT header_text, footer_text FROM form_configurations
            WHERE domain_id = ?
            LIMIT 1
        ');

        $stmt->bind_param('i', $domainId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        // If no domain-specific config, get global config
        $result = $this->db->query('
            SELECT header_text, footer_text FROM form_configurations
            WHERE domain_id IS NULL
            LIMIT 1
        ');

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        // Default empty config if none found
        return [
            'header_text' => '',
            'footer_text' => '',
        ];
    }

    /**
     * Add a game option
     *
     * @param string $gameName Game name
     * @param int|null $domainId Domain ID (null for global)
     * @param int $displayOrder Display order
     * @return bool Success status
     */
    public function addGameOption($gameName, $domainId = null, $displayOrder = 0)
    {
        $stmt = $this->db->prepare('
            INSERT INTO form_game_options
            (game_name, domain_id, display_order, active)
            VALUES (?, ?, ?, 1)
        ');

        $domainIdParam = $domainId;
        $stmt->bind_param('sii', $gameName, $domainIdParam, $displayOrder);

        return $stmt->execute();
    }

    /**
     * Add a payment method
     *
     * @param string $methodName Method name
     * @param int|null $domainId Domain ID (null for global)
     * @param int $displayOrder Display order
     * @return bool Success status
     */
    public function addPaymentMethod($methodName, $domainId = null, $displayOrder = 0)
    {
        $stmt = $this->db->prepare('
            INSERT INTO form_payment_methods
            (method_name, domain_id, display_order, active)
            VALUES (?, ?, ?, 1)
        ');

        if ($domainId) {
            $stmt->bind_param('sii', $methodName, $domainId, $displayOrder);
        } else {
            $stmt->bind_param('sii', $methodName, null, $displayOrder);
        }

        return $stmt->execute();
    }

    /**
     * Update form configuration
     *
     * @param array $config Configuration data
     * @param int|null $domainId Domain ID (null for global)
     * @return bool Success status
     */
    public function updateFormConfig($config, $domainId = null)
    {
        // Check if config exists
        if ($domainId) {
            $stmt = $this->db->prepare('
                SELECT id FROM form_configurations
                WHERE domain_id = ?
                LIMIT 1
            ');
            $stmt->bind_param('i', $domainId);
        } else {
            $stmt = $this->db->prepare('
                SELECT id FROM form_configurations
                WHERE domain_id IS NULL
                LIMIT 1
            ');
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing config
            if ($domainId) {
                $stmt = $this->db->prepare('
                    UPDATE form_configurations
                    SET header_text = ?, footer_text = ?
                    WHERE domain_id = ?
                ');
                $stmt->bind_param('ssi', $config['header_text'], $config['footer_text'], $domainId);
            } else {
                $stmt = $this->db->prepare('
                    UPDATE form_configurations
                    SET header_text = ?, footer_text = ?
                    WHERE domain_id IS NULL
                ');
                $stmt->bind_param('ss', $config['header_text'], $config['footer_text']);
            }
        } else {
            // Insert new config
            $stmt = $this->db->prepare('
                INSERT INTO form_configurations
                (domain_id, header_text, footer_text)
                VALUES (?, ?, ?)
            ');

            if ($domainId) {
                $stmt->bind_param('iss', $domainId, $config['header_text'], $config['footer_text']);
            } else {
                $stmt->bind_param('iss', null, $config['header_text'], $config['footer_text']);
            }
        }

        return $stmt->execute();
    }

    /**
     * Delete a game option
     *
     * @param int $optionId Option ID
     * @return bool Success status
     */
    public function deleteGameOption($optionId)
    {
        $stmt = $this->db->prepare('DELETE FROM form_game_options WHERE id = ?');
        $stmt->bind_param('i', $optionId);

        return $stmt->execute();
    }

    /**
     * Delete a payment method
     *
     * @param int $methodId Method ID
     * @return bool Success status
     */
    public function deletePaymentMethod($methodId)
    {
        $stmt = $this->db->prepare('DELETE FROM form_payment_methods WHERE id = ?');
        $stmt->bind_param('i', $methodId);

        return $stmt->execute();
    }

    /**
     * Get all game options
     *
     * @return array All game options
     */
    public function getAllGameOptions()
    {
        $result = $this->db->query('
            SELECT id, game_name, domain_id, display_order, active
            FROM form_game_options
            ORDER BY domain_id, display_order, game_name
        ');

        $options = [];

        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }

        return $options;
    }

    /**
     * Get all payment methods
     *
     * @return array All payment methods
     */
    public function getAllPaymentMethods()
    {
        $result = $this->db->query('
            SELECT id, method_name, domain_id, display_order, active
            FROM form_payment_methods
            ORDER BY domain_id, display_order, method_name
        ');

        $methods = [];

        while ($row = $result->fetch_assoc()) {
            $methods[] = $row;
        }

        return $methods;
    }

    /**
     * Get a specific game option by ID
     *
     * @param int $gameId Game ID
     * @return array|null Game option data or null if not found
     */
    public function getGameOption($gameId)
    {
        $stmt = $this->db->prepare('
            SELECT id, game_name, domain_id, display_order, active
            FROM form_game_options
            WHERE id = ?
            LIMIT 1
        ');

        $stmt->bind_param('i', $gameId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Get game options specific to a domain
     *
     * @param int $domainId Domain ID
     * @return array Domain-specific game options
     */
    public function getGameOptionsForDomain($domainId)
    {
        $stmt = $this->db->prepare('
            SELECT id, game_name, domain_id, display_order, active
            FROM form_game_options
            WHERE domain_id = ?
            ORDER BY display_order, game_name
        ');

        $stmt->bind_param('i', $domainId);
        $stmt->execute();
        $result = $stmt->get_result();

        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }

        return $options;
    }

    /**
     * Get global game options
     *
     * @return array Global game options
     */
    public function getGlobalGameOptions()
    {
        $result = $this->db->query('
            SELECT id, game_name, domain_id, display_order, active
            FROM form_game_options
            WHERE domain_id IS NULL
            ORDER BY display_order, game_name
        ');

        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }

        return $options;
    }

    /**
     * Toggle game option active status
     *
     * @param int $gameId Game ID
     * @param bool $active Active status
     * @return bool Success status
     */
    public function toggleGameOption($gameId, $active)
    {
        $stmt = $this->db->prepare('
            UPDATE form_game_options
            SET active = ?
            WHERE id = ?
        ');

        $activeInt = $active ? 1 : 0;
        $stmt->bind_param('ii', $activeInt, $gameId);

        return $stmt->execute();
    }

    /**
     * Get all available games (both global and domain-specific)
     *
     * @return array All available games
     */
    public function getAllAvailableGames()
    {
        $result = $this->db->query('
            SELECT DISTINCT game_name
            FROM form_game_options
            ORDER BY game_name
        ');

        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row['game_name'];
        }

        return $games;
    }

    /**
     * Get enabled games for a domain
     *
     * @param int $domainId Domain ID
     * @return array Enabled games for the domain
     */
    public function getEnabledGamesForDomain($domainId)
    {
        $stmt = $this->db->prepare('
            SELECT game_name
            FROM form_game_options
            WHERE domain_id = ?
            ORDER BY display_order, game_name
        ');

        $stmt->bind_param('i', $domainId);
        $stmt->execute();
        $result = $stmt->get_result();

        $games = [];
        while ($row = $result->fetch_assoc()) {
            $games[] = $row['game_name'];
        }

        return $games;
    }

    /**
     * Update enabled games for a domain
     *
     * @param int $domainId Domain ID
     * @param array $enabledGames Array of enabled game names
     * @return bool Success status
     */
    public function updateEnabledGames($domainId, $enabledGames)
    {
        // Start transaction
        $this->db->begin_transaction();

        try {
            // Delete existing games for this domain
            $stmt = $this->db->prepare('DELETE FROM form_game_options WHERE domain_id = ?');
            $stmt->bind_param('i', $domainId);
            $stmt->execute();

            // Insert new enabled games
            $stmt = $this->db->prepare('
                INSERT INTO form_game_options
                (game_name, domain_id, display_order)
                VALUES (?, ?, ?)
            ');

            $order = 0;
            foreach ($enabledGames as $game) {
                $stmt->bind_param('sii', $game, $domainId, $order);
                $stmt->execute();
                $order++;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Search domains by name
     *
     * @param string $searchTerm Search term
     * @return array Matching domains
     */
    public function searchDomains($searchTerm)
    {
        $searchTerm = "%{$searchTerm}%";
        $stmt       = $this->db->prepare('
            SELECT id, domain, group_name
            FROM form_domains
            WHERE domain LIKE ? OR group_name LIKE ?
            ORDER BY domain
        ');

        $stmt->bind_param('ss', $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        $domains = [];
        while ($row = $result->fetch_assoc()) {
            $domains[] = $row;
        }

        return $domains;
    }

    /**
     * Get commission percentage for a domain
     *
     * @param int $domainId Domain ID
     * @return number Commission percentage for a domain
     */
    public function getCommissionPercentage($domainId)
    {
        $stmt = $this->db->prepare('
            SELECT IFNULL( commission_percentages.admin_customer, 4 ) + IFNULL( commission_percentages.distributor_customer, 0 ) as commission_percentage
            FROM form_domains
            LEFT JOIN commission_percentages ON form_domains.id = commission_percentages.domain_id
            WHERE form_domains.id = ?
        ');

        $stmt->bind_param('i', $domainId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row    = $result->fetch_assoc();

        return $row['commission_percentage'];
    }
}
