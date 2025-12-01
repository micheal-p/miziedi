<?php
namespace Miziedi\Models;

use Database;
use PDO;

class Product {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getPdo();
    }

    // Fetch All Products (with Filters)
    public function getAll($filter = [], $limit = 100) {
        $sql = "SELECT * FROM products";
        $params = [];
        $clauses = [];

        // 1. Category Filter
        if (isset($filter['category']['$regex'])) {
            $clauses[] = "category LIKE ?";
            $params[] = "%" . $filter['category']['$regex'] . "%";
        }
        
        // 2. Search Filter
        if (isset($filter['$or'])) {
            $searchTerm = '';
            foreach($filter['$or'] as $cond) {
                if(isset($cond['name']['$regex'])) $searchTerm = $cond['name']['$regex'];
            }
            if($searchTerm) {
                $clauses[] = "(name LIKE ? OR description LIKE ?)";
                $params[] = "%$searchTerm%";
                $params[] = "%$searchTerm%";
            }
        }

        if (!empty($clauses)) {
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }

        $sql .= " ORDER BY id DESC LIMIT $limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Normalize every row (Convert JSON to Array)
        return array_map([$this, 'normalize'], $products);
    }

    // Fetch Single Product
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        return $product ? $this->normalize($product) : null;
    }

    // Create Product
    public function create($data) {
        $sql = "INSERT INTO products (
                    name, price, stock, category, description, image_url, images, sizes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['price'],
            $data['stock'] ?? 0,
            $data['category'],
            $data['description'],
            $data['image_url'],
            json_encode($data['images'] ?? []), // Save Gallery as JSON
            json_encode($data['sizes'] ?? [])   // Save Sizes as JSON
        ]);

        // Return ID object compatible with Controller logic
        $lastId = $this->pdo->lastInsertId();
        return (object)['getInsertedId' => function() use ($lastId) { return $lastId; }];
    }

    // Helper: Convert SQL Data types (JSON strings) to PHP Arrays
    private function normalize($row) {
        if (!$row) return null;
        
        // Alias ID for compatibility
        $row['_id'] = $row['id']; 
        
        // Decode Sizes
        $row['sizes'] = !empty($row['sizes']) ? json_decode($row['sizes'], true) : [];
        
        // Decode Images
        if (!empty($row['images'])) {
            $decoded = json_decode($row['images'], true);
            $row['images'] = is_array($decoded) ? $decoded : [];
        } else {
            $row['images'] = [];
        }

        return $row;
    }
}
