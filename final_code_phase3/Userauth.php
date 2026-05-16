<?php
require_once "DB.php";

class UserAuth {

    private mysqli $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getUserByEmail(string $email): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE emailaddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->num_rows > 0 ? $result->fetch_assoc() : null;
        $stmt->close();
        return $user;
    }

    public function isBlocked(string $email): bool {
        $stmt = $this->conn->prepare("SELECT id FROM blockeduser WHERE emailaddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $blocked = $result->num_rows > 0;
        $stmt->close();
        return $blocked;
    }

    public function verifyPassword(string $input, string $hashed): bool {
        return password_verify($input, $hashed);
    }

    public function emailExists(string $email): bool {
        $stmt = $this->conn->prepare("SELECT id FROM user WHERE emailaddress = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function registerUser(string $firstName, string $lastName, string $email, string $password, string $photoFileName = "default.png"): int {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userType = "user";
        $stmt = $this->conn->prepare(
            "INSERT INTO user (firstname, lastname, emailaddress, password, photofilename, usertype) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $firstName, $lastName, $email, $hashedPassword, $photoFileName, $userType);
        $stmt->execute();
        $newId = $this->conn->insert_id;
        $stmt->close();
        return $newId;
    }

    public function uploadProfilePhoto(array $fileData): string {
        if (empty($fileData['name'])) {
            return "default.png";
        }
        $extension   = strtolower(pathinfo(basename($fileData['name']), PATHINFO_EXTENSION));
        $newFileName = uniqid("user_", true) . "." . $extension;
        $uploadPath  = "uploads/" . $newFileName;
        if (move_uploaded_file($fileData['tmp_name'], $uploadPath)) {
            return $newFileName;
        }
        return "default.png";
    }
}