<?php
require_once 'config.php';
session_start();
$conn = getDB();

// Set current page for nav highlighting
$current_page = 'booking';

// ============================================
// CHECK: User must be logged in to book
// ============================================
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    // Store the booking URL to redirect back after login
    $_SESSION['booking_redirect'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

$salonId = isset($_GET['salon_id']) ? (int)$_GET['salon_id'] : 0;
$success = false;
$bookingData = null;

// Fetch salon info using prepared statement
$salon = null;
if ($salonId) {
    $stmt = $conn->prepare("SELECT * FROM salons WHERE id = ? AND is_admin = 0");
    $stmt->bind_param("i", $salonId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $salon = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$salon) {
    header('Location: salons.php');
    exit;
}

// Set page title & description
$page_title = 'Book ' . htmlspecialchars($salon['name']) . ' — Mumbai Glam Studio';
$page_description = 'Book an appointment at ' . htmlspecialchars($salon['name']) . ' in ' . $salon['locality'] . '. Choose your service, date, and time.';

// Pre-fill customer details (user is already logged in here)
$customerName = $_SESSION['customer_name'] ?? '';
$customerPhone = '';
$stmt = $conn->prepare("SELECT phone FROM customers WHERE id = ?");
$stmt->bind_param("i", $_SESSION['customer_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $customerPhone = $row['phone'];
}
$stmt->close();

// Process booking
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceType = sanitize($_POST['service_type'] ?? '');
    $bookingDate = sanitize($_POST['booking_date'] ?? '');
    $timeSlot = sanitize($_POST['time_slot'] ?? '');
    $customerName = sanitize($_POST['customer_name'] ?? '');
    $customerPhone = sanitize($_POST['customer_phone'] ?? '');

    // Validate
    if (!in_array($serviceType, ['haircut', 'bridal', 'mens'])) {
        $errors[] = 'Please select a valid service type.';
    }
    if (empty($bookingDate) || $bookingDate < date('Y-m-d')) {
        $errors[] = 'Please select a valid future date.';
    }
    if (empty($timeSlot)) {
        $errors[] = 'Please select a time slot.';
    }
    if (strlen($customerName) < 2) {
        $errors[] = 'Please enter your full name.';
    }
    $phoneDigits = preg_replace('/\D/', '', $customerPhone);
    if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 12) {
        $errors[] = 'Please enter a valid 10-digit phone number.';
    }

    // Check if time slot is already booked for this salon and date
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT id FROM bookings WHERE salon_id = ? AND booking_date = ? AND time_slot = ?");
        $checkStmt->bind_param("iss", $salonId, $bookingDate, $timeSlot);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows > 0) {
            $errors[] = 'This time slot is already booked. Please choose another.';
        }
        $checkStmt->close();
    }

    if (empty($errors)) {
        $customer_id = $_SESSION['customer_id'];

        $stmt = $conn->prepare("INSERT INTO bookings (salon_id, customer_id, service_type, booking_date, time_slot, customer_name, customer_phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $salonId, $customer_id, $serviceType, $bookingDate, $timeSlot, $customerName, $customerPhone);

        if ($stmt->execute()) {
            $bookingId = $stmt->insert_id;
            $success = true;
            $bookingData = [
                'id' => $bookingId,
                'salon_name' => $salon['name'],
                'service' => formatService($serviceType),
                'date' => date('d M Y', strtotime($bookingDate)),
                'time' => $timeSlot,
                'customer' => $customerName,
                'phone' => $customerPhone
            ];
        } else {
            $errors[] = 'Something went wrong. Please try again.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php require_once 'includes/header.php'; ?>
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ BOOKING SECTION ============ -->
<section class="booking-section">
    <div class="container">
        <?php if ($success && $bookingData): ?>
        <!-- Success State -->
        <div class="booking-success animate-in">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h2>Booking Confirmed! 🎉</h2>
            <p style="color:var(--charcoal-muted);">Your appointment has been booked successfully.</p>
            <div class="booking-id">#MG-<?php echo str_pad($bookingData['id'], 4, '0', STR_PAD_LEFT); ?></div>
            <p style="font-size:0.85rem; color:var(--charcoal-muted);">Save this booking ID for your reference</p>
            <div class="booking-details">
                <div class="detail-row"><span class="detail-label">Salon</span><span class="detail-value"><?php echo htmlspecialchars($bookingData['salon_name']); ?></span></div>
                <div class="detail-row"><span class="detail-label">Service</span><span class="detail-value"><?php echo $bookingData['service']; ?></span></div>
                <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value"><?php echo $bookingData['date']; ?></span></div>
                <div class="detail-row"><span class="detail-label">Time</span><span class="detail-value"><?php echo $bookingData['time']; ?></span></div>
                <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value"><?php echo htmlspecialchars($bookingData['customer']); ?></span></div>
                <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value"><?php echo htmlspecialchars($bookingData['phone']); ?></span></div>
            </div>
            <div style="margin-top:28px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                <a href="salons.php" class="btn btn-outline">Browse More Salons</a>
                <a href="customer_dashboard.php" class="btn btn-primary">View My Bookings</a>
                <a href="index.php" class="btn btn-outline">Back to Home</a>
            </div>
        </div>
        <?php else: ?>
        <!-- Booking Form -->
        <div class="booking-form-wrapper animate-in">
            <a href="salons.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Salons</a>
            <div class="form-header">
                <h2>Book Your Appointment</h2>
                <p>at <strong style="color:var(--teal);"><?php echo htmlspecialchars($salon['name']); ?></strong>, <?php echo htmlspecialchars($salon['locality']); ?></p>
                <p style="font-size:0.85rem; color:var(--charcoal-muted); margin-top:4px;">
                    <i class="fas fa-user-check" style="color:var(--success);"></i> 
                    Booking as <strong><?php echo htmlspecialchars($_SESSION['customer_name']); ?></strong>
                </p>
            </div>
            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-circle-exclamation"></i>
                <div><?php foreach ($errors as $err) echo '<div>• ' . $err . '</div>'; ?></div>
            </div>
            <?php endif; ?>
            <form id="booking-form" method="POST" action="booking.php?salon_id=<?php echo $salonId; ?>" novalidate>
                <input type="hidden" name="salon_id" value="<?php echo $salonId; ?>">
                <div class="form-group">
                    <label for="service_type">Service Type <span class="required">*</span></label>
                    <select name="service_type" id="service_type" class="form-control" required>
                        <option value="">Choose a service</option>
                        <option value="haircut" <?php echo (isset($_POST['service_type']) && $_POST['service_type'] === 'haircut') ? 'selected' : ''; ?>>Haircut & Styling</option>
                        <option value="bridal" <?php echo (isset($_POST['service_type']) && $_POST['service_type'] === 'bridal') ? 'selected' : ''; ?>>Bridal Package</option>
                        <option value="mens" <?php echo (isset($_POST['service_type']) && $_POST['service_type'] === 'mens') ? 'selected' : ''; ?>>Men's Grooming</option>
                    </select>
                    <div class="form-error"></div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="booking_date">Date <span class="required">*</span></label>
                        <input type="date" name="booking_date" id="booking_date" class="form-control"
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo isset($_POST['booking_date']) ? htmlspecialchars($_POST['booking_date']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="time_slot">Time Slot <span class="required">*</span></label>
                        <select name="time_slot" id="time_slot" class="form-control" required>
                            <option value="">Pick a time</option>
                            <?php
                            $times = ['09:00 AM','10:00 AM','11:00 AM','12:00 PM','01:00 PM','02:00 PM','03:00 PM','04:00 PM','05:00 PM','06:00 PM'];
                            foreach ($times as $t):
                                $sel = (isset($_POST['time_slot']) && $_POST['time_slot'] === $t) ? 'selected' : '';
                                echo "<option value=\"$t\" $sel>$t</option>";
                            endforeach;
                            ?>
                        </select>
                        <div class="form-error"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customer_name">Your Name <span class="required">*</span></label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control"
                           placeholder="Enter your full name"
                           value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : htmlspecialchars($customerName); ?>"
                           required>
                    <div class="form-error"></div>
                </div>
                <div class="form-group">
                    <label for="customer_phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" name="customer_phone" id="customer_phone" class="form-control"
                           placeholder="e.g., 9876543210"
                           value="<?php echo isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : htmlspecialchars($customerPhone); ?>"
                           required>
                    <div class="form-error"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block mt-2" style="padding:14px;">
                    <i class="fas fa-calendar-check"></i> Confirm Booking
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>