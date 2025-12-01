<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Category;
use Database;
use PDO;

class AdminController {

    private function checkAuth() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
    }

    public function loginPage() {
        if (isset($_SESSION['admin_id'])) {
            header('Location: /admin/dashboard');
            exit;
        }
        view('admin/login', ['pageTitle' => 'Admin Login']);
    }

    public function dashboard() {
        $this->checkAuth();
        view('admin/dashboard', ['pageTitle' => 'Dashboard']);
    }

    public function productsPage() {
        $this->checkAuth();
        $catModel = new Category();
        $categories = $catModel->getAll();

        view('admin/products', [
            'pageTitle' => 'Manage Products',
            'categories' => $categories
        ]);
    }

    public function categoriesPage() {
        $this->checkAuth();
        view('admin/categories', ['pageTitle' => 'Manage Categories']);
    }

    public function ordersPage() {
        $this->checkAuth();
        view('admin/orders', ['pageTitle' => 'Manage Orders']);
    }

    // Render Settings Page
    public function settingsPage() {
        $this->checkAuth();
        view('admin/settings', ['pageTitle' => 'System Settings']);
    }

    // API: Save Settings (Converted to MySQL)
    public function saveSettings() {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $deliveryFee = $_POST['delivery_fee'] ?? 10000;
        $taxLabel = $_POST['tax_label'] ?? 'TBD';
        $tagline = $_POST['invoice_tagline'] ?? '';

        // 1. Connect via PDO
        $pdo = \Database::getInstance()->getPdo();
        
        // 2. Fetch current settings to get existing signature if needed
        $stmt = $pdo->prepare("SELECT * FROM settings WHERE type = 'general'");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        // Handle Signature Upload
        $sigPath = $settings['ceo_signature'] ?? '/assets/images/ceo.png';

        if (isset($_FILES['ceo_signature']) && $_FILES['ceo_signature']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'ceo_' . uniqid() . '.png';
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['ceo_signature']['tmp_name'], $targetPath)) {
                $sigPath = '/assets/images/' . $filename;
            }
        }

        // 3. Update or Insert (Upsert Logic)
        if ($settings) {
            // Update existing
            $sql = "UPDATE settings SET 
                    delivery_fee = ?, 
                    tax_label = ?, 
                    invoice_tagline = ?, 
                    ceo_signature = ?, 
                    updated_at = NOW() 
                    WHERE type = 'general'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$deliveryFee, $taxLabel, $tagline, $sigPath]);
        } else {
            // Insert new
            $sql = "INSERT INTO settings (type, delivery_fee, tax_label, invoice_tagline, ceo_signature, updated_at) 
                    VALUES ('general', ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$deliveryFee, $taxLabel, $tagline, $sigPath]);
        }

        jsonResponse(['message' => 'Settings saved']);
    }
}
