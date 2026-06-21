<?php
require_once 'config.php';
session_start();

// Set current page for nav highlighting
$current_page = 'customer_dashboard';

// Redirect if not logged in as customer
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$customerId = $_SESSION['customer_id'];
$customerName = $_SESSION['customer_name'];
$conn = getDB();

// Get customer's bookings with salon details
$stmt = $conn->prepare("
    SELECT 
        b.*, 
        s.name as salon_name, 
        s.locality as salon_locality,
        s.address as salon_address
    FROM bookings b 
    JOIN salons s ON b.salon_id = s.id 
    WHERE b.customer_id = ? 
    ORDER BY b.booking_date DESC, b.time_slot ASC
");
$stmt->bind_param("i", $customerId);
$stmt->execute();
$bookingsResult = $stmt->get_result();

$upcomingBookings = [];
$pastBookings = [];
$today = date('Y-m-d');

while ($b = $bookingsResult->fetch_assoc()) {
    if ($b['booking_date'] >= $today) {
        $upcomingBookings[] = $b;
    } else {
        $pastBookings[] = $b;
    }
}
$stmt->close();

$page_title = 'My Bookings — Mumbai Glam Studio';
$page_description = 'View and manage your appointments.';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
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
                    <h2>Welcome, <?php echo htmlspecialchars($customerName); ?>! 👋</h2>
                    <span class="welcome-date">
                        <i class="far fa-calendar"></i> <?php echo date('l, d F Y'); ?>
                    </span>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="salons.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Booking
                    </a>
                    <a href="logout.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="dashboard-stats">
            <div class="stat-card gold-border">
                <div class="stat-val"><?php echo count($upcomingBookings); ?></div>
                <div class="stat-lbl">Upcoming</div>
            </div>
            <div class="stat-card blue-border">
                <div class="stat-val"><?php echo count($pastBookings); ?></div>
                <div class="stat-lbl">Past</div>
            </div>
            <div class="stat-card green-border">
                <div class="stat-val"><?php echo count($upcomingBookings) + count($pastBookings); ?></div>
                <div class="stat-lbl">Total Bookings</div>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <div class="bookings-table-wrap" style="margin-bottom:30px;">
            <div style="padding:20px 24px; border-bottom:1px solid var(--cream-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <h3 style="margin:0; font-size:1.1rem;">
                    <i class="fas fa-clock" style="color:var(--gold);"></i> Upcoming Bookings
                </h3>
                <span style="font-size:0.8rem; color:var(--charcoal-muted);">Your scheduled appointments</span>
            </div>
            <?php if (empty($upcomingBookings)): ?>
            <div class="empty-state">
                <i class="far fa-calendar-check"></i>
                <p>No upcoming bookings. <a href="salons.php" style="color:var(--teal); font-weight:600;">Book a salon now!</a></p>
            </div>
            <?php else: ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Salon</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcomingBookings as $b): ?>
                    <tr>
                        <td style="font-weight:600; color:var(--teal);">#MG-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($b['salon_name']); ?></strong>
                            <br><small style="color:var(--charcoal-muted);"><?php echo htmlspecialchars($b['salon_locality']); ?></small>
                        </td>
                        <td><?php echo formatService($b['service_type']); ?></td>
                        <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                        <td><?php echo htmlspecialchars($b['time_slot']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $b['status']; ?>">
                                <i class="fas fa-<?php echo $b['status'] === 'pending' ? 'clock' : ($b['status'] === 'confirmed' ? 'check' : 'check-double'); ?>"></i> 
                                <?php echo ucfirst($b['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($b['status'] === 'pending'): ?>
                            <span style="font-size:0.7rem; color:var(--charcoal-muted);">Awaiting confirmation</span>
                            <?php elseif ($b['status'] === 'confirmed'): ?>
                            <span style="font-size:0.7rem; color:var(--success);"><i class="fas fa-check-circle"></i> Confirmed</span>
                            <?php else: ?>
                            <span style="font-size:0.7rem; color:var(--charcoal-muted);">Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Past Bookings -->
        <div class="bookings-table-wrap">
            <div style="padding:20px 24px; border-bottom:1px solid var(--cream-dark); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <h3 style="margin:0; font-size:1.1rem;">
                    <i class="fas fa-history" style="color:var(--charcoal-muted);"></i> Past Bookings
                </h3>
                <span style="font-size:0.8rem; color:var(--charcoal-muted);">Your completed appointments</span>
            </div>
            <?php if (empty($pastBookings)): ?>
            <div class="empty-state">
                <i class="far fa-calendar-xmark"></i>
                <p>No past bookings yet.</p>
            </div>
            <?php else: ?>
            <table class="bookings-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Salon</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pastBookings as $b): ?>
                    <tr>
                        <td style="font-weight:600; color:var(--charcoal-muted);">#MG-<?php echo str_pad($b['id'], 4, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($b['salon_name']); ?></strong>
                            <br><small style="color:var(--charcoal-muted);"><?php echo htmlspecialchars($b['salon_locality']); ?></small>
                        </td>
                        <td><?php echo formatService($b['service_type']); ?></td>
                        <td><?php echo date('d M Y', strtotime($b['booking_date'])); ?></td>
                        <td><?php echo htmlspecialchars($b['time_slot']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $b['status']; ?>">
                                <i class="fas fa-<?php echo $b['status'] === 'pending' ? 'clock' : ($b['status'] === 'confirmed' ? 'check' : 'check-double'); ?>"></i> 
                                <?php echo ucfirst($b['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div style="margin-top:30px; display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:16px;">
            <a href="salons.php" class="btn btn-primary" style="padding:14px;">
                <i class="fas fa-plus"></i> Find a Salon
            </a>
            <a href="salons.php?rain_safe=1" class="btn btn-outline" style="padding:14px;">
                <i class="fas fa-umbrella"></i> Rain-Safe Salons
            </a>
        </div>

    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>