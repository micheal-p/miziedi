<?php
// FETCH CATEGORIES DYNAMICALLY FOR THE MENU
$categoryModel = new \Miziedi\Models\Category();
$navCategories = $categoryModel->getAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Miziedi' ?> | Premium Gear</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

    <header class="site-header">
        <div class="container header-inner">
            <button class="mobile-toggle" aria-label="Menu" onclick="toggleMenu()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>

            <a href="/" class="logo">
                <img src="/assets/images/logo.svg" alt="Miziedi Logo">
            </a>

            <nav class="desktop-nav">
                <?php foreach($navCategories as $cat): ?>
                    <a href="/?category=<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
                <a href="/track">Track Order</a>
            </nav>

            <div class="header-icons">
                <button class="icon-link hidden-mobile" onclick="toggleSearch()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
                
                <a href="/admin/login" class="icon-link">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </a>

                <a href="/cart" class="icon-link cart-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    <?php $count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
                    <span id="cart-count" class="cart-badge" style="<?= $count > 0 ? '' : 'display:none;' ?>"><?= $count ?></span>
                </a>
            </div>
        </div>

        <div id="search-bar" class="search-bar-container">
            <form action="/" method="GET" style="width: 100%; display:flex; justify-content:center;">
                <input type="text" name="search" class="search-input" placeholder="Search for products...">
            </form>
        </div>
        
        <div id="mobile-menu" class="mobile-menu">
            <div class="mobile-menu-header">
                <span class="logo"><img src="/assets/images/logo.svg" alt="Miziedi"></span>
                <button class="close-btn" onclick="toggleMenu()">&times;</button>
            </div>
            <nav class="mobile-nav-links">
                <?php foreach($navCategories as $cat): ?>
                    <a href="/?category=<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
                <a href="/track">Track Order</a>
                <a href="/admin/login">Admin Access</a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <?php 
        if (isset($path) && file_exists(__DIR__ . '/' . $path . '.php')) {
            include __DIR__ . '/' . $path . '.php';
        } else {
            echo "<div class='container' style='padding:50px; text-align:center;'>View not found: " . htmlspecialchars($path ?? 'Unknown') . "</div>";
        }
        ?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-links">
                <div class="col">
                    <h4>Shop</h4>
                    <a href="/new-arrivals">New Arrivals</a>
                    <a href="/best-sellers">Best Sellers</a>
                </div>
                <div class="col">
                    <h4>Help</h4>
                    <a href="/track">Order Status</a>
                    <a href="/returns">Returns</a>
                </div>
                <div class="col">
                    <h4>About</h4>
                    <a href="/story">Our Story</a>
                    <a href="/sustainability">Sustainability</a>
                </div>
                <div class="col">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg></a>
                        <a href="#"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Miziedi. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="/assets/js/main.js"></script>
</body>
</html>