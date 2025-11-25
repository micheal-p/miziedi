<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Category;
use Database;

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

    // NEW: Render Settings Page
    public function settingsPage() {
        $this->checkAuth();
        view('admin/settings', ['pageTitle' => 'System Settings']);
    }

    // NEW API: Save Settings
    public function saveSettings() {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $deliveryFee = $_POST['delivery_fee'] ?? 10000;
        $taxLabel = $_POST['tax_label'] ?? 'TBD';
        $tagline = $_POST['invoice_tagline'] ?? '';

        $db = Database::getInstance()->getDb();
        $settings = $db->settings->findOne(['type' => 'general']);

        // Handle Signature Upload
        $sigPath = $settings['ceo_signature'] ?? '/assets/images/ceo.png';

        if (isset($_FILES['ceo_signature']) && $_FILES['ceo_signature']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $filename = 'ceo_' . uniqid() . '.png'; // Force PNG or retain extension
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['ceo_signature']['tmp_name'], $targetPath)) {
                $sigPath = '/assets/images/' . $filename;
            }
        }

        // Update DB
        $db->settings->updateOne(
            ['type' => 'general'],
            ['$set' => [
                'delivery_fee' => (float)$deliveryFee,
                'tax_label' => $taxLabel,
                'invoice_tagline' => $tagline,
                'ceo_signature' => $sigPath,
                'updated_at' => new \MongoDB\BSON\UTCDateTime()
            ]],
            ['upsert' => true]
        );

        jsonResponse(['message' => 'Settings saved']);
    }
}