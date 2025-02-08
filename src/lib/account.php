<?php declare(strict_types=1);

require_once(__DIR__ . '/record/Record.php');
require_once(__DIR__ . '/record/ColumnTypes.php');

class Account {
    private $db;
    private const PEPPER = "SediCMS_Secure_Pepper_2023"; // In production, this should be in a secure config

    public function __construct($db) {
        $this->db = $db;
    }

    public function createAccount($username, $password, $email) {
        if (empty($username) || empty($password) || empty($email)) {
            return false;
        }

        // Generate a random salt
        $salt = bin2hex(random_bytes(16));
        
        // Hash password with salt and pepper
        $peppered = hash_hmac("sha256", $password, self::PEPPER);
        $hashed = password_hash($salt . $peppered, PASSWORD_ARGON2ID);

        // Create account record
        $record = new Record('accounts', array());
        $record->field('username', ColumnTypes::VARCHAR, $username)
               ->field('password', ColumnTypes::TEXT, $hashed)
               ->field('salt', ColumnTypes::VARCHAR, $salt)
               ->field('email', ColumnTypes::VARCHAR, $email);

        return $this->db->insert_record($record);
    }

    public function verifyPassword($username, $password) {
        // Create record for query
        $queryRecord = new Record('accounts', array());
        $queryRecord->field('username', ColumnTypes::VARCHAR, $username);

        // Get user record
        $results = $this->db->get_records($queryRecord);
        if (empty($results)) {
            return false;
        }

        $user = $results[0];
        $storedHash = $user->get_field_property('password', 'value');
        $salt = $user->get_field_property('salt', 'value');
        
        // Recreate the hashed password using the stored salt and pepper
        $peppered = hash_hmac("sha256", $password, self::PEPPER);
        
        if (password_verify($salt . $peppered, $storedHash)) {
            return $user->get_field_property('id', 'value');
        }
        
        return false;
    }

    public function createSession($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Create session record
        $record = new Record('sessions', array());
        $record->field('user_id', ColumnTypes::INT, $userId)
               ->field('session_token', ColumnTypes::VARCHAR, $token)
               ->field('expires_at', ColumnTypes::TIMESTAMP, $expiresAt);

        if ($this->db->insert_record($record)) {
            return $token;
        }
        
        return false;
    }

    public function validateSession($token) {
        // Create record for query
        $queryRecord = new Record('sessions', array());
        $queryRecord->field('session_token', ColumnTypes::VARCHAR, $token);

        // Get session record
        $results = $this->db->get_records($queryRecord);
        if (empty($results)) {
            return false;
        }

        $session = $results[0];
        $expiresAt = strtotime($session->get_field_property('expires_at', 'value'));
        
        if ($expiresAt < time()) {
            return false;
        }
        
        return $session->get_field_property('user_id', 'value');
    }
}
