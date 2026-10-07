<?php
// ============================================================
// PHP 5.4 COMPATIBILITY FUNCTIONS
// ============================================================

// array_column() was added in PHP 5.5
if (!function_exists('array_column')) {
    function array_column($array, $column_key, $index_key = null) {
        $result = array();
        foreach ($array as $row) {
            if ($index_key !== null && isset($row[$index_key])) {
                $result[$row[$index_key]] = isset($row[$column_key]) ? $row[$column_key] : null;
            } else {
                $result[] = isset($row[$column_key]) ? $row[$column_key] : null;
            }
        }
        return $result;
    }
}

// hash_equals() was added in PHP 5.6
if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string) {
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < strlen($known_string); $i++) {
            $result |= ord($known_string[$i]) ^ ord($user_string[$i]);
        }
        return $result === 0;
    }
}

// random_bytes() was added in PHP 7.0
if (!function_exists('random_bytes')) {
    function random_bytes($length) {
        $bytes = '';
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length);
        } elseif (function_exists('mcrypt_create_iv')) {
            $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        } else {
            for ($i = 0; $i < $length; $i++) {
                $bytes .= chr(mt_rand(0, 255));
            }
        }
        return $bytes;
    }
}

// ============================================================
// PHP 5.4 PASSWORD HASHING COMPATIBILITY
// ============================================================

if (!defined('PASSWORD_BCRYPT')) {
    define('PASSWORD_BCRYPT', 1);
}
if (!defined('PASSWORD_DEFAULT')) {
    define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
}

if (!function_exists('password_hash')) {
    function password_hash($password, $algo = PASSWORD_DEFAULT, $options = array()) {
        $cost = isset($options['cost']) ? $options['cost'] : 10;
        $cost = sprintf('%02d', min(31, max(4, $cost)));
        
        $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9), array('.', '/'));
        $salt = '';
        for ($i = 0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }
        
        $salt = '$2y$' . $cost . '$' . $salt;
        $hash = crypt($password, $salt);
        
        if (strlen($hash) < 13) {
            return false;
        }
        
        return $hash;
    }
}

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        $test_hash = crypt($password, $hash);
        return $hash === $test_hash;
    }
}

if (!function_exists('password_needs_rehash')) {
    function password_needs_rehash($hash, $algo = PASSWORD_DEFAULT, $options = array()) {
        $cost = isset($options['cost']) ? $options['cost'] : 10;
        if (strpos($hash, '$2y$') !== 0) {
            return true;
        }
        if (preg_match('/^\$2y\$(\d{2})\$/', $hash, $matches)) {
            if (intval($matches[1]) !== $cost) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('password_get_info')) {
    function password_get_info($hash) {
        $info = array('algo' => 0, 'algoName' => 'unknown', 'options' => array());
        if (strpos($hash, '$2y$') === 0) {
            $info['algo'] = PASSWORD_BCRYPT;
            $info['algoName'] = 'bcrypt';
            if (preg_match('/^\$2y\$(\d{2})\$/', $hash, $matches)) {
                $info['options']['cost'] = intval($matches[1]);
            }
        }
        return $info;
    }
}

// ============================================================

if (!isset($_SESSION)) {
    session_start();
}

require_once 'db.php';

// ── SECURITY ──
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

// ── AUTH ──
function isLoggedIn() {
    return isset($_SESSION['customer_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: admin.php');
        exit;
    }
}

// ── FORMATTING ──
function formatPrice($price) {
    return 'UGX ' . number_format($price);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y \a\t h:i A', strtotime($datetime));
}

function initials($name) {
    $parts = explode(' ', trim($name));
    $initial = strtoupper(substr($parts[0], 0, 1));
    if (count($parts) > 1) {
        $initial .= strtoupper(substr($parts[count($parts)-1], 0, 1));
    }
    return $initial;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' min ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return formatDate($datetime);
}

// ── CART ──
function getCart() {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
    return $_SESSION['cart'];
}

function cartCount() {
    $cart = getCart();
    return array_sum(array_column($cart, 'qty'));
}

function cartTotal() {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

function addToCart($productId, $name, $price, $image, $qty = 1) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = array();
    $key = 'p' . $productId;
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$key] = array(
            'id' => $productId,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'qty' => $qty
        );
    }
}

function removeFromCart($key) {
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
    }
}

function clearCart() {
    $_SESSION['cart'] = array();
}

// ── WISHLIST ──
function getWishlist() {
    if (!isLoggedIn()) return array();
    return fetchAll("SELECT w.*, p.name, p.price, p.image, p.slug FROM wishlist w JOIN products p ON w.product_id = p.id WHERE w.customer_id = ?", "i", array($_SESSION['customer_id']));
}

function wishlistCount() {
    if (!isLoggedIn()) return 0;
    $result = fetchOne("SELECT COUNT(*) as c FROM wishlist WHERE customer_id = ?", "i", array($_SESSION['customer_id']));
    return $result ? $result['c'] : 0;
}

