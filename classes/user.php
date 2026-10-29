<?php
class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function register($username, $email, $password, $role = 'seller') {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :u OR email = :e");
            $stmt->execute([':u' => $username, ':e' => $email]);
            if ($stmt->fetch()) {
                $this->conn->rollBack();
                return "Username or email already exists.";
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->conn->prepare(
                "INSERT INTO users (username, email, password, role) 
                 VALUES (:u, :e, :p, :r)"
            );
            $stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash, ':r' => $role]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return "Registration failed: " . $e->getMessage();
        }
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = :u");
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>