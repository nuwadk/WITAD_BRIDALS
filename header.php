<?php
require_once 'functions.php';

// Get settings
$settings = array();
$settingsRows = fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$pageTitle = isset($pageTitle) ? $pageTitle : '';
$pageDesc = isset($pageDesc) ? $pageDesc : '';
$canonical = isset($canonical) ? $canonical : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php seoMeta($pageTitle, $pageDesc); ?>
  <link rel="canonical" href="<?php echo $canonical; ?>" />
  <link rel="icon" type="image/png" href="favicon.png" />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    :root {
      --primary: #F4A7B9;
      --primary-dk: #e08fa2;
      --secondary: #FFFFFF;
      --accent: #C9A84C;
      --accent-lt: #e8d5a3;
      --accent-dk: #b8972e;
      --text: #3D3D3D;
      --text-lt: #6b6b6b;
      --bg: #FDF8F4;
      --bg-card: #FFFAF7;
      --border: #f0e0d6;
      --shadow: rgba(201,168,76,0.15);
      --success: #10b981;
      --error: #ef4444;
      --warning: #f59e0b;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text);
      background: var(--bg);
      line-height: 1.7;
      overflow-x: hidden;
    }
    img { max-width: 100%; display: block; }
    a { text-decoration: none; color: inherit; }
    h1, h2, h3, h4 { font-family: 'Playfair Display', serif; line-height: 1.25; }
    .serif-sub { font-family: 'Cormorant Garamond', serif; font-style: italic; }

    .container { max-width: 1180px; margin: 0 auto; padding: 0 24px; }
    .section { padding: 90px 0; }
    .section-label {
      font-family: 'Cormorant Garamond', serif; font-style: italic;
      font-size: 1.1rem; color: var(--accent); letter-spacing: 2px;
      margin-bottom: 10px; display: block;
    }
    .section-title {
      font-size: clamp(2rem, 4vw, 2.8rem); color: var(--text); margin-bottom: 18px;
    }
    .section-desc { color: var(--text-lt); max-width: 620px; font-size: 1.02rem; }
    .centered { text-align: center; }
    .centered .section-desc { margin: 0 auto; }
    .divider {
      width: 60px; height: 2px;
      background: linear-gradient(90deg, var(--accent), var(--primary));
      margin: 16px 0 28px;
    }
    .divider.center { margin: 16px auto 28px; }

    /* BUTTONS */
    .btn {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 14px 32px; border-radius: 50px;
      font-family: 'Poppins', sans-serif; font-size: 0.9rem;
      font-weight: 500; cursor: pointer; border: none;
      transition: all 0.3s ease; letter-spacing: 0.5px;
    }
    .btn-primary {
      background: linear-gradient(135deg, var(--accent), var(--accent-dk));
      color: #f9fbfb; box-shadow: 0 4px 18px rgba(201,168,76,0.4);
    }
    .btn-primary:hover {
      transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.5);
    }
    .btn-outline {
      background: transparent; color: var(--accent);
      border: 2px solid var(--accent);
    }
    .btn-outline:hover { background: var(--accent); color: #fff; }
    .btn-outline-dark {
      background: transparent; color: var(--text);
      border: 2px solid var(--border);
    }
    .btn-outline-dark:hover { border-color: var(--accent); color: var(--accent); }
    .btn-sm { padding: 10px 22px; font-size: 0.82rem; }
    .btn-lg { padding: 16px 40px; font-size: 1rem; }

    .gold-tag {
      display: inline-block;
      background: linear-gradient(135deg, var(--accent-lt), var(--accent));
      color: #fff; font-size: 0.75rem; font-weight: 600;
      padding: 4px 14px; border-radius: 50px;
      letter-spacing: 1px; text-transform: uppercase;
    }

    /* NAVBAR */
    .navbar {
      position: fixed; top: 0; left: 0; right: 0;
      z-index: 1000; padding: 18px 0;
      transition: all 0.4s ease;
    }
    .navbar.scrolled {
      background: rgba(253,248,244,0.97);
      backdrop-filter: blur(12px);
      box-shadow: 0 2px 20px rgba(0,0,0,0.08);
      padding: 12px 0;
    }
    .nav-inner {
      display: flex; align-items: center; justify-content: space-between;
    }
    .logo { display: flex; flex-direction: column; line-height: 1; }
    .logo-name {
      font-family: 'Playfair Display', serif; font-size: 1.5rem;
      font-weight: 700; color: var(--secondary);
      transition: color 0.4s; letter-spacing: 1px;
    }
    .logo-tag {
      font-family: 'Cormorant Garamond', serif; font-style: italic;
      font-size: 0.8rem; color: var(--accent-lt);
      letter-spacing: 3px; transition: color 0.4s;
    }
    .navbar.scrolled .logo-name { color: var(--text); }
    .navbar.scrolled .logo-tag { color: var(--accent); }

    .nav-links {
      display: flex; gap: 28px; list-style: none; align-items: center;
    }
    .nav-links a {
      font-size: 0.85rem; font-weight: 500;
      color: rgba(255,255,255,0.9); letter-spacing: 0.5px;
      transition: color 0.3s; position: relative;
    }
    .nav-links a::after {
      content: ''; position: absolute; left: 0; bottom: -3px;
      width: 0; height: 1.5px; background: var(--accent);
      transition: width 0.3s;
    }
    .nav-links a:hover::after,
    .nav-links a.active::after { width: 100%; }
    .navbar.scrolled .nav-links a { color: var(--text); }

    .nav-cta {
      background: linear-gradient(135deg, var(--accent), var(--accent-dk)) !important;
      color: #fff !important; padding: 9px 20px !important;
      border-radius: 50px !important; font-size: 0.82rem !important;
    }
    .nav-cta::after { display: none !important; }

    .nav-icon {
      position: relative; font-size: 1.1rem; color: rgba(255,255,255,0.9);
      transition: color 0.3s;
    }
    .navbar.scrolled .nav-icon { color: var(--text); }
    .nav-icon .badge {
      position: absolute; top: -8px; right: -8px;
      background: var(--primary); color: #fff;
      font-size: 0.65rem; font-weight: 600;
      width: 18px; height: 18px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
    }

    .hamburger {
      display: none; flex-direction: column; gap: 5px;
      cursor: pointer; padding: 4px;
    }
    .hamburger span {
      width: 26px; height: 2px; background: #fff;
      border-radius: 2px; transition: all 0.3s;
    }
    .navbar.scrolled .hamburger span { background: var(--text); }

    .mobile-menu {
      display: none; position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: var(--bg); z-index: 999;
      flex-direction: column; align-items: center;
      justify-content: center; gap: 32px;
    }
    .mobile-menu.open { display: flex; }
    .mobile-menu a {
      font-family: 'Playfair Display', serif;
      font-size: 1.8rem; color: var(--text);
      transition: color 0.3s;
    }
    .mobile-menu a:hover { color: var(--accent); }
    .mobile-close {
      position: absolute; top: 24px; right: 24px;
      font-size: 2rem; cursor: pointer; color: var(--text);
    }

    /* FLASH MESSAGES */
    @keyframes slideDown {
      from { opacity: 0; transform: translate(-50%, -20px); }
      to { opacity: 1; transform: translate(-50%, 0); }
    }

    /* BREADCRUMBS */
    .breadcrumbs {
      background: linear-gradient(135deg, var(--text), #2a1a25);
      padding: 100px 0 40px; color: #fff;
    }
    .breadcrumbs h1 { font-size: 2.2rem; margin-bottom: 8px; }
    .breadcrumbs .crumb {
      font-size: 0.85rem; color: rgba(255,255,255,0.6);
    }
    .breadcrumbs .crumb a { color: var(--accent-lt); transition: color 0.3s; }
    .breadcrumbs .crumb a:hover { color: #fff; }
    .breadcrumbs .crumb i { margin: 0 8px; font-size: 0.7rem; }

    /* PAGINATION */
    .pagination {
      display: flex; justify-content: center; gap: 8px;
      margin-top: 48px;
    }
    .pagination a, .pagination span {
      padding: 10px 16px; border-radius: 10px;
      font-size: 0.9rem; font-weight: 500;
      transition: all 0.3s;
    }
    .pagination a {
      background: var(--bg-card); border: 1px solid var(--border);
      color: var(--text);
    }
    .pagination a:hover {
      background: var(--accent); border-color: var(--accent); color: #fff;
    }
    .pagination span {
      background: var(--accent); color: #fff;
    }

    /* RESPONSIVE */
    @media (max-width: 1024px) {
      .nav-links { display: none; }
      .hamburger { display: flex; }
    }
    @media (max-width: 768px) {
      .section { padding: 60px 0; }
      .breadcrumbs h1 { font-size: 1.6rem; }
    }
  </style>
</head>
<body>
  <!-- FLASH MESSAGES -->
  <?php showFlash(); ?>

  <!-- NAVBAR -->
  <nav class="navbar" id="navbar">
    <div class="container nav-inner">
      <a class="logo" href="index.php">
        <span class="logo-name">Witad</span>
        <span class="logo-tag">Bridal Collection</span>
      </a>
      <ul class="nav-links" id="navLinks">
        <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'class="active"':''; ?>>Home</a></li>
        <li><a href="about.php" <?php echo basename($_SERVER['PHP_SELF'])=='about.php'?'class="active"':''; ?>>About</a></li>
        <li><a href="services.php" <?php echo basename($_SERVER['PHP_SELF'])=='services.php'?'class="active"':''; ?>>Services</a></li>
        <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF'])=='products.php'?'class="active"':''; ?>>Collection</a></li>
        <li><a href="gallery.php" <?php echo basename($_SERVER['PHP_SELF'])=='gallery.php'?'class="active"':''; ?>>Gallery</a></li>
        <li><a href="blog.php" <?php echo basename($_SERVER['PHP_SELF'])=='blog.php'?'class="active"':''; ?>>Journal</a></li>
        <li><a href="testimonials.php" <?php echo basename($_SERVER['PHP_SELF'])=='testimonials.php'?'class="active"':''; ?>>Reviews</a></li>
        <li><a href="contact.php" class="nav-cta">Book Now</a></li>
        <li>
          <a href="cart.php" class="nav-icon" title="Cart">
            <i class="fa fa-shopping-bag"></i>
            <?php if (cartCount() > 0): ?>
            <span class="badge"><?php echo cartCount(); ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li>
          <a href="wishlist.php" class="nav-icon" title="Wishlist">
            <i class="fa fa-heart"></i>
            <?php if (wishlistCount() > 0): ?>
            <span class="badge"><?php echo wishlistCount(); ?></span>
            <?php endif; ?>
          </a>
        </li>
        <?php if (isLoggedIn()): ?>
        <li>
          <a href="account.php" class="nav-icon" title="My Account">
            <i class="fa fa-user"></i>
          </a>
        </li>
        <?php else: ?>
        <li>
          <a href="login.php" class="nav-icon" title="Login">
            <i class="fa fa-sign-in-alt"></i>
          </a>
        </li>
        <?php endif; ?>
      </ul>
      <div class="hamburger" id="hamburger" onclick="openMobile()">
        <span></span><span></span><span></span>
      </div>
    </div>
  </nav>

  <!-- MOBILE MENU -->
  <div class="mobile-menu" id="mobileMenu">
    <span class="mobile-close" onclick="closeMobile()"><i class="fa fa-times"></i></span>
    <a href="index.php" onclick="closeMobile()">Home</a>
    <a href="about.php" onclick="closeMobile()">About</a>
    <a href="services.php" onclick="closeMobile()">Services</a>
    <a href="products.php" onclick="closeMobile()">Collection</a>
    <a href="gallery.php" onclick="closeMobile()">Gallery</a>
    <a href="blog.php" onclick="closeMobile()">Journal</a>
    <a href="testimonials.php" onclick="closeMobile()">Reviews</a>
    <a href="contact.php" onclick="closeMobile()">Book Appointment</a>
    <a href="cart.php" onclick="closeMobile()">Cart (<?php echo cartCount(); ?>)</a>
    <?php if (isLoggedIn()): ?>
    <a href="account.php" onclick="closeMobile()">My Account</a>
    <?php else: ?>
    <a href="login.php" onclick="closeMobile()">Login / Register</a>
    <?php endif; ?>
  </div>
