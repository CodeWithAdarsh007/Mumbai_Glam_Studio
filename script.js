/* =====================================================
   Mumbai Glam Studio — Enhanced Interactions v2.0
   ===================================================== */

(function () {
    'use strict';

    // --- Dark Mode Toggle (Default: Dark) ---
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        // Use 'dark' as default if no preference is stored
        const currentTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);

        themeToggle.addEventListener('click', function () {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            updateThemeIcon(next);
        });

        function updateThemeIcon(theme) {
            const icon = themeToggle.querySelector('i');
            if (icon) {
                // Show sun when dark (to switch to light), moon when light (to switch to dark)
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
    }

    // --- Navbar scroll effect ---
    var navbar = document.querySelector('.navbar');
    if (navbar) {
        var scrolled = false;
        window.addEventListener('scroll', function () {
            var shouldBeScrolled = window.scrollY > 10;
            if (shouldBeScrolled !== scrolled) {
                scrolled = shouldBeScrolled;
                navbar.classList.toggle('scrolled', scrolled);
            }
        }, { passive: true });
    }

    // --- Mobile nav toggle ---
    var navToggle = document.querySelector('.nav-toggle');
    var navMenu = document.querySelector('.navbar-nav');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navToggle.classList.toggle('active');
            navMenu.classList.toggle('open');
        });
        navMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navToggle.classList.remove('active');
                navMenu.classList.remove('open');
            });
        });
    }

    // --- 3D Tilt on Salon Cards ---
    var cards = document.querySelectorAll('.salon-card');
    cards.forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            var rect = this.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            this.style.transform = 'perspective(800px) rotateY(' + (x * 8) + 'deg) rotateX(' + (-y * 8) + 'deg) translateY(-10px)';
        });
        card.addEventListener('mouseleave', function () {
            this.style.transform = '';
        });
    });

    // --- Counter Animation for Hero Stats ---
    var stats = document.querySelectorAll('.hero-stat .stat-number');
    if (stats.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    var target = parseInt(el.textContent);
                    if (target > 0 && !el.dataset.counted) {
                        el.dataset.counted = 'true';
                        var current = 0;
                        var step = Math.ceil(target / 30);
                        var interval = setInterval(function () {
                            current += step;
                            if (current >= target) {
                                el.textContent = target;
                                clearInterval(interval);
                            } else {
                                el.textContent = current;
                            }
                        }, 30);
                    }
                }
            });
        }, { threshold: 0.5 });
        stats.forEach(function (stat) { observer.observe(stat); });
    }

    // --- Ripple effect on buttons ---
    var btns = document.querySelectorAll('.btn');
    btns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var rect = this.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var y = e.clientY - rect.top;
            var ripple = document.createElement('span');
            ripple.className = 'ripple';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            this.appendChild(ripple);
            setTimeout(function () { ripple.remove(); }, 600);
        });
    });

    // Add ripple CSS dynamically if not present
    if (!document.querySelector('style[data-ripple]')) {
        var style = document.createElement('style');
        style.setAttribute('data-ripple', 'true');
        style.textContent = `
            .btn .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.4);
                width: 80px;
                height: 80px;
                transform: scale(0);
                animation: rippleAnim 0.6s ease-out;
                pointer-events: none;
            }
            @keyframes rippleAnim {
                to { transform: scale(4); opacity: 0; }
            }
            .btn { position: relative; overflow: hidden; }
        `;
        document.head.appendChild(style);
    }

    // --- Filter chip toggle (rain-safe) ---
    var rainChips = document.querySelectorAll('.filter-chip');
    rainChips.forEach(function (chip) {
        chip.addEventListener('click', function (e) {
            // If it's a button, we let form submit; just toggle active for visual
            this.classList.toggle('active');
        });
    });

    // --- Date input min today ---
    var dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function (input) {
        if (input.hasAttribute('min')) return;
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        input.setAttribute('min', yyyy + '-' + mm + '-' + dd);
    });

    // --- Booking form validation ---
    var bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function (e) {
            var valid = true;
            clearErrors();

            var name = document.getElementById('customer_name');
            if (name && name.value.trim().length < 2) {
                showError(name, 'Please enter your full name');
                valid = false;
            }

            var phone = document.getElementById('customer_phone');
            if (phone) {
                var phoneVal = phone.value.trim().replace(/\D/g, '');
                if (phoneVal.length < 10 || phoneVal.length > 12) {
                    showError(phone, 'Please enter a valid 10-digit phone number');
                    valid = false;
                }
            }

            var date = document.getElementById('booking_date');
            if (date) {
                var selected = new Date(date.value);
                var today = new Date();
                today.setHours(0, 0, 0, 0);
                if (!date.value || selected < today) {
                    showError(date, 'Please select a future date');
                    valid = false;
                }
            }

            var service = document.getElementById('service_type');
            if (service && !service.value) {
                showError(service, 'Please select a service');
                valid = false;
            }

            var timeSlot = document.getElementById('time_slot');
            if (timeSlot && !timeSlot.value) {
                showError(timeSlot, 'Please select a time slot');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    // --- Login form validation ---
    var loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            var valid = true;
            clearErrors();

            var username = document.getElementById('username');
            if (username && !username.value.trim()) {
                showError(username, 'Username is required');
                valid = false;
            }

            var password = document.getElementById('password');
            if (password && !password.value.trim()) {
                showError(password, 'Password is required');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    // --- Helper functions for form validation ---
    function showError(input, message) {
        input.classList.add('is-invalid');
        var errEl = input.parentElement.querySelector('.form-error');
        if (errEl) {
            errEl.textContent = message;
            errEl.classList.add('visible');
        }
    }

    function clearErrors() {
        document.querySelectorAll('.is-invalid').forEach(function (el) {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.form-error.visible').forEach(function (el) {
            el.classList.remove('visible');
        });
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            var errEl = e.target.parentElement.querySelector('.form-error');
            if (errEl) errEl.classList.remove('visible');
        }
    });

    // --- Scroll-triggered animations ---
    var animateEls = document.querySelectorAll('.animate-in');
    if (animateEls.length && 'IntersectionObserver' in window) {
        var animObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                    animObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animateEls.forEach(function (el) {
            el.style.animationPlayState = 'paused';
            animObserver.observe(el);
        });
    }

    // --- Smooth scroll for anchor links ---
    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // --- Dashboard status update confirmation ---
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            var msg = this.getAttribute('data-confirm');
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

    // --- Phone number formatting hint ---
    var phoneInput = document.getElementById('customer_phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9+]/g, '').slice(0, 13);
        });
    }

    // --- Bottom navigation (mobile) - create only if not exists ---
    if (window.innerWidth <= 768 && !document.querySelector('.bottom-nav')) {
        var bottomNav = document.createElement('nav');
        bottomNav.className = 'bottom-nav';
        bottomNav.innerHTML = `
            <a href="index.php"><i class="fas fa-home"></i><span>Home</span></a>
            <a href="salons.php"><i class="fas fa-list"></i><span>Salons</span></a>
            <a href="dashboard.php"><i class="fas fa-user"></i><span>Login</span></a>
            <a href="salons.php?locality=Andheri" class="bottom-nav-cta"><i class="fas fa-calendar-plus"></i><span>Book</span></a>
        `;
        document.body.appendChild(bottomNav);
        // Add CSS for bottom-nav dynamically
        var bnStyle = document.createElement('style');
        bnStyle.textContent = `
            .bottom-nav {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: var(--white);
                border-top: 1px solid var(--cream-dark);
                display: flex;
                justify-content: space-around;
                padding: 8px 0 env(safe-area-inset-bottom);
                z-index: 1000;
                backdrop-filter: blur(12px);
            }
            .bottom-nav a {
                display: flex;
                flex-direction: column;
                align-items: center;
                font-size: 0.65rem;
                color: var(--charcoal-muted);
                gap: 2px;
            }
            .bottom-nav a i { font-size: 1.2rem; }
            .bottom-nav a.active { color: var(--teal); }
            .bottom-nav .bottom-nav-cta {
                background: var(--gold);
                color: var(--white);
                padding: 6px 16px;
                border-radius: 30px;
                font-weight: 600;
            }
            @media (min-width: 769px) { .bottom-nav { display: none; } }
        `;
        document.head.appendChild(bnStyle);
    }

    // --- Hero search redirect - REMOVED (conflicts with smart search) ---
    // The old listener is removed to prevent redirects on mobile

    // ============================================
    // SMART SEARCH FUNCTIONALITY (Keyword-based)
    // ============================================

    /**
     * Handle smart search form submission
     */
    window.handleSmartSearch = function(e) {
        e.preventDefault();
        const query = document.getElementById('search-query').value.trim();
        const locality = document.getElementById('search-locality').value;
        
        if (query.length < 2) {
            showSmartNotification('Please enter at least 2 characters to search.', 'warning');
            return false;
        }

        const resultsContainer = document.getElementById('ai-search-results');
        const loading = document.getElementById('ai-loading');
        const resultsGrid = document.getElementById('ai-results-grid');
        const resultCount = document.getElementById('ai-result-count');
        const noResults = document.getElementById('ai-no-results');
        const errorDiv = document.getElementById('ai-error');

        // Mobile fallback: if elements don't exist, redirect
        if (!resultsContainer || !loading || !resultsGrid) {
            window.location.href = 'salons.php?search=' + encodeURIComponent(query);
            return false;
        }

        // Show loading state
        resultsContainer.style.display = 'block';
        loading.style.display = 'flex';
        resultsGrid.innerHTML = '';
        resultCount.textContent = '';
        noResults.style.display = 'none';
        errorDiv.style.display = 'none';

        // Build query with locality if selected
        let searchQuery = query;
        if (locality) {
            searchQuery = query + ' ' + locality;
        }

        // Make AJAX call
        fetch('/api/search.php?query=' + encodeURIComponent(searchQuery) + '&limit=6')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                loading.style.display = 'none';

                if (data.error) {
                    errorDiv.style.display = 'block';
                    errorDiv.innerHTML = `<p style="color: var(--error); text-align: center; padding: 20px;">
                        <i class="fas fa-exclamation-circle"></i> ${data.error}
                    </p>`;
                    return;
                }

                if (data.results && data.results.length > 0) {
                    resultCount.textContent = `✨ Found ${data.results.length} matching salon(s) for "${data.query}"`;
                    noResults.style.display = 'none';
                    
                    let html = '';
                    data.results.forEach((salon, idx) => {
                        const imageHtml = salon.image 
                            ? `<img src="${salon.image}" alt="${salon.name}" class="salon-image" onerror="this.style.display='none'">` 
                            : `<i class="fas fa-store salon-icon"></i>`;
                        const stars = renderStarsForSearch(salon.rating);
                        const price = formatPriceForSearch(salon.price_min, salon.price_max);
                        
                        html += `
                            <div class="salon-card animate-in animate-delay-${(idx % 3) + 1}">
                                <div class="salon-card-img">
                                    ${imageHtml}
                                    ${salon.verified ? `<span class="badge-verified"><i class="fas fa-circle-check"></i> Verified</span>` : ''}
                                    ${salon.rain_safe ? `<span class="badge-rain-safe"><i class="fas fa-umbrella"></i> Rain-safe</span>` : ''}
                                </div>
                                <div class="salon-card-body">
                                    <h3>${escapeHtmlForSearch(salon.name)}</h3>
                                    <div class="salon-locality"><i class="fas fa-map-pin"></i> ${escapeHtmlForSearch(salon.locality)}</div>
                                    ${salon.tagline ? `<p class="salon-tagline">${escapeHtmlForSearch(salon.tagline)}</p>` : ''}
                                    <div class="salon-meta">
                                        <div class="salon-rating">${stars} <span class="rating-num">${salon.rating}</span></div>
                                        <div class="salon-price">${price}</div>
                                    </div>
                                </div>
                                <div class="salon-card-actions">
                                    <a href="detail_salon.php?id=${salon.id}" class="btn btn-outline btn-block">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </div>
                            </div>
                        `;
                    });
                    resultsGrid.innerHTML = html;
                    
                    document.querySelectorAll('#ai-results-grid .animate-in').forEach(el => {
                        el.style.animationPlayState = 'running';
                    });
                } else {
                    noResults.style.display = 'block';
                    resultCount.textContent = `No results found for "${data.query || query}"`;
                }
            })
            .catch(error => {
                loading.style.display = 'none';
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = `<p style="color: var(--error); text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-circle"></i> 
                    ${error.message === 'Failed to fetch' 
                        ? 'Network error. Please check your connection.' 
                        : 'Something went wrong. Please try again.'}
                </p>`;
                console.error('Search Error:', error);
            });

        return false;
    };

    /**
     * Render star rating for search results
     */
    function renderStarsForSearch(rating) {
        const full = Math.floor(rating);
        const half = (rating - full) >= 0.5;
        let html = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= full) html += '<i class="fas fa-star"></i>';
            else if (i === full + 1 && half) html += '<i class="fas fa-star-half-alt"></i>';
            else html += '<i class="far fa-star"></i>';
        }
        return html;
    }

    /**
     * Format price for search results
     */
    function formatPriceForSearch(min, max) {
        return '₹' + Number(min).toLocaleString() + ' – ₹' + Number(max).toLocaleString();
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtmlForSearch(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Show notification for search
     */
    function showSmartNotification(message, type) {
        const existing = document.querySelector('.ai-notification');
        if (existing) existing.remove();
        
        const notification = document.createElement('div');
        notification.className = 'ai-notification';
        notification.innerHTML = `
            <div style="display:flex; align-items:center; gap:10px; padding:12px 18px; background:var(--charcoal); color:var(--text-light); border-radius:var(--radius-sm); border-left:3px solid ${type === 'warning' ? 'var(--warning)' : 'var(--gold)'}; box-shadow:var(--shadow-md); margin-bottom:16px;">
                <i class="fas fa-${type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;color:var(--charcoal-muted);cursor:pointer;margin-left:auto;">✕</button>
            </div>
        `;
        
        const heroContent = document.querySelector('.hero-content');
        if (heroContent) {
            const searchForm = heroContent.querySelector('#hero-search');
            if (searchForm) {
                heroContent.insertBefore(notification, searchForm);
            } else {
                heroContent.prepend(notification);
            }
        }
    }
    // ============================================
    // SERVICE PRICE UPDATE (Booking Page)
    // ============================================
    function updatePrice() {
        const select = document.getElementById('service_type');
        if (!select) return;
        const selected = select.options[select.selectedIndex];
        const price = selected.getAttribute('data-price') || 0;
        const priceDisplay = document.getElementById('selected-price');
        if (priceDisplay) {
            priceDisplay.textContent = price;
        }
    }

    // Initialize price update on page load
    document.addEventListener('DOMContentLoaded', function() {
        updatePrice();
    });

})();
