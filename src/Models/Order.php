<?php
namespace Miziedi\Models;

use Database;
use PDO;

class Order {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getPdo();
    }

    public function create($data) {
        $sql = "INSERT INTO orders (
            invoice_number, customer_info, items, subtotal, delivery_fee, 
            tax_label, total_amount, status, paystack_reference, history, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['invoice_number'],
            json_encode($data['customer']),
            json_encode($data['items']),
            $data['subtotal'],
            $data['delivery_fee'],
            $data['tax_label'],
            $data['total_amount'],
            $data['status'],
            $data['paystack_reference'],
            json_encode($data['history'])
        ]);
    }

    public function getByInvoice($invoice) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE invoice_number = ?");
        $stmt->execute([$invoice]);
        $order = $stmt->fetch();
        return $order ? $this->normalize($order) : null;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = $stmt->fetchAll();
        return array_map([$this, 'normalize'], $orders);
    }

    public function updateStatus($id, $status, $note = '') {
        // 1. Fetch current history
        $order = $this->getById($id);
        $history = $order['history'] ?? [];
        $history[] = ['status' => $status, 'note' => $note, 'date' => date('Y-m-d H:i:s')];

        // 2. Update
        $sql = "UPDATE orders SET status = ?, history = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$status, json_encode($history), $id]);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        return $order ? $this->normalize($order) : null;
    }

    private function normalize($row) {
        if (!$row) return null;
        $row['_id'] = $row['id'];
        $row['customer'] = json_decode($row['customer_info'], true);
        $row['items'] = json_decode($row['items'], true);
        $row['history'] = json_decode($row['history'], true);
        
        // Convert SQL datetime string to DateTime object for Views ->format()
        $row['created_at'] = new \DateTime($row['created_at']); 
        return $row;
    }
}
