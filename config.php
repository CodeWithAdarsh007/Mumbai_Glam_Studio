<?php
// Mumbai Glam Studio - Configuration with Environment Variables

// Load environment variables from .env (if exists)
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

define('DB_HOST', getenv('DB_HOST') ?: 'Your_Host_Name');
define('DB_USER', getenv('DB_USER') ?: 'DB_User');
define('DB_PASS', getenv('DB_PASS') ?: 'DB_Pass');
define('DB_NAME', getenv('DB_NAME') ?: 'DB_Name');

// OpenAI API key for AI recommendations (optional)
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');

// Demo credentials are now stored in DB with hashed passwords
define('SITE_NAME', 'Mumbai Glam Studio');
define('SITE_TAGLINE', "Mumbai's Finest Salon Marketplace");

// Error reporting (disable display in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', 0); // set to 1 if using HTTPS

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Database connection with prepared statements support
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log('Database connection failed: ' . $conn->connect_error);
            return false; // Return false instead of dying
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Helper: sanitize input (use with prepared statements)
function sanitize($input) {
    $conn = getDB();
    if (!$conn) {
        return false;
    }
    return $conn->real_escape_string(trim($input));
}

// Helper: render star rating
function renderStars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5;
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($i === $full + 1 && $half) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    return $html;
}

// Helper: format price range
function formatPrice($min, $max) {
    return '&#8377;' . number_format($min) . ' – &#8377;' . number_format($max);
}

// Helper: format service type (PHP 7.4 compatible – no match)
function formatService($type) {
    switch($type) {
        case 'haircut':
            return 'Haircut & Styling';
        case 'bridal':
            return 'Bridal Package';
        case 'mens':
            return "Men's Grooming";
        default:
            return ucfirst($type);
    }
}

// Helper: get locality salons count (using prepared statement)
function getLocalityCount($locality) {
    $conn = getDB();
    if (!$conn) {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM salons WHERE locality = ?");
    $stmt->bind_param("s", $locality);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['cnt'] ?? 0;
}

// Helper: Get salon image URL (no crop, full size)
function getSalonImage($name) {
    // Sanitize name: lowercase, replace spaces and special chars with underscore
    $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
    $safeName = preg_replace('/_+/', '_', $safeName); // remove duplicate underscores
    $safeName = trim($safeName, '_');

    // Possible extensions (check in order)
    $extensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
    $basePath = 'assets/salons/';

    foreach ($extensions as $ext) {
        $path = $basePath . $safeName . '.' . $ext;
        if (file_exists($path)) {
            return $path;
        }
    }

    // Also try with original name (case-sensitive) – fallback
    foreach ($extensions as $ext) {
        $path = $basePath . $name . '.' . $ext;
        if (file_exists($path)) {
            return $path;
        }
    }

    return null; // no image found
}

// Helper: Check if time slot is available
function isTimeSlotAvailable($salonId, $bookingDate, $timeSlot) {
    $conn = getDB();
    if (!$conn) {
        return false;
    }
    $stmt = $conn->prepare("SELECT id FROM bookings WHERE salon_id = ? AND booking_date = ? AND time_slot = ?");
    $stmt->bind_param("iss", $salonId, $bookingDate, $timeSlot);
    $stmt->execute();
    $result = $stmt->get_result();
    $available = $result->num_rows === 0;
    $stmt->close();
    return $available;
}
