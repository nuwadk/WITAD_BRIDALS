<?php
// WITAD BRIDAL - Secure Database Connection (PHP 5.4+ Compatible)
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'witad_bridal';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ── CORE QUERY FUNCTIONS ──

function query($sql, $types = '', $params = array()) {
    global $conn;
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("SQL Prepare Error: " . $conn->error . " <br>Query: " . $sql);
    }

    if ($types && $params) {
        $bind_names = array($types);
        for ($i = 0; $i < count($params); $i++) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array(array($stmt, 'bind_param'), $bind_names);
    }
    $stmt->execute();
    return $stmt;
}

function fetchAll($sql, $types = '', $params = array()) {
    $stmt = query($sql, $types, $params);
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchOne($sql, $types = '', $params = array()) {
    $results = fetchAll($sql, $types, $params);
    return $results ? $results[0] : null;
}

function insertId() {
    global $conn;
    return $conn->insert_id;
}

// ── TABLE/COLUMN INTROSPECTION ──

function tableExists($table) {
    global $conn;
    $result = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table) . "'");
    return $result && $result->num_rows > 0;
}

function columnExists($table, $column) {
    global $conn;
    $result = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "` LIKE '" . $conn->real_escape_string($column) . "'");
    return $result && $result->num_rows > 0;
}

function getColumns($table) {
    global $conn;
    $cols = array();
    $result = $conn->query("SHOW COLUMNS FROM `" . $conn->real_escape_string($table) . "`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cols[] = $row['Field'];
        }
    }
    return $cols;
}

// ── DATA MANIPULATION HELPERS (function_exists guards) ──

