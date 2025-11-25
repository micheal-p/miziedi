<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Product;
use Miziedi\Models\Category;

class ProductController {
    
    // GET / (Homepage with Filters)
    public function index() {
        $productModel = new Product();
        $categoryModel = new Category();

        $filter = [];
        $isFiltering = false; // Flag to check if we are searching/filtering

        // Check for Category Filter
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $isFiltering = true;
            // Case-Insensitive Regex Match
            $filter['category'] = [
                '$regex' => '^' . preg_quote($_GET['category']) . '$', 
                '$options' => 'i'
            ];
        }

        // Check for Search Query
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $isFiltering = true;
            $search = preg_quote($_GET['search']);
            // Search in Name OR Description
            $filter['$or'] = [
                ['name' => ['$regex' => $search, '$options' => 'i']],
                ['description' => ['$regex' => $search, '$options' => 'i']]
            ];
        }

        $products = $productModel->getAll($filter);
        $categories = $categoryModel->getAll();

        // Logic Switch
        if ($isFiltering) {
            view('product_list', [
                'products' => $products,
                'categories' => $categories,
                'pageTitle' => ucfirst($_GET['category'] ?? 'Search Results')
            ]);
        } else {
            view('home', [
                'products' => $products,
                'categories' => $categories,
                'pageTitle' => 'Home'
            ]);
        }
    }

    // GET /product/{id}
    public function detail($id) {
        $productModel = new Product();
        $product = $productModel->getById($id);

        if (!$product) {
            http_response_code(404);
            echo "Product not found";
            return;
        }

        $pageTitle = $product['name'] ?? $product['title'] ?? 'Product Detail';

        view('product_detail', [
            'product' => $product,
            'pageTitle' => $pageTitle
        ]);
    }

    // API: Create Product (Admin)
    public function create() {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $name = $_POST['name'] ?? $_POST['title'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = (int)($_POST['stock'] ?? 0); // CORRECTION: Capture Stock
        $category = $_POST['category'] ?? 'uncategorized';
        $desc = $_POST['description'] ?? '';
        
        // Capture Sizes (Array)
        $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];

        if (!$name || !$price) {
            jsonResponse(['error' => 'Product Name and Price are required'], 400);
        }

        // Handle Image
        $webPath = '/assets/images/logo.svg'; // Default
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'prod_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $webPath = '/assets/images/products/' . $filename;
            }
        }

        $productModel = new Product();
        
        $result = $productModel->create([
            'name'        => $name,
            'price'       => (float)$price,
            'stock'       => $stock, // Save Stock
            'category'    => strtolower($category),
            'description' => $desc,
            'image_url'   => $webPath,
            'sizes'       => $sizes,
            'created_at'  => new \MongoDB\BSON\UTCDateTime()
        ]);

        jsonResponse(['message' => 'Product created', 'id' => (string)$result->getInsertedId()]);
    }

    // API: Update Product (Admin)
    public function update($id) {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $name = $_POST['name'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = (int)($_POST['stock'] ?? 0); // CORRECTION: Update Stock
        $category = $_POST['category'] ?? 'uncategorized';
        $desc = $_POST['description'] ?? '';
        $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];

        $productModel = new Product();
        $currentProduct = $productModel->getById($id);

        if (!$currentProduct) {
            jsonResponse(['error' => 'Product not found'], 404);
        }

        // Image Logic: Keep existing if no new file uploaded
        $imagePath = $currentProduct['image_url'] ?? '/assets/images/logo.svg';

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'prod_' . uniqid() . '.' . $extension;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $imagePath = '/assets/images/products/' . $filename;
            }
        }

        // Update Database
        $collection = \Database::getInstance()->getDb()->products;
        $result = $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => [
                'name'        => $name,
                'price'       => (float)$price,
                'stock'       => $stock, // Update Stock
                'category'    => strtolower($category),
                'description' => $desc,
                'image_url'   => $imagePath,
                'sizes'       => $sizes,
                'updated_at'  => new \MongoDB\BSON\UTCDateTime()
            ]]
        );

        jsonResponse(['message' => 'Product updated']);
    }

    // API: Delete Product
    public function delete($id) {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }
        $collection = \Database::getInstance()->getDb()->products;
        $collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        jsonResponse(['message' => 'Deleted']);
    }
    
    // API: Get Products JSON
    public function getProductsApi() {
        $productModel = new Product();
        $products = $productModel->getAll();
        jsonResponse($products);
    }
}