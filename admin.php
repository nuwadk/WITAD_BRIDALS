<?php
// WITAD BRIDAL - ADMIN DASHBOARD (MySQLi with db.php Integration)
// PHP 5.4+ Compatible with FILE UPLOAD SUPPORT
session_start();

require_once 'db.php';

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
ini_set('display_errors', '1');

$admin_username = 'admin';
$admin_password = 'witad2025';

$upload_dir = 'uploads/';
$allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
$max_file_size = 5 * 1024 * 1024;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function handleFileUpload($file_field, $upload_dir, $allowed_extensions, $max_file_size) {
    if (!isset($_FILES[$file_field]) || $_FILES[$file_field]['error'] == UPLOAD_ERR_NO_FILE) {
        return array('success' => false, 'path' => '', 'error' => 'No file uploaded');
    }
    $file = $_FILES[$file_field];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = array(
            UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        );
        $error_msg = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Unknown upload error';
        return array('success' => false, 'path' => '', 'error' => $error_msg);
    }
    if ($file['size'] > $max_file_size) {
        return array('success' => false, 'path' => '', 'error' => 'File too large. Max size: ' . ($max_file_size / 1024 / 1024) . 'MB');
    }
    $filename = basename($file['name']);
    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        return array('success' => false, 'path' => '', 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions));
    }
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $target_path = $upload_dir . $new_filename;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return array('success' => true, 'path' => $target_path, 'error' => '');
    } else {
        return array('success' => false, 'path' => '', 'error' => 'Failed to move uploaded file');
    }
}

// ── LOGIN HANDLING ──
if (isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Invalid username or password';
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ── STATUS UPDATES ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $new_status = $_POST['status'];
    $type = $_POST['type'];
    if ($type == 'feedback') {
        updateStatus('feedback', $id, $new_status);
    } else {
        updateStatus('bookings', $id, $new_status);
    }
    header('Location: admin.php?page=' . $page . '&updated=1');
    exit;
}

// ── DELETE ──
if ($is_logged_in && isset($_GET['delete']) && isset($_GET['type'])) {
    $id = (int)$_GET['delete'];
    $type = $_GET['type'];
    deleteById($type, $id);
    header('Location: admin.php?page=' . $page . '&deleted=1');
    exit;
}

// ── HELPER: Build data array only with columns that exist in table ──
function buildDataFromPost($table, $fieldMap) {
    $cols = getColumns($table);
    $data = array();
    foreach ($fieldMap as $postKey => $dbKey) {
        if (in_array($dbKey, $cols) && isset($_POST[$postKey])) {
            $val = $_POST[$postKey];
            if ($dbKey == 'price') $val = (float)$val;
            elseif ($dbKey == 'rating' || $dbKey == 'sort_order') $val = (int)$val;
            $data[$dbKey] = $val;
        }
    }
    return $data;
}

// ── BLOG POST ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_blog'])) {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $data = buildDataFromPost('blog_posts', array(
        'title' => 'title', 'slug' => 'slug', 'content' => 'content', 'body' => 'content', 'text' => 'content',
        'excerpt' => 'excerpt', 'summary' => 'excerpt', 'snippet' => 'excerpt',
        'category' => 'category', 'cat' => 'category', 'type' => 'category',
        'author' => 'author', 'writer' => 'author', 'posted_by' => 'author',
        'status' => 'status', 'state' => 'status',
        'featured_image' => 'featured_image', 'image' => 'featured_image', 'photo' => 'featured_image', 'picture' => 'featured_image'
    ));
    if (isset($_FILES['featured_image_file']) && $_FILES['featured_image_file']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = handleFileUpload('featured_image_file', $upload_dir, $allowed_extensions, $max_file_size);
        if ($upload_result['success'] && in_array('featured_image', getColumns('blog_posts'))) {
            $data['featured_image'] = $upload_result['path'];
        }
    }
    if (empty($data['slug']) && !empty($data['title'])) {
        $data['slug'] = createSlug($data['title']);
    }
    if ($edit_id > 0) {
        $setParts = array(); $values = array(); $types = '';
        foreach ($data as $col => $val) { $setParts[] = "$col=?"; $values[] = $val; $types .= 's'; }
        $values[] = $edit_id; $types .= 'i';
        if (!empty($setParts)) query("UPDATE blog_posts SET " . implode(', ', $setParts) . " WHERE id=?", $types, $values);
    } else {
        $columns = array_keys($data); $values = array_values($data); $types = str_repeat('s', count($values));
        if (!empty($columns)) query("INSERT INTO blog_posts (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ")", $types, $values);
    }
    header('Location: admin.php?page=blog&saved=1');
    exit;
}

// ── TESTIMONIAL ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_testimonial'])) {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $data = buildDataFromPost('testimonials', array(
        'full_name' => 'full_name', 'customer_name' => 'full_name', 'name' => 'full_name',
        'location' => 'location', 'place' => 'location', 'city' => 'location',
        'wedding_date' => 'wedding_date', 'event_date' => 'wedding_date', 'date' => 'wedding_date',
        'rating' => 'rating', 'stars' => 'rating', 'score' => 'rating',
        'quote' => 'quote', 'content' => 'quote', 'review' => 'quote', 'message' => 'quote', 'text' => 'quote',
        'status' => 'status', 'state' => 'status', 'active' => 'status'
    ));
    if ($edit_id > 0) {
        $setParts = array(); $values = array(); $types = '';
        foreach ($data as $col => $val) { $setParts[] = "$col=?"; $values[] = $val; $types .= is_int($val) ? 'i' : 's'; }
        $values[] = $edit_id; $types .= 'i';
        if (!empty($setParts)) query("UPDATE testimonials SET " . implode(', ', $setParts) . " WHERE id=?", $types, $values);
    } else {
        $columns = array_keys($data); $values = array_values($data); $types = '';
        foreach ($values as $val) { $types .= is_int($val) ? 'i' : 's'; }
        if (!empty($columns)) query("INSERT INTO testimonials (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ")", $types, $values);
    }
    header('Location: admin.php?page=testimonials&saved=1');
    exit;
}

// ── GALLERY ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_gallery'])) {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $data = buildDataFromPost('gallery', array(
        'title' => 'title', 'heading' => 'title', 'subject' => 'title',
        'image_path' => 'image_path', 'image' => 'image_path', 'photo' => 'image_path', 'picture' => 'image_path', 'url' => 'image_path',
        'category' => 'category', 'cat' => 'category', 'type' => 'category',
        'description' => 'description', 'desc' => 'description', 'details' => 'description',
        'sort_order' => 'sort_order', 'order' => 'sort_order', 'position' => 'sort_order'
    ));
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = handleFileUpload('image_file', $upload_dir, $allowed_extensions, $max_file_size);
        if ($upload_result['success'] && in_array('image_path', getColumns('gallery'))) {
            $data['image_path'] = $upload_result['path'];
        } elseif (!$upload_result['success']) {
            $upload_error = $upload_result['error'];
        }
    }
    if ($edit_id > 0) {
        $setParts = array(); $values = array(); $types = '';
        foreach ($data as $col => $val) { $setParts[] = "$col=?"; $values[] = $val; $types .= is_int($val) ? 'i' : 's'; }
        $values[] = $edit_id; $types .= 'i';
        if (!empty($setParts)) query("UPDATE gallery SET " . implode(', ', $setParts) . " WHERE id=?", $types, $values);
    } else {
        $columns = array_keys($data); $values = array_values($data); $types = '';
        foreach ($values as $val) { $types .= is_int($val) ? 'i' : 's'; }
        if (!empty($columns)) query("INSERT INTO gallery (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ")", $types, $values);
    }
    header('Location: admin.php?page=gallery&saved=1');
    exit;
}

// ── GOWN/PRODUCT ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_gown'])) {
    $edit_id = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
    $data = buildDataFromPost('gowns', array(
        'name' => 'name', 'title' => 'name',
        'description' => 'description', 'desc' => 'description',
        'price' => 'price', 'amount' => 'price', 'cost' => 'price',
        'category' => 'category', 'cat' => 'category', 'type' => 'category',
        'size' => 'size', 'sizes' => 'size',
        'color' => 'color', 'colors' => 'color', 'colour' => 'color',
        'image_path' => 'image_path', 'image' => 'image_path', 'photo' => 'image_path', 'picture' => 'image_path',
        'status' => 'status', 'state' => 'status'
    ));
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_result = handleFileUpload('image_file', $upload_dir, $allowed_extensions, $max_file_size);
        if ($upload_result['success'] && in_array('image_path', getColumns('gowns'))) {
            $data['image_path'] = $upload_result['path'];
        } elseif (!$upload_result['success']) {
            $upload_error = $upload_result['error'];
        }
    }
    if ($edit_id > 0) {
        $setParts = array(); $values = array(); $types = '';
        foreach ($data as $col => $val) {
            $setParts[] = "$col=?"; $values[] = $val;
            if (is_int($val)) $types .= 'i'; elseif (is_float($val)) $types .= 'd'; else $types .= 's';
        }
        $values[] = $edit_id; $types .= 'i';
        if (!empty($setParts)) query("UPDATE gowns SET " . implode(', ', $setParts) . " WHERE id=?", $types, $values);
    } else {
        $columns = array_keys($data); $values = array_values($data); $types = '';
        foreach ($values as $val) {
            if (is_int($val)) $types .= 'i'; elseif (is_float($val)) $types .= 'd'; else $types .= 's';
        }
        if (!empty($columns)) query("INSERT INTO gowns (" . implode(', ', $columns) . ") VALUES (" . implode(', ', array_fill(0, count($columns), '?')) . ")", $types, $values);
    }
    header('Location: admin.php?page=gowns&saved=1');
    exit;
}

