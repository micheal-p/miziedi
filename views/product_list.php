<div class="container" style="margin-top: 40px; margin-bottom: 80px;">
    
    <div style="margin-bottom: 40px; text-align: center;">
        <h1 style="font-family: var(--font-primary); font-size: 2.5rem; text-transform: uppercase;">
            <?= htmlspecialchars(ucfirst($_GET['category'] ?? 'All Products')) ?>
        </h1>
        <p style="color: #666;"><?= count($products) ?> items found</p>
    </div>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: #f9f9f9; border-radius: 20px;">
                <p style="font-size: 1.2rem; margin-bottom: 20px;">No products found in this category.</p>
                <a href="/" class="btn">Back to Home</a>
            </div>
        <?php else: ?>
            <?php foreach($products as $product): ?>
                <?php 
                    $id = (string)$product['_id'];
                    $title = $product['name'] ?? $product['title'] ?? 'Untitled';
                    $price = $product['price'] ?? 0;
                    $img = $product['image_url'] ?? $product['image'] ?? '/assets/images/PLACEHOLDER.jpg';
                ?>
                <div class="product-card">
                    <a href="/product/<?= $id ?>" class="product-image-link">
                        <div class="product-image">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
                            
                            <form action="/cart/add" method="POST" class="quick-add-form" onclick="event.stopPropagation();">
                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                <button type="submit" class="add-btn" aria-label="Add to Bag">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </form>
                        </div>
                    </a>
                    <div class="product-info">
                        <a href="/product/<?= $id ?>">
                            <h3 class="product-name"><?= htmlspecialchars($title) ?></h3>
                            <p class="product-price">₦<?= number_format($price) ?></p>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>