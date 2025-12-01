<?php
namespace Miziedi\Models;
use Database;
use PDO;

class Category {
    private $pdo;
    public function __construct() { $this->pdo = Database::getInstance()->getPdo(); }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($name, $slug) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        $stmt->execute([$name, $slug]);
    }
}
