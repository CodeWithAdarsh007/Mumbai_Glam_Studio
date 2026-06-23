<?php
require_once 'config.php';
session_start();
$conn = getDB();

// ============================================
// SET CURRENT PAGE FOR NAV HIGHLIGHTING
// ============================================
$current_page = 'detail_salon';

$salonId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($salonId === 0) {
    header('Location: salons.php');
    exit;
}

// Fetch salon details
$salon = null;
$stmt = $conn->prepare("SELECT * FROM salons WHERE id = ? AND is_admin = 0");
$stmt->bind_param("i", $salonId);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $salon = $result->fetch_assoc();
}
$stmt->close();

if (!$salon) {
    header('Location: salons.php');
    exit;
}

// Fetch approved reviews
$reviewsStmt = $conn->prepare("
    SELECT r.*, c.name as customer_name 
    FROM customer_reviews r 
    JOIN customers c ON r.customer_id = c.id 
    WHERE r.salon_id = ? AND r.status = 'approved' 
    ORDER BY r.created_at DESC
");
$reviewsStmt->bind_param("i", $salonId);
$reviewsStmt->execute();
$reviews = $reviewsStmt->get_result();
$reviewsStmt->close();

// Process review submission – auto-approved
$reviewSuccess = false;
$reviewErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
        $reviewErrors[] = 'Please login to submit a review.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = sanitize($_POST['comment'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $reviewErrors[] = 'Please select a valid rating (1-5).';
        }
        if (strlen($comment) < 10) {
            $reviewErrors[] = 'Please write a review of at least 10 characters.';
        }

        if (empty($reviewErrors)) {
            $customerId = $_SESSION['customer_id'];
            
            $checkStmt = $conn->prepare("SELECT id FROM customer_reviews WHERE salon_id = ? AND customer_id = ?");
            $checkStmt->bind_param("ii", $salonId, $customerId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $reviewErrors[] = 'You have already reviewed this salon.';
            } else {
                $stmt = $conn->prepare("INSERT INTO customer_reviews (salon_id, customer_id, rating, comment, status) VALUES (?, ?, ?, ?, 'approved')");
                $stmt->bind_param("iiis", $salonId, $customerId, $rating, $comment);
                if ($stmt->execute()) {
                    $reviewSuccess = true;
                } else {
                    $reviewErrors[] = 'Something went wrong. Please try again.';
                }
                $stmt->close();
            }
            $checkStmt->close();
        }
    }
}

$isCustomer = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'customer';

$page_title = htmlspecialchars($salon['name']) . ' — Mumbai Glam Studio';
$page_description = htmlspecialchars($salon['tagline'] ?? '') . ' - Book your appointment at ' . htmlspecialchars($salon['name']) . ' in ' . htmlspecialchars($salon['locality']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <?php require_once 'includes/header.php'; ?>
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ SALON DETAILS ============ -->
<section class="salon-detail-section">
    <div class="container">
        <a href="salons.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Salons</a>

        <div class="salon-detail-wrapper animate-in">

            <!-- ===== HEADER: Image Left + Info Right ===== -->
            <div class="salon-detail-header">
                <!-- Image Left -->
                <div class="salon-detail-image">
                    <?php 
                    $imagePath = getSalonImage($salon['name']);
                    if ($imagePath): ?>
                        <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($salon['name']); ?>" class="salon-detail-img">
                    <?php else: ?>
                        <div class="salon-detail-placeholder"><i class="fas fa-store"></i></div>
                    <?php endif; ?>
                    
                    <!-- Badges – same as salons.php (left + right) -->
                    <div class="salon-detail-badges">
                        <?php if ($salon['verified']): ?>
                        <span class="badge-verified"><i class="fas fa-circle-check"></i> Verified</span>
                        <?php endif; ?>
                        <?php if ($salon['rain_safe']): ?>
                        <span class="badge-rain-safe"><i class="fas fa-umbrella"></i> Rain-safe</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Right -->
                <div class="salon-detail-info">
                    <h1><?php echo htmlspecialchars($salon['name']); ?></h1>
                    <div class="salon-detail-meta">
                        <div class="salon-detail-rating">
                            <div class="stars"><?php echo renderStars($salon['avg_rating'] ?: $salon['rating']); ?></div>
                            <span class="rating-score"><?php echo number_format($salon['avg_rating'] ?: $salon['rating'], 1); ?></span>
                            <span class="rating-count">(<?php echo $salon['review_count']; ?> reviews)</span>
                        </div>
                        <div class="salon-detail-locality"><i class="fas fa-map-pin"></i> <?php echo htmlspecialchars($salon['locality']); ?></div>
                        <div class="salon-detail-price"><i class="fas fa-tag"></i> <?php echo formatPrice($salon['price_min'], $salon['price_max']); ?></div>
                        <?php if ($salon['established_year']): ?>
                        <div class="salon-detail-established"><i class="fas fa-calendar-alt"></i> Est. <?php echo $salon['established_year']; ?></div>
                        <?php endif; ?>
                    </div>

                    <?php if ($salon['tagline']): ?>
                    <p class="salon-detail-tagline"><?php echo htmlspecialchars($salon['tagline']); ?></p>
                    <?php endif; ?>

                    <div class="salon-detail-actions">
                        <?php if ($isCustomer): ?>
                        <a href="booking.php?salon_id=<?php echo $salon['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Book Appointment
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-primary"><i class="fas fa-lock"></i> Login to Book</a>
                        <?php endif; ?>
                        <?php if ($salon['contact_phone']): ?>
                        <a href="tel:<?php echo $salon['contact_phone']; ?>" class="btn btn-outline"><i class="fas fa-phone"></i> Call Now</a>
                        <?php endif; ?>
                        <?php if ($salon['website']): ?>
                        <a href="https://<?php echo $salon['website']; ?>" target="_blank" class="btn btn-outline"><i class="fas fa-globe"></i> Website</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ===== EXTRA DETAILS (below the header) ===== -->
            <div class="salon-detail-content">
                <!-- About -->
                <?php if ($salon['description']): ?>
                <div class="salon-detail-section-block">
                    <h3><i class="fas fa-info-circle"></i> About</h3>
                    <p><?php echo nl2br(htmlspecialchars($salon['description'])); ?></p>
                </div>
                <?php endif; ?>

                <!-- Services -->
                <?php if ($salon['services']): ?>
                <div class="salon-detail-section-block">
                    <h3><i class="fas fa-scissors"></i> Services</h3>
                    <div class="services-list">
                        <?php 
                        $services = explode(',', $salon['services']);
                        foreach ($services as $service): 
                        ?>
                        <span class="service-tag"><i class="fas fa-check-circle"></i> <?php echo trim(htmlspecialchars($service)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Facilities -->
                <?php if ($salon['facilities']): ?>
                <div class="salon-detail-section-block">
                    <h3><i class="fas fa-concierge-bell"></i> Facilities</h3>
                    <div class="facilities-list">
                        <?php 
                        $facilities = explode(',', $salon['facilities']);
                        foreach ($facilities as $facility): 
                        ?>
                        <span class="facility-tag"><i class="fas fa-check-circle"></i> <?php echo trim(htmlspecialchars($facility)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Working Hours -->
                <?php if ($salon['working_hours']): ?>
                <div class="salon-detail-section-block">
                    <h3><i class="fas fa-clock"></i> Working Hours</h3>
                    <p><?php echo nl2br(htmlspecialchars($salon['working_hours'])); ?></p>
                </div>
                <?php endif; ?>

                <!-- Contact -->
                <div class="salon-detail-section-block">
                    <h3><i class="fas fa-address-card"></i> Contact</h3>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($salon['address']); ?></p>
                        <?php if ($salon['contact_phone']): ?>
                        <p><i class="fas fa-phone"></i> <a href="tel:<?php echo $salon['contact_phone']; ?>"><?php echo htmlspecialchars($salon['contact_phone']); ?></a></p>
                        <?php endif; ?>
                        <?php if ($salon['contact_email']): ?>
                        <p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo $salon['contact_email']; ?>"><?php echo htmlspecialchars($salon['contact_email']); ?></a></p>
                        <?php endif; ?>
                        <?php if ($salon['website']): ?>
                        <p><i class="fas fa-globe"></i> <a href="https://<?php echo $salon['website']; ?>" target="_blank"><?php echo htmlspecialchars($salon['website']); ?></a></p>
                        <?php endif; ?>
                    </div>
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps?q=<?php echo urlencode($salon['address']); ?>&output=embed" width="100%" height="250" style="border:0; border-radius:var(--radius-md);" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>

                <!-- Reviews -->
                <div class="salon-detail-section-block">
                    <h3><i class="fas fa-star"></i> Customer Reviews</h3>

                    <!-- Review Form -->
                    <?php if ($isCustomer): ?>
                    <div class="review-form-wrapper">
                        <h4>Write a Review</h4>
                        <?php if ($reviewSuccess): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Thank you! Your review has been submitted.</div>
                        <?php endif; ?>
                        <?php if (!empty($reviewErrors)): ?>
                        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> <div><?php foreach ($reviewErrors as $err) echo '<div>• ' . $err . '</div>'; ?></div></div>
                        <?php endif; ?>
                        <form method="POST" action="detail_salon.php?id=<?php echo $salonId; ?>">
                            <input type="hidden" name="action" value="submit_review">
                            <div class="form-group">
                                <label>Rating <span class="required">*</span></label>
                                <div class="star-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                                    <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> stars"><i class="far fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="review_comment">Your Review <span class="required">*</span></label>
                                <textarea name="comment" id="review_comment" class="form-control" rows="4" placeholder="Share your experience..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                    <?php else: ?>
                    <p style="color:var(--charcoal-muted);"><a href="login.php" style="color:var(--teal); font-weight:600;">Login</a> to write a review.</p>
                    <?php endif; ?>

                    <!-- Reviews List -->
                    <?php if ($reviews && $reviews->num_rows > 0): ?>
                    <div class="reviews-list">
                        <?php while ($review = $reviews->fetch_assoc()): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <strong><?php echo htmlspecialchars($review['customer_name']); ?></strong>
                                    <span class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                                </div>
                                <div class="review-stars"><?php echo renderStars($review['rating']); ?></div>
                            </div>
                            <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    <?php else: ?>
                    <p style="color:var(--charcoal-muted);">No reviews yet. Be the first to review!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<!-- Star Rating CSS and JavaScript -->
<style>
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 4px;
    }
    .star-rating input { display: none; }
    .star-rating label {
        font-size: 1.8rem;
        color: var(--charcoal-muted);
        cursor: pointer;
        transition: color 0.2s;
    }
    .star-rating label i { font-size: 1.8rem; }
    .star-rating input:checked ~ label { color: var(--gold); }
    .star-rating label:hover,
    .star-rating label:hover ~ label { color: var(--gold-light); }
    .star-rating input:checked ~ label i,
    .star-rating label:hover i,
    .star-rating label:hover ~ label i { font-weight: 900; }
</style>

<script>
    document.querySelectorAll('.star-rating label').forEach(label => {
        label.addEventListener('mouseenter', function() {
            const siblings = this.parentElement.querySelectorAll('label');
            let found = false;
            siblings.forEach(s => {
                if (s === this || found) {
                    s.querySelector('i').className = 'fas fa-star';
                } else {
                    s.querySelector('i').className = 'far fa-star';
                }
            });
        });
        label.addEventListener('mouseleave', function() {
            const parent = this.parentElement;
            const checked = parent.querySelector('input:checked');
            const siblings = parent.querySelectorAll('label');
            siblings.forEach(s => {
                if (checked && parseInt(s.htmlFor.replace('star', '')) <= parseInt(checked.value)) {
                    s.querySelector('i').className = 'fas fa-star';
                } else {
                    s.querySelector('i').className = 'far fa-star';
                }
            });
        });
    });
</script>

<script src="script.js"></script>
</body>
</html>