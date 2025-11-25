<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Category;
use Database; // Ensure Database is accessible

class CategoryController {
    
    // API: Get All Categories (Public)
    public function getAll() {
        $model = new Category();
        $cats = $model->getAll();
        jsonResponse($cats);
    }

    // API: Create Category (Admin Only)
    public function create() {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        
        if (!$name) {
            jsonResponse(['error' => 'Category name is required'], 400);
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        $model = new Category();
        $model->create($name, $slug);
        
        jsonResponse(['message' => 'Category created', 'slug' => $slug]);
    }

    // API: Update Category (Admin Only)
    public function update($id) {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';

        if (!$name) {
            jsonResponse(['error' => 'Category name is required'], 400);
        }

        // Note: We update the NAME, but usually keep the SLUG same to prevent broken links
        // If you want to update slug too, you'd need to update all products using that slug.
        // For now, we just update the display name.
        
        $collection = \Database::getInstance()->getDb()->categories;
        
        $result = $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => ['name' => $name]]
        );

        jsonResponse(['message' => 'Category updated']);
    }
}