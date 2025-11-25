<?php
namespace Miziedi\Controllers;

use Database;

class AuthController {
    
    // POST /api/admin/login
    public function adminLogin() {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // For simplicity in this project, we check against ENV or DB.
        // Here we simulate a DB check. You should seed a real admin user in MongoDB.
        // Hardcoded fallback for initial access:
        $envEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@miziedi.com';
        
        // In a real app, query 'admins' collection and password_verify()
        if ($email === $envEmail && $password === 'admin123') {
            $_SESSION['admin_id'] = 'master_admin';
            jsonResponse(['message' => 'Login successful', 'redirect' => '/admin/dashboard']);
        } else {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
    }
}