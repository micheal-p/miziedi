<section class="hero-marquee">
    <div class="marquee-slide active">
        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1920&h=1080&fit=crop" alt="Fashion">
        <div class="hero-overlay"></div>
        <div class="marquee-content">
            <h1>NEW SEASON ARRIVALS</h1>
            <p>Discover the latest trends in premium gear.</p>
            <a href="#shop" class="cta-btn">Shop Now</a>
        </div>
    </div>
    <div class="marquee-slide">
        <img src="https://images.unsplash.com/photo-1523381294911-8d3cead13475?w=1920&h=1080&fit=crop" alt="Men">
        <div class="hero-overlay"></div>
        <div class="marquee-content">
            <h1>MEN'S COLLECTION</h1>
            <p>Engineered for performance and style.</p>
            <a href="/?category=men" class="cta-btn">Shop Men</a>
        </div>
    </div>
    <div class="marquee-slide">
        <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=1920&h=1080&fit=crop" alt="Women">
        <div class="hero-overlay"></div>
        <div class="marquee-content">
            <h1>WOMEN'S EXCLUSIVES</h1>
            <p>Elevate your everyday wardrobe.</p>
            <a href="/?category=women" class="cta-btn">Shop Women</a>
        </div>
    </div>
</section>

<section class="featured-categories">
    <div class="container">
        <h2>Shop by Category</h2>
        
        <div class="category-grid">
            <?php foreach($categories as $cat): ?>
                <?php
                    // Default images based on slug
                    $bgImage = 'https://images.unsplash.com/photo-1523779105320-d1cd346ff52b?w=800&h=1000&fit=crop';
                    if($cat['slug'] == 'men') $bgImage = 'https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?w=800&h=1000&fit=crop';
                    if($cat['slug'] == 'women') $bgImage = 'https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=800&h=1000&fit=crop';
                ?>
                <a href="/?category=<?= $cat['slug'] ?>" class="category-card">
                    <img src="<?= $bgImage ?>" alt="<?= htmlspecialchars($cat['name']) ?>">
                    <div class="category-overlay"></div>
                    <h3><?= htmlspecialchars($cat['name']) ?></h3>
                    <span class="category-btn">Shop <?= htmlspecialchars($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="product-showcase">
    <div class="showcase-carousel-wrapper">
        
        <button class="showcase-toggle" id="showcaseToggle" onclick="toggleShowcaseState()" aria-label="Pause Showcase">
            <svg id="icon-pause" width="24" height="24" viewBox="0 0 24 24" fill="white">
                <rect x="6" y="4" width="4" height="16"></rect>
                <rect x="14" y="4" width="4" height="16"></rect>
            </svg>
            <svg id="icon-play" width="24" height="24" viewBox="0 0 24 24" fill="white" style="display: none;">
                <polygon points="5 3 19 12 5 21 5 3"></polygon>
            </svg>
        </button>

        <button class="showcase-nav showcase-prev" onclick="scrollShowcase(-1)">&#10094;</button>
        <button class="showcase-nav showcase-next" onclick="scrollShowcase(1)">&#10095;</button>

        <div class="showcase-grid" id="showcaseGrid">
            <a href="#" class="showcase-item">
                <div class="showcase-image">
                    <img src="https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800&h=1000&fit=crop" loading="lazy">
                </div>
                <div class="showcase-overlay">
                    <h3 class="showcase-title">HYDRENALITE™</h3>
                </div>
            </a>
            <div class="showcase-item showcase-video-item">
                <a href="#" class="showcase-link">
                    <div class="showcase-video">
                        <video autoplay muted loop playsinline>
                            <source src="https://videos.pexels.com/video-files/3205915/3205915-hd_1920_1080_25fps.mp4" type="video/mp4">
                        </video>
                    </div>
                    <div class="showcase-overlay">
                        <h3 class="showcase-title">COLLECTION 2025</h3>
                    </div>
                </a>
            </div>
            <a href="#" class="showcase-item">
                <div class="showcase-image">
                    <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=800&h=1000&fit=crop" loading="lazy">
                </div>
                <div class="showcase-overlay">
                    <h3 class="showcase-title">URBAN</h3>
                </div>
            </a>
        </div>
    </div>
</section>

<section id="shop" class="featured-products">
    <div class="container">
        <h2>Trending Now</h2>
        <div class="product-grid">
            <?php if (empty($products)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                    <p>No products found in this category.</p>
                    <a href="/" style="text-decoration: underline;">View all products</a>
                </div>
            <?php else: ?>
                <?php foreach(array_slice($products, 0, 6) as $product): ?>
                    <?php 
                        $id = (string)$product['_id'];
                        $title = $product['name'] ?? $product['title'] ?? 'Untitled';
                        $price = $product['price'] ?? 0;
                        
                        // Uses normalized Image Path
                        $img = $product['image_url'] ?? $product['image'] ?? '/assets/images/PLACEHOLDER.jpg';
                    ?>
                    <div class="product-card">
                        <a href="/product/<?= $id ?>" class="product-image-link">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($title) ?>" loading="lazy">
                                
                                <form action="/cart/add" method="POST" class="quick-add-form" onclick="event.stopPropagation();">
                                    <input type="hidden" name="product_id" value="<?= $id ?>">
                                    <button type="submit" class="add-btn" aria-label="Add to Bag">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
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
</section>