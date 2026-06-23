<?php
require_once 'config.php';
session_start();
$conn = getDB();

// ============================================
// SET CURRENT PAGE FOR NAV HIGHLIGHTING
// ============================================
$current_page = 'salons';

// Build query with prepared statements
$locality = isset($_GET['locality']) ? sanitize($_GET['locality']) : '';
$rainSafe = isset($_GET['rain_safe']) ? (int)$_GET['rain_safe'] : 0;

$sql = "SELECT * FROM salons WHERE is_admin = 0";
$params = [];
$types = "";

if ($locality) {
    $sql .= " AND locality = ?";
    $params[] = $locality;
    $types .= "s";
}
if ($rainSafe) {
    $sql .= " AND rain_safe = 1";
}
$sql .= " ORDER BY rating DESC, name ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$salons = $stmt->get_result();

$pageTitle = $locality ? 'Salons in ' . $locality : 'All Salons';

// Check if user is logged in as customer
$isCustomer = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <?php require_once 'includes/header.php'; ?>
    <title><?php echo $pageTitle; ?> — Mumbai Glam Studio</title>
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ PAGE HEADER ============ -->
<div class="page-header">
    <div class="container">
        <h1><?php echo $pageTitle; ?></h1>
        <p>Find your perfect salon — filter by locality and rain-safe availability.</p>
    </div>
</div>

<!-- ============ MAIN CONTENT ============ -->
<section class="featured" style="padding-top:0;">
    <div class="container">

        <!-- Filters -->
        <form method="GET" action="salons.php" class="filters-bar">
            <div class="filter-group">
                <label for="filter-locality"><i class="fas fa-map-marker-alt"></i> Locality:</label>
                <select name="locality" id="filter-locality" onchange="this.form.submit()">
                    <option value="">All Localities</option>
                    <?php
                    // Get distinct localities from database
                    $locStmt = $conn->prepare("SELECT DISTINCT locality FROM salons WHERE is_admin = 0 ORDER BY locality");
                    $locStmt->execute();
                    $locResult = $locStmt->get_result();
                    while ($locRow = $locResult->fetch_assoc()):
                        $selected = ($locality === $locRow['locality']) ? 'selected' : '';
                    ?>
                    <option value="<?php echo htmlspecialchars($locRow['locality']); ?>" <?php echo $selected; ?>>
                        <?php echo htmlspecialchars($locRow['locality']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" name="rain_safe" value="1" class="filter-chip <?php echo $rainSafe ? 'active' : ''; ?>">
                    <i class="fas fa-umbrella"></i> Rain-safe only
                </button>
            </div>
            <?php if ($locality || $rainSafe): ?>
            <a href="salons.php" class="btn btn-outline btn-sm">
                <i class="fas fa-times"></i> Clear filters
            </a>
            <?php endif; ?>
        </form>

        <?php if ($salons && $salons->num_rows > 0): ?>
        <div class="salon-grid">
            <?php
            $icons = ['fa-scissors', 'fa-wand-magic-sparkles', 'fa-spa', 'fa-hand-sparkles', 'fa-face-smile-beam', 'fa-gem', 'fa-leaf'];
            $idx = 0;
            while ($salon = $salons->fetch_assoc()):
                $icon = $icons[$idx % count($icons)];
                $imagePath = getSalonImage($salon['name']);
            ?>
            <div class="salon-card animate-in animate-delay-<?php echo ($idx % 3) + 1; ?>">
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
                    <div class="salon-locality"><i class="fas fa-map-pin"></i> <?php echo htmlspecialchars($salon['locality']); ?> — <?php echo htmlspecialchars($salon['address']); ?></div>
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
                    <a href="detail_salon.php?id=<?php echo $salon['id']; ?>" class="btn btn-outline btn-block" style="margin-top: 8px;">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            <?php $idx++; endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <p>No salons found matching your filters. Try clearing the filters to see all salons.</p>
            <a href="salons.php" class="btn btn-outline mt-2">View All Salons</a>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ============ CTA SECTION ============ -->
<section class="cta-section">
    <div class="container">
        <h2>Own a Salon? Join Mumbai Glam</h2>
        <p>Get your studio listed on Mumbai's fastest-growing salon marketplace and reach thousands of new customers.</p>
        <a href="dashboard.php" class="btn btn-gold">Salon Dashboard</a>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>