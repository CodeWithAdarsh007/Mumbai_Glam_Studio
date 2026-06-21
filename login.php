<?php
require_once 'config.php';
session_start();

// Enable error reporting temporarily (remove for production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = getDB();

// Check if connection is valid
if (!$conn) {
    die('Database connection failed. Please check your database credentials in config.php');
}

// Set current page
$current_page = 'login';
$error = '';

// Check if customers table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'customers'");
if ($tableCheck === false) {
    die('ERROR: Database query failed. Check your database connection.');
}
if ($tableCheck->num_rows === 0) {
    die('ERROR: "customers" table does not exist. Please run the SQL to create it.');
}
$tableCheck->close();

// Check if there's a booking redirect saved
$redirectUrl = $_SESSION['booking_redirect'] ?? null;

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = $_POST['password'] ?? '';

    if ($identifier && $password) {
        $loggedIn = false;
        $conn = getDB();

        // 1. Try customers table
        $stmt = $conn->prepare("SELECT id, name, email, password FROM customers WHERE email = ? LIMIT 1");
        if ($stmt === false) {
            die('Prepare failed (customers): ' . $conn->error);
        }
        $stmt->bind_param("s", $identifier);
        if (!$stmt->execute()) {
            die('Execute failed (customers): ' . $stmt->error);
        }
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_type'] = 'customer';
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_email'] = $user['email'];
                $loggedIn = true;
                
                // Redirect to booking page if coming from booking
                if ($redirectUrl) {
                    unset($_SESSION['booking_redirect']);
                    header('Location: ' . $redirectUrl);
                    exit;
                }
                header('Location: customer_dashboard.php');
                exit;
            }
        }
        $stmt->close();

        // 2. Try salons table
        if (!$loggedIn) {
            $stmt = $conn->prepare("SELECT id, name, username, password, is_admin FROM salons WHERE username = ? LIMIT 1");
            if ($stmt === false) {
                die('Prepare failed (salons): ' . $conn->error);
            }
            $stmt->bind_param("s", $identifier);
            if (!$stmt->execute()) {
                die('Execute failed (salons): ' . $stmt->error);
            }
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_type'] = 'salon';
                    $_SESSION['salon_id'] = $user['id'];
                    $_SESSION['salon_name'] = $user['name'];
                    $_SESSION['salon_username'] = $user['username'];
                    $_SESSION['is_admin'] = (int)($user['is_admin'] ?? 0);
                    $loggedIn = true;
                    header('Location: dashboard.php');
                    exit;
                }
            }
            $stmt->close();
        }

        if (!$loggedIn) {
            $error = 'Invalid credentials. Please check your email/username and password.';
        }
    } else {
        $error = 'Please enter both email/username and password.';
    }
}

$page_title = 'Login — Mumbai Glam Studio';
$page_description = 'Sign in to manage your bookings or salon dashboard.';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <?php require_once 'includes/header.php'; ?>
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ LOGIN SECTION ============ -->
<section class="login-section">
    <div class="container">
        <div class="login-card animate-in">
            <div class="form-header">
                <div class="logo-stamp">
                    <img src="assets/favicon.png" alt="Mumbai Glam Studio" style="width:100%; height:100%; object-fit:contain;">
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to your account – customer or salon.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="identifier">Email or Username <span class="required">*</span></label>
                    <input type="text" name="identifier" id="identifier" class="form-control"
                           placeholder="your@email.com or salon_username"
                           value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>"
                           required>
                    <div class="form-error"></div>
                    <small style="color:var(--charcoal-muted); font-size:0.75rem;">Use your email (customer) or salon username.</small>
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" name="password" id="password" class="form-control"
                           placeholder="Enter your password" required>
                    <div class="form-error"></div>
                </div>
                <button type="submit" class="btn btn-primary btn-block" style="padding:14px;">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div style="text-align:center; margin-top:16px;">
                <small style="color:var(--charcoal-muted);">
                    Don't have an account?
                    <a href="register.php" style="color:var(--teal); font-weight:600;">Register Here</a>
                </small>
            </div>

            <div class="login-hint" style="margin-top:16px; border-top:1px solid var(--cream-dark); padding-top:16px;">
                <strong>Demo credentials:</strong><br>
                Customer: <code>demo@example.com</code> / <code>demo123</code><br>
                Salon: <code>andheri1</code> / <code>demo</code><br>
                Admin: <code>admin</code> / <code>admin123</code>
            </div>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script src="script.js"></script>
</body>
</html>