// ── NOTIFICATIONS ──
function setFlash($type, $message) {
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function showFlash() {
    $flash = getFlash();
    if ($flash) {
        $icon = $flash['type'] == 'success' ? 'check-circle' : ($flash['type'] == 'error' ? 'exclamation-circle' : 'info-circle');
        $color = $flash['type'] == 'success' ? '#10b981' : ($flash['type'] == 'error' ? '#ef4444' : '#3b82f6');
        echo '<div style="position:fixed;top:90px;left:50%;transform:translateX(-50%);z-index:9999;background:'.$color.';color:#fff;padding:14px 28px;border-radius:12px;font-size:0.95rem;font-weight:500;box-shadow:0 8px 30px rgba(0,0,0,0.2);display:flex;align-items:center;gap:10px;animation:slideDown 0.3s ease;">
            <i class="fa fa-'.$icon.'"></i> '.sanitize($flash['message']).'
            <span style="margin-left:10px;cursor:pointer;font-size:1.2rem;" onclick="this.parentElement.remove()">&times;</span>
        </div>';
    }
}

// ── SEO ──


function insertQuery($sql, $types = '', $params = array()) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!empty($types) && !empty($params)) {
        $refs = array();
        foreach ($params as $key => $value) {
            $refs[$key] = &$params[$key];
        }
        array_unshift($refs, $types);
        call_user_func_array(array($stmt, 'bind_param'), $refs);
    }
    $stmt->execute();
    return $stmt->insert_id;
}

function seoMeta($title = '', $desc = '', $image = '') {
    $site = 'Witad Bridal Collection';
    $fullTitle = $title ? $title . ' | ' . $site : $site;
    $defaultDesc = 'Uganda\'s premier bridal destination. Discover stunning Mushanana, accessories, and personalized bridal services in Kabwohe-Sheema.';
    $defaultImg = 'https://yourdomain.com/images/og-image.jpg';

    $descToUse = $desc ? $desc : $defaultDesc;
    $imgToUse = $image ? $image : $defaultImg;

    echo '<title>' . sanitize($fullTitle) . '</title>';
    echo '<meta name="description" content="' . sanitize($descToUse) . '">';
    echo '<meta property="og:title" content="' . sanitize($fullTitle) . '">';
    echo '<meta property="og:description" content="' . sanitize($descToUse) . '">';
    echo '<meta property="og:image" content="' . $imgToUse . '">';
    echo '<meta property="og:type" content="website">';
    echo '<meta name="twitter:card" content="summary_large_image">';
}

// ── PAGINATION ──
function paginate($sql, $types = '', $params = array(), $perPage = 12) {
    global $conn;
    $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
    $offset = ($page - 1) * $perPage;

    $countSql = preg_replace('/SELECT.*?FROM/i', 'SELECT COUNT(*) as total FROM', $sql, 1);
    $countSql = preg_replace('/ORDER BY.*/i', '', $countSql);
    $totalResult = fetchOne($countSql, $types, $params);
    $total = $totalResult ? $totalResult['total'] : 0;
    $pages = ceil($total / $perPage);

    $sql .= " LIMIT ? OFFSET ?";
    $types .= 'ii';
    
    if (!isset($params) || !is_array($params)) {
        $params = array();
    }
    $params[] = $perPage;
    $params[] = $offset;

    $items = fetchAll($sql, $types, $params);

    return array(
        'items' => $items,
        'page' => $page,
        'pages' => $pages,
        'total' => $total,
        'perPage' => $perPage
    );
}

// ── UPLOAD ──
function uploadImage($file, $folder = 'uploads/') {
    $allowed = array('jpg', 'jpeg', 'png', 'webp');
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return array('error' => 'Only JPG, PNG, WEBP images allowed');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return array('error' => 'Image must be under 5MB');
    }

    $filename = uniqid() . '_' . time() . '.' . $ext;
    $path = $folder . $filename;

    if (!is_dir($folder)) mkdir($folder, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $path)) {
        return array('success' => true, 'path' => $path);
    }
    return array('error' => 'Upload failed');
}

// ── EMAIL ──

function sendEmail($to, $subject, $body, $from = null) {
    if (!$from) {
        $from = 'Witad Bridal Collection <info@witadbridal.com>';
    }
    
    $headers  = "From: " . $from . "\r\n";
    $headers .= "Reply-To: info@witadbridal.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Suppress warning on mail failure, return boolean instead
    $result = @mail($to, $subject, $body, $headers);
    
    // Log failure for debugging (optional — check your PHP error log)
    if (!$result) {
        error_log("Mail failed to send to: " . $to . " | Subject: " . $subject);
    }
    
    return $result;
}

function emailTemplate($title, $content) {
    return '<!DOCTYPE html>
    <html><head><meta charset="UTF-8"><title>'.$title.'</title>
    <style>
        body{font-family:Poppins,sans-serif;background:#FDF8F4;margin:0;padding:20px;}
        .container{max-width:600px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,0.1);}
        .header{background:linear-gradient(135deg,#C9A84C,#F4A7B9);padding:40px;text-align:center;}
        .header h1{color:#fff;font-family:"Playfair Display",serif;margin:0;font-size:1.8rem;}
        .body{padding:40px;color:#3D3D3D;line-height:1.8;}
        .footer{background:#1e1018;padding:24px;text-align:center;color:rgba(255,255,255,0.5);font-size:0.8rem;}
        .btn{display:inline-block;background:linear-gradient(135deg,#C9A84C,#b8972e);color:#fff;padding:14px 32px;border-radius:50px;text-decoration:none;font-weight:600;margin:20px 0;}
    </style></head>
    <body><div class="container"><div class="header"><h1>Witad Bridal Collection</h1></div><div class="body">'.$content.'</div><div class="footer">© 2025 Witad Bridal Collection. All rights reserved.<br>Kabwohe-Sheema, Uganda</div></div></body></html>';
}
?>