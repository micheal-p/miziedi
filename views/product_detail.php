<?php
// 1. Normalize Data
$title = $product['name'] ?? $product['title'] ?? 'Untitled Product';

// Handle Images
$images = [];
if (!empty($product['images'])) {
    $images = is_array($product['images']) ? $product['images'] : json_decode($product['images'], true);
} 
if (empty($images) && !empty($product['image_url'])) {
    $images[] = $product['image_url'];
} elseif (empty($images)) {
    $images[] = '/assets/images/PLACEHOLDER.jpg';
}
if (!is_array($images)) $images = [];
$mainImage = $images[0] ?? '/assets/images/PLACEHOLDER.jpg';

$category = $product['category'] ?? 'Gear';
$price = $product['price'] ?? 0;
$stock = $product['stock'] ?? 0;
$desc = $product['description'] ?? 'No description available.';
$id = (string)$product['_id'];

// 2. Robust Size Fetching
$sizes = [];
if (isset($product['sizes'])) {
    $sizes = is_array($product['sizes']) ? $product['sizes'] : json_decode($product['sizes'], true);
}
?>

<div class="container" style="margin-top: 40px; margin-bottom: 80px;">
    <div class="product-detail-layout">
        
        <div class="gallery-container">
            <div class="detail-image">
                <img id="mainImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($title) ?>" style="cursor: zoom-in;" onclick="openModal(this.src)">
            </div>
            <?php if (count($images) > 1): ?>
                <div class="thumbnail-grid">
                    <?php foreach ($images as $index => $src): ?>
                        <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" onclick="changeImage('<?= htmlspecialchars($src) ?>', this)">
                            <img src="<?= htmlspecialchars($src) ?>" alt="Thumbnail">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-info">
            <div class="breadcrumb">
                <a href="/">Home</a> / <a href="/?category=<?= htmlspecialchars($category) ?>"><?= ucfirst(htmlspecialchars($category)) ?></a>
            </div>

            <h1><?= htmlspecialchars($title) ?></h1>
            <div class="price">₦<?= number_format($price) ?></div>

            <div style="margin-bottom: 25px; font-size: 0.9rem;">
                <?php if($stock > 0): ?>
                    <span style="color: #27ae60; font-weight: 700; letter-spacing: 0.5px;">● In Stock (<?= $stock ?> units)</span>
                <?php else: ?>
                    <span style="color: #e74c3c; font-weight: 700; letter-spacing: 0.5px;">● Sold Out</span>
                <?php endif; ?>
            </div>

            <div class="description">
                <?= nl2br(htmlspecialchars($desc)) ?>
            </div>

            <form action="/cart/add" method="POST" class="add-cart-form">
                <input type="hidden" name="product_id" value="<?= $id ?>">
                
                <?php if (!empty($sizes)): ?>
                <div style="margin-bottom: 25px;">
                    <label style="font-weight: 700; display:block; margin-bottom: 10px; font-size: 0.9rem;">SELECT SIZE</label>
                    <div class="size-selector-container">
                        <?php foreach($sizes as $size): ?>
                            <input type="radio" name="size" id="size-<?= $size ?>" value="<?= $size ?>" class="size-input" required>
                            <label for="size-<?= $size ?>" class="size-label"><?= $size ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-bottom: 30px;">
                    <label style="font-weight: 700; display:block; margin-bottom: 10px; font-size: 0.9rem;">QUANTITY</label>
                    <div class="qty-pill">
                        <button type="button" onclick="adjustQty(-1)" class="qty-btn">−</button>
                        <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= $stock ?>" readonly class="qty-input">
                        <button type="button" onclick="adjustQty(1)" class="qty-btn">+</button>
                    </div>
                </div>

                <?php if($stock > 0): ?>
                    <button type="submit" class="btn btn-block add-to-bag-btn">Add to Bag</button>
                <?php else: ?>
                    <button type="button" class="btn btn-block" style="background: #eee; color: #999; cursor: not-allowed; border-color: #eee;">Sold Out</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<div id="imageModal" class="image-modal" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-content" id="fullScreenImage">
</div>

