<?php
require_once 'config.php';
$conn = getDB();

// Set current page for nav highlighting
$current_page = 'register';

// Determine registration type from POST or default to 'customer'
$regType = isset($_POST['reg_type']) ? $_POST['reg_type'] : 'customer';

// Set page title and description based on type
if ($regType === 'salon') {
    $page_title = 'Join Mumbai Glam Studio — Register Your Salon';
    $page_description = 'List your salon on Mumbai\'s fastest-growing beauty marketplace.';
} else {
    $page_title = 'Join Mumbai Glam Studio — Create Customer Account';
    $page_description = 'Create an account to manage your bookings and appointments.';
}

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regType = $_POST['reg_type'] ?? 'customer';

    if ($regType === 'salon') {
        // ----- SALON REGISTRATION -----
        $name = sanitize($_POST['name'] ?? '');
        $locality = sanitize($_POST['locality'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $tagline = sanitize($_POST['tagline'] ?? '');
        $priceMin = (int)($_POST['price_min'] ?? 0);
        $priceMax = (int)($_POST['price_max'] ?? 0);
        $rating = (float)($_POST['rating'] ?? 0.0);
        $rainSafe = isset($_POST['rain_safe']) ? 1 : 0;
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $contactPhone = sanitize($_POST['contact_phone'] ?? '');
        $contactEmail = sanitize($_POST['contact_email'] ?? '');
        
        // NEW FIELDS
        $description = sanitize($_POST['description'] ?? '');
        $services = sanitize($_POST['services'] ?? '');
        $facilities = sanitize($_POST['facilities'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $workingHours = sanitize($_POST['working_hours'] ?? '');
        $establishedYear = (int)($_POST['established_year'] ?? 0);

        // Validate
        if (strlen($name) < 3) {
            $errors[] = 'Salon name must be at least 3 characters.';
        }
        if (strlen($locality) < 2) {
            $errors[] = 'Please enter a valid locality (e.g., Andheri, Bandra, Dadar, etc.).';
        }
        if (strlen($address) < 5) {
            $errors[] = 'Please enter a valid address.';
        }
        if ($priceMin < 0 || $priceMax < 0) {
            $errors[] = 'Price must be a positive number.';
        }
        if ($priceMin > $priceMax) {
            $errors[] = 'Minimum price cannot be greater than maximum price.';
        }
        if ($rating < 0 || $rating > 5) {
            $errors[] = 'Rating must be between 0 and 5.';
        }
        if (strlen($username) < 4) {
            $errors[] = 'Username must be at least 4 characters.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        $phoneDigits = preg_replace('/\D/', '', $contactPhone);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 12) {
            $errors[] = 'Please enter a valid 10-digit phone number.';
        }

        // Check if username already exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM salons WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = 'Username already taken. Please choose another.';
            }
            $stmt->close();
        }

        // Handle image upload
        $imagePath = null;
        if (empty($errors) && isset($_FILES['salon_image']) && $_FILES['salon_image']['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($_FILES['salon_image']['type'], $allowedTypes)) {
                $errors[] = 'Please upload a valid image (JPEG, PNG, WEBP, or GIF).';
            } elseif ($_FILES['salon_image']['size'] > $maxSize) {
                $errors[] = 'Image size must be under 2MB.';
            } else {
                $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
                $safeName = preg_replace('/_+/', '_', $safeName);
                $safeName = trim($safeName, '_');
                
                $ext = pathinfo($_FILES['salon_image']['name'], PATHINFO_EXTENSION);
                $filename = $safeName . '.' . $ext;
                $targetPath = 'assets/salons/' . $filename;

                if (!is_dir('assets/salons')) {
                    mkdir('assets/salons', 0755, true);
                }

                foreach (['jpg', 'jpeg', 'png', 'webp', 'gif'] as $existingExt) {
                    $existingPath = 'assets/salons/' . $safeName . '.' . $existingExt;
                    if (file_exists($existingPath)) {
                        unlink($existingPath);
                    }
                }

                if (move_uploaded_file($_FILES['salon_image']['tmp_name'], $targetPath)) {
                    $imagePath = $targetPath;
                } else {
                    $errors[] = 'Failed to upload image. Please try again.';
                }
            }
        }

        // Insert salon with all new fields
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO salons (name, locality, address, tagline, price_min, price_max, rating, username, password, verified, rain_safe, description, services, facilities, contact_phone, contact_email, website, working_hours, established_year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiidssisssssssi", $name, $locality, $address, $tagline, $priceMin, $priceMax, $rating, $username, $hashedPassword, $rainSafe, $description, $services, $facilities, $contactPhone, $contactEmail, $website, $workingHours, $establishedYear);

            if ($stmt->execute()) {
                $success = true;
                $successType = 'salon';
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        // ----- CUSTOMER REGISTRATION -----
        $customerName = sanitize($_POST['customer_name'] ?? '');
        $customerEmail = sanitize($_POST['customer_email'] ?? '');
        $customerPhone = sanitize($_POST['customer_phone'] ?? '');
        $customerPassword = $_POST['customer_password'] ?? '';
        $customerConfirm = $_POST['customer_confirm'] ?? '';

        // Validate
        if (strlen($customerName) < 2) {
            $errors[] = 'Please enter your full name.';
        }
        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        $phoneDigits = preg_replace('/\D/', '', $customerPhone);
        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 12) {
            $errors[] = 'Please enter a valid 10-digit phone number.';
        }
        if (strlen($customerPassword) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($customerPassword !== $customerConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        // Check if email already exists
        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id FROM customers WHERE email = ?");
            $stmt->bind_param("s", $customerEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $errors[] = 'Email already registered. Please login.';
            }
            $stmt->close();
        }

        // Insert customer
        if (empty($errors)) {
            $hashedPassword = password_hash($customerPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO customers (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $customerName, $customerEmail, $customerPhone, $hashedPassword);

            if ($stmt->execute()) {
                $success = true;
                $successType = 'customer';
            } else {
                $errors[] = 'Database error: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="assets/favicon.png">
</head>
<body>

<!-- ============ NAVIGATION ============ -->
<?php require_once 'includes/nav.php'; ?>

<!-- ============ REGISTRATION SECTION ============ -->
<section class="register-section">
    <div class="container">
        <div class="register-wrapper animate-in">

            <?php if ($success): ?>
            <!-- Success State -->
            <div class="register-success">
                <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                <?php if ($successType === 'salon'): ?>
                <h2>Your Salon is Now Listed! 🎉</h2>
                <p>Your salon has been successfully registered on Mumbai Glam Studio.</p>
                <?php if (isset($imagePath) && $imagePath): ?>
                <div style="margin: 16px auto; max-width: 200px;">
                    <img src="<?php echo $imagePath; ?>" alt="Salon image" style="width:100%; border-radius:var(--radius-md); box-shadow:var(--shadow-sm);">
                </div>
                <?php endif; ?>
                <div class="success-message">
                    <p><strong>Your salon is now live!</strong> Customers can discover and book appointments at your studio.</p>
                    <p style="margin-top: 10px; font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Your salon is currently <strong>unverified</strong>. 
                        Contact us to get the <span class="text-gold">✨ Verified</span> badge and increase trust with customers.
                    </p>
                </div>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                    <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
                    <a href="index.php" class="btn btn-outline">Return to Home</a>
                </div>
                <?php else: ?>
                <h2>Account Created Successfully! 🎉</h2>
                <p>Your customer account has been created. You can now log in and manage your bookings.</p>
                <div class="success-message">
                    <p><strong>Welcome to Mumbai Glam Studio!</strong> You can now book appointments at Mumbai's finest salons.</p>
                </div>
                <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                    <a href="login.php" class="btn btn-primary">Log In Now</a>
                    <a href="salons.php" class="btn btn-outline">Browse Salons</a>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>

            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>

            <div class="form-header">
                <h2>Create Your Account</h2>
                <p>Choose whether you're a customer or a salon owner.</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-circle-exclamation"></i>
                <div><?php foreach ($errors as $err) echo '<div>• ' . $err . '</div>'; ?></div>
            </div>
            <?php endif; ?>

            <!-- Radio Toggle -->
            <div style="display: flex; gap: 0; margin-bottom: 24px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--cream-dark);">
                <button type="button" id="tabCustomer" class="reg-tab active" style="flex:1; padding:12px; background:var(--teal); color:var(--text-light); border:none; cursor:pointer; font-weight:600; transition:background 0.3s;">
                    <i class="fas fa-user"></i> Customer
                </button>
                <button type="button" id="tabSalon" class="reg-tab" style="flex:1; padding:12px; background:var(--cream); color:var(--charcoal-light); border:none; cursor:pointer; font-weight:600; transition:background 0.3s;">
                    <i class="fas fa-store"></i> Salon Owner
                </button>
            </div>

            <!-- Form -->
            <form id="register-form" method="POST" action="register.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="reg_type" id="reg_type" value="customer">

                <!-- ===== CUSTOMER FORM ===== -->
                <div id="customerForm">
                    <div class="form-group">
                        <label for="customer_name">Full Name <span class="required">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control"
                               placeholder="Your full name"
                               value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="customer_email">Email Address <span class="required">*</span></label>
                        <input type="email" name="customer_email" id="customer_email" class="form-control"
                               placeholder="your@email.com"
                               value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-group">
                        <label for="customer_phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" name="customer_phone" id="customer_phone" class="form-control"
                               placeholder="9876543210"
                               value="<?php echo isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_password">Password <span class="required">*</span></label>
                            <input type="password" name="customer_password" id="customer_password" class="form-control"
                                   placeholder="Min 6 characters" required>
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer_confirm">Confirm Password <span class="required">*</span></label>
                            <input type="password" name="customer_confirm" id="customer_confirm" class="form-control"
                                   placeholder="Re-enter password" required>
                            <div class="form-error"></div>
                        </div>
                    </div>
                </div>

                <!-- ===== SALON FORM ===== -->
                <div id="salonForm" style="display: none;">
                    <!-- Salon Details -->
                    <div class="form-group">
                        <label for="name">Salon Name <span class="required">*</span></label>
                        <input type="text" name="name" id="name" class="form-control"
                               placeholder="e.g., The Bombay Curl Co."
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="locality">Locality <span class="required">*</span></label>
                            <input type="text" name="locality" id="locality" class="form-control"
                                   placeholder="e.g., Andheri West, Bandra, Dadar, Powai, etc."
                                   value="<?php echo isset($_POST['locality']) ? htmlspecialchars($_POST['locality']) : ''; ?>"
                                   required>
                            <div class="form-error"></div>
                            <small style="color:var(--charcoal-muted); font-size:0.75rem;">Enter any area in Mumbai.</small>
                        </div>
                        <div class="form-group">
                            <label for="tagline">Tagline</label>
                            <input type="text" name="tagline" id="tagline" class="form-control"
                                   placeholder="e.g., Where brides become legends"
                                   value="<?php echo isset($_POST['tagline']) ? htmlspecialchars($_POST['tagline']) : ''; ?>">
                            <div class="form-error"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Full Address <span class="required">*</span></label>
                        <input type="text" name="address" id="address" class="form-control"
                               placeholder="e.g., 14, Lokhandwala Market, Andheri West"
                               value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                    </div>

                    <!-- Price Range -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="price_min">Price Range – Minimum (₹) <span class="required">*</span></label>
                            <input type="number" name="price_min" id="price_min" class="form-control"
                                   placeholder="e.g., 500"
                                   min="0"
                                   value="<?php echo isset($_POST['price_min']) ? htmlspecialchars($_POST['price_min']) : ''; ?>"
                                   required>
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="price_max">Price Range – Maximum (₹) <span class="required">*</span></label>
                            <input type="number" name="price_max" id="price_max" class="form-control"
                                   placeholder="e.g., 3500"
                                   min="0"
                                   value="<?php echo isset($_POST['price_max']) ? htmlspecialchars($_POST['price_max']) : ''; ?>"
                                   required>
                            <div class="form-error"></div>
                        </div>
                    </div>

                    <!-- Rating (Optional) -->
                    <div class="form-group">
                        <label for="rating">Rating (out of 5)</label>
                        <input type="number" name="rating" id="rating" class="form-control"
                               placeholder="e.g., 4.5"
                               min="0" max="5" step="0.1"
                               value="<?php echo isset($_POST['rating']) ? htmlspecialchars($_POST['rating']) : ''; ?>">
                        <div class="form-error"></div>
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Leave blank if you don't have a rating yet (default 0).</small>
                    </div>

                    <!-- ============================================================
                         NEW SALON DETAILS FIELDS
                         ============================================================ -->
                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">About Your Salon</label>
                        <textarea name="description" id="description" class="form-control" rows="4" placeholder="Tell customers about your salon, your expertise, and what makes you special..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">A detailed description helps customers understand your salon better.</small>
                        <div class="form-error"></div>
                    </div>

                    <!-- Services -->
                    <div class="form-group">
                        <label for="services">Services Offered</label>
                        <input type="text" name="services" id="services" class="form-control"
                               placeholder="e.g., Haircut, Bridal Makeup, Men's Grooming, etc."
                               value="<?php echo isset($_POST['services']) ? htmlspecialchars($_POST['services']) : ''; ?>">
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Separate services with commas.</small>
                        <div class="form-error"></div>
                    </div>

                    <!-- Facilities -->
                    <div class="form-group">
                        <label for="facilities">Facilities</label>
                        <input type="text" name="facilities" id="facilities" class="form-control"
                               placeholder="e.g., Parking, Wi-Fi, Air Conditioning, etc."
                               value="<?php echo isset($_POST['facilities']) ? htmlspecialchars($_POST['facilities']) : ''; ?>">
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Separate facilities with commas.</small>
                        <div class="form-error"></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact_phone">Contact Phone</label>
                            <input type="tel" name="contact_phone" id="contact_phone" class="form-control"
                                   placeholder="e.g., +91 98765 43210"
                                   value="<?php echo isset($_POST['contact_phone']) ? htmlspecialchars($_POST['contact_phone']) : ''; ?>">
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="contact_email">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email" class="form-control"
                                   placeholder="e.g., salon@email.com"
                                   value="<?php echo isset($_POST['contact_email']) ? htmlspecialchars($_POST['contact_email']) : ''; ?>">
                            <div class="form-error"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="website">Website</label>
                            <input type="text" name="website" id="website" class="form-control"
                                   placeholder="e.g., www.salonwebsite.com"
                                   value="<?php echo isset($_POST['website']) ? htmlspecialchars($_POST['website']) : ''; ?>">
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="working_hours">Working Hours</label>
                            <input type="text" name="working_hours" id="working_hours" class="form-control"
                                   placeholder="e.g., Mon–Sat: 9AM–9PM, Sun: 10AM–6PM"
                                   value="<?php echo isset($_POST['working_hours']) ? htmlspecialchars($_POST['working_hours']) : ''; ?>">
                            <div class="form-error"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="established_year">Year Established</label>
                        <select name="established_year" id="established_year" class="form-control">
                            <option value="">Select year</option>
                            <?php for ($year = date('Y'); $year >= 1980; $year--): ?>
                            <option value="<?php echo $year; ?>" <?php echo (isset($_POST['established_year']) && $_POST['established_year'] == $year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                        <div class="form-error"></div>
                    </div>

                    <!-- ============================================================
                         END OF NEW FIELDS
                         ============================================================ -->

                    <!-- Rain-safe Checkbox -->
                    <div class="form-group" style="background: var(--cream); padding: 16px; border-radius: var(--radius-md); border-left: 3px solid var(--gold);">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <input type="checkbox" name="rain_safe" id="rain_safe" value="1" 
                                   <?php echo isset($_POST['rain_safe']) ? 'checked' : ''; ?>
                                   style="width: 20px; height: 20px; margin-top: 2px; cursor: pointer; flex-shrink: 0;">
                            <div>
                                <label for="rain_safe" style="cursor: pointer; font-weight: 600; font-size: 1rem; color: var(--teal);">
                                    <i class="fas fa-umbrella" style="color: var(--gold);"></i> My salon is rain‑safe
                                </label>
                                <p style="margin: 6px 0 4px 0; font-size: 0.9rem; color: var(--charcoal-light); line-height: 1.5;">
                                    I have a covered, indoor setup with no water seepage – customers can book with confidence even during Mumbai's heavy rains.
                                </p>
                                <ul style="margin: 6px 0 0 0; padding-left: 18px; font-size: 0.82rem; color: var(--charcoal-muted); line-height: 1.6;">
                                    <li>✔️ Salons with this badge get <strong>more bookings</strong> during monsoon season.</li>
                                    <li>✔️ Customers actively <strong>filter</strong> by rain‑safe when searching.</li>
                                    <li>✔️ Reduces last‑minute <strong>cancellations</strong> due to weather.</li>
                                </ul>
                                <small style="color: var(--charcoal-muted); font-size: 0.75rem; display: block; margin-top: 6px;">
                                    <i class="fas fa-info-circle"></i> Check this box if your salon is fully protected from rain.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group">
                        <label for="salon_image">Salon Image</label>
                        <input type="file" name="salon_image" id="salon_image" class="form-control" 
                               accept="image/jpeg,image/png,image/webp,image/gif"
                               style="padding: 10px;">
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Upload a photo of your salon (max 2MB). JPEG, PNG, WEBP, or GIF.</small>
                        <div class="form-error"></div>
                    </div>

                    <!-- Login Credentials -->
                    <div class="form-group">
                        <label for="username">Desired Username <span class="required">*</span></label>
                        <input type="text" name="username" id="username" class="form-control"
                               placeholder="e.g., thebombaycurl"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                               required>
                        <div class="form-error"></div>
                        <small style="color:var(--charcoal-muted); font-size:0.75rem;">Used to log in to your dashboard.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="Min 6 characters" required>
                            <div class="form-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                   placeholder="Re-enter password" required>
                            <div class="form-error"></div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="padding:14px; margin-top: 8px;">
                    <i class="fas fa-paper-plane"></i> <span id="submitLabel">Create Account</span>
                </button>

                <div style="text-align:center; margin-top:16px;">
                    <small style="color:var(--charcoal-muted);">
                        Already have an account? <a href="login.php" style="color:var(--teal); font-weight:600;">Sign In</a>
                    </small>
                </div>
            </form>

            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<?php require_once 'includes/footer.php'; ?>

<script>
    // Tab switching logic
    document.addEventListener('DOMContentLoaded', function() {
        const tabCustomer = document.getElementById('tabCustomer');
        const tabSalon = document.getElementById('tabSalon');
        const customerForm = document.getElementById('customerForm');
        const salonForm = document.getElementById('salonForm');
        const regType = document.getElementById('reg_type');
        const submitLabel = document.getElementById('submitLabel');

        function setActiveTab(type) {
            if (type === 'customer') {
                tabCustomer.className = 'reg-tab active';
                tabCustomer.style.background = 'var(--teal)';
                tabCustomer.style.color = 'var(--text-light)';
                tabSalon.className = 'reg-tab';
                tabSalon.style.background = 'var(--cream)';
                tabSalon.style.color = 'var(--charcoal-light)';
                customerForm.style.display = 'block';
                salonForm.style.display = 'none';
                regType.value = 'customer';
                submitLabel.textContent = 'Create Account';
                document.querySelector('.form-header h2').textContent = 'Create Customer Account';
                document.querySelector('.form-header p').textContent = 'Sign up to manage your bookings and appointments.';
            } else {
                tabSalon.className = 'reg-tab active';
                tabSalon.style.background = 'var(--teal)';
                tabSalon.style.color = 'var(--text-light)';
                tabCustomer.className = 'reg-tab';
                tabCustomer.style.background = 'var(--cream)';
                tabCustomer.style.color = 'var(--charcoal-light)';
                salonForm.style.display = 'block';
                customerForm.style.display = 'none';
                regType.value = 'salon';
                submitLabel.textContent = 'List My Salon';
                document.querySelector('.form-header h2').textContent = 'List Your Salon on Mumbai Glam';
                document.querySelector('.form-header p').textContent = 'Join Mumbai\'s fastest-growing beauty marketplace.';
            }
        }

        tabCustomer.addEventListener('click', function() {
            setActiveTab('customer');
        });

        tabSalon.addEventListener('click', function() {
            setActiveTab('salon');
        });

        // Check if there's a POST value and restore the active tab
        <?php if (isset($_POST['reg_type']) && $_POST['reg_type'] === 'salon'): ?>
        setActiveTab('salon');
        <?php else: ?>
        setActiveTab('customer');
        <?php endif; ?>
    });
</script>

<script src="script.js"></script>
</body>
</html>