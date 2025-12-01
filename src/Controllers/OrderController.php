<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Product;
use Miziedi\Models\Order;
use Database; // Ensure Database is accessible

class OrderController {

    /* ============================================================
       SHOPPING CART LOGIC
       ============================================================ */

    // GET /cart
    public function cart() {
        $cartItems = [];
        $subtotal = 0;

        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $productModel = new Product();
            
            foreach ($_SESSION['cart'] as $key => $qty) {
                // Key format is "ID_SIZE" or just "ID"
                $parts = explode('_', $key);
                $id = $parts[0];
                $size = $parts[1] ?? null;

                $product = $productModel->getById($id);
                if ($product) {
                    $itemTotal = $product['price'] * $qty;
                    $subtotal += $itemTotal;
                    $cartItems[] = [
                        'key' => $key, // Unique key for removal
                        'product' => $product,
                        'size' => $size,
                        'qty' => $qty,
                        'total' => $itemTotal
                    ];
                }
            }
        }

        view('cart', [
            'pageTitle' => 'Your Bag', 
            'cartItems' => $cartItems, 
            'subtotal' => $subtotal
        ]);
    }

    // POST /cart/add
    public function addToCart() {
        $id = $_POST['product_id'] ?? null;
        $qty = (int)($_POST['quantity'] ?? 1);
        $size = $_POST['size'] ?? null;

        if ($id) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            // Create unique key for ID + Size (e.g., 123abc_L)
            $cartKey = $size ? $id . '_' . $size : $id;

            if (isset($_SESSION['cart'][$cartKey])) {
                $_SESSION['cart'][$cartKey] += $qty;
            } else {
                $_SESSION['cart'][$cartKey] = $qty;
            }

            // Remove item if quantity is 0 or less
            if ($_SESSION['cart'][$cartKey] <= 0) {
                unset($_SESSION['cart'][$cartKey]);
            }
        }

        // --- RECALCULATION LOGIC (AJAX SUPPORT) ---
        $productModel = new Product();
        $globalSubtotal = 0;
        $currentItemTotal = 0;
        $currentQty = 0;

        // Recalculate totals based on session
        foreach ($_SESSION['cart'] as $key => $q) {
            $parts = explode('_', $key);
            $pid = $parts[0];
            $prod = $productModel->getById($pid);
            if ($prod) {
                $lineTotal = $prod['price'] * $q;
                $globalSubtotal += $lineTotal;

                // Data for the specific item changed
                if (isset($cartKey) && $key === $cartKey) {
                    $currentItemTotal = $lineTotal;
                    $currentQty = $q;
                }
            }
        }

        $totalItems = array_sum($_SESSION['cart']);

        // Return JSON if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'cartCount' => $totalItems,
                'itemTotal' => number_format($currentItemTotal),
                'globalSubtotal' => number_format($globalSubtotal),
                'newQty' => $currentQty
            ]);
            exit;
        }

        // Standard Fallback (Redirect)
        header('Location: /cart');
        exit;
    }

    // POST /cart/remove
    public function removeFromCart() {
        // Remove by the unique Cart Key
        $key = $_POST['cart_key'] ?? null;
        if ($key && isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }
        header('Location: /cart');
        exit;
    }

    /* ============================================================
       CHECKOUT & PAYSTACK API
       ============================================================ */

    // GET /checkout (The View)
    public function checkout() { 
        view('checkout', ['pageTitle' => 'Checkout']); 
    }

    // POST /api/checkout
    public function createOrder() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $reference = $input['reference'] ?? null;
        $customer = $input['customer'] ?? [];

        if (!$reference || empty($_SESSION['cart'])) {
            jsonResponse(['error' => 'Invalid request or empty cart'], 400);
        }

        // 1. Verify Transaction with Paystack API
        $secretKey = $_ENV['PAYSTACK_SECRET_KEY'];
        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache"
        ]);
        
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            jsonResponse(['error' => 'Payment verification failed (Network)'], 500);
        }

        $result = json_decode($response, true);

        if (!$result || !isset($result['data']['status']) || $result['data']['status'] !== 'success') {
            jsonResponse(['error' => 'Payment verification failed (Invalid Status)'], 400);
        }

        // 2. Prepare Order Items & Stock Update
        $productModel = new Product();
        $pdo = \Database::getInstance()->getPdo(); // Get MySQL PDO connection
        
        $items = [];
        $subtotal = 0;

        foreach ($_SESSION['cart'] as $key => $qty) {
            $parts = explode('_', $key);
            $id = $parts[0];
            $size = $parts[1] ?? null;

            $product = $productModel->getById($id);
            
            if ($product) {
                $price = (float)$product['price'];
                
                $items[] = [
                    'product_id' => $id,
                    'title' => $product['name'] ?? $product['title'], // Handle name variation
                    'size' => $size,
                    'price' => $price,
                    'qty' => $qty,
                    'image' => $product['image_url'] ?? '/assets/images/logo.svg'
                ];
                $subtotal += $price * $qty;

                // SQL: Decrement Stock
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$qty, $id]);
            }
        }

        // 3. Fetch Fees from Settings (SQL)
        $stmt = $pdo->prepare("SELECT * FROM settings WHERE type = 'general'");
        $stmt->execute();
        $settings = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $deliveryFee = $settings['delivery_fee'] ?? 10000; 
        $taxLabel = $settings['tax_label'] ?? 'TBD';
        
        $finalTotal = $subtotal + $deliveryFee;

        // 4. Security Check
        $paystackAmount = $result['data']['amount'] / 100; 
        
        if ($paystackAmount < $finalTotal) {
            jsonResponse(['error' => "Amount mismatch. Paid: $paystackAmount, Expected: $finalTotal"], 400);
        }

        // 5. Create Order
        $invoiceNumber = 'INV-' . strtoupper(uniqid());
        $orderModel = new Order();
        
        $orderData = [
            'invoice_number' => $invoiceNumber,
            'customer' => $customer,
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'tax_label' => $taxLabel,
            'total_amount' => $finalTotal,
            'status' => 'paid', 
            'paystack_reference' => $reference,
            'history' => [
                ['status' => 'paid', 'note' => 'Payment verified via Paystack', 'date' => date('Y-m-d H:i:s')]
            ]
        ];

        $orderModel->create($orderData);

        // 6. Finish
        unset($_SESSION['cart']);

        jsonResponse(['message' => 'Order created', 'invoice_number' => $invoiceNumber]);
    }

    // POST /api/paystack/webhook
    public function paystackWebhook() {
        $input = @file_get_contents("php://input");
        $event = json_decode($input, true);

        if(!$event) exit();

        $secretKey = $_ENV['PAYSTACK_SECRET_KEY'];
        if($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $secretKey)) {
            exit();
        }

        http_response_code(200);
    }

    /* ============================================================
       TRACKING & ADMIN API
       ============================================================ */

    // GET /track (View)
    public function trackPage() { 
        view('track_order', ['pageTitle' => 'Track Order']); 
    }

    // GET /api/admin/orders (Admin Only)
    public function getAllOrders() {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $orderModel = new Order();
        $ordersRaw = $orderModel->getAll();

        // Clean Data for JSON output (MySQL version)
        $cleanOrders = [];
        foreach ($ordersRaw as $order) {
            // Date format
            $date = 'N/A';
            if (isset($order['created_at']) && $order['created_at'] instanceof \DateTime) {
                $date = $order['created_at']->format('M d, Y h:i A');
            } elseif (is_string($order['created_at'])) {
                $date = date('M d, Y h:i A', strtotime($order['created_at']));
            }

            // Handle JSON Columns from SQL (items, customer)
            // Model usually handles this, but we ensure array format here
            $items = is_string($order['items']) ? json_decode($order['items'], true) : $order['items'];
            $customer = is_string($order['customer_info']) ? json_decode($order['customer_info'], true) : ($order['customer'] ?? []);

            $cleanItems = [];
            if (is_array($items)) {
                foreach ($items as $item) {
                    $cleanItems[] = [
                        'title' => $item['title'] ?? $item['name'] ?? 'Unknown Item',
                        'qty'   => $item['qty'] ?? 1,
                        'price' => $item['price'] ?? 0,
                        'size'  => $item['size'] ?? null
                    ];
                }
            }

            // Build clean structure
            $cleanOrders[] = [
                '_id' => ['$oid' => $order['id']], // Maintain structure for frontend compatibility
                'invoice_number' => $order['invoice_number'] ?? 'N/A',
                'customer' => $customer ?? ['name' => 'Guest', 'phone' => ''],
                'total_amount' => $order['total_amount'] ?? 0,
                'status' => $order['status'] ?? 'pending',
                'items' => $cleanItems,
                'created_at_formatted' => $date
            ];
        }
        
        jsonResponse($cleanOrders);
    }

    // PUT /api/admin/order/{id}/status (Admin Only)
    public function updateStatus($id) {
        if (!isset($_SESSION['admin_id'])) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? null;
        $note = $data['note'] ?? '';

        if($status) {
            $orderModel = new Order();
            $orderModel->updateStatus($id, $status, $note);
            jsonResponse(['message' => 'Order updated']);
        } else {
            jsonResponse(['error' => 'Status required'], 400);
        }
    }
}
