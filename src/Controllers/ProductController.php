<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Product;
use Miziedi\Models\Category;
use Database;

class ProductController {
    
    // GET / (Homepage with Filters)
    public function index() {
        $productModel = new Product();
        $categoryModel = new Category();

        $filter = [];
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $filter['category'] = ['$regex' => $_GET['category']];
        }
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $filter['$or'] = [['name' => ['$regex' => $search]], ['description' => ['$regex' => $search]]];
        }
        
        $products = $productModel->getAll($filter);
        $categories = $categoryModel->getAll();

        $pageTitle = 'Home';
        if (isset($_GET['category'])) {
            foreach ($categories as $cat) {
                if ($cat['slug'] == $_GET['category']) {
                    $pageTitle = $cat['name'];
                    break;
                }
            }
        } elseif (isset($_GET['search'])) {
            $pageTitle = 'Search: ' . htmlspecialchars($_GET['search']);
        }

        if (!empty($filter)) {
            view('product_list', ['products' => $products, 'categories' => $categories, 'pageTitle' => $pageTitle]);
        } else {
            view('home', ['products' => $products, 'categories' => $categories, 'pageTitle' => 'Home']);
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
        view('product_detail', ['product' => $product, 'pageTitle' => $pageTitle]);
    }

    // API: Create Product (Admin)
    public function create() {
        if (!isset($_SESSION['admin_id'])) jsonResponse(['error' => 'Unauthorized'], 401);

        $name = $_POST['name'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = (int)($_POST['stock'] ?? 0);
        $category = $_POST['category'] ?? 'uncategorized';
        $desc = $_POST['description'] ?? '';
        $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];

        if (!$name || !$price) jsonResponse(['error' => 'Name and Price required'], 400);

        // OPTIMIZED UPLOAD LOGIC
        $uploadedImages = [];
        $uploadDir = __DIR__ . '/../../public/assets/images/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$i];
                    $origName = $_FILES['images']['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    
                    $filename = 'prod_' . uniqid() . '_' . $i . '.' . $ext;
                    $destPath = $uploadDir . $filename;

                    // Use Smart Processor
                    if ($this->processImage($tmpName, $destPath, $ext)) {
                        $uploadedImages[] = '/assets/images/products/' . $filename;
                    }
                } elseif ($files['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    jsonResponse(['error' => "File upload failed. Error Code: " . $files['images']['error'][$i]], 400);
                }
            }
        }

        $mainImage = !empty($uploadedImages) ? $uploadedImages[0] : '/assets/images/logo.svg';

        $productModel = new Product();
        $result = $productModel->create([
            'name' => $name,
            'price' => (float)$price,
            'stock' => $stock,
            'category' => strtolower($category),
            'description' => $desc,
            'image_url' => $mainImage,
            'images' => $uploadedImages,
            'sizes' => $sizes
        ]);

        jsonResponse(['message' => 'Created', 'id' => (string)$result->getInsertedId()]);
    }

    // API: Update Product (Admin)
    public function update($id) {
        if (!isset($_SESSION['admin_id'])) jsonResponse(['error' => 'Unauthorized'], 401);

        $name = $_POST['name'] ?? null;
        $price = $_POST['price'] ?? 0;
        $stock = (int)($_POST['stock'] ?? 0);
        $category = $_POST['category'] ?? 'uncategorized';
        $desc = $_POST['description'] ?? '';
        $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];
        $setMainImage = $_POST['set_main_image'] ?? null;

        $productModel = new Product();
        $currentProduct = $productModel->getById($id);
        if (!$currentProduct) jsonResponse(['error' => 'Not found'], 404);

        // OPTIMIZED UPLOAD LOGIC
        $uploadedImages = [];
        $uploadDir = __DIR__ . '/../../public/assets/images/products/';
        
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $count = count($_FILES['images']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK && !empty($_FILES['images']['name'][$i])) {
                    $tmpName = $_FILES['images']['tmp_name'][$i];
                    $origName = $_FILES['images']['name'][$i];
                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                    
                    $filename = 'prod_' . uniqid() . '_' . $i . '.' . $ext;
                    $destPath = $uploadDir . $filename;

                    // Use Smart Processor
                    if ($this->processImage($tmpName, $destPath, $ext)) {
                        $uploadedImages[] = '/assets/images/products/' . $filename;
                    }
                } elseif ($files['images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    jsonResponse(['error' => "Upload Error Code: " . $files['images']['error'][$i]], 400);
                }
            }
        }

        // Safe Merge of Images
        $existingImages = [];
        if (!empty($currentProduct['images'])) {
            $existingImages = is_string($currentProduct['images']) ? json_decode($currentProduct['images'], true) : $currentProduct['images'];
        }
        // Ensure it's an array before merging
        if (!is_array($existingImages)) $existingImages = [];
        
        $finalImages = array_merge($existingImages, $uploadedImages);

        if ($setMainImage && !empty($setMainImage)) {
            $mainImage = $setMainImage;
        } elseif (!empty($uploadedImages)) {
            $mainImage = $uploadedImages[0];
        } else {
            $mainImage = $currentProduct['image_url'] ?? '/assets/images/logo.svg';
        }
        
        if(empty($mainImage) && !empty($finalImages)) {
            $mainImage = $finalImages[0];
        }

        $pdo = \Database::getInstance()->getPdo();
        $sql = "UPDATE products SET 
                name = ?, price = ?, stock = ?, category = ?, 
                description = ?, image_url = ?, images = ?, sizes = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, $price, $stock, strtolower($category), 
            $desc, $mainImage, json_encode($finalImages), json_encode($sizes), $id
        ]);

        jsonResponse(['message' => 'Updated']);
    }

    public function delete($id) {
        if (!isset($_SESSION['admin_id'])) jsonResponse(['error' => 'Unauthorized'], 401);
        \Database::getInstance()->getPdo()->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        jsonResponse(['message' => 'Deleted']);
    }
    
    public function getProductsApi() {
        $productModel = new Product();
        $products = $productModel->getAll();
        jsonResponse($products);
    }

    // --- HELPER: Smart Image Compression ---
    private function processImage($source, $destination, $ext) {
        // If Video, just move it (Don't try to compress)
        if (in_array($ext, ['mp4', 'mov', 'avi', 'webm'])) {
            return move_uploaded_file($source, $destination);
        }

        // If Image, Compress & Resize
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            list($width, $height) = getimagesize($source);
            $maxWidth = 1000; // Cap width at 1000px for speed

            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = floor($height * ($maxWidth / $width));
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            $srcResource = null;
            switch ($ext) {
                case 'jpg': case 'jpeg': $srcResource = imagecreatefromjpeg($source); break;
                case 'png': $srcResource = imagecreatefrompng($source); break;
                case 'webp': $srcResource = imagecreatefromwebp($source); break;
            }

            if (!$srcResource) return move_uploaded_file($source, $destination); // Fallback

            $dstResource = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve Transparency (PNG/WebP)
            if ($ext == 'png' || $ext == 'webp') {
                imagecolortransparent($dstResource, imagecolorallocatealpha($dstResource, 0, 0, 0, 127));
                imagealphablending($dstResource, false);
                imagesavealpha($dstResource, true);
            }

            // Resize
            imagecopyresampled($dstResource, $srcResource, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save Compressed
            $saved = false;
            switch ($ext) {
                case 'jpg': case 'jpeg': $saved = imagejpeg($dstResource, $destination, 80); break; // 80% Quality
                case 'png': $saved = imagepng($dstResource, $destination, 8); break; // Compression Level 8
                case 'webp': $saved = imagewebp($dstResource, $destination, 80); break; // 80% Quality
            }

            // Cleanup
            imagedestroy($srcResource);
            imagedestroy($dstResource);

            return $saved;
        }

        // Unknown format fallback
        return move_uploaded_file($source, $destination);
    }
}
