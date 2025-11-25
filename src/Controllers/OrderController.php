<?php
namespace Miziedi\Controllers;

use Miziedi\Models\Product;
use Miziedi\Models\Order;
use Database;

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

            // Prevent negative quantity (Remove if 0 or less)
            if ($_SESSION['cart'][$cartKey] <= 0) {
                unset($_SESSION['cart'][$cartKey]);
            }
        }

        // --- RECALCULATION LOGIC START ---
        $productModel = new Product();
        $globalSubtotal = 0;
        $currentItemTotal = 0;
        $currentQty = 0;

        // Loop to calculate new totals
        foreach ($_SESSION['cart'] as $key => $q) {
            $parts = explode('_', $key);
            $pid = $parts[0];
            $prod = $productModel->getById($pid);
            if ($prod) {
                $lineTotal = $prod['price'] * $q;
                $globalSubtotal += $lineTotal;

                // If this is the item we just updated
                if (isset($cartKey) && $key === $cartKey) {
                    $currentItemTotal = $lineTotal;
                    $currentQty = $q;
                }
            }
        }

        $totalItems = array_sum($_SESSION['cart']);

        // Check if AJAX Request (Fetch) - Return JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'cartCount' => $totalItems,
                'itemTotal' => number_format($currentItemTotal),   // New Item Total
                'globalSubtotal' => number_format($globalSubtotal), // New Subtotal
                'newQty' => $currentQty
            ]);
            exit;
        }
        // --- RECALCULATION LOGIC END ---

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
        
        // 1. Get Input
        $input = json_decode(file_get_contents('php://input'), true);
        $reference = $input['reference'] ?? null;
        $customer = $input['customer'] ?? [];

        if (!$reference || empty($_SESSION['cart'])) {
            jsonResponse(['error' => 'Invalid request or empty cart'], 400);
        }

        // 2. Verify Transaction with Paystack API
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

        // 3. Check Status
        if (!$result || !isset($result['data']['status']) || $result['data']['status'] !== 'success') {
            jsonResponse(['error' => 'Payment verification failed (Invalid Status)'], 400);
        }

        // 4. Re-calculate Total & Build Order Items (Security Step)
        $productModel = new Product();
        $db = Database::getInstance()->getDb(); // Connect for stock update
        $items = [];
        $subtotal = 0;

        foreach ($_SESSION['cart'] as $key => $qty) {
            $parts = explode('_', $key);
            $id = $parts[0];
            $size = $parts[1] ?? null;

            $product = $productModel->getById($id);
            
            if ($product) {
                $price = (float)$product['price'];
                
                // Handle DB field differences (name vs title, image_url vs image)
                $title = $product['name'] ?? $product['title'] ?? 'Untitled Product';
                $image = $product['image_url'] ?? $product['image'] ?? '/assets/images/logo.svg';

                $items[] = [
                    'product_id' => $id,
                    'title' => $title,
                    'size' => $size,
                    'price' => $price,
                    'qty' => $qty,
                    'image' => $image
                ];
                $subtotal += $price * $qty;

                // CORRECTION: Decrement Stock
                $db->products->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectId($id)],
                    ['$inc' => ['stock' => -1 * $qty]] // Subtract qty from stock
                );
            }
        }

        // 5. Add Dynamic Fees from DB
        $settings = $db->settings->findOne(['type' => 'general']);
        
        $deliveryFee = $settings['delivery_fee'] ?? 10000; 
        $taxLabel = $settings['tax_label'] ?? 'TBD';
        
        $finalTotal = $subtotal + $deliveryFee;

        // 6. SECURITY: Compare Paystack Amount vs Calculated Amount
        $paystackAmount = $result['data']['amount'] / 100; // Kobo to NGN
        
        // Allow small floating point differences, but block major fraud
        if ($paystackAmount < $finalTotal) {
            jsonResponse(['error' => "Amount mismatch. Paid: $paystackAmount, Expected: $finalTotal"], 400);
        }

        // 7. Create Order in DB
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
            'status' => 'paid', // Verified by Paystack
            'paystack_reference' => $reference,
            'created_at' => new \MongoDB\BSON\UTCDateTime(),
            'history' => [
                ['status' => 'paid', 'note' => 'Payment verified via Paystack', 'date' => new \MongoDB\BSON\UTCDateTime()]
            ]
        ];

        $orderModel->create($orderData);

        // 8. Clear Cart & Success
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

        // Optional: Handle specific webhook events here
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
        $ordersCursor = $orderModel->getAll();

        // CORRECTION: Clean Data for JSON output to avoid JS errors
        $cleanOrders = [];
        foreach ($ordersCursor as $order) {
            // Format Date safely
            $date = 'N/A';
            if (isset($order['created_at']) && $order['created_at'] instanceof \MongoDB\BSON\UTCDateTime) {
                $date = $order['created_at']->toDateTime()->format('M d, Y h:i A');
            }

            $id = (string)$order['_id'];

            // Clean Items
            $cleanItems = [];
            if (isset($order['items']) && (is_array($order['items']) || is_object($order['items']))) {
                foreach ($order['items'] as $item) {
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
                '_id' => ['$oid' => $id],
                'invoice_number' => $order['invoice_number'] ?? 'N/A',
                'customer' => $order['customer'] ?? ['name' => 'Guest', 'phone' => '', 'email' => '', 'address' => ''],
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