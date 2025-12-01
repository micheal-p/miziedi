<?php
namespace Miziedi\Controllers;

use Database;
use PDO;

class AuthController {
    
    // POST /api/admin/login
    public function adminLogin() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            jsonResponse(['error' => 'Email and password are required'], 400);
        }

        // 1. Connect to MySQL
        $pdo = \Database::getInstance()->getPdo();

        // 2. Query the 'admins' table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // 3. Verify Credentials
        // Note: Since the seed script stored plain text 'admin123', we compare directly.
        // In a production update later, you should use password_verify($password, $admin['password'])
        if ($admin && $password === $admin['password']) {
            
            // Set Session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_name'] = $admin['name'];

            jsonResponse(['message' => 'Login successful', 'redirect' => '/admin/dashboard']);
        } else {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }
}
