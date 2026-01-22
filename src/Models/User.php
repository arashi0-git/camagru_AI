<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model {
    public function create($username, $email, $password, $token) {
        $sql = "INSERT INTO users (username, email, password, verification_token) VALUES (:username, :email, :password, :token)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password, // Password should be hashed before calling this
            ':token' => $token
        ]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function verifyUser($token) {
        $sql = "UPDATE users SET is_verified = TRUE, verification_token = NULL WHERE verification_token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':token' => $token]);
    }

    public function setResetToken($email, $token) {
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $sql = "UPDATE users SET reset_token = :token, reset_expires_at = :expiry WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':token' => $token,
            ':expiry' => $expiry,
            ':email' => $email
        ]);
    }

    public function findByResetToken($token) {
        // PostgreSQL uses CURRENT_TIMESTAMP. Adjust syntax if needed but this is standard SQL mostly.
        $sql = "SELECT * FROM users WHERE reset_token = :token AND reset_expires_at > CURRENT_TIMESTAMP LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $password) {
        $sql = "UPDATE users SET password = :password, reset_token = NULL, reset_expires_at = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':password' => $password,
            ':id' => $id
        ]);
    }
}