// ── ADMIN USER ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_admin'])) {
    $username = $_POST['username']; $password_plain = $_POST['password']; $full_name = $_POST['full_name']; $email = $_POST['email']; $role = $_POST['role'];
    if (function_exists('password_hash')) {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);
    } else {
        $password = hash('sha256', $password_plain);
    }
    query("INSERT INTO admins (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)", 'sssss', array($username, $password, $full_name, $email, $role));
    header('Location: admin.php?page=admins&saved=1');
    exit;
}

// ── SETTINGS ──
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    $cols = getColumns('settings');
    $hasKey = in_array('setting_key', $cols); $hasValue = in_array('setting_value', $cols);
    $hasKeyCol = in_array('key', $cols); $hasValCol = in_array('value', $cols);
    $keyCol = $hasKey ? 'setting_key' : ($hasKeyCol ? 'key' : null);
    $valCol = $hasValue ? 'setting_value' : ($hasValCol ? 'value' : null);
    if ($keyCol && $valCol) {
        $settings = array(
            'site_name' => $_POST['site_name'], 'site_email' => $_POST['site_email'], 'phone' => $_POST['phone'],
            'address' => $_POST['address'], 'working_hours' => $_POST['working_hours'],
            'facebook' => $_POST['facebook'], 'instagram' => $_POST['instagram'], 'whatsapp' => $_POST['whatsapp']
        );
        foreach ($settings as $key => $value) {
            query("INSERT INTO settings ({$keyCol}, {$valCol}) VALUES (?, ?) ON DUPLICATE KEY UPDATE {$valCol}=?", 'sss', array($key, $value, $value));
        }
    }
    header('Location: admin.php?page=settings&saved=1');
    exit;
}

// ── LOAD DATA ──
$search = isset($_GET['search']) ? $_GET['search'] : '';
$feedbackList = getFeedback($search);
$appointmentsList = getAppointments($search);
$blogPosts = getAllBlogPostsAdmin();
$testimonials = getAllTestimonialsAdmin();
$galleryItems = getAllGalleryAdmin();
$gowns = getAllGownsAdmin();
$admins = getAllAdmins();
$settings = getAllSettings();

// ── STATS ──
$total_bookings = count($appointmentsList);
$total_feedback = count($feedbackList);
$pending_count = 0; $confirmed_count = 0; $completed_count = 0;
foreach ($appointmentsList as $item) {
    $status = isset($item['status']) ? strtolower($item['status']) : 'pending';
    if ($status == 'pending') $pending_count++;
    elseif ($status == 'confirmed') $confirmed_count++;
    elseif ($status == 'completed') $completed_count++;
}
$today = date('Y-m-d');
$today_count = 0;
foreach ($appointmentsList as $item) {
    if (isset($item['created_at']) && strpos($item['created_at'], $today) === 0) $today_count++;
}
$service_counts = array();
foreach ($appointmentsList as $item) {
    $service = isset($item['service_needed']) ? $item['service_needed'] : 'General Inquiry';
    if (!isset($service_counts[$service])) $service_counts[$service] = 0;
    $service_counts[$service]++;
}
$months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$monthly_counts = array_fill(0, 12, 0);
foreach ($appointmentsList as $item) {
    if (isset($item['created_at'])) {
        $month = (int)substr($item['created_at'], 5, 2) - 1;
        if ($month >= 0 && $month < 12) $monthly_counts[$month]++;
    }
}
$max_count = !empty($monthly_counts) ? max($monthly_counts) : 1; if ($max_count == 0) $max_count = 1;

// ── EXPORT CSV ──
if ($is_logged_in && isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bookings_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('ID', 'Full Name', 'Phone', 'Email', 'Wedding Date', 'Service', 'Notes', 'Status', 'Submitted At'));
    foreach ($appointmentsList as $item) {
        fputcsv($output, array(
            isset($item['id']) ? $item['id'] : '', isset($item['full_name']) ? $item['full_name'] : '', isset($item['phone']) ? $item['phone'] : '',
            isset($item['email']) ? $item['email'] : '', isset($item['wedding_date']) ? $item['wedding_date'] : '', isset($item['service_needed']) ? $item['service_needed'] : '',
            isset($item['notes']) ? $item['notes'] : '', isset($item['status']) ? $item['status'] : '', isset($item['created_at']) ? $item['created_at'] : ''
        ));
    }
    fclose($output);
    exit;
}

// ── EDIT MODE ──
$edit_blog = null; $edit_testimonial = null; $edit_gallery = null; $edit_gown = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    if ($page == 'blog') $edit_blog = fetchOne("SELECT * FROM blog_posts WHERE id=?", 'i', array($edit_id));
    elseif ($page == 'testimonials') $edit_testimonial = fetchOne("SELECT * FROM testimonials WHERE id=?", 'i', array($edit_id));
    elseif ($page == 'gallery') $edit_gallery = fetchOne("SELECT * FROM gallery WHERE id=?", 'i', array($edit_id));
    elseif ($page == 'gowns') $edit_gown = fetchOne("SELECT * FROM gowns WHERE id=?", 'i', array($edit_id));
}

// ── SAFE VALUE HELPER ──
function sv($array, $key, $default = '') {
    if (isset($array[$key]) && $array[$key] !== null && $array[$key] !== '') return $array[$key];
    $aliases = array(
        'full_name' => array('customer_name', 'name', 'client_name', 'author'),
        'quote' => array('content', 'review', 'message', 'text'),
        'featured_image' => array('image', 'photo', 'picture', 'img'),
        'image_path' => array('image', 'photo', 'picture', 'img', 'url'),
        'service_needed' => array('service', 'service_type', 'type'),
        'submitted_at' => array('created_at', 'date', 'timestamp'),
        'wedding_date' => array('event_date', 'date'),
        'phone' => array('telephone', 'mobile', 'contact'),
        'price' => array('amount', 'cost', 'fee'),
        'size' => array('sizes', 'dimensions'),
        'color' => array('colors', 'colour'),
        'status' => array('state', 'active'),
        'category' => array('cat', 'type', 'group'),
        'description' => array('desc', 'details', 'info'),
        'excerpt' => array('summary', 'snippet', 'preview'),
        'author' => array('writer', 'posted_by', 'user'),
        'location' => array('place', 'city', 'address'),
        'rating' => array('stars', 'score'),
        'sort_order' => array('order', 'position', 'rank'),
        'title' => array('heading', 'subject', 'name'),
        'slug' => array('url', 'permalink', 'link'),
        'content' => array('body', 'text', 'article'),
        'views' => array('hits', 'visits', 'count'),
        'published_at' => array('created_at', 'date', 'posted_at')
    );
    if (isset($aliases[$key])) {
        foreach ($aliases[$key] as $alias) {
            if (isset($array[$alias]) && $array[$alias] !== null && $array[$alias] !== '') return $array[$alias];
        }
    }
    return $default;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Witad Bridal | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        :root {
            --primary: #F4A7B9; --primary-dk: #e08fa2; --secondary: #FFFFFF;
            --accent: #C9A84C; --accent-lt: #e8d5a3; --text: #3D3D3D;
            --text-lt: #6b6b6b; --bg: #FDF8F4; --bg-card: #FFFAF7;
            --border: #f0e0d6; --shadow: rgba(201,168,76,0.15);
            --admin-dark: #1a1020; --admin-darker: #120a14;
            --admin-sidebar: #2a1a25; --admin-sidebar-hover: #3d2535;
            --admin-card: #24182a; --admin-border: rgba(201,168,76,0.15);
            --admin-text: #e8e0e0; --admin-text-muted: #9a8a8a;
            --admin-success: #4ade80; --admin-warning: #fbbf24;
            --admin-danger: #f87171; --admin-info: #60a5fa;
            --admin-purple: #c084fc;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Poppins', sans-serif; color: var(--admin-text); background: var(--admin-darker); line-height: 1.6; overflow-x: hidden; }

        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--admin-dark) 0%, #2a1a25 50%, var(--admin-darker) 100%); position: relative; overflow: hidden; }
        .login-page::before { content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%); border-radius: 50%; }
        .login-page::after { content: ''; position: absolute; bottom: -30%; left: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(244,167,185,0.06) 0%, transparent 70%); border-radius: 50%; }
        .login-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 24px; padding: 48px 40px; width: 100%; max-width: 420px; position: relative; z-index: 1; box-shadow: 0 20px 60px rgba(0,0,0,0.4); }
        .login-logo { text-align: center; margin-bottom: 36px; }
        .login-logo .logo-name { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #fff; letter-spacing: 1px; }
        .login-logo .logo-tag { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 0.9rem; color: var(--accent); letter-spacing: 3px; }
        .login-logo .admin-badge { display: inline-block; margin-top: 12px; background: linear-gradient(135deg, var(--accent), #b8972e); color: #fff; font-size: 0.7rem; font-weight: 600; padding: 4px 16px; border-radius: 50px; letter-spacing: 2px; text-transform: uppercase; }
        .login-form .form-group { margin-bottom: 20px; }
        .login-form label { display: block; font-size: 0.82rem; font-weight: 500; color: var(--admin-text-muted); margin-bottom: 8px; }
        .login-form input { width: 100%; padding: 14px 18px; border: 1.5px solid var(--admin-border); border-radius: 12px; background: var(--admin-dark); font-family: 'Poppins', sans-serif; font-size: 0.9rem; color: var(--admin-text); transition: all 0.3s; outline: none; }
        .login-form input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        .login-btn { width: 100%; padding: 15px; background: linear-gradient(135deg, var(--accent), #b8972e); color: #fff; border: none; border-radius: 12px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; font-weight: 600; cursor: pointer; letter-spacing: 0.5px; transition: all 0.3s; margin-top: 8px; }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.4); }
        .login-error { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.3); color: var(--admin-danger); padding: 12px 16px; border-radius: 10px; font-size: 0.85rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }

        .dashboard { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--admin-sidebar); border-right: 1px solid var(--admin-border); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: transform 0.3s; overflow-y: auto; }
        .sidebar-header { padding: 28px 24px; border-bottom: 1px solid var(--admin-border); }
        .sidebar-header .logo-name { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fff; letter-spacing: 1px; }
        .sidebar-header .logo-tag { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 0.75rem; color: var(--accent); letter-spacing: 2px; }
        .sidebar-nav { flex: 1; padding: 20px 16px; }
        .nav-section { margin-bottom: 24px; }
        .nav-section-title { font-size: 0.65rem; font-weight: 600; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: 1.5px; padding: 0 12px; margin-bottom: 10px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; color: var(--admin-text-muted); font-size: 0.88rem; font-weight: 500; cursor: pointer; transition: all 0.3s; text-decoration: none; margin-bottom: 4px; border: none; background: none; width: 100%; text-align: left; font-family: 'Poppins', sans-serif; }
        .nav-item:hover, .nav-item.active { background: var(--admin-sidebar-hover); color: var(--admin-text); }
        .nav-item.active { background: linear-gradient(135deg, rgba(201,168,76,0.15), rgba(244,167,185,0.08)); color: var(--accent); border: 1px solid rgba(201,168,76,0.2); }
        .nav-item i { font-size: 1rem; width: 20px; text-align: center; }
        .nav-item .badge { margin-left: auto; background: var(--accent); color: #fff; font-size: 0.65rem; font-weight: 600; padding: 2px 8px; border-radius: 50px; }
        .sidebar-footer { padding: 20px 24px; border-top: 1px solid var(--admin-border); }
        .admin-profile { display: flex; align-items: center; gap: 12px; }
        .admin-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--primary)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.9rem; }
        .admin-info h4 { font-size: 0.85rem; color: #fff; font-weight: 600; }
        .admin-info span { font-size: 0.72rem; color: var(--admin-text-muted); }
        .logout-btn { display: flex; align-items: center; gap: 8px; color: var(--admin-text-muted); font-size: 0.82rem; text-decoration: none; margin-top: 12px; transition: color 0.3s; padding: 8px 0; }
        .logout-btn:hover { color: var(--admin-danger); }

        .main-content { flex: 1; margin-left: 260px; padding: 0; min-height: 100vh; }
        .top-bar { background: var(--admin-card); border-bottom: 1px solid var(--admin-border); padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .page-title h1 { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: #fff; font-weight: 600; }
        .page-title span { font-size: 0.8rem; color: var(--admin-text-muted); }
        .top-bar-actions { display: flex; align-items: center; gap: 16px; }
        .top-btn { background: var(--admin-dark); border: 1px solid var(--admin-border); color: var(--admin-text-muted); padding: 10px 18px; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.82rem; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .top-btn:hover { border-color: var(--accent); color: var(--accent); }
        .top-btn i { font-size: 0.9rem; }
        .search-box { display: flex; align-items: center; gap: 8px; background: var(--admin-dark); border: 1px solid var(--admin-border); border-radius: 10px; padding: 8px 14px; }
        .search-box input { background: none; border: none; color: var(--admin-text); font-family: 'Poppins', sans-serif; font-size: 0.85rem; outline: none; width: 200px; }
        .search-box input::placeholder { color: #5a4a5a; }
        .search-box button { background: none; border: none; color: var(--accent); cursor: pointer; }

        .content-area { padding: 32px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 16px; padding: 24px; position: relative; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.2); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; }
        .stat-card.total::before { background: linear-gradient(180deg, var(--accent), #b8972e); }
        .stat-card.pending::before { background: linear-gradient(180deg, var(--admin-warning), #d97706); }
        .stat-card.confirmed::before { background: linear-gradient(180deg, var(--admin-info), #3b82f6); }
        .stat-card.completed::before { background: linear-gradient(180deg, var(--admin-success), #22c55e); }
        .stat-card.today::before { background: linear-gradient(180deg, var(--primary), #e08fa2); }
        .stat-card.feedback::before { background: linear-gradient(180deg, var(--admin-purple), #a855f7); }
        .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; margin-bottom: 16px; }
        .stat-card.total .stat-icon { background: rgba(201,168,76,0.15); color: var(--accent); }
        .stat-card.pending .stat-icon { background: rgba(251,191,36,0.15); color: var(--admin-warning); }
        .stat-card.confirmed .stat-icon { background: rgba(96,165,250,0.15); color: var(--admin-info); }
        .stat-card.completed .stat-icon { background: rgba(74,222,128,0.15); color: var(--admin-success); }
        .stat-card.today .stat-icon { background: rgba(244,167,185,0.15); color: var(--primary); }
        .stat-card.feedback .stat-icon { background: rgba(192,132,252,0.15); color: var(--admin-purple); }
        .stat-value { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700; color: #fff; line-height: 1; margin-bottom: 6px; }
        .stat-label { font-size: 0.85rem; color: var(--admin-text-muted); margin-bottom: 12px; }
        .stat-trend { font-size: 0.78rem; display: flex; align-items: center; gap: 4px; }
        .stat-trend.up { color: var(--admin-success); }

        .chart-section { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 32px; }
        .chart-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 16px; padding: 24px; }
        .chart-card h3 { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #fff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .chart-card h3 i { color: var(--accent); font-size: 0.9rem; }
        .activity-chart { display: flex; align-items: flex-end; gap: 8px; height: 200px; padding-top: 20px; }
        .activity-bar { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .activity-bar-fill { width: 100%; background: linear-gradient(180deg, var(--accent), rgba(201,168,76,0.3)); border-radius: 6px 6px 0 0; min-height: 4px; transition: height 0.8s ease; }
        .activity-bar-fill:hover { background: linear-gradient(180deg, var(--primary), rgba(244,167,185,0.3)); }
        .activity-bar-label { font-size: 0.7rem; color: var(--admin-text-muted); }
        .activity-bar-value { font-size: 0.65rem; color: var(--accent); font-weight: 600; }
        .service-bars { display: flex; flex-direction: column; gap: 16px; }
        .service-bar-item { display: flex; flex-direction: column; gap: 8px; }
        .service-bar-header { display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; }
        .service-bar-header span:first-child { color: var(--admin-text); }
        .service-bar-header span:last-child { color: var(--accent); font-weight: 600; }
        .service-bar-track { height: 8px; background: var(--admin-dark); border-radius: 4px; overflow: hidden; }
        .service-bar-fill { height: 100%; border-radius: 4px; transition: width 0.8s ease; }
        .service-bar-fill.gold { background: linear-gradient(90deg, var(--accent), #e8d5a3); }
        .service-bar-fill.pink { background: linear-gradient(90deg, var(--primary), #e08fa2); }
        .service-bar-fill.blue { background: linear-gradient(90deg, var(--admin-info), #93c5fd); }
        .service-bar-fill.green { background: linear-gradient(90deg, var(--admin-success), #86efac); }
        .service-bar-fill.purple { background: linear-gradient(90deg, var(--admin-purple), #d8b4fe); }
        .service-bar-fill.orange { background: linear-gradient(90deg, var(--admin-warning), #fde68a); }

        .table-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 16px; overflow: hidden; margin-bottom: 32px; }
        .table-header { padding: 20px 24px; border-bottom: 1px solid var(--admin-border); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
        .table-header h3 { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #fff; display: flex; align-items: center; gap: 10px; }
        .table-header h3 i { color: var(--accent); }
        .table-header .count-badge { background: var(--accent); color: #fff; font-size: 0.7rem; font-weight: 600; padding: 2px 10px; border-radius: 50px; }
        .table-filters { display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-pill { padding: 6px 14px; border-radius: 50px; border: 1px solid var(--admin-border); background: transparent; color: var(--admin-text-muted); font-family: 'Poppins', sans-serif; font-size: 0.78rem; cursor: pointer; transition: all 0.3s; }
        .filter-pill:hover, .filter-pill.active { background: var(--accent); border-color: var(--accent); color: #fff; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th { background: var(--admin-dark); color: var(--admin-text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--admin-border); }
        .data-table tbody td { padding: 16px 20px; font-size: 0.85rem; color: var(--admin-text); border-bottom: 1px solid rgba(201,168,76,0.08); vertical-align: middle; }
        .data-table tbody tr:hover { background: rgba(201,168,76,0.03); }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .customer-cell { display: flex; align-items: center; gap: 12px; }
        .customer-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--primary)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.8rem; flex-shrink: 0; }
        .customer-info h4 { font-size: 0.88rem; color: #fff; font-weight: 500; margin-bottom: 2px; }
        .customer-info span { font-size: 0.75rem; color: var(--admin-text-muted); }
        .status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .status-badge.pending { background: rgba(251,191,36,0.12); color: var(--admin-warning); border: 1px solid rgba(251,191,36,0.2); }
        .status-badge.confirmed { background: rgba(96,165,250,0.12); color: var(--admin-info); border: 1px solid rgba(96,165,250,0.2); }
        .status-badge.completed { background: rgba(74,222,128,0.12); color: var(--admin-success); border: 1px solid rgba(74,222,128,0.2); }
        .status-badge.cancelled { background: rgba(248,113,113,0.12); color: var(--admin-danger); border: 1px solid rgba(248,113,113,0.2); }
        .status-badge.available { background: rgba(74,222,128,0.12); color: var(--admin-success); border: 1px solid rgba(74,222,128,0.2); }
        .status-badge.rented { background: rgba(96,165,250,0.12); color: var(--admin-info); border: 1px solid rgba(96,165,250,0.2); }
        .status-badge.sold { background: rgba(248,113,113,0.12); color: var(--admin-danger); border: 1px solid rgba(248,113,113,0.2); }
        .status-badge.maintenance { background: rgba(251,191,36,0.12); color: var(--admin-warning); border: 1px solid rgba(251,191,36,0.2); }
        .status-badge.published { background: rgba(74,222,128,0.12); color: var(--admin-success); border: 1px solid rgba(74,222,128,0.2); }
        .status-badge.draft { background: rgba(154,138,138,0.12); color: var(--admin-text-muted); border: 1px solid rgba(154,138,138,0.2); }
        .status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .service-tag { display: inline-block; background: rgba(201,168,76,0.1); color: var(--accent); font-size: 0.75rem; font-weight: 500; padding: 4px 12px; border-radius: 50px; border: 1px solid rgba(201,168,76,0.2); }
        .action-btns { display: flex; gap: 6px; }
        .action-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--admin-border); background: var(--admin-dark); color: var(--admin-text-muted); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; font-size: 0.8rem; text-decoration: none; }
        .action-btn:hover { border-color: var(--accent); color: var(--accent); background: rgba(201,168,76,0.1); }
        .action-btn.delete:hover { border-color: var(--admin-danger); color: var(--admin-danger); background: rgba(248,113,113,0.1); }
        .action-btn.edit:hover { border-color: var(--admin-info); color: var(--admin-info); background: rgba(96,165,250,0.1); }
        .status-select { background: var(--admin-dark); border: 1px solid var(--admin-border); color: var(--admin-text); padding: 6px 12px; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 0.8rem; cursor: pointer; outline: none; }
        .status-select:focus { border-color: var(--accent); }

        .form-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 16px; padding: 32px; margin-bottom: 32px; }
        .form-card h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: #fff; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }
        .form-card h3 i { color: var(--accent); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.82rem; font-weight: 500; color: var(--admin-text-muted); margin-bottom: 8px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 16px; border: 1.5px solid var(--admin-border); border-radius: 10px; background: var(--admin-dark); font-family: 'Poppins', sans-serif; font-size: 0.88rem; color: var(--admin-text); transition: all 0.3s; outline: none; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(201,168,76,0.1); }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .submit-btn { padding: 12px 28px; background: linear-gradient(135deg, var(--accent), #b8972e); color: #fff; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,168,76,0.4); }
        .cancel-btn { padding: 12px 28px; background: transparent; color: var(--admin-text-muted); border: 1px solid var(--admin-border); border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.9rem; cursor: pointer; transition: all 0.3s; margin-left: 10px; text-decoration: none; display: inline-block; }
        .cancel-btn:hover { border-color: var(--admin-danger); color: var(--admin-danger); }

        .file-upload-wrapper { position: relative; }
        .file-upload-input { display: none; }
        .file-upload-label { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border: 2px dashed var(--admin-border); border-radius: 10px; background: var(--admin-dark); cursor: pointer; transition: all 0.3s; color: var(--admin-text-muted); font-size: 0.88rem; }
        .file-upload-label:hover { border-color: var(--accent); color: var(--accent); }
        .file-upload-label i { font-size: 1.2rem; }
        .file-upload-preview { margin-top: 12px; border-radius: 10px; overflow: hidden; border: 1px solid var(--admin-border); max-width: 200px; display: none; }
        .file-upload-preview img { width: 100%; height: auto; display: block; }
        .file-upload-preview.show { display: block; }
        .file-upload-filename { margin-top: 8px; font-size: 0.8rem; color: var(--admin-text-muted); }
        .upload-or-divider { display: flex; align-items: center; gap: 12px; margin: 16px 0; color: var(--admin-text-muted); font-size: 0.8rem; }
        .upload-or-divider::before, .upload-or-divider::after { content: ''; flex: 1; height: 1px; background: var(--admin-border); }
        .upload-help { font-size: 0.75rem; color: var(--admin-text-muted); margin-top: 6px; }
        .upload-help i { color: var(--accent); margin-right: 4px; }

        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 32px; }
        .quick-action-card { background: var(--admin-card); border: 1px solid var(--admin-border); border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.3s; text-decoration: none; color: inherit; }
        .quick-action-card:hover { transform: translateY(-3px); border-color: var(--accent); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
        .quick-action-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .quick-action-icon.gold { background: rgba(201,168,76,0.15); color: var(--accent); }
        .quick-action-icon.pink { background: rgba(244,167,185,0.15); color: var(--primary); }
        .quick-action-icon.blue { background: rgba(96,165,250,0.15); color: var(--admin-info); }
        .quick-action-icon.green { background: rgba(74,222,128,0.15); color: var(--admin-success); }
        .quick-action-info h4 { font-size: 0.9rem; color: #fff; font-weight: 600; margin-bottom: 2px; }
        .quick-action-info span { font-size: 0.75rem; color: var(--admin-text-muted); }

        .gallery-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 20px; }
        .gallery-admin-item { background: var(--admin-dark); border: 1px solid var(--admin-border); border-radius: 12px; overflow: hidden; transition: all 0.3s; }
        .gallery-admin-item:hover { border-color: var(--accent); transform: translateY(-4px); }
        .gallery-admin-item img { width: 100%; height: 150px; object-fit: cover; }
        .gallery-admin-info { padding: 12px; }
        .gallery-admin-info h4 { font-size: 0.85rem; color: #fff; margin-bottom: 4px; }
        .gallery-admin-info span { font-size: 0.75rem; color: var(--admin-text-muted); }
        .gallery-admin-actions { display: flex; gap: 6px; padding: 0 12px 12px; }

        .blog-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .blog-admin-card { background: var(--admin-dark); border: 1px solid var(--admin-border); border-radius: 14px; overflow: hidden; transition: all 0.3s; }
        .blog-admin-card:hover { border-color: var(--accent); }
        .blog-admin-card img { width: 100%; height: 160px; object-fit: cover; }
        .blog-admin-body { padding: 16px; }
        .blog-admin-body h4 { font-size: 0.95rem; color: #fff; margin-bottom: 6px; }
        .blog-admin-body p { font-size: 0.8rem; color: var(--admin-text-muted); margin-bottom: 12px; line-height: 1.5; }
        .blog-admin-meta { display: flex; gap: 12px; font-size: 0.75rem; color: var(--admin-text-muted); margin-bottom: 12px; }
        .blog-admin-actions { display: flex; gap: 6px; }

        .testi-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .testi-admin-card { background: var(--admin-dark); border: 1px solid var(--admin-border); border-radius: 14px; padding: 20px; transition: all 0.3s; }
        .testi-admin-card:hover { border-color: var(--accent); }
        .testi-admin-card .stars { color: var(--accent); font-size: 0.9rem; margin-bottom: 10px; }
        .testi-admin-card p { font-family: 'Cormorant Garamond', serif; font-style: italic; font-size: 1rem; color: var(--admin-text); line-height: 1.7; margin-bottom: 16px; }
        .testi-admin-author { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
        .testi-admin-author .avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--primary)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 600; font-size: 0.8rem; }
        .testi-admin-author h5 { font-size: 0.85rem; color: #fff; }
        .testi-admin-author span { font-size: 0.75rem; color: var(--admin-text-muted); }

        .gown-admin-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; margin-top: 20px; }
        .gown-admin-card { background: var(--admin-dark); border: 1px solid var(--admin-border); border-radius: 14px; overflow: hidden; transition: all 0.3s; }
        .gown-admin-card:hover { border-color: var(--accent); transform: translateY(-4px); }
        .gown-admin-card img { width: 100%; height: 180px; object-fit: cover; }
        .gown-admin-body { padding: 16px; }
        .gown-admin-body h4 { font-size: 0.9rem; color: #fff; margin-bottom: 4px; }
        .gown-admin-body .price { font-size: 1.1rem; color: var(--accent); font-weight: 600; margin-bottom: 8px; }
        .gown-admin-body .meta { font-size: 0.75rem; color: var(--admin-text-muted); margin-bottom: 12px; }
        .gown-admin-actions { display: flex; gap: 6px; padding: 0 16px 16px; }

        .alert { padding: 14px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-size: 0.88rem; animation: slideIn 0.4s ease; }
        .alert.success { background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.2); color: var(--admin-success); }
        .alert.error { background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.2); color: var(--admin-danger); }
        .alert i { font-size: 1.1rem; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 3rem; color: var(--admin-border); margin-bottom: 16px; }
        .empty-state h4 { font-family: 'Playfair Display', serif; color: var(--admin-text); font-size: 1.1rem; margin-bottom: 8px; }
        .empty-state p { color: var(--admin-text-muted); font-size: 0.85rem; }

        .settings-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }

        .tabs { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 1px solid var(--admin-border); padding-bottom: 0; }
        .tab-btn { padding: 10px 20px; background: none; border: none; border-bottom: 2px solid transparent; color: var(--admin-text-muted); font-family: 'Poppins', sans-serif; font-size: 0.85rem; cursor: pointer; transition: all 0.3s; margin-bottom: -1px; }
        .tab-btn:hover { color: var(--admin-text); }
        .tab-btn.active { color: var(--accent); border-bottom-color: var(--accent); }

        .mobile-toggle { display: none; background: none; border: none; color: var(--admin-text); font-size: 1.3rem; cursor: pointer; padding: 8px; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99; }
        .sidebar-overlay.show { display: block; }

        @media (max-width: 1024px) {
            .chart-section { grid-template-columns: 1fr; }
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .mobile-toggle { display: block; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .data-table { display: block; overflow-x: auto; }
            .content-area { padding: 20px; }
            .top-bar { padding: 16px 20px; }
            .search-box input { width: 120px; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
            .login-card { padding: 32px 24px; margin: 16px; }
            .gallery-admin-grid, .blog-admin-grid, .testi-admin-grid, .gown-admin-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <div class="logo-name">Witad</div>
            <div class="logo-tag">Bridal Collection</div>
            <div class="admin-badge">Admin Portal</div>
        </div>
        <?php if (isset($login_error)): ?>
        <div class="login-error"><i class="fa fa-exclamation-circle"></i><?php echo $login_error; ?></div>
        <?php endif; ?>
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label><i class="fa fa-user" style="margin-right:6px;color:var(--accent);"></i>Username</label>
                <input type="text" name="username" placeholder="Enter username" required />
            </div>
            <div class="form-group">
                <label><i class="fa fa-lock" style="margin-right:6px;color:var(--accent);"></i>Password</label>
                <input type="password" name="password" placeholder="Enter password" required />
            </div>
            <button type="submit" name="login" class="login-btn"><i class="fa fa-sign-in-alt" style="margin-right:8px;"></i>Sign In to Dashboard</button>
        </form>
        <div style="text-align:center;margin-top:24px;padding-top:20px;border-top:1px solid var(--admin-border);">
            <a href="witad.php" style="color:var(--admin-text-muted);font-size:0.82rem;text-decoration:none;"><i class="fa fa-arrow-left" style="margin-right:6px;"></i>Back to Website</a>
        </div>
    </div>
</div>

<?php else: ?>
<div class="dashboard">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-name">Witad</div>
            <div class="logo-tag">Bridal Collection</div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="?page=dashboard" class="nav-item <?php echo $page=='dashboard'?'active':''; ?>"><i class="fa fa-th-large"></i>Dashboard</a>
                <a href="witad.php" class="nav-item" target="_blank"><i class="fa fa-globe"></i>View Website</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Management</div>
                <a href="?page=appointments" class="nav-item <?php echo $page=='appointments'?'active':''; ?>"><i class="fa fa-calendar-check"></i>Appointments<span class="badge"><?php echo $total_bookings; ?></span></a>
                <a href="?page=feedback" class="nav-item <?php echo $page=='feedback'?'active':''; ?>"><i class="fa fa-comments"></i>Feedback<span class="badge"><?php echo $total_feedback; ?></span></a>
                <a href="?page=blog" class="nav-item <?php echo $page=='blog'?'active':''; ?>"><i class="fa fa-newspaper"></i>Blog Posts</a>
                <a href="?page=testimonials" class="nav-item <?php echo $page=='testimonials'?'active':''; ?>"><i class="fa fa-star"></i>Testimonials</a>
                <a href="?page=gallery" class="nav-item <?php echo $page=='gallery'?'active':''; ?>"><i class="fa fa-images"></i>Gallery</a>
                <a href="?page=gowns" class="nav-item <?php echo $page=='gowns'?'active':''; ?>"><i class="fa fa-tshirt"></i>Gowns & Products</a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">Administration</div>
                <a href="?page=admins" class="nav-item <?php echo $page=='admins'?'active':''; ?>"><i class="fa fa-users-cog"></i>Admin Users</a>
                <a href="?page=settings" class="nav-item <?php echo $page=='settings'?'active':''; ?>"><i class="fa fa-cog"></i>Settings</a>
            </div>
        </nav>
        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div class="admin-info"><h4>Administrator</h4><span>Witad Bridal</span></div>
            </div>
            <a href="?logout=1" class="logout-btn"><i class="fa fa-sign-out-alt"></i>Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="top-bar">
            <div style="display:flex;align-items:center;gap:16px;">
                <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fa fa-bars"></i></button>
                <div class="page-title">
                    <h1><?php echo ucfirst($page); ?></h1>
                    <span>Welcome back, Admin</span>
                </div>
            </div>
            <div class="top-bar-actions">
                <?php if ($page == 'appointments' || $page == 'feedback'): ?>
                <form method="GET" action="" style="display:flex;align-items:center;gap:8px;">
                    <input type="hidden" name="page" value="<?php echo $page; ?>" />
                    <div class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>" />
                        <button type="submit"><i class="fa fa-arrow-right"></i></button>
                    </div>
                </form>
                <?php endif; ?>
                <?php if ($page == 'appointments'): ?>
                <a href="?page=appointments&export=csv" class="top-btn"><i class="fa fa-file-csv"></i>Export CSV</a>
                <?php endif; ?>
                <button class="top-btn" onclick="window.location.reload()"><i class="fa fa-sync-alt"></i>Refresh</button>
                <a href="witad.php" class="top-btn" target="_blank"><i class="fa fa-external-link-alt"></i>View Site</a>
            </div>
        </div>

        <div class="content-area">
            <?php if (isset($upload_error)): ?>
            <div class="alert error"><i class="fa fa-exclamation-circle"></i><?php echo htmlspecialchars($upload_error); ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['updated'])): ?>
            <div class="alert success"><i class="fa fa-check-circle"></i>Status updated successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
            <div class="alert success"><i class="fa fa-check-circle"></i>Item deleted successfully!</div>
            <?php endif; ?>
            <?php if (isset($_GET['saved'])): ?>
            <div class="alert success"><i class="fa fa-check-circle"></i>Saved successfully!</div>
            <?php endif; ?>

            <?php if ($page == 'dashboard'): ?>
            <div class="stats-grid">
                <div class="stat-card total"><div class="stat-icon"><i class="fa fa-calendar-check"></i></div><div class="stat-value"><?php echo $total_bookings; ?></div><div class="stat-label">Total Bookings</div><div class="stat-trend up"><i class="fa fa-arrow-up"></i><span>All time appointments</span></div></div>
                <div class="stat-card pending"><div class="stat-icon"><i class="fa fa-clock"></i></div><div class="stat-value"><?php echo $pending_count; ?></div><div class="stat-label">Pending</div><div class="stat-trend"><i class="fa fa-hourglass-half"></i><span>Awaiting action</span></div></div>
                <div class="stat-card confirmed"><div class="stat-icon"><i class="fa fa-check-circle"></i></div><div class="stat-value"><?php echo $confirmed_count; ?></div><div class="stat-label">Confirmed</div><div class="stat-trend up"><i class="fa fa-arrow-up"></i><span>Scheduled</span></div></div>
                <div class="stat-card completed"><div class="stat-icon"><i class="fa fa-star"></i></div><div class="stat-value"><?php echo $completed_count; ?></div><div class="stat-label">Completed</div><div class="stat-trend up"><i class="fa fa-arrow-up"></i><span>Finished</span></div></div>
                <div class="stat-card today"><div class="stat-icon"><i class="fa fa-calendar-day"></i></div><div class="stat-value"><?php echo $today_count; ?></div><div class="stat-label">Today</div><div class="stat-trend"><i class="fa fa-calendar"></i><span>New today</span></div></div>
                <div class="stat-card feedback"><div class="stat-icon"><i class="fa fa-comments"></i></div><div class="stat-value"><?php echo $total_feedback; ?></div><div class="stat-label">Total Feedback</div><div class="stat-trend"><i class="fa fa-inbox"></i><span>All messages</span></div></div>
            </div>

            <div class="chart-section">
                <div class="chart-card">
                    <h3><i class="fa fa-chart-bar"></i>Booking Activity (Monthly)</h3>
                    <div class="activity-chart">
                        <?php for ($i = 0; $i < 12; $i++): $height = max(($monthly_counts[$i] / $max_count) * 100, 4); ?>
                        <div class="activity-bar">
                            <div class="activity-bar-value"><?php echo $monthly_counts[$i]; ?></div>
                            <div class="activity-bar-fill" style="height:<?php echo $height; ?>%;"></div>
                            <div class="activity-bar-label"><?php echo $months[$i]; ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="chart-card">
                    <h3><i class="fa fa-pie-chart"></i>Services Breakdown</h3>
                    <div class="service-bars">
                        <?php $colors = array('gold','pink','blue','green','purple','orange'); $ci = 0; $maxs = !empty($service_counts) ? max($service_counts) : 1; if ($maxs == 0) $maxs = 1; foreach ($service_counts as $s => $c): $col = $colors[$ci % count($colors)]; $w = ($c / $maxs) * 100; ?>
                        <div class="service-bar-item">
                            <div class="service-bar-header"><span><?php echo htmlspecialchars($s); ?></span><span><?php echo $c; ?></span></div>
                            <div class="service-bar-track"><div class="service-bar-fill <?php echo $col; ?>" style="width:<?php echo $w; ?>%;"></div></div>
                        </div>
                        <?php $ci++; endforeach; if (empty($service_counts)): ?>
                        <div class="empty-state"><i class="fa fa-chart-pie"></i><h4>No Data Yet</h4><p>Service data will appear once bookings come in.</p></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="?page=appointments" class="quick-action-card"><div class="quick-action-icon gold"><i class="fa fa-calendar-check"></i></div><div class="quick-action-info"><h4>View Appointments</h4><span>Manage all bookings</span></div></a>
                <a href="?page=feedback" class="quick-action-card"><div class="quick-action-icon pink"><i class="fa fa-comments"></i></div><div class="quick-action-info"><h4>View Feedback</h4><span>Check customer messages</span></div></a>
                <a href="?page=blog" class="quick-action-card"><div class="quick-action-icon blue"><i class="fa fa-newspaper"></i></div><div class="quick-action-info"><h4>Manage Blog</h4><span>Add/edit blog posts</span></div></a>
                <a href="?page=gallery" class="quick-action-card"><div class="quick-action-icon green"><i class="fa fa-images"></i></div><div class="quick-action-info"><h4>Manage Gallery</h4><span>Update photo gallery</span></div></a>
            </div>

            <div class="table-card">
                <div class="table-header"><h3><i class="fa fa-calendar-check"></i>Recent Appointments</h3><a href="?page=appointments" class="top-btn" style="padding:6px 14px;font-size:0.75rem;">View All</a></div>
                <?php if (!empty($appointmentsList)): ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr><th>Customer</th><th>Service</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            <?php $recent = array_slice($appointmentsList, 0, 5); foreach ($recent as $item): $status = isset($item['status']) ? strtolower($item['status']) : 'pending'; ?>
                            <tr>
                                <td><div class="customer-cell"><div class="customer-avatar"><?php echo strtoupper(substr($item['full_name'],0,1)); ?></div><div class="customer-info"><h4><?php echo htmlspecialchars($item['full_name']); ?></h4><span><?php echo htmlspecialchars($item['phone']); ?></span></div></div></td>
                                <td><span class="service-tag"><?php echo htmlspecialchars(sv($item, 'service_needed', 'General')); ?></span></td>
                                <td><span class="status-badge <?php echo $status; ?>"><?php echo ucfirst($status); ?></span></td>
                                <td><span style="color:var(--admin-text-muted);font-size:0.8rem;"><?php echo $item['created_at']; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-calendar-times"></i><h4>No Appointments Yet</h4></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'appointments'): ?>
            <div class="table-card" id="appointments">
                <div class="table-header"><h3><i class="fa fa-calendar-check"></i>Appointments</h3><span class="count-badge"><?php echo $total_bookings; ?> total</span></div>
                <?php if (!empty($appointmentsList)): ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr><th>Customer</th><th>Contact</th><th>Service</th><th>Wedding Date</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($appointmentsList as $item): $status = isset($item['status']) ? strtolower($item['status']) : 'pending'; $initials = strtoupper(substr($item['full_name'],0,1) . substr(strrchr($item['full_name'],' '),1,1)); ?>
                            <tr>
                                <td><div class="customer-cell"><div class="customer-avatar"><?php echo $initials; ?></div><div class="customer-info"><h4><?php echo htmlspecialchars($item['full_name']); ?></h4><span>ID: #<?php echo $item['id']; ?></span></div></div></td>
                                <td><div style="font-size:0.82rem;"><div style="color:var(--admin-text);"><i class="fa fa-phone" style="color:var(--accent);margin-right:6px;font-size:0.7rem;"></i><?php echo htmlspecialchars($item['phone']); ?></div><?php if (!empty($item['email'])): ?><div style="color:var(--admin-text-muted);margin-top:4px;"><i class="fa fa-envelope" style="color:var(--primary);margin-right:6px;font-size:0.7rem;"></i><?php echo htmlspecialchars($item['email']); ?></div><?php endif; ?></div></td>
                                <td><span class="service-tag"><?php echo htmlspecialchars(sv($item, 'service_needed', 'General')); ?></span></td>
                                <td><?php echo !empty($item['wedding_date']) ? htmlspecialchars($item['wedding_date']) : '<span style="color:var(--admin-text-muted);">Not set</span>'; ?></td>
                                <td><span style="color:var(--admin-text-muted);font-size:0.8rem;"><?php echo $item['created_at']; ?></span></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>" />
                                        <input type="hidden" name="type" value="appointment" />
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $status=='pending'?'selected':''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $status=='confirmed'?'selected':''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $status=='completed'?'selected':''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $status=='cancelled'?'selected':''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1" />
                                    </form>
                                </td>
                                <td><div class="action-btns"><a href="?page=appointments&delete=<?php echo $item['id']; ?>&type=appointments" class="action-btn delete" onclick="return confirm('Delete this appointment?')" title="Delete"><i class="fa fa-trash-alt"></i></a></div></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-calendar-times"></i><h4>No Appointments Yet</h4><p>Bookings will appear here once customers submit appointments.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'feedback'): ?>
            <div class="table-card" id="feedback">
                <div class="table-header"><h3><i class="fa fa-comments"></i>Feedback & Messages</h3><span class="count-badge"><?php echo $total_feedback; ?> total</span></div>
                <?php if (!empty($feedbackList)): ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr><th>Customer</th><th>Contact</th><th>Service</th><th>Notes</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($feedbackList as $item): $status = isset($item['status']) ? strtolower($item['status']) : 'pending'; $initials = strtoupper(substr($item['full_name'],0,1) . substr(strrchr($item['full_name'],' '),1,1)); ?>
                            <tr>
                                <td><div class="customer-cell"><div class="customer-avatar"><?php echo $initials; ?></div><div class="customer-info"><h4><?php echo htmlspecialchars($item['full_name']); ?></h4><span>ID: #<?php echo $item['id']; ?></span></div></div></td>
                                <td><div style="font-size:0.82rem;"><div style="color:var(--admin-text);"><i class="fa fa-phone" style="color:var(--accent);margin-right:6px;font-size:0.7rem;"></i><?php echo htmlspecialchars($item['phone']); ?></div><?php if (!empty($item['email'])): ?><div style="color:var(--admin-text-muted);margin-top:4px;"><i class="fa fa-envelope" style="color:var(--primary);margin-right:6px;font-size:0.7rem;"></i><?php echo htmlspecialchars($item['email']); ?></div><?php endif; ?></div></td>
                                <td><span class="service-tag"><?php echo htmlspecialchars(sv($item, 'service_needed', 'General')); ?></span></td>
                                <td><span style="color:var(--admin-text-muted);font-size:0.8rem;max-width:200px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php $sv_notes = sv($item, 'notes'); echo !empty($sv_notes) ? htmlspecialchars($sv_notes) : '<em>No notes</em>'; ?></span></td>
                                <td><span style="color:var(--admin-text-muted);font-size:0.8rem;"><?php echo $item['submitted_at']; ?></span></td>
                                <td>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>" />
                                        <input type="hidden" name="type" value="feedback" />
                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $status=='pending'?'selected':''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $status=='confirmed'?'selected':''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $status=='completed'?'selected':''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $status=='cancelled'?'selected':''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1" />
                                    </form>
                                </td>
                                <td><div class="action-btns"><a href="?page=feedback&delete=<?php echo $item['id']; ?>&type=feedback" class="action-btn delete" onclick="return confirm('Delete this feedback?')" title="Delete"><i class="fa fa-trash-alt"></i></a></div></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-inbox"></i><h4>No Feedback Yet</h4><p>Customer messages will appear here once submitted.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'blog'): ?>
            <div class="form-card">
                <h3><i class="fa fa-<?php echo $edit_blog ? 'edit' : 'plus'; ?>"></i><?php echo $edit_blog ? 'Edit Blog Post' : 'Add New Blog Post'; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_blog ? $edit_blog['id'] : 0; ?>" />
                    <div class="form-row">
                        <div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['title']) : ''; ?>" required /></div>
                        <div class="form-group"><label>Slug (URL-friendly)</label><input type="text" name="slug" value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['slug']) : ''; ?>" placeholder="my-blog-post" required /></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Category</label><input type="text" name="category" value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['category']) : ''; ?>" placeholder="Bridal Fashion Trends" /></div>
                        <div class="form-group"><label>Author</label><input type="text" name="author" value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['author']) : 'Witad Team'; ?>" /></div>
                    </div>
                    <div class="form-group"><label>Excerpt (Short Description)</label><textarea name="excerpt" rows="2" placeholder="Brief summary..."><?php echo $edit_blog ? htmlspecialchars($edit_blog['excerpt']) : ''; ?></textarea></div>
                    <div class="form-group"><label>Content</label><textarea name="content" rows="6" placeholder="Full blog content..."><?php echo $edit_blog ? htmlspecialchars($edit_blog['content']) : ''; ?></textarea></div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Featured Image</label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="featured_image_file" id="featured_image_file" class="file-upload-input" accept="image/*" onchange="previewImage(this, 'blogPreview')" />
                                <label for="featured_image_file" class="file-upload-label"><i class="fa fa-cloud-upload-alt"></i><span>Click to upload image</span></label>
                                <div class="file-upload-preview" id="blogPreview"><img src="" alt="Preview" /></div>
                                <div class="file-upload-filename" id="blogFilename"></div>
                            </div>
                            <div class="upload-or-divider"><span>OR</span></div>
                            <input type="text" name="featured_image" value="<?php echo $edit_blog ? htmlspecialchars($edit_blog['featured_image']) : ''; ?>" placeholder="Enter image URL or path" />
                            <div class="upload-help"><i class="fa fa-info-circle"></i>Max 5MB. JPG, PNG, GIF, WEBP only.</div>
                            <?php if ($edit_blog && !empty($edit_blog['featured_image'])): ?>
                            <div style="margin-top:10px;"><img src="<?php echo htmlspecialchars($edit_blog['featured_image']); ?>" style="max-width:150px;border-radius:8px;border:1px solid var(--admin-border);" alt="Current" /></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group"><label>Status</label>
                            <select name="status">
                                <option value="published" <?php echo ($edit_blog && $edit_blog['status']=='published')?'selected':''; ?>>Published</option>
                                <option value="draft" <?php echo ($edit_blog && $edit_blog['status']=='draft')?'selected':''; ?>>Draft</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="save_blog" class="submit-btn"><i class="fa fa-save" style="margin-right:8px;"></i><?php echo $edit_blog ? 'Update Post' : 'Add Post'; ?></button>
                    <?php if ($edit_blog): ?><a href="?page=blog" class="cancel-btn">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header"><h3><i class="fa fa-newspaper"></i>All Blog Posts</h3><span class="count-badge"><?php echo count($blogPosts); ?> posts</span></div>
                <?php if (!empty($blogPosts)): ?>
                <div class="blog-admin-grid">
                    <?php foreach ($blogPosts as $post): ?>
                    <div class="blog-admin-card">
                        <img src="<?php $sv_img = sv($post, 'featured_image'); echo !empty($sv_img) ? htmlspecialchars($sv_img) : 'https://via.placeholder.com/400x160?text=No+Image'; ?>" alt="" />
                        <div class="blog-admin-body">
                            <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                            <p><?php echo htmlspecialchars(substr($post['excerpt'] ?: $post['content'], 0, 80)) . '...'; ?></p>
                            <div class="blog-admin-meta"><span><i class="fa fa-user" style="color:var(--accent);margin-right:4px;"></i><?php echo htmlspecialchars(sv($post, 'author', 'Witad Team')); ?></span><span><i class="fa fa-calendar" style="color:var(--accent);margin-right:4px;"></i><?php $sv_date = sv($post, 'created_at'); echo !empty($sv_date) ? date('M d, Y', strtotime($sv_date)) : ''; ?></span></div>
                            <span class="status-badge <?php echo $post['status']; ?>" style="margin-bottom:10px;"><?php echo ucfirst(sv($post, 'status', 'draft')); ?></span>
                            <div class="blog-admin-actions">
                                <a href="?page=blog&edit=<?php echo $post['id']; ?>" class="action-btn edit" title="Edit"><i class="fa fa-edit"></i></a>
                                <a href="?page=blog&delete=<?php echo $post['id']; ?>&type=blog_posts" class="action-btn delete" onclick="return confirm('Delete this post?')" title="Delete"><i class="fa fa-trash-alt"></i></a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-newspaper"></i><h4>No Blog Posts Yet</h4><p>Add your first blog post above.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'testimonials'): ?>
            <div class="form-card">
                <h3><i class="fa fa-<?php echo $edit_testimonial ? 'edit' : 'plus'; ?>"></i><?php echo $edit_testimonial ? 'Edit Testimonial' : 'Add New Testimonial'; ?></h3>
                <form method="POST" action="">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_testimonial ? $edit_testimonial['id'] : 0; ?>" />
                    <div class="form-row">
                        <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" value="<?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['full_name']) : ''; ?>" required /></div>
                        <div class="form-group"><label>Location</label><input type="text" name="location" value="<?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['location']) : ''; ?>" placeholder="Kabwohe-Sheema" /></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Wedding Date</label><input type="date" name="wedding_date" value="<?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['wedding_date']) : ''; ?>" /></div>
                        <div class="form-group"><label>Rating (1-5)</label>
                            <select name="rating">
                                <?php for ($r=1; $r<=5; $r++): ?>
                                <option value="<?php echo $r; ?>" <?php echo ($edit_testimonial && $edit_testimonial['rating']==$r)?'selected':''; ?>><?php echo $r; ?> Star<?php echo $r>1?'s':''; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group"><label>Quote / Review *</label><textarea name="quote" rows="4" placeholder="What did the bride say about her experience..." required><?php echo $edit_testimonial ? htmlspecialchars($edit_testimonial['quote']) : ''; ?></textarea></div>
                    <button type="submit" name="save_testimonial" class="submit-btn"><i class="fa fa-save" style="margin-right:8px;"></i><?php echo $edit_testimonial ? 'Update' : 'Add'; ?> Testimonial</button>
                    <?php if ($edit_testimonial): ?><a href="?page=testimonials" class="cancel-btn">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header"><h3><i class="fa fa-star"></i>All Testimonials</h3><span class="count-badge"><?php echo count($testimonials); ?> reviews</span></div>
                <?php if (!empty($testimonials)): ?>
                <div class="testi-admin-grid">
                    <?php foreach ($testimonials as $t): ?>
                    <div class="testi-admin-card">
                        <div class="stars"><?php echo str_repeat('★', $t['rating']); ?></div>
                        <p>"<?php echo htmlspecialchars(sv($t, 'quote', 'No review provided.')); ?>"</p>
                        <div class="testi-admin-author">
                            <div class="avatar"><?php echo strtoupper(substr($t['full_name'],0,1)); ?></div>
                            <div><h5><?php echo htmlspecialchars(sv($t, 'full_name', 'Anonymous')); ?></h5><span><?php echo htmlspecialchars(sv($t, 'location', '')); ?> <?php $sv_wd = sv($t, 'wedding_date'); if(!empty($sv_wd)) echo '• ' . $sv_wd; ?></span></div>
                        </div>
                        <div class="action-btns">
                            <a href="?page=testimonials&edit=<?php echo $t['id']; ?>" class="action-btn edit" title="Edit"><i class="fa fa-edit"></i></a>
                            <a href="?page=testimonials&delete=<?php echo $t['id']; ?>&type=testimonials" class="action-btn delete" onclick="return confirm('Delete this testimonial?')" title="Delete"><i class="fa fa-trash-alt"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-star"></i><h4>No Testimonials Yet</h4><p>Add your first bride review above.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'gallery'): ?>
            <div class="form-card">
                <h3><i class="fa fa-<?php echo $edit_gallery ? 'edit' : 'plus'; ?>"></i><?php echo $edit_gallery ? 'Edit Gallery Item' : 'Add New Gallery Image'; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_gallery ? $edit_gallery['id'] : 0; ?>" />
                    <div class="form-row">
                        <div class="form-group"><label>Title</label><input type="text" name="title" value="<?php echo $edit_gallery ? htmlspecialchars($edit_gallery['title']) : ''; ?>" /></div>
                        <div class="form-group"><label>Category</label>
                            <select name="category">
                                <option value="gowns" <?php echo ($edit_gallery && $edit_gallery['category']=='gowns')?'selected':''; ?>>Mushanana</option>
                                <option value="bridesmaids" <?php echo ($edit_gallery && $edit_gallery['category']=='bridesmaids')?'selected':''; ?>>Bridesmaids</option>
                                <option value="accessories" <?php echo ($edit_gallery && $edit_gallery['category']=='accessories')?'selected':''; ?>>Accessories</option>
                                <option value="brides" <?php echo ($edit_gallery && $edit_gallery['category']=='brides')?'selected':''; ?>>Happy Brides</option>
                                <option value="collections" <?php echo ($edit_gallery && $edit_gallery['category']=='collections')?'selected':''; ?>>Collections</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Image *</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="image_file" id="gallery_image_file" class="file-upload-input" accept="image/*" onchange="previewImage(this, 'galleryPreview')" />
                            <label for="gallery_image_file" class="file-upload-label"><i class="fa fa-cloud-upload-alt"></i><span>Click to upload image from your computer</span></label>
                            <div class="file-upload-preview" id="galleryPreview"><img src="" alt="Preview" /></div>
                            <div class="file-upload-filename" id="galleryFilename"></div>
                        </div>
                        <div class="upload-or-divider"><span>OR enter URL</span></div>
                        <input type="text" name="image_path" value="<?php echo $edit_gallery ? htmlspecialchars($edit_gallery['image_path']) : ''; ?>" placeholder="https://images.unsplash.com/... or local path" />
                        <div class="upload-help"><i class="fa fa-info-circle"></i>Max 5MB. JPG, PNG, GIF, WEBP only. Upload from your computer or enter a URL.</div>
                        <?php if ($edit_gallery && !empty($edit_gallery['image_path'])): ?>
                        <div style="margin-top:10px;"><img src="<?php echo htmlspecialchars($edit_gallery['image_path']); ?>" style="max-width:150px;border-radius:8px;border:1px solid var(--admin-border);" alt="Current" /></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group"><label>Description</label><textarea name="description" rows="2" placeholder="Brief description..."><?php echo $edit_gallery ? htmlspecialchars($edit_gallery['description']) : ''; ?></textarea></div>
                    <div class="form-group"><label>Sort Order</label><input type="number" name="sort_order" value="<?php echo $edit_gallery ? $edit_gallery['sort_order'] : 0; ?>" min="0" /></div>
                    <button type="submit" name="save_gallery" class="submit-btn"><i class="fa fa-save" style="margin-right:8px;"></i><?php echo $edit_gallery ? 'Update' : 'Add'; ?> Image</button>
                    <?php if ($edit_gallery): ?><a href="?page=gallery" class="cancel-btn">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header"><h3><i class="fa fa-images"></i>Gallery Items</h3><span class="count-badge"><?php echo count($galleryItems); ?> images</span></div>
                <?php if (!empty($galleryItems)): ?>
                <div class="gallery-admin-grid">
                    <?php foreach ($galleryItems as $g): ?>
                    <div class="gallery-admin-item">
                        <img src="<?php $sv_img = sv($g, 'image_path'); echo !empty($sv_img) ? htmlspecialchars($sv_img) : 'https://via.placeholder.com/200x150?text=No+Image'; ?>" alt="<?php echo htmlspecialchars(sv($g, 'title', 'Untitled')); ?>" />
                        <div class="gallery-admin-info"><h4><?php echo htmlspecialchars($g['title'] ?: 'Untitled'); ?></h4><span class="service-tag"><?php echo htmlspecialchars($g['category']); ?></span></div>
                        <div class="gallery-admin-actions">
                            <a href="?page=gallery&edit=<?php echo $g['id']; ?>" class="action-btn edit" title="Edit"><i class="fa fa-edit"></i></a>
                            <a href="?page=gallery&delete=<?php echo $g['id']; ?>&type=gallery" class="action-btn delete" onclick="return confirm('Delete this image?')" title="Delete"><i class="fa fa-trash-alt"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-images"></i><h4>No Gallery Images Yet</h4><p>Add your first gallery image above.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'gowns'): ?>
            <div class="form-card">
                <h3><i class="fa fa-<?php echo $edit_gown ? 'edit' : 'plus'; ?>"></i><?php echo $edit_gown ? 'Edit Gown/Product' : 'Add New Gown/Product'; ?></h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" value="<?php echo $edit_gown ? $edit_gown['id'] : 0; ?>" />
                    <div class="form-row">
                        <div class="form-group"><label>Name *</label><input type="text" name="name" value="<?php echo $edit_gown ? htmlspecialchars($edit_gown['name']) : ''; ?>" required /></div>
                        <div class="form-group"><label>Price (UGX)</label><input type="number" name="price" value="<?php echo $edit_gown ? $edit_gown['price'] : ''; ?>" placeholder="500000" /></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Category</label>
                            <select name="category">
                                <option value="wedding_gown" <?php echo ($edit_gown && $edit_gown['category']=='wedding_gown')?'selected':''; ?>>Wedding Gown</option>
                                <option value="bridesmaid" <?php echo ($edit_gown && $edit_gown['category']=='bridesmaid')?'selected':''; ?>>Bridesmaid Dress</option>
                                <option value="accessory" <?php echo ($edit_gown && $edit_gown['category']=='accessory')?'selected':''; ?>>Accessory</option>
                                <option value="veil" <?php echo ($edit_gown && $edit_gown['category']=='veil')?'selected':''; ?>>Veil</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Size</label><input type="text" name="size" value="<?php echo $edit_gown ? htmlspecialchars($edit_gown['size']) : ''; ?>" placeholder="S, M, L, XL or custom" /></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Color</label><input type="text" name="color" value="<?php echo $edit_gown ? htmlspecialchars($edit_gown['color']) : ''; ?>" placeholder="Ivory, White, Blush..." /></div>
                        <div class="form-group"><label>Status</label>
                            <select name="status">
                                <option value="available" <?php echo ($edit_gown && $edit_gown['status']=='available')?'selected':''; ?>>Available</option>
                                <option value="rented" <?php echo ($edit_gown && $edit_gown['status']=='rented')?'selected':''; ?>>Rented</option>
                                <option value="sold" <?php echo ($edit_gown && $edit_gown['status']=='sold')?'selected':''; ?>>Sold</option>
                                <option value="maintenance" <?php echo ($edit_gown && $edit_gown['status']=='maintenance')?'selected':''; ?>>Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="image_file" id="gown_image_file" class="file-upload-input" accept="image/*" onchange="previewImage(this, 'gownPreview')" />
                            <label for="gown_image_file" class="file-upload-label"><i class="fa fa-cloud-upload-alt"></i><span>Click to upload image from your computer</span></label>
                            <div class="file-upload-preview" id="gownPreview"><img src="" alt="Preview" /></div>
                            <div class="file-upload-filename" id="gownFilename"></div>
                        </div>
                        <div class="upload-or-divider"><span>OR enter URL</span></div>
                        <input type="text" name="image_path" value="<?php echo $edit_gown ? htmlspecialchars($edit_gown['image_path']) : ''; ?>" placeholder="Image path or URL" />
                        <div class="upload-help"><i class="fa fa-info-circle"></i>Max 5MB. JPG, PNG, GIF, WEBP only. Upload from your computer or enter a URL.</div>
                        <?php if ($edit_gown && !empty($edit_gown['image_path'])): ?>
                        <div style="margin-top:10px;"><img src="<?php echo htmlspecialchars($edit_gown['image_path']); ?>" style="max-width:150px;border-radius:8px;border:1px solid var(--admin-border);" alt="Current" /></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group"><label>Description</label><textarea name="description" rows="3" placeholder="Product description..."><?php echo $edit_gown ? htmlspecialchars($edit_gown['description']) : ''; ?></textarea></div>
                    <button type="submit" name="save_gown" class="submit-btn"><i class="fa fa-save" style="margin-right:8px;"></i><?php echo $edit_gown ? 'Update' : 'Add'; ?> Product</button>
                    <?php if ($edit_gown): ?><a href="?page=gowns" class="cancel-btn">Cancel</a><?php endif; ?>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header"><h3><i class="fa fa-tshirt"></i>All Gowns & Products</h3><span class="count-badge"><?php echo count($gowns); ?> items</span></div>
                <?php if (!empty($gowns)): ?>
                <div class="gown-admin-grid">
                    <?php foreach ($gowns as $g): ?>
                    <div class="gown-admin-card">
                        <img src="<?php $sv_img = sv($g, 'image_path'); echo !empty($sv_img) ? htmlspecialchars($sv_img) : 'https://via.placeholder.com/220x180?text=No+Image'; ?>" alt="" />
                        <div class="gown-admin-body">
                            <h4><?php echo htmlspecialchars($g['name']); ?></h4>
                            <div class="price">UGX <?php echo number_format($g['price'] ?: 0); ?></div>
                            <div class="meta"><?php echo htmlspecialchars($g['category']); ?> • Size: <?php echo htmlspecialchars($g['size'] ?: 'N/A'); ?> • <?php echo htmlspecialchars($g['color'] ?: 'N/A'); ?></div>
                            <span class="status-badge <?php echo $g['status']; ?>" style="margin-bottom:8px;"><?php echo ucfirst(sv($g, 'status', 'available')); ?></span>
                        </div>
                        <div class="gown-admin-actions">
                            <a href="?page=gowns&edit=<?php echo $g['id']; ?>" class="action-btn edit" title="Edit"><i class="fa fa-edit"></i></a>
                            <a href="?page=gowns&delete=<?php echo $g['id']; ?>&type=gowns" class="action-btn delete" onclick="return confirm('Delete this product?')" title="Delete"><i class="fa fa-trash-alt"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-tshirt"></i><h4>No Products Yet</h4><p>Add your first gown or product above.</p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'admins'): ?>
            <div class="form-card">
                <h3><i class="fa fa-user-plus"></i>Add New Admin User</h3>
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group"><label>Username *</label><input type="text" name="username" required /></div>
                        <div class="form-group"><label>Password *</label><input type="password" name="password" required /></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" /></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" /></div>
                    </div>
                    <div class="form-group"><label>Role</label>
                        <select name="role">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <button type="submit" name="save_admin" class="submit-btn"><i class="fa fa-user-plus" style="margin-right:8px;"></i>Add Admin</button>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header"><h3><i class="fa fa-users-cog"></i>Admin Users</h3><span class="count-badge"><?php echo count($admins); ?> users</span></div>
                <?php if (!empty($admins)): ?>
                <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr><th>Username</th><th>Full Name</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($admins as $a): ?>
                            <tr>
                                <td><strong style="color:#fff;"><?php echo htmlspecialchars($a['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($a['full_name'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($a['email'] ?: '-'); ?></td>
                                <td><span class="service-tag"><?php echo ucfirst($a['role']); ?></span></td>
                                <td><span style="color:var(--admin-text-muted);font-size:0.8rem;"><?php echo $a['created_at']; ?></span></td>
                                <td><div class="action-btns"><a href="?page=admins&delete=<?php echo $a['id']; ?>&type=admins" class="action-btn delete" onclick="return confirm('Delete this admin user?')" title="Delete"><i class="fa fa-trash-alt"></i></a></div></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state"><i class="fa fa-users"></i><h4>No Admin Users</h4></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($page == 'settings'): ?>
            <div class="form-card">
                <h3><i class="fa fa-cog"></i>Site Settings</h3>
                <form method="POST" action="">
                    <div class="settings-grid">
                        <div>
                            <div class="form-group"><label>Site Name</label><input type="text" name="site_name" value="<?php echo htmlspecialchars((isset($settings['site_name']) ? $settings['site_name'] : 'Witad Bridal Collection')); ?>" /></div>
                            <div class="form-group"><label>Site Email</label><input type="email" name="site_email" value="<?php echo htmlspecialchars((isset($settings['site_email']) ? $settings['site_email'] : 'info@witadbridal.com')); ?>" /></div>
                            <div class="form-group"><label>Phone Number</label><input type="text" name="phone" value="<?php echo htmlspecialchars((isset($settings['phone']) ? $settings['phone'] : '+256 750 900 134')); ?>" /></div>
                        </div>
                        <div>
                            <div class="form-group"><label>Address</label><textarea name="address" rows="3"><?php echo htmlspecialchars((isset($settings['address']) ? $settings['address'] : "Witad Bridal Collection
Kabwohe-Sheema, Uganda")); ?></textarea></div>
                            <div class="form-group"><label>Working Hours</label><textarea name="working_hours" rows="3"><?php echo htmlspecialchars((isset($settings['working_hours']) ? $settings['working_hours'] : "Mon-Fri: 8AM - 7PM
Sat: 9AM - 6PM
Sun: By Appointment")); ?></textarea></div>
                        </div>
                        <div>
                            <div class="form-group"><label>Facebook URL</label><input type="text" name="facebook" value="<?php echo htmlspecialchars((isset($settings['facebook']) ? $settings['facebook'] : '')); ?>" placeholder="https://facebook.com/..." /></div>
                            <div class="form-group"><label>Instagram URL</label><input type="text" name="instagram" value="<?php echo htmlspecialchars((isset($settings['instagram']) ? $settings['instagram'] : '')); ?>" placeholder="https://instagram.com/..." /></div>
                            <div class="form-group"><label>WhatsApp Number</label><input type="text" name="whatsapp" value="<?php echo htmlspecialchars((isset($settings['whatsapp']) ? $settings['whatsapp'] : '+256XXXXXXXXX')); ?>" /></div>
                        </div>
                    </div>
                    <button type="submit" name="save_settings" class="submit-btn"><i class="fa fa-save" style="margin-right:8px;"></i>Save Settings</button>
                </form>
            </div>
            <?php endif; ?>

            <div style="text-align:center;padding:24px 0;color:var(--admin-text-muted);font-size:0.8rem;border-top:1px solid var(--admin-border);margin-top:16px;">
                <p>&copy; 2025 Witad Bridal Collection. Admin Dashboard. All rights reserved.</p>
            </div>
        </div>
    </main>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    function previewImage(input, previewId) {
        var preview = document.getElementById(previewId);
        var filenameDiv = document.getElementById(previewId.replace('Preview', 'Filename'));
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.classList.add('show');
            };
            reader.readAsDataURL(input.files[0]);
            if (filenameDiv) {
                filenameDiv.textContent = 'Selected: ' + input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB)';
            }
        }
    }
</script>
<?php endif; ?>
</body>
</html>