// ==========================================
// GLOBAL VARIABLES
// ==========================================
let showcaseInterval;
let isPaused = false;

// ==========================================
// BACKGROUND MUSIC LOGIC
// ==========================================
window.toggleMusic = function() {
    const audio = document.getElementById('bg-music');
    const onIcon = document.getElementById('music-icon-on');
    const offIcon = document.getElementById('music-icon-off');

    if (audio.paused) {
        audio.play();
        localStorage.setItem('musicPlaying', 'true');
        if(onIcon) onIcon.style.display = 'block';
        if(offIcon) offIcon.style.display = 'none';
    } else {
        audio.pause();
        localStorage.setItem('musicPlaying', 'false');
        if(onIcon) onIcon.style.display = 'none';
        if(offIcon) offIcon.style.display = 'block';
    }
};

function initMusic() {
    const audio = document.getElementById('bg-music');
    if (!audio) return;

    // 1. Recover saved timestamp
    const savedTime = localStorage.getItem('bgMusicTime');
    if (savedTime) {
        audio.currentTime = parseFloat(savedTime);
    }

    // 2. Check if user previously paused it. If undefined, default to Play ('true')
    const shouldPlay = localStorage.getItem('musicPlaying');

    if (shouldPlay !== 'false') {
        // 3. Attempt Auto-Play
        const playPromise = audio.play();
        
        if (playPromise !== undefined) {
            playPromise.catch(error => {
                // Browser prevented auto-play. Wait for ANY user interaction.
                console.log("Auto-play blocked. Waiting for interaction.");
                const startAudio = () => {
                    audio.play();
                    // Update icons
                    document.getElementById('music-icon-on').style.display = 'block';
                    document.getElementById('music-icon-off').style.display = 'none';
                    // Remove listener so it doesn't fire again
                    document.removeEventListener('click', startAudio);
                    document.removeEventListener('scroll', startAudio);
                };
                // Listen for first interaction
                document.addEventListener('click', startAudio);
                document.addEventListener('scroll', startAudio);
            });
        }
    } else {
        // User manually paused previously, so show "Off" icon
        document.getElementById('music-icon-on').style.display = 'none';
        document.getElementById('music-icon-off').style.display = 'block';
    }

    // 4. Save timestamp continuously (every 1s) so reload works
    setInterval(() => {
        if (!audio.paused) {
            localStorage.setItem('bgMusicTime', audio.currentTime);
        }
    }, 1000);
}

// ==========================================
// UI HELPERS (Attached to window for Global Access)
// ==========================================
window.toggleMenu = function() {
    const menu = document.getElementById('mobile-menu');
    if (!menu) return;
    
    menu.classList.toggle('active');
    document.body.style.overflow = menu.classList.contains('active') ? 'hidden' : 'auto';
};

window.toggleSearch = function() {
    const searchBar = document.getElementById('search-bar');
    if (searchBar) {
        searchBar.classList.toggle('active');
        if (searchBar.classList.contains('active')) {
            const input = searchBar.querySelector('input');
            if (input) input.focus();
        }
    }
};

// ==========================================
// SHOWCASE & VIDEO LOGIC
// ==========================================
function startShowcaseScroll() {
    const showcaseGrid = document.getElementById('showcaseGrid');
    if (showcaseInterval) clearInterval(showcaseInterval);

    if (showcaseGrid && window.innerWidth < 768) {
        const videos = document.querySelectorAll('.product-showcase video');
        if (videos.length > 0) {
            videos.forEach(video => {
                video.playsInline = true;
                video.loop = false; 
                video.play().catch(() => {});
                video.addEventListener('ended', () => {
                    if (!isPaused) {
                        scrollShowcase(1);
                        setTimeout(() => {
                            video.currentTime = 0;
                            video.play();
                        }, 1000);
                    }
                });
            });
        } else {
            showcaseInterval = setInterval(() => {
                if (!isPaused) scrollShowcase(1);
            }, 4000);
        }
    }
}

function scrollShowcase(direction) {
    const grid = document.getElementById('showcaseGrid');
    if (grid) {
        const scrollAmount = window.innerWidth;
        grid.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
    }
}

window.toggleShowcaseState = function() {
    isPaused = !isPaused;
    const pauseIcon = document.getElementById('icon-pause');
    const playIcon = document.getElementById('icon-play');
    const videos = document.querySelectorAll('.product-showcase video');

    if (isPaused) {
        if (pauseIcon) pauseIcon.style.display = 'none';
        if (playIcon) playIcon.style.display = 'block';
        videos.forEach(v => v.pause());
        clearInterval(showcaseInterval);
    } else {
        if (pauseIcon) pauseIcon.style.display = 'block';
        if (playIcon) playIcon.style.display = 'none';
        videos.forEach(v => v.play());
        startShowcaseScroll();
    }
};

// ==========================================
// CART LOGIC
// ==========================================
window.updateCartQty = async function(cartKey, productId, change, currentQty) {
    if (currentQty + change < 1) return;
    const formData = new FormData();
    formData.append('product_id', productId);
    const parts = cartKey.split('_');
    if (parts[1]) formData.append('size', parts[1]);
    formData.append('quantity', change);

    try {
        const res = await fetch('/cart/add', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.status === 'success') {
            const qtyEl = document.getElementById('qty-display-' + cartKey);
            if (qtyEl) qtyEl.innerText = data.newQty;
            const priceEl = document.getElementById('price-display-' + cartKey);
            if (priceEl) priceEl.innerText = '₦' + data.itemTotal;
            const subtotalEl = document.getElementById('cart-subtotal');
            if (subtotalEl) subtotalEl.innerText = '₦' + data.globalSubtotal;
            const badge = document.getElementById('cart-count');
            if (badge) {
                badge.innerText = data.cartCount;
                badge.style.display = 'flex';
            }
            const btnMinus = document.getElementById('btn-minus-' + cartKey);
            const btnPlus = document.getElementById('btn-plus-' + cartKey);
            if (btnMinus) btnMinus.setAttribute('onclick', `updateCartQty('${cartKey}', '${productId}', -1, ${data.newQty})`);
            if (btnPlus) btnPlus.setAttribute('onclick', `updateCartQty('${cartKey}', '${productId}', 1, ${data.newQty})`);
        }
    } catch (e) {
        console.error(e);
    }
};

// ==========================================
// INIT & LISTENERS
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    
    // Initialize Music
    initMusic();

    // Hero Slider
    const slides = document.querySelectorAll('.marquee-slide');
    if (slides.length > 1) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000);
    }

    startShowcaseScroll();

    // Handle Add to Cart Forms
    const forms = document.querySelectorAll('form[action="/cart/add"]');
    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            const formData = new FormData(form);

            try {
                const res = await fetch('/cart/add', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if (data.status === 'success') {
                    if (form.classList.contains('add-cart-form')) {
                        window.location.href = '/cart';
                    } else {
                        const badge = document.getElementById('cart-count');
                        if (badge) {
                            badge.innerText = data.cartCount;
                            badge.style.display = 'flex';
                            badge.style.transform = 'scale(1.5)';
                            setTimeout(() => badge.style.transform = 'scale(1)', 200);
                        }
                        if (btn) {
                            btn.classList.add('btn-success');
                            setTimeout(() => {
                                btn.classList.remove('btn-success');
                                btn.disabled = false;
                            }, 2000);
                        }
                    }
                }
            } catch (err) {
                console.error('Cart Error', err);
                if (btn) btn.disabled = false;
            }
        });
    });
});
