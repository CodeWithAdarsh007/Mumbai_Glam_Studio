<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine current page (set $current_page before including this file)
// If not set, default to 'home'
$current_page = $current_page ?? 'home';

// Helper to check if a link is active
function isActive($page, $current) {
    return ($page === $current) ? 'active' : '';
}

// Check if user is logged in (customer or salon)
$isLoggedIn = isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['customer', 'salon']);

// Determine dashboard link based on user type
$dashboardLink = 'login.php';
$dashboardPage = 'login';

if ($isLoggedIn) {
    if ($_SESSION['user_type'] === 'customer') {
        $dashboardLink = 'customer_dashboard.php';
        $dashboardPage = 'customer_dashboard';
    } elseif ($_SESSION['user_type'] === 'salon') {
        $dashboardLink = 'dashboard.php';
        $dashboardPage = 'dashboard';
    }
}

// If on dashboard page, use that for active state
if ($current_page === 'customer_dashboard' || $current_page === 'dashboard') {
    $dashboardPage = $current_page;
}
?>

<nav class="navbar">
    <div class="container">
        <div class="navbar-inner">
            <!-- Logo as SVG (square) -->
            <a href="index.php" class="navbar-brand">
                <img src="assets/logo.svg" alt="Mumbai Glam Studio" class="logo-image">
            </a>

            <!-- Right side: Theme toggle + desktop nav links -->
            <div class="navbar-right">
                <!-- Desktop nav links (hidden on mobile) -->
                <ul class="navbar-nav desktop-nav">
                    <li>
                        <a href="index.php" class="<?php echo isActive('home', $current_page); ?>">Home</a>
                    </li>
                    <li>
                        <a href="salons.php" class="<?php echo ($current_page === 'detail_salon' || $current_page === 'salons') ? 'active' : ''; ?>">
                            <?php echo ($current_page === 'detail_salon') ? 'Details' : 'All Salons'; ?>
                        </a>
                    </li>
                    <li>
                        <a href="register.php" class="<?php echo isActive('register', $current_page); ?>">Register</a>
                    </li>
                    <li>
                        <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $dashboardLink; ?>" class="<?php echo isActive($dashboardPage, $current_page); ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="<?php echo isActive('login', $current_page); ?>">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <?php endif; ?>
                    </li>
                    <li>
                        <!-- Book Now button – active when on booking page -->
                        <a href="salons.php?locality=Andheri" class="btn-nav <?php echo ($current_page === 'booking') ? 'active' : ''; ?>">
                            Book Now
                        </a>
                    </li>
                </ul>

                <!-- Theme Toggle (visible on all screen sizes) -->
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Bottom Navigation -->
<nav class="bottom-nav" aria-label="Mobile navigation">
    <a href="index.php" class="<?php echo isActive('home', $current_page); ?>">
        <i class="fas fa-home"></i><span>Home</span>
    </a>
    <a href="salons.php" class="<?php echo ($current_page === 'detail_salon' || $current_page === 'salons') ? 'active' : ''; ?>">
        <i class="fas fa-list"></i>
        <span><?php echo ($current_page === 'detail_salon') ? 'Details' : 'Salons'; ?></span>
    </a>
    <a href="register.php" class="<?php echo isActive('register', $current_page); ?>">
        <i class="fas fa-store"></i><span>Join</span>
    </a>
    <?php if ($isLoggedIn): ?>
    <a href="<?php echo $dashboardLink; ?>" class="<?php echo isActive($dashboardPage, $current_page); ?>">
        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
    </a>
    <?php else: ?>
    <a href="login.php" class="<?php echo isActive('login', $current_page); ?>">
        <i class="fas fa-sign-in-alt"></i><span>Login</span>
    </a>
    <?php endif; ?>
    <!-- Book icon – active when on booking page -->
    <a href="salons.php?locality=Andheri" class="<?php echo isActive('booking', $current_page); ?>">
        <i class="fas fa-calendar-plus"></i><span>Book</span>
    </a>
</nav>

<style>
    /* ============================================
       Navbar – Desktop & Mobile
       ============================================ */
    .navbar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: clamp(10px, 1.5vw, 16px) 0;
        width: 100%;
    }

    /* Logo image – square, large */
    .logo-image {
        width: 60px;
        height: 60px;
        display: block;
        transition: opacity 0.3s;
        object-fit: contain;
    }
    .logo-image:hover {
        opacity: 0.85;
    }

    .navbar-right {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Desktop nav links */
    .desktop-nav {
        display: flex;
        align-items: center;
        gap: clamp(16px, 3vw, 32px);
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .desktop-nav a {
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--charcoal-light);
        position: relative;
        padding: 4px 0;
        text-decoration: none;
        transition: color var(--transition-fast);
    }

    .desktop-nav a::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--gold);
        transition: width var(--transition-smooth);
    }

    .desktop-nav a:hover::after {
        width: 100%;
    }

    .desktop-nav a.active::after {
        width: 100% !important;
        background: var(--gold) !important;
    }
    .desktop-nav a.active {
        color: var(--gold) !important;
    }

    /* Book Now button – special styling */
    .desktop-nav .btn-nav {
        background: var(--teal);
        color: var(--white);
        padding: 8px 20px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        transition: background var(--transition-fast), transform var(--transition-fast);
    }
    .desktop-nav .btn-nav::after {
        display: none; /* no underline for button */
    }
    .desktop-nav .btn-nav:hover {
        background: var(--teal-light);
        transform: translateY(-1px);
        color: var(--white);
    }
    /* Active state for Book Now button – gold background */
    .desktop-nav .btn-nav.active {
        background: var(--gold) !important;
        color: var(--charcoal) !important;
        transform: translateY(-1px);
    }
    .desktop-nav .btn-nav.active:hover {
        background: var(--gold-light) !important;
        color: var(--charcoal) !important;
    }

    /* Theme Toggle */
    .theme-toggle {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        color: var(--charcoal-light);
        padding: 4px 8px;
        transition: transform 0.3s, color 0.3s;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .theme-toggle:hover {
        transform: rotate(20deg);
        color: var(--gold);
    }

    /* ============================================
       Mobile Bottom Navigation
       ============================================ */
    .bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--white);
        border-top: 1px solid var(--cream-dark);
        display: none;
        justify-content: space-around;
        align-items: center;
        padding: 6px 0 env(safe-area-inset-bottom);
        z-index: 1000;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        height: 60px;
    }

    .bottom-nav a {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 0.6rem;
        color: var(--charcoal-muted);
        gap: 2px;
        text-decoration: none;
        padding: 4px 12px;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
        min-width: 56px;
        height: 100%;
        position: relative;
    }

    .bottom-nav a i {
        font-size: 1.2rem;
        color: var(--charcoal-muted);
        transition: color var(--transition-fast);
    }

    .bottom-nav a span {
        font-size: 0.55rem;
        font-weight: 500;
        line-height: 1;
        color: var(--charcoal-muted);
        transition: color var(--transition-fast);
    }

    .bottom-nav a.active {
        color: var(--gold) !important;
        background: rgba(212, 175, 55, 0.1);
        border-radius: var(--radius-sm);
    }
    .bottom-nav a.active i {
        color: var(--gold) !important;
    }
    .bottom-nav a.active span {
        color: var(--gold) !important;
    }

    .bottom-nav a:not(.active):hover {
        color: var(--teal);
    }
    .bottom-nav a:not(.active):hover i {
        color: var(--teal);
    }

    /* ============================================
       Responsive
       ============================================ */
    @media (max-width: 768px) {
        .desktop-nav {
            display: none !important;
        }
        .bottom-nav {
            display: flex !important;
        }
        .navbar-right {
            gap: 8px;
        }
        body {
            padding-bottom: 70px;
        }
    }

    @media (min-width: 769px) {
        .bottom-nav {
            display: none !important;
        }
        body {
            padding-bottom: 0;
        }
    }

    /* ============================================
       Dark mode adjustments
       ============================================ */
    [data-theme="dark"] .bottom-nav {
        background: #1a1a1a;
        border-top-color: #333;
    }
    [data-theme="dark"] .bottom-nav a {
        color: #888;
    }
    [data-theme="dark"] .bottom-nav a i {
        color: #888;
    }
    [data-theme="dark"] .bottom-nav a span {
        color: #888;
    }
    [data-theme="dark"] .bottom-nav a.active {
        background: rgba(212, 175, 55, 0.15);
    }
    [data-theme="dark"] .bottom-nav a.active i,
    [data-theme="dark"] .bottom-nav a.active span {
        color: var(--gold) !important;
    }

    [data-theme="dark"] .theme-toggle {
        color: #b0b0b0;
    }
    [data-theme="dark"] .theme-toggle:hover {
        color: var(--gold);
    }

    [data-theme="dark"] .desktop-nav .btn-nav {
        background: var(--teal);
        color: var(--white);
    }
    [data-theme="dark"] .desktop-nav .btn-nav:hover {
        background: var(--teal-light);
        color: var(--white);
    }
    [data-theme="dark"] .desktop-nav .btn-nav.active {
        background: var(--gold) !important;
        color: var(--charcoal) !important;
    }
    [data-theme="dark"] .desktop-nav .btn-nav.active:hover {
        background: var(--gold-light) !important;
        color: var(--charcoal) !important;
    }
</style>