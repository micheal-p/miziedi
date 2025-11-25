<?php
// Fetch Settings for Fee Display
$db = Database::getInstance()->getDb();
$settings = $db->settings->findOne(['type' => 'general']);
$deliveryFee = $settings['delivery_fee'] ?? 10000;
$taxLabel = $settings['tax_label'] ?? 'TBD';

// Calculate Subtotal (FIXED LOGIC)
$subtotal = 0;
if(isset($_SESSION['cart'])) {
    $pModel = new \Miziedi\Models\Product();
    
    foreach($_SESSION['cart'] as $key => $qty) {
        // FIX: Split the key to get the real Product ID (ignoring the size suffix)
        $parts = explode('_', $key);
        $id = $parts[0]; // "64f..." instead of "64f..._L"
        
        $p = $pModel->getById($id);
        if($p) {
            $subtotal += $p['price'] * $qty;
        }
    }
}

// Calculate Final Total
$total = $subtotal + $deliveryFee;

// Redirect if empty (Security)
if($subtotal <= 0) {
    header('Location: /cart');
    exit;
}
?>

<div class="container" style="max-width: 600px; margin-top: 50px; margin-bottom: 80px;">
    <h1 style="font-family: var(--font-primary); margin-bottom: 30px;">Checkout</h1>
    
    <div style="background: #f9f9f9; padding: 30px; border-radius: 20px;">
        
        <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #ddd;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Subtotal</span>
                <span>₦<?= number_format($subtotal) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span>Estimated Delivery</span>
                <span>₦<?= number_format($deliveryFee) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #666;">
                <span>Estimated Tax</span>
                <span><?= $taxLabel ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.3rem; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ccc;">
                <span>Total to Pay</span>
                <span>₦<?= number_format($total) ?></span>
            </div>
        </div>

        <form id="paymentForm">
            <div style="margin-bottom: 15px;">
                <label>Full Name</label>
                <input type="text" id="name" class="form-control" required style="width: 100%;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Email Address</label>
                <input type="email" id="email" class="form-control" required style="width: 100%;">
            </div>

            <div style="margin-bottom: 15px;">
                <label>Phone Number</label>
                <input type="tel" id="phone" class="form-control" required style="width: 100%;">
            </div>

            <div style="margin-bottom: 20px;">
                <label>Delivery Address</label>
                <textarea id="address" rows="3" class="form-control" required style="width: 100%;"></textarea>
            </div>

            <button type="submit" class="btn btn-block" style="padding: 18px; font-size: 1rem;">Pay ₦<?= number_format($total) ?></button>
        </form>
        <p id="msg" style="text-align: center; margin-top: 10px; font-weight: bold;"></p>
    </div>
</div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
    const paymentForm = document.getElementById('paymentForm');
    const publicKey = "<?= $_ENV['PAYSTACK_PUBLIC_KEY'] ?? '' ?>";
    const amount = <?= $total * 100 ?>; // Total in Kobo

    paymentForm.addEventListener("submit", function(e) {
        e.preventDefault();
        
        const email = document.getElementById("email").value;
        const name = document.getElementById("name").value;
        const phone = document.getElementById("phone").value;
        const address = document.getElementById("address").value;

        if(!publicKey) {
            alert("Paystack Public Key not set in .env");
            return;
        }

        const handler = PaystackPop.setup({
            key: publicKey,
            email: email,
            amount: amount,
            currency: "NGN",
            metadata: {
                custom_fields: [
                    { display_name: "Mobile Number", variable_name: "mobile_number", value: phone }
                ]
            },
            callback: function(response) {
                verifyTransaction(response.reference, { name, email, phone, address });
            },
            onClose: function() {
                alert('Transaction was not completed.');
            }
        });

        handler.openIframe();
    });

    async function verifyTransaction(reference, customer) {
        const msg = document.getElementById('msg');
        msg.innerText = "Verifying payment...";

        try {
            const res = await fetch('/api/checkout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reference, customer })
            });

            const data = await res.json();

            if (res.ok) {
                window.location.href = `/track?invoice=${data.invoice_number}&new=true`;
            } else {
                msg.innerText = "Verification failed: " + data.error;
                msg.style.color = "red";
            }
        } catch (err) {
            console.error(err);
            msg.innerText = "Network error occurred.";
        }
    }
</script>