if (!function_exists('sanitize')) {
    function sanitize($string) {
        if (is_null($string)) return '';
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($amount) {
        if (is_null($amount) || $amount === '' || $amount == 0) return 'UGX 0';
        return 'UGX ' . number_format((float)$amount);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'M d, Y') {
        if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') return '';
        $timestamp = strtotime($date);
        if ($timestamp === false) return '';
        return date($format, $timestamp);
    }
}

// ── SETTINGS HELPERS ──

function getSetting($key, $default = '') {
    if (!tableExists('settings')) return $default;
    $result = fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", 's', array($key));
    return $result ? $result['setting_value'] : $default;
}

function getAllSettings() {
    if (!tableExists('settings')) return array();
    $rows = fetchAll("SELECT setting_key, setting_value FROM settings");
    $settings = array();
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// ── FRONT-END DATA FETCHERS ──

function getFeaturedProducts($limit = 4) {
    if (!tableExists('products')) return array();
    $hasFeatured = columnExists('products', 'featured');
    $hasStatus = columnExists('products', 'status');
    $hasSort = columnExists('products', 'sort_order');
    $hasCategory = columnExists('products', 'category_id');
    
    $sql = "SELECT p.*";
    if ($hasCategory && tableExists('categories')) {
        $sql .= ", c.name as category_name";
    }
    $sql .= " FROM products p";
    if ($hasCategory && tableExists('categories')) {
        $sql .= " LEFT JOIN categories c ON p.category_id = c.id";
    }
    $sql .= " WHERE 1=1";
    if ($hasFeatured) $sql .= " AND p.featured = 1";
    if ($hasStatus) $sql .= " AND p.status = 'active'";
    $sql .= " ORDER BY ";
    if ($hasSort) $sql .= "p.sort_order, ";
    $sql .= "p.id DESC LIMIT ?";
    
    return fetchAll($sql, 'i', array($limit));
}

function getTestimonials($limit = 3) {
    if (!tableExists('testimonials')) return array();
    $hasStatus = columnExists('testimonials', 'status');
    $hasSort = columnExists('testimonials', 'sort_order');
    
    $sql = "SELECT * FROM testimonials WHERE 1=1";
    if ($hasStatus) $sql .= " AND status = 'active'";
    $sql .= " ORDER BY ";
    if ($hasSort) $sql .= "sort_order, ";
    $sql .= "id DESC LIMIT ?";
    
    return fetchAll($sql, 'i', array($limit));
}

function getBlogPosts($limit = 2) {
    if (!tableExists('blog_posts')) return array();
    $hasStatus = columnExists('blog_posts', 'status');
    $hasPublished = columnExists('blog_posts', 'published_at');
    $hasCreated = columnExists('blog_posts', 'created_at');
    
    $sql = "SELECT * FROM blog_posts WHERE 1=1";
    if ($hasStatus) $sql .= " AND status = 'published'";
    $sql .= " ORDER BY ";
    if ($hasPublished) $sql .= "published_at";
    elseif ($hasCreated) $sql .= "created_at";
    else $sql .= "id";
    $sql .= " DESC LIMIT ?";
    
    return fetchAll($sql, 'i', array($limit));
}

function getGalleryImages($limit = 7) {
    if (tableExists('gallery_images')) {
        $hasStatus = columnExists('gallery_images', 'status');
        $hasSort = columnExists('gallery_images', 'sort_order');
        
        $sql = "SELECT * FROM gallery_images WHERE 1=1";
        if ($hasStatus) $sql .= " AND status = 'active'";
        $sql .= " ORDER BY ";
        if ($hasSort) $sql .= "sort_order, ";
        $sql .= "id DESC LIMIT ?";
        
        return fetchAll($sql, 'i', array($limit));
    }
    
    if (tableExists('gallery')) {
        $hasSort = columnExists('gallery', 'sort_order');
        
        $sql = "SELECT * FROM gallery ORDER BY ";
        if ($hasSort) $sql .= "sort_order, ";
        $sql .= "id DESC LIMIT ?";
        
        return fetchAll($sql, 'i', array($limit));
    }
    
    return array();
}

function getAllProducts($category = null, $limit = null) {
    if (!tableExists('products')) return array();
    $hasStatus = columnExists('products', 'status');
    $hasSort = columnExists('products', 'sort_order');
    $hasCategory = columnExists('products', 'category_id');
    $hasCatSlug = tableExists('categories') && columnExists('categories', 'slug');
    
    $sql = "SELECT p.*";
    if ($hasCategory && tableExists('categories')) {
        $sql .= ", c.name as category_name";
    }
    $sql .= " FROM products p";
    if ($hasCategory && tableExists('categories')) {
        $sql .= " LEFT JOIN categories c ON p.category_id = c.id";
    }
    $sql .= " WHERE 1=1";
    if ($hasStatus) $sql .= " AND p.status = 'active'";
    
    $types = '';
    $params = array();
    
    if ($category && $hasCatSlug) {
        $sql .= " AND c.slug = ?";
        $types .= 's';
        $params[] = $category;
    }
    
    $sql .= " ORDER BY ";
    if ($hasSort) $sql .= "p.sort_order, ";
    $sql .= "p.id DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $types .= 'i';
        $params[] = $limit;
    }
    
    return fetchAll($sql, $types, $params);
}

function getProductBySlug($slug) {
    if (!tableExists('products')) return null;
    $hasStatus = columnExists('products', 'status');
    $hasCategory = columnExists('products', 'category_id');
    
    $sql = "SELECT p.*";
    if ($hasCategory && tableExists('categories')) {
        $sql .= ", c.name as category_name";
    }
    $sql .= " FROM products p";
    if ($hasCategory && tableExists('categories')) {
        $sql .= " LEFT JOIN categories c ON p.category_id = c.id";
    }
    $sql .= " WHERE p.slug = ?";
    if ($hasStatus) $sql .= " AND p.status = 'active'";
    
    return fetchOne($sql, 's', array($slug));
}

function getCategories() {
    if (!tableExists('categories')) return array();
    $hasStatus = columnExists('categories', 'status');
    $hasSort = columnExists('categories', 'sort_order');
    
    $sql = "SELECT * FROM categories WHERE 1=1";
    if ($hasStatus) $sql .= " AND status = 'active'";
    $sql .= " ORDER BY ";
    if ($hasSort) $sql .= "sort_order, ";
    $sql .= "name";
    
    return fetchAll($sql);
}

function getServices() {
    if (!tableExists('services')) return array();
    $hasStatus = columnExists('services', 'status');
    $hasSort = columnExists('services', 'sort_order');
    
    $sql = "SELECT * FROM services WHERE 1=1";
    if ($hasStatus) $sql .= " AND status = 'active'";
    $sql .= " ORDER BY ";
    if ($hasSort) $sql .= "sort_order, ";
    $sql .= "id";
    
    return fetchAll($sql);
}

// ── ADMIN DASHBOARD DATA FETCHERS ──

function getAppointments($search = '', $status = '') {
    $table = tableExists('bookings') ? 'bookings' : (tableExists('appointments') ? 'appointments' : null);
    if (!$table) return array();
    
    $sql = "SELECT * FROM {$table} WHERE 1=1";
    $types = '';
    $params = array();
    
    if ($search) {
        $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $searchTerm = "%{$search}%";
        $types .= 'sss';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($status) {
        $sql .= " AND status = ?";
        $types .= 's';
        $params[] = $status;
    }
    
    $hasCreated = columnExists($table, 'created_at');
    $sql .= " ORDER BY ";
    if ($hasCreated) $sql .= "created_at";
    else $sql .= "id";
    $sql .= " DESC";
    
    return fetchAll($sql, $types, $params);
}

function getFeedback($search = '') {
    if (!tableExists('feedback')) return array();
    
    $sql = "SELECT * FROM feedback WHERE 1=1";
    $types = '';
    $params = array();
    
    if ($search) {
        $sql .= " AND (full_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $searchTerm = "%{$search}%";
        $types .= 'sss';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $hasSubmitted = columnExists('feedback', 'submitted_at');
    $sql .= " ORDER BY ";
    if ($hasSubmitted) $sql .= "submitted_at";
    else $sql .= "id";
    $sql .= " DESC";
    
    return fetchAll($sql, $types, $params);
}

function getAllBlogPostsAdmin() {
    if (!tableExists('blog_posts')) return array();
    $hasCreated = columnExists('blog_posts', 'created_at');
    
    $sql = "SELECT * FROM blog_posts ORDER BY ";
    if ($hasCreated) $sql .= "created_at";
    else $sql .= "id";
    $sql .= " DESC";
    
    return fetchAll($sql);
}

function getAllTestimonialsAdmin() {
    if (!tableExists('testimonials')) return array();
    $hasCreated = columnExists('testimonials', 'created_at');
    
    $sql = "SELECT * FROM testimonials ORDER BY ";
    if ($hasCreated) $sql .= "created_at";
    else $sql .= "id";
    $sql .= " DESC";
    
    return fetchAll($sql);
}

function getAllGalleryAdmin() {
    if (!tableExists('gallery')) return array();
    $hasSort = columnExists('gallery', 'sort_order');
    
    $sql = "SELECT * FROM gallery ORDER BY ";
    if ($hasSort) $sql .= "sort_order, ";
    $sql .= "id DESC";
    
    return fetchAll($sql);
}

function getAllGownsAdmin() {
    if (!tableExists('gowns')) return array();
    return fetchAll("SELECT * FROM gowns ORDER BY id DESC");
}

function getAllAdmins() {
    if (!tableExists('admins')) return array();
    $cols = getColumns('admins');
    $selectCols = array();
    foreach (array('id', 'username', 'full_name', 'email', 'role', 'created_at') as $col) {
        if (in_array($col, $cols)) $selectCols[] = $col;
    }
    if (empty($selectCols)) $selectCols = array('*');
    
    return fetchAll("SELECT " . implode(', ', $selectCols) . " FROM admins ORDER BY id DESC");
}

// ── ADMIN ACTIONS ──

function updateStatus($table, $id, $status) {
    $allowedTables = array('bookings', 'feedback', 'appointments', 'testimonials', 'blog_posts', 'products', 'gallery', 'gowns');
    if (!in_array($table, $allowedTables)) return false;
    if (!tableExists($table)) return false;
    if (!columnExists($table, 'status')) return false;
    
    $stmt = query("UPDATE {$table} SET status = ? WHERE id = ?", 'si', array($status, $id));
    return $stmt->affected_rows > 0;
}

function deleteById($table, $id) {
    $allowedTables = array(
        'feedback', 'bookings', 'appointments', 'gallery', 
        'blog_posts', 'testimonials', 'gowns', 'admins', 
        'products', 'categories', 'gallery_images', 'services',
        'customers', 'orders', 'order_items', 'contact_messages',
        'newsletter_subscribers', 'password_resets', 'wishlist',
        'accessories', 'bridesmaids_dresses'
    );
    if (!in_array($table, $allowedTables)) return false;
    if (!tableExists($table)) return false;
    
    $stmt = query("DELETE FROM {$table} WHERE id = ?", 'i', array($id));
    return $stmt->affected_rows > 0;
}

// ── UTILITY FUNCTIONS (function_exists guards) ──

if (!function_exists('createSlug')) {
    function createSlug($string) {
        $slug = strtolower(trim($string));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}

if (!function_exists('uploadImage')) {
    function uploadImage($file, $uploadDir = 'uploads/') {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return false;
        
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) return false;
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return $filepath;
        }
        
        return false;
    }
}

if (!function_exists('sendMail')) {
    function sendMail($to, $subject, $message, $from = '') {
        if (empty($from)) {
            $from = getSetting('site_email', 'info@witadbridal.com');
        }
        
        $headers = "From: " . $from . "\r\n";
        $headers .= "Reply-To: " . $from . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return mail($to, $subject, $message, $headers);
    }
}

if (!function_exists('isAdminLoggedIn')) {
    function isAdminLoggedIn() {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }
}

if (!function_exists('redirect')) {
    function redirect($url, $message = '', $type = 'success') {
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'success';
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            return array('message' => $message, 'type' => $type);
        }
        return null;
    }
}

if (!function_exists('paginate')) {
    function paginate($sql, $types = '', $params = array(), $perPage = 10, $page = 1) {
        global $conn;
        
        $countSql = preg_replace('/SELECT.*?FROM/i', 'SELECT COUNT(*) as total FROM', $sql, 1);
        $countSql = preg_replace('/ORDER BY.*/i', '', $countSql);
        $countResult = fetchOne($countSql, $types, $params);
        $total = $countResult ? (int)$countResult['total'] : 0;
        
        $totalPages = ceil($total / $perPage);
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        $data = fetchAll($sql, $types, $params);
        
        return array(
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasNext' => $page < $totalPages,
            'hasPrev' => $page > 1
        );
    }
}