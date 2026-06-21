<?php
require_once 'config.php';
session_start();
$conn = getDB();

// Set current page for nav highlighting
$current_page = 'home';

// =================== DYNAMIC STATS ===================
// Count verified salons (excluding admin)
$verifiedStmt = $conn->prepare("SELECT COUNT(*) as count FROM salons WHERE is_admin = 0 AND verified = 1");
$verifiedStmt->execute();
$verifiedResult = $verifiedStmt->get_result();
$verifiedCount = $verifiedResult->fetch_assoc()['count'] ?? 0;
$verifiedStmt->close();

// Count distinct localities (excluding admin)
$localityStmt = $conn->prepare("SELECT COUNT(DISTINCT locality) as count FROM salons WHERE is_admin = 0");
$localityStmt->execute();
$localityResult = $localityStmt->get_result();
$localityCount = $localityResult->fetch_assoc()['count'] ?? 0;
$localityStmt->close();

// Average rating (excluding admin, only salons with rating > 0)
$ratingStmt = $conn->prepare("SELECT AVG(rating) as avg FROM salons WHERE is_admin = 0 AND rating > 0");
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();
$avgRating = $ratingResult->fetch_assoc()['avg'] ?? 0;
$avgRating = number_format($avgRating, 1);
$ratingStmt->close();

// Fetch distinct localities for dropdown
$localitiesStmt = $conn->prepare("SELECT DISTINCT locality FROM salons WHERE is_admin = 0 ORDER BY locality");
$localitiesStmt->execute();
$localitiesResult = $localitiesStmt->get_result();
$localities = [];
while ($row = $localitiesResult->fetch_assoc()) {
    $localities[] = $row['locality'];
}
$localitiesStmt->close();

// Check if user is logged in as customer
$isCustomer = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mumbai Glam Studio — Mumbai's Finest Salon Marketplace</title>
    <meta name="description" content="Discover and book Mumbai's best salons. Rain-safe studios across <?php echo htmlspecialchars(implode(', ', $localities)); ?>.">

    <!-- Fonts: Fraunces + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/favicon.png">
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ HERO ============ -->
<section class="hero">
    <!-- Replace src with your own high-quality video (MP4) 
    <video autoplay muted loop playsinline class="hero-video">
        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4"> -->
        <!-- Fallback image -->
        <img src="https://images.pexels.com/photos/3992874/pexels-photo-3992874.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Salon" style="display:none;">
    </video>
    <div class="container">
        <div class="hero-content">
            <h1>Where Mumbai Gets Its <span class="gold-accent">Glow On</span></h1>
            <p class="hero-subtitle">
                From Anywhere in Mumbai, find trusted studios for every style. Rain-safe bookings, verified artists, and zero hassle.
            </p>

            <form id="hero-search" class="hero-search">
                <select name="locality" aria-label="Select locality">
                    <option value="">All Localities</option>
                    <?php foreach ($localities as $loc): ?>
                    <option value="<?php echo htmlspecialchars($loc); ?>"><?php echo htmlspecialchars($loc); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" placeholder="Search salons or services..." aria-label="Search">
                <button type="submit" class="btn-hero">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="stat-number"><?php echo $verifiedCount; ?>+</div>
                    <div class="stat-label">Verified Salons</div>
                </div>
                <div class="hero-stat">
                    <div class="stat-number"><?php echo $localityCount; ?></div>
                    <div class="stat-label">Mumbai Localities</div>
                </div>
                <div class="hero-stat">
                    <div class="stat-number"><?php echo $avgRating; ?></div>
                    <div class="stat-label">Avg. Rating</div>
                </div>
            </div>

            <!-- Real-time data indicator -->
            <div style="text-align: left; margin-top: 12px;">
                <span style="font-size: 0.7rem; color: rgba(255,255,255,0.5); letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px;">
                    <span style="display: inline-block; width: 6px; height: 6px; background: #2ECC71; border-radius: 50%; animation: pulse-dot 2s ease-in-out infinite;"></span>
                    These data are real-time
                </span>
            </div>

        </div>
    </div>
</section>

<!-- ============ LOCALITIES ============ -->
<section class="localities">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>Explore by Locality</h2>
                <p class="section-sub">Find studios in your neighbourhood — each one handpicked for quality.</p>
            </div>
        </div>

        <div class="locality-cards">
            <?php
            $displayLocalities = array_slice($localities, 0, 6);
            foreach ($displayLocalities as $loc):
                $count = getLocalityCount($loc);
                $desc = "Salons in " . $loc;
            ?>
            <a href="salons.php?locality=<?php echo urlencode($loc); ?>" class="locality-card animate-in">
                <div class="locality-count"><?php echo $count; ?></div>
                <h4><?php echo htmlspecialchars($loc); ?></h4>
                <p><?php echo htmlspecialchars($desc); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="curl-divider"></div>

<!-- ============ FEATURED SALONS ============ -->
<section class="featured">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>Featured Salons</h2>
                <p class="section-sub">Top-rated studios loved by Mumbaikars, handpicked by our team.</p>
            </div>
            <a href="salons.php" class="section-link">
                View all salons <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="salon-grid">
            <?php
            $featured = $conn->query("SELECT * FROM salons WHERE is_admin = 0 ORDER BY rating DESC LIMIT 3");
            if ($featured && $featured->num_rows > 0):
                $icons = ['fa-scissors', 'fa-wand-magic-sparkles', 'fa-spa'];
                $idx = 0;
                while ($salon = $featured->fetch_assoc()):
                    $icon = $icons[$idx % count($icons)];
                    $imagePath = getSalonImage($salon['name']);
            ?>
            <div class="salon-card animate-in animate-delay-<?php echo $idx + 1; ?>">
                <div class="salon-card-img">
                    <?php if ($imagePath): ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($salon['name']); ?>" class="salon-image">
                    <?php else: ?>
                        <i class="fas <?php echo $icon; ?> salon-icon"></i>
                    <?php endif; ?>
                    <?php if ($salon['verified']): ?>
                    <span class="badge-verified"><i class="fas fa-circle-check"></i> Verified</span>
                    <?php endif; ?>
                    <?php if ($salon['rain_safe']): ?>
                    <span class="badge-rain-safe"><i class="fas fa-umbrella"></i> Rain-safe</span>
                    <?php endif; ?>
                </div>
                <div class="salon-card-body">
                    <h3><?php echo htmlspecialchars($salon['name']); ?></h3>
                    <div class="salon-locality"><i class="fas fa-map-pin"></i> <?php echo htmlspecialchars($salon['locality']); ?></div>
                    <?php if ($salon['tagline']): ?>
                    <p class="salon-tagline"><?php echo htmlspecialchars($salon['tagline']); ?></p>
                    <?php endif; ?>
                    <div class="salon-meta">
                        <div class="salon-rating">
                            <?php echo renderStars($salon['rating']); ?>
                            <span class="rating-num"><?php echo number_format($salon['rating'], 1); ?></span>
                        </div>
                        <div class="salon-price"><?php echo formatPrice($salon['price_min'], $salon['price_max']); ?></div>
                    </div>
                </div>
                <div class="salon-card-actions">
                    <?php if ($isCustomer): ?>
                    <a href="booking.php?salon_id=<?php echo $salon['id']; ?>" class="btn btn-primary btn-block">
                        Book Appointment
                    </a>
                    <?php else: ?>
                    <a href="login.php" class="btn btn-outline btn-block" style="border-color: var(--gold); color: var(--gold);">
                        <i class="fas fa-lock"></i> Login to Book
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php $idx++; endwhile; endif; ?>
        </div>
    </div>
</section>

<div class="curl-divider"></div>

<!-- ============ HOW IT WORKS ============ -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>How It Works</h2>
                <p class="section-sub">Three simple steps to your next great look.</p>
            </div>
        </div>

        <div class="steps-grid">
            <div class="step-card animate-in animate-delay-1">
                <h4>Search & Filter</h4>
                <p>Pick your locality and find salons that match your style and budget. Filter by rain-safe studios for monsoon-proof appointments.</p>
            </div>
            <div class="step-card animate-in animate-delay-2">
                <h4>Book Instantly</h4>
                <p>Choose your service, date, and time slot. Your booking is confirmed in seconds — no calls, no waiting.</p>
            </div>
            <div class="step-card animate-in animate-delay-3">
                <h4>Walk In & Glow</h4>
                <p>Arrive at your scheduled time and enjoy a premium experience. Track your booking status in real time.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============ CTA SECTION ============ -->
<section class="cta-section">
    <div class="container">
        <h2>Ready for Your Next Transformation?</h2>
        <p>Join hundreds of Mumbaikars who trust Mumbai Glam Studio for their beauty needs.</p>
        <a href="salons.php" class="btn btn-gold">Browse All Salons</a>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>