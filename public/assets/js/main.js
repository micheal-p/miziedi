// ==========================================
// GLOBAL VARIABLES
// ==========================================
let showcaseInterval;
let isPaused = false;

// ==========================================
// UI HELPERS
// ==========================================

// Toggle Mobile Menu
function toggleMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('active');
    document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : 'auto';
}

// Toggle Search Bar
function toggleSearch() {
    const searchBar = document.getElementById('search-bar');
    if (searchBar) {
        searchBar.classList.toggle('active');
        if (searchBar.classList.contains('active')) {
            const input = searchBar.querySelector('input');
            if (input) input.focus();
        }
    }
}

// ==========================================
// SHOWCASE & SLIDER LOGIC
// ==========================================

// Auto-Scroll for Showcase
function startShowcaseScroll() {
    const showcaseGrid = document.getElementById('showcaseGrid');
    if (showcaseInterval) clearInterval(showcaseInterval);

    // Only auto-scroll on mobile screens
    if (showcaseGrid && window.innerWidth < 768) { 
        showcaseInterval = setInterval(() => {
            if (!isPaused) {
                const scrollAmount = window.innerWidth;
                const maxScroll = showcaseGrid.scrollWidth - showcaseGrid.clientWidth;
                
                if (showcaseGrid.scrollLeft + scrollAmount >= maxScroll) {
                    showcaseGrid.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    showcaseGrid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
                }
            }
        }, 4000);
    }
}

// Manual Scroll (Arrows)
function scrollShowcase(direction) {
    const grid = document.getElementById('showcaseGrid');
    if (grid) {
        const scrollAmount = window.innerWidth; 
        grid.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }
}

// Toggle Play/Pause
function toggleShowcaseState() {
    isPaused = !isPaused;
    const pauseIcon = document.getElementById('icon-pause');
    const playIcon = document.getElementById('icon-play');
    const videos = document.querySelectorAll('.product-showcase video');

    if (isPaused) {
        pauseIcon.style.display = 'none';
        playIcon.style.display = 'block';
        videos.forEach(v => v.pause());
        clearInterval(showcaseInterval);
    } else {
        pauseIcon.style.display = 'block';
        playIcon.style.display = 'none';
        videos.forEach(v => v.play());
        startShowcaseScroll();
    }
}

// ==========================================
// CART LOGIC (AJAX)
// ==========================================

// Update Quantity (Cart Page)
async function updateCartQty(cartKey, productId, change, currentQty) {
    if (currentQty + change < 1) return; 

    const formData = new FormData();
    formData.append('product_id', productId);
    
    const parts = cartKey.split('_');
    if(parts[1]) formData.append('size', parts[1]);
    
    formData.append('quantity', change);

    try {
        const res = await fetch('/cart/add', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            // Update UI Elements by ID
            const qtyDisplay = document.getElementById('qty-display-' + cartKey);
            if (qtyDisplay) qtyDisplay.innerText = data.newQty;

            const priceDisplay = document.getElementById('price-display-' + cartKey);
            if (priceDisplay) priceDisplay.innerText = '₦' + data.itemTotal;

            const subtotalDisplay = document.getElementById('cart-subtotal');
            if (subtotalDisplay) subtotalDisplay.innerText = '₦' + data.globalSubtotal;

            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.innerText = data.cartCount;
                badge.style.display = 'flex';
            }

            // Update onclick to prevent stale numbers
            const btnMinus = document.getElementById('btn-minus-' + cartKey);
            const btnPlus = document.getElementById('btn-plus-' + cartKey);
            
            if(btnMinus) btnMinus.setAttribute('onclick', `updateCartQty('${cartKey}', '${productId}', -1, ${data.newQty})`);
            if(btnPlus) btnPlus.setAttribute('onclick', `updateCartQty('${cartKey}', '${productId}', 1, ${data.newQty})`);
        }
    } catch (e) {
        console.error(e);
    }
}

// ==========================================
// INITIALIZATION & EVENT LISTENERS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Hero Slider Animation
    const slides = document.querySelectorAll('.marquee-slide');
    if (slides.length > 1) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000);
    }

    // 2. Start Showcase
    startShowcaseScroll();

    // 3. Handle Add to Cart Forms
    const forms = document.querySelectorAll('form[action="/cart/add"]');
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            
            // CORRECTION: Just disable click, don't change text
            btn.disabled = true;
            
            const formData = new FormData(form);

            try {
                const res = await fetch('/cart/add', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (data.status === 'success') {
                    // Logic: If it's the main Product Page form, redirect to Cart
                    if (form.classList.contains('add-cart-form')) {
                        window.location.href = '/cart';
                    } 
                    // Logic: If it's a quick add (homepage), turn GREEN
                    else {
                        const badge = document.getElementById('cart-count');
                        if (badge) {
                            badge.innerText = data.cartCount;
                            badge.style.display = 'flex';
                            badge.style.transform = 'scale(1.5)';
                            setTimeout(() => badge.style.transform = 'scale(1)', 200);
                        }
                        
                        // CORRECTION: Turn Green Class ON
                        btn.classList.add('btn-success');
                        
                        // Revert after 2 seconds
                        setTimeout(() => {
                            btn.classList.remove('btn-success');
                            btn.disabled = false;
                        }, 2000);
                    }
                }
            } catch (err) {
                console.error('Cart Error', err);
                btn.disabled = false; // Re-enable on error
            }
        });
    });
});