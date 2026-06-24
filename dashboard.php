<?php
require_once 'config.php';
session_start();

// Set current page for nav highlighting
$current_page = 'dashboard';

// Redirect if not logged in as salon
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'salon') {
    header('Location: login.php');
    exit;
}

$conn = getDB();
$salonId = $_SESSION['salon_id'];
$salonName = $_SESSION['salon_name'];
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $newStatus = sanitize($_POST['new_status'] ?? '');
    if ($bookingId && in_array($newStatus, ['confirmed', 'completed'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ? AND salon_id = ?");
        $stmt->bind_param("sii", $newStatus, $bookingId, $salonId);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: dashboard.php');
    exit;
}

// ============================================
// ADMIN: Process salon verification/deletion
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($isAdmin && ($_POST['action'] === 'verify_salon' || $_POST['action'] === 'delete_salon')) {
        $targetSalonId = (int)($_POST['salon_id'] ?? 0);
        if ($targetSalonId) {
            if ($_POST['action'] === 'verify_salon') {
                $stmt = $conn->prepare("UPDATE salons SET verified = 1 WHERE id = ? AND is_admin = 0");
                $stmt->bind_param("i", $targetSalonId);
                $stmt->execute();
                $stmt->close();
            } elseif ($_POST['action'] === 'delete_salon') {
                $stmt = $conn->prepare("DELETE FROM salons WHERE id = ? AND is_admin = 0");
                $stmt->bind_param("i", $targetSalonId);
                $stmt->execute();
                $stmt->close();
            }
        }
        header('Location: dashboard.php');
        exit;
    }
}

// ============================================
// SALON OWNER: Update salon details (with service prices)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_salon_details') {
    if (!isset($_SESSION['salon_id'])) {
        header('Location: dashboard.php');
        exit;
    }
    
    $tagline = sanitize($_POST['tagline'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $services = sanitize($_POST['services'] ?? '');
    $facilities = sanitize($_POST['facilities'] ?? '');
    $contactPhone = sanitize($_POST['contact_phone'] ?? '');
    $contactEmail = sanitize($_POST['contact_email'] ?? '');
    $website = sanitize($_POST['website'] ?? '');
    $workingHours = sanitize($_POST['working_hours'] ?? '');
    $establishedYear = (int)($_POST['established_year'] ?? 0);
    $priceMin = (int)($_POST['price_min'] ?? 0);
    $priceMax = (int)($_POST['price_max'] ?? 0);
    $priceHaircut = (int)($_POST['price_haircut'] ?? 0);
    $priceBridal = (int)($_POST['price_bridal'] ?? 0);
    $priceMens = (int)($_POST['price_mens'] ?? 0);
    $rainSafe = isset($_POST['rain_safe']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE salons SET 
        tagline = ?, 
        description = ?, 
        services = ?, 
        facilities = ?, 
        contact_phone = ?, 
        contact_email = ?, 
        website = ?, 
        working_hours = ?, 
        established_year = ?,
        price_min = ?,
        price_max = ?,
        price_haircut = ?,
        price_bridal = ?,
        price_mens = ?,
        rain_safe = ?
        WHERE id = ? AND is_admin = 0
    ");
    $stmt->bind_param("ssssssssiiiisssi", 
        $tagline, $description, $services, $facilities, 
        $contactPhone, $contactEmail, $website, $workingHours, 
        $establishedYear, $priceMin, $priceMax, $priceHaircut, $priceBridal, $priceMens, $rainSafe, $salonId
    );
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php?edit=success');
    exit;
}

// Get bookings (with price column)
$stmt = $conn->prepare("SELECT * FROM bookings WHERE salon_id = ? ORDER BY booking_date DESC, time_slot ASC");
$stmt->bind_param("i", $salonId);
$stmt->execute();
$bookings = $stmt->get_result();

$pendingCount = $confirmedCount = $completedCount = 0;
$allBookings = [];
if ($bookings) {
    while ($b = $bookings->fetch_assoc()) {
        $allBookings[] = $b;
        if ($b['status'] === 'pending') $pendingCount++;
        if ($b['status'] === 'confirmed') $confirmedCount++;
        if ($b['status'] === 'completed') $completedCount++;
    }
}
$totalCount = count($allBookings);

// Fetch current salon details for editing
$editStmt = $conn->prepare("SELECT * FROM salons WHERE id = ? AND is_admin = 0");
$editStmt->bind_param("i", $salonId);
$editStmt->execute();
$editSalon = $editStmt->get_result()->fetch_assoc();
$editStmt->close();

$page_title = 'Dashboard — ' . htmlspecialchars($salonName) . ' — Mumbai Glam Studio';
$page_description = 'Manage your salon bookings and appointments.';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <?php require_once 'includes/header.php'; ?>
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ DASHBOARD ============ -->
<section class="dashboard">
    <div class="container">
        <div class="dashboard-header">
            <div class="dashboard-welcome">
                <div>
                    <h2>Welcome back, <?php echo htmlspecialchars($salonName); ?>!</h2>
                    <span class="welcome-date">
                        <i class="far fa-calendar"></i> <?php echo date('l, d F Y'); ?> — <?php echo date('h:i A'); ?>
                    </span>
                    <?php if ($isAdmin): ?>
                    <span style="display: inline-block; margin-top: 4px; background: var(--gold); color: var(--charcoal); padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600;">
                        <i class="fas fa-shield-alt"></i> Admin
                    </span>
                    <?php endif; ?>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] === 'success'): ?>
                    <span style="display: inline-block; margin-top: 4px; background: var(--success-bg); color: var(--success); padding: 2px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600;">
                        <i class="fas fa-check-circle"></i> Updated successfully!
                    </span>
                    <?php endif; ?>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="dashboard-stats">
            <div class="stat-card"><div class="stat-val"><?php echo $totalCount; ?></div><div class="stat-lbl">Total Bookings</div></div>
            <div class="stat-card gold-border"><div class="stat-val"><?php echo $pendingCount; ?></div><div class="stat-lbl">Pending</div></div>
            <div class="stat-card blue-border"><div class="stat-val"><?php echo $confirmedCount; ?></div><div class="stat-lbl">Confirmed</div></div>
            <div class="stat-card green-border"><div class="stat-val"><?php echo $completedCount; ?></div><div class="stat-lbl">Completed</div></div>
        </div>

        <!-- Bookings Table -->
        <div class="bookings-table-wrap">
            <div style="padding:20px 24px; border-bottom:1px solid var(--cream-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <h3 style="margin:0; font-size:1.1rem;">Your Bookings</h3>
                <span style="font-size:0.8rem; color:var(--charcoal-muted);">Showing all bookings for <?php echo htmlspecialchars($salonName); ?></span>
            </div>
            <?php if (empty($allBookings)): ?>
            <div class="empty-state">
                <i class="far fa-calendar-xmark"></i>
                <p>No bookings yet. Share your salon link to start getting appointments!</p>
            </div>
            <?php else: ?>
            <table class="bookings-table">
                <thead>
                    <tr><th>Booking ID</th><th>Customer</th><th>Phone</th><th>Service</th><th>Date</th><th>Time</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($allBookings as $b): ?>
                    <tr>
                        <td style="font-weight:600; color:var(--teal);">#MG-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo htmlspecialchars($b['customer_name']); ?></td>
                        <td style="font-family:monospace; font-size:0.85rem;"><?php echo htmlspecialchars($b['customer_phone']); ?></td>
                        <td><?php echo formatService($b['service_type']); ?></td>
                        <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                        <td><?php echo htmlspecialchars($b['time_slot']); ?></td>
                        <td style="font-weight:600; color:var(--teal);">₹<?php echo number_format($b['price'] ?? 0); ?></td>
                        <td><span class="status-badge status-<?php echo $b['status']; ?>"><i class="fas fa-<?php echo $b['status'] === 'pending' ? 'clock' : ($b['status'] === 'confirmed' ? 'check' : 'check-double'); ?>"></i> <?php echo ucfirst($b['status']); ?></span></td>
                        <td>
                            <div class="action-btns">
                                <?php if ($b['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <input type="hidden" name="new_status" value="confirmed">
                                    <button type="submit" class="btn btn-confirm" data-confirm="Confirm this booking?">Confirm</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($b['status'] === 'confirmed'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <input type="hidden" name="new_status" value="completed">
                                    <button type="submit" class="btn btn-complete" data-confirm="Mark this booking as completed?">Complete</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($b['status'] === 'completed'): ?>
                                <span style="font-size:0.8rem; color:var(--success);"><i class="fas fa-check-circle"></i> Done</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- ============================================
             SALON OWNER: Edit Salon Details
             ============================================ -->
        <?php if ($editSalon && !$isAdmin): ?>
        <div class="bookings-table-wrap" style="margin-top: 40px; border-left: 3px solid var(--teal);">
            <div style="padding:20px 24px; border-bottom:1px solid var(--cream-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background: var(--teal-pale);">
                <h3 style="margin:0; font-size:1.1rem;">
                    <i class="fas fa-edit" style="color:var(--teal);"></i> 
                    Edit Salon Details
                </h3>
                <span style="font-size:0.8rem; color:var(--charcoal-muted);">Update your salon information</span>
            </div>
            <div style="padding:20px 24px;">
                <form method="POST" action="dashboard.php">
                    <input type="hidden" name="action" value="update_salon_details">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_tagline">Tagline</label>
                            <input type="text" name="tagline" id="edit_tagline" class="form-control" 
                                   value="<?php echo htmlspecialchars($editSalon['tagline'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_description">About Your Salon</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="4"><?php echo htmlspecialchars($editSalon['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_services">Services Offered</label>
                        <input type="text" name="services" id="edit_services" class="form-control" 
                               value="<?php echo htmlspecialchars($editSalon['services'] ?? ''); ?>"
                               placeholder="e.g., Haircut, Bridal Makeup, Men's Grooming">
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Separate with commas.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_facilities">Facilities</label>
                        <input type="text" name="facilities" id="edit_facilities" class="form-control" 
                               value="<?php echo htmlspecialchars($editSalon['facilities'] ?? ''); ?>"
                               placeholder="e.g., Parking, Wi-Fi, Air Conditioning">
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Separate with commas.</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_contact_phone">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="edit_contact_phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($editSalon['contact_phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="edit_contact_email">Contact Email</label>
                            <input type="email" name="contact_email" id="edit_contact_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($editSalon['contact_email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_website">Website</label>
                            <input type="text" name="website" id="edit_website" class="form-control" 
                                   value="<?php echo htmlspecialchars($editSalon['website'] ?? ''); ?>"
                                   placeholder="www.example.com">
                        </div>
                        <div class="form-group">
                            <label for="edit_working_hours">Working Hours</label>
                            <input type="text" name="working_hours" id="edit_working_hours" class="form-control" 
                                   value="<?php echo htmlspecialchars($editSalon['working_hours'] ?? ''); ?>"
                                   placeholder="Mon–Sat: 9AM–9PM, Sun: 10AM–6PM">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_established_year">Year Established</label>
                            <select name="established_year" id="edit_established_year" class="form-control">
                                <option value="">Select year</option>
                                <?php for ($year = date('Y'); $year >= 1980; $year--): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($editSalon['established_year'] == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_rain_safe">
                                <input type="checkbox" name="rain_safe" id="edit_rain_safe" value="1" <?php echo ($editSalon['rain_safe'] == 1) ? 'checked' : ''; ?>>
                                <i class="fas fa-umbrella"></i> Rain-safe
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_price_min">Price Min (₹)</label>
                            <input type="number" name="price_min" id="edit_price_min" class="form-control" 
                                   value="<?php echo $editSalon['price_min']; ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="edit_price_max">Price Max (₹)</label>
                            <input type="number" name="price_max" id="edit_price_max" class="form-control" 
                                   value="<?php echo $editSalon['price_max']; ?>" min="0">
                        </div>
                    </div>

                    <!-- Service Prices (Edit) -->
                    <div class="form-group" style="background: var(--cream); padding: 16px; border-radius: var(--radius-md); border-left: 3px solid var(--teal); margin-top: 8px;">
                        <h4 style="margin: 0 0 12px 0; font-size: 1rem; color: var(--teal);">
                            <i class="fas fa-tags"></i> Service Prices
                        </h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_price_haircut">Haircut & Styling (₹)</label>
                                <input type="number" name="price_haircut" id="edit_price_haircut" class="form-control" 
                                       value="<?php echo $editSalon['price_haircut'] ?? 0; ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_price_bridal">Bridal Package (₹)</label>
                                <input type="number" name="price_bridal" id="edit_price_bridal" class="form-control" 
                                       value="<?php echo $editSalon['price_bridal'] ?? 0; ?>" min="0">
                            </div>
                            <div class="form-group">
                                <label for="edit_price_mens">Men's Grooming (₹)</label>
                                <input type="number" name="price_mens" id="edit_price_mens" class="form-control" 
                                       value="<?php echo $editSalon['price_mens'] ?? 0; ?>" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="margin-top: 8px;">
                        <i class="fas fa-save"></i> Update Details
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================
             ADMIN: VERIFY SALONS PANEL
             ============================================ -->
        <?php if ($isAdmin): ?>
        <?php
        $unverifiedStmt = $conn->prepare("SELECT * FROM salons WHERE verified = 0 AND is_admin = 0 ORDER BY id DESC");
        $unverifiedStmt->execute();
        $unverifiedSalons = $unverifiedStmt->get_result();
        $unverifiedCount = $unverifiedSalons->num_rows;
        ?>

        <?php if ($unverifiedCount > 0): ?>
        <div class="bookings-table-wrap" style="margin-top: 40px; border-left: 3px solid var(--gold);">
            <div style="padding:20px 24px; border-bottom:1px solid var(--cream-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background: var(--gold-pale);">
                <h3 style="margin:0; font-size:1.1rem;">
                    <i class="fas fa-shield-alt" style="color:var(--gold);"></i> 
                    Verify Salons (<?php echo $unverifiedCount; ?> pending)
                </h3>
                <span style="font-size:0.8rem; color:var(--charcoal-muted);">Verify new salons to give them the ✨ Verified badge</span>
            </div>
            <table class="bookings-table">
                <thead>
                    <tr><th>#</th><th>Salon Name</th><th>Locality</th><th>Username</th><th>Address</th><th>Added</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php $idx = 1; while ($pending = $unverifiedSalons->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $idx++; ?></td>
                        <td><strong><?php echo htmlspecialchars($pending['name']); ?></strong></td>
                        <td><?php echo $pending['locality']; ?></td>
                        <td><?php echo htmlspecialchars($pending['username']); ?></td>
                        <td style="font-size:0.8rem;"><?php echo htmlspecialchars($pending['address']); ?></td>
                        <td><?php echo date('d M Y', strtotime($pending['created_at'])); ?></td>
                        <td>
                            <div class="action-btns">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="verify_salon">
                                    <input type="hidden" name="salon_id" value="<?php echo $pending['id']; ?>">
                                    <button type="submit" class="btn btn-confirm" data-confirm="Verify this salon? It will get the ✨ Verified badge.">
                                        <i class="fas fa-check-circle"></i> Verify
                                    </button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="delete_salon">
                                    <input type="hidden" name="salon_id" value="<?php echo $pending['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm" style="color:var(--error); border-color:var(--error);" data-confirm="Delete this salon permanently?">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="bookings-table-wrap" style="margin-top: 40px; border-left: 3px solid var(--success);">
            <div style="padding:20px 24px; border-bottom:1px solid var(--cream-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; background: var(--success-bg);">
                <h3 style="margin:0; font-size:1.1rem;">
                    <i class="fas fa-shield-alt" style="color:var(--success);"></i> 
                    Verify Salons
                </h3>
                <span style="font-size:0.8rem; color:var(--charcoal-muted);">No pending salons to verify</span>
            </div>
            <div style="padding:20px; text-align:center; color:var(--charcoal-muted);">
                <i class="fas fa-check-circle" style="color:var(--success); font-size:1.5rem;"></i>
                <p style="margin-top:8px;">All salons are verified! ✨</p>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>