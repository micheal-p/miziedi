<div class="container main-cart-wrapper">
    <h1 class="cart-title">Your Bag</h1>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <p>Your bag is empty.</p>
            <a href="/" class="btn">Start Shopping</a>
        </div>
    <?php else: ?>
        
        <div class="cart-grid">
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                    <?php 
                        $product = $item['product'];
                        $title = $product['name'] ?? $product['title'] ?? 'Untitled Product';
                        $img = $product['image_url'] ?? $product['image'] ?? '/assets/images/PLACEHOLDER.jpg';
                        $cartKey = $item['key'];
                        $size = $item['size'] ?? null;
                        $qty = $item['qty'];
                        $price = $product['price'];
                    ?>
                    <div class="cart-item" id="item-<?= $cartKey ?>">
                        <div class="cart-image">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>">
                        </div>
                        
                        <div class="cart-details">
                            <div class="cart-header">
                                <h3 class="item-title"><?= htmlspecialchars($title) ?></h3>
                                <div class="item-price" id="price-display-<?= $cartKey ?>">₦<?= number_format($item['total']) ?></div>
                            </div>

                            <div class="item-meta">
                                <?php if ($size): ?>
                                    <span class="meta-badge">Size: <strong><?= htmlspecialchars($size) ?></strong></span>
                                <?php else: ?>
                                    <span class="meta-text">One Size</span>
                                <?php endif; ?>
                            </div>

                            <div class="cart-actions-row">
                                <div class="qty-pill small">
                                    <button id="btn-minus-<?= $cartKey ?>" onclick="updateCartQty('<?= $cartKey ?>', '<?= $product['_id'] ?>', -1, <?= $qty ?>)" class="qty-btn">−</button>
                                    <span id="qty-display-<?= $cartKey ?>" class="qty-val"><?= $qty ?></span>
                                    <button id="btn-plus-<?= $cartKey ?>" onclick="updateCartQty('<?= $cartKey ?>', '<?= $product['_id'] ?>', 1, <?= $qty ?>)" class="qty-btn">+</button>
                                </div>

                                <form action="/cart/remove" method="POST">
                                    <input type="hidden" name="cart_key" value="<?= $cartKey ?>">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="cart-subtotal">₦<?= number_format($subtotal) ?></span>
                </div>
                <div class="summary-note">Shipping & taxes calculated at checkout</div>
                <a href="/checkout" class="btn btn-block checkout-btn">PROCEED TO CHECKOUT</a>
            </div>
        </div>

    <?php endif; ?>
</div>

<style>
    /* --- LAYOUT --- */
    .main-cart-wrapper { margin-top: 40px; margin-bottom: 100px; }
    .cart-title { font-family: var(--font-primary); font-size: 2rem; font-weight: 800; margin-bottom: 30px; }
    .empty-cart { text-align: center; padding: 80px 0; font-size: 1.2rem; color: #666; }

    /* Desktop Grid: 2 Columns (Items | Summary) */
    .cart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 50px; align-items: start; }

    /* --- ITEM CARD --- */
    .cart-item {
        display: flex; gap: 25px; border-bottom: 1px solid #e5e5e5; padding-bottom: 30px; margin-bottom: 30px;
    }
    
    .cart-image {
        width: 120px; height: 150px; flex-shrink: 0;
        background: #f5f5f5; border-radius: 15px; overflow: hidden;
    }
    .cart-image img { width: 100%; height: 100%; object-fit: cover; }

    .cart-details { flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    
    .cart-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .item-title { font-size: 1.1rem; font-weight: 700; font-family: var(--font-primary); margin: 0; line-height: 1.4; }
    .item-price { font-weight: 700; font-size: 1.1rem; white-space: nowrap; }

    .item-meta { margin: 10px 0; font-size: 0.9rem; }
    .meta-badge { background: #f0f0f0; padding: 4px 10px; border-radius: 6px; color: #333; }
    .meta-text { color: #999; font-style: italic; }

    .cart-actions-row { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }

    /* --- CONTROLS --- */
    .qty-pill.small {
        display: inline-flex; align-items: center;
        border: 1px solid #ddd; border-radius: 25px; padding: 2px;
        width: 100px; justify-content: space-between; height: 35px;
    }
    .qty-btn { width: 30px; height: 100%; background: none; border: none; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .qty-val { font-weight: 700; font-size: 0.9rem; }
    
    .remove-btn { background: none; border: none; color: #666; text-decoration: underline; cursor: pointer; font-size: 0.85rem; padding: 0; }
    .remove-btn:hover { color: #000; }

    /* --- SUMMARY --- */
    .cart-summary {
        background: #f9f9f9; padding: 30px; border-radius: 20px; position: sticky; top: 100px;
    }
    .summary-row { display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: 800; margin-bottom: 10px; font-family: var(--font-primary); }
    .summary-note { font-size: 0.85rem; color: #666; margin-bottom: 20px; }
    .checkout-btn { padding: 18px; font-size: 1rem; letter-spacing: 1px; }

    /* --- MOBILE RESPONSIVE --- */
    @media (max-width: 768px) {
        .cart-grid { grid-template-columns: 1fr; gap: 30px; }
        
        .cart-item { gap: 15px; }
        .cart-image { width: 90px; height: 110px; }
        
        .item-title { font-size: 1rem; }
        .item-price { font-size: 1rem; }
        
        .cart-summary { position: relative; top: 0; margin-top: 0; }
        
        /* Fix sticky bottom button on mobile? Optional, but good UX */
        /* .checkout-btn { position: fixed; bottom: 20px; left: 20px; right: 20px; width: auto; z-index: 100; box-shadow: 0 10px 30px rgba(0,0,0,0.3); } */
    }
</style>

<script>
// AJAX Update for Quantity
async function updateCartQty(cartKey, productId, change, currentQty) {
    if (currentQty + change < 1) return; // Prevent 0

    const formData = new FormData();
    formData.append('product_id', productId);
    
    // We extract the size from the key (ID_SIZE)
    const parts = cartKey.split('_');
    if(parts[1]) formData.append('size', parts[1]);
    
    formData.append('quantity', change); // +1 or -1

    try {
        const res = await fetch('/cart/add', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            // 1. Update the Quantity Number (Middle of Pill)
            const qtyDisplay = document.getElementById('qty-display-' + cartKey);
            if (qtyDisplay) qtyDisplay.innerText = data.newQty;

            // 2. Update the Item Price
            const priceDisplay = document.getElementById('price-display-' + cartKey);
            if (priceDisplay) priceDisplay.innerText = '₦' + data.itemTotal;

            // 3. Update the Global Subtotal
            const subtotalDisplay = document.getElementById('cart-subtotal');
            if (subtotalDisplay) subtotalDisplay.innerText = '₦' + data.globalSubtotal;

            // 4. Update Header Badge
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.innerText = data.cartCount;
                badge.style.display = 'flex';
            }

            // 5. Update Buttons OnClick (To prevent stale data)
            const btnMinus = document.getElementById('btn-minus-' + cartKey);
            const btnPlus = document.getElementById('btn-plus-' + cartKey);
            
            if(btnMinus) btnMinus.setAttribute('onclick', `updateCartQty('${cartKey}', '${productId}', -1, ${data.newQty})`);
            if(btnPlus) btnPlus.setAttribute('onclick', `updateCartQty('${cartKey}', '${productId}', 1, ${data.newQty})`);
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