<style>
    /* Modal Styles */
    .image-modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 9999; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0,0,0,0.95); /* Black w/ opacity */
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.2s ease-in-out;
    }

    .modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 5px;
        animation: zoomIn 0.3s ease;
    }

    .close-modal {
        position: absolute;
        top: 20px;
        right: 30px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
        cursor: pointer;
        z-index: 10000;
    }

    .close-modal:hover,
    .close-modal:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }

    @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
    @keyframes zoomIn { from {transform: scale(0.9);} to {transform: scale(1);} }

    /* Layout */
    .product-detail-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: start; }

    /* Gallery Styles */
    .gallery-container { display: flex; flex-direction: column; gap: 15px; }
    
    .detail-image { 
        background: #f5f5f5; 
        border-radius: 20px; 
        aspect-ratio: 3/4; 
        overflow: hidden; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
    }
    .detail-image img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s; }
    
    .thumbnail-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 10px; }
    .thumbnail { aspect-ratio: 1/1; border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid transparent; opacity: 0.7; transition: 0.2s; }
    .thumbnail.active { border-color: var(--black); opacity: 1; }
    .thumbnail:hover { opacity: 1; }
    .thumbnail img { width: 100%; height: 100%; object-fit: cover; }

    /* Typography */
    .breadcrumb { font-size: 0.85rem; color: #888; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
    .breadcrumb a:hover { color: var(--black); }
    .detail-info h1 { font-family: var(--font-primary); font-size: 2.8rem; line-height: 1.1; margin-bottom: 15px; font-weight: 800; text-transform: uppercase; }
    .detail-info .price { font-size: 1.8rem; font-weight: 500; margin-bottom: 10px; font-family: var(--font-secondary); color: #333; }
    .description { margin-bottom: 30px; color: #555; font-size: 1rem; line-height: 1.7; }

    /* Quantity Pill */
    .qty-pill { display: inline-flex; align-items: center; border: 2px solid #e5e5e5; border-radius: 30px; padding: 5px; width: 160px; justify-content: space-between; }
    .qty-btn { width: 40px; height: 40px; border-radius: 50%; border: none; background: transparent; font-size: 1.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
    .qty-btn:hover { background: #f0f0f0; }
    .qty-input { width: 50px; text-align: center; border: none; background: transparent; font-size: 1.2rem; font-weight: 700; color: #000; appearance: textfield; -moz-appearance: textfield; }
    .qty-input::-webkit-outer-spin-button, .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    .add-to-bag-btn { padding: 18px; font-size: 1rem; letter-spacing: 1px; }

    /* Size UI */
    .size-selector-container { display: flex; gap: 10px; flex-wrap: wrap; }
    .size-input { display: none; }
    .size-label { min-width: 45px; height: 45px; padding: 0 10px; border-radius: 50%; border: 1px solid #ddd; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: 600; transition: 0.2s; }
    .size-label:hover { border-color: #000; }
    .size-input:checked + .size-label { background: #000; color: #fff; border-color: #000; transform: scale(1.1); }
    
    /* Mobile */
    @media (max-width: 768px) { 
        .product-detail-layout { grid-template-columns: 1fr; gap: 30px; } 
        .detail-image { aspect-ratio: 1/1; } 
        .detail-info h1 { font-size: 2rem; }
        .qty-pill { width: 100%; padding: 8px; }
        .add-to-bag-btn { padding: 20px; }
    }
</style>

<script>
function adjustQty(amount) {
    const input = document.getElementById('qty');
    const max = parseInt(input.getAttribute('max'));
    let val = parseInt(input.value) + amount;
    
    if (val < 1) val = 1;
    if (val > max) {
        val = max;
        alert("Only " + max + " items left in stock!");
    }
    input.value = val;
}

function changeImage(src, thumbnail) {
    const mainImage = document.getElementById('mainImage');
    mainImage.style.opacity = 0;
    
    setTimeout(() => {
        mainImage.src = src;
        mainImage.style.opacity = 1;
    }, 150);

    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
    thumbnail.classList.add('active');
}

// NEW: Modal Functions
function openModal(src) {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("fullScreenImage");
    modal.style.display = "flex"; // Flex to center
    modalImg.src = src;
    document.body.style.overflow = "hidden"; // Stop background scroll
}

function closeModal() {
    const modal = document.getElementById("imageModal");
    modal.style.display = "none";
    document.body.style.overflow = "auto"; // Restore scroll
}
</script>
