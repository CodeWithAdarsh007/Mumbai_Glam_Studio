<?php
// Set default title if not defined
$page_title = $page_title ?? 'Mumbai Glam Studio — Mumbai\'s Finest Salon Marketplace';
$page_description = $page_description ?? 'Discover and book Mumbai\'s best salons. Rain-safe studios across Andheri, Bandra, and Dadar.';
?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($page_title); ?></title>
<meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">

<!-- ============ FAVICON ============ -->
<link rel="icon" type="image/png" href="/assets/favicon.png">
<link rel="icon" type="image/x-icon" href="/assets/favicon.png">
<link rel="shortcut icon" type="image/png" href="/assets/favicon.png">

<!-- ============ OPEN GRAPH (Social Sharing) ============ -->
<meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="https://<?php echo $_SERVER['HTTP_HOST']; ?><?php echo $_SERVER['REQUEST_URI']; ?>">
<meta property="og:image" content="https://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/og-image.jpg">
<meta name="twitter:card" content="summary_large_image">

<!-- ============ THEME COLOR ============ -->
<meta name="theme-color" content="#F9F6F0">

<!-- ============ FONTS ============ -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- ============ FONT AWESOME ============ -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- ============ MAIN STYLESHEET ============ -->
<link rel="stylesheet" href="style.css">