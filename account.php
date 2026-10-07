<?php
$pageTitle = 'My Account';
$pageDesc = 'Manage your Witad Bridal Collection account, view orders, appointments, and wishlist.';
$canonical = 'https://witadbridal.com/account';
require_once 'header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=account.php');
    exit;
}

$customerId = $_SESSION['customer_id'];

// Get customer details
$customer = fetchOne("SELECT * FROM customers WHERE id = ?", "i", array($customerId));

// Get orders
$orders = fetchAll("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5", "i", array($customerId));

// Get appointments
$appointments = fetchAll("SELECT * FROM bookings WHERE customer_id = ? ORDER BY booking_date DESC LIMIT 5", "i", array($customerId));

// Get wishlist count
$wishlistCount = fetchOne("SELECT COUNT(*) as c FROM wishlist WHERE customer_id = ?", "i", array($customerId));
$wishlistTotal = $wishlistCount ? $wishlistCount['c'] : 0;

// Handle profile update
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCsrf($_POST['csrf_token'])) {
        $message = 'Invalid request.';
        $messageType = 'error';
    } else {
        $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $address = isset($_POST['address']) ? trim($_POST['address']) : '';
        $weddingDate = isset($_POST['wedding_date']) ? $_POST['wedding_date'] : '';

        query("UPDATE customers SET full_name = ?, phone = ?, address = ?, wedding_date = ? WHERE id = ?", "ssssi", array($fullName, $phone, $address, $weddingDate, $customerId));

        $_SESSION['customer_name'] = $fullName;
        $message = 'Profile updated successfully!';
        $messageType = 'success';

        // Refresh customer data
        $customer = fetchOne("SELECT * FROM customers WHERE id = ?", "i", array($customerId));
    }
}
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Welcome Back</span>
      <h1>My Account</h1>
      <p class="page-sub">Manage your profile, orders, appointments, and more.</p>
    </div>
  </div>
</section>

<!-- ACCOUNT DASHBOARD -->
<section class="section">
  <div class="container">
    <div class="account-layout">
      <!-- SIDEBAR -->
      <aside class="account-sidebar">
        <div class="profile-card">
          <div class="profile-avatar">
            <?php if ($customer && $customer['avatar']): ?>
            <img src="<?php echo $customer['avatar']; ?>" alt="<?php echo sanitize($customer['full_name']); ?>" />
            <?php else: ?>
            <div class="avatar-fallback"><?php echo initials($customer['full_name']); ?></div>
            <?php endif; ?>
          </div>
          <h3><?php echo sanitize($customer['full_name']); ?></h3>
          <p><?php echo sanitize($customer['email']); ?></p>
        </div>

        <nav class="account-nav">
          <a href="#profile" class="active"><i class="fa fa-user"></i> Profile</a>
          <a href="orders.php"><i class="fa fa-box"></i> Orders</a>
          <a href="bookings.php"><i class="fa fa-calendar"></i> Appointments</a>
          <a href="wishlist.php"><i class="fa fa-heart"></i> Wishlist <span class="nav-badge"><?php echo $wishlistTotal; ?></span></a>
          <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </nav>
      </aside>

      <!-- MAIN CONTENT -->
      <div class="account-main">
        <!-- STATS CARDS -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-box"></i></div>
            <div class="stat-info">
              <strong><?php echo count($orders); ?></strong>
              <span>Orders</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-calendar"></i></div>
            <div class="stat-info">
              <strong><?php echo count($appointments); ?></strong>
              <span>Appointments</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><i class="fa fa-heart"></i></div>
            <div class="stat-info">
              <strong><?php echo $wishlistTotal; ?></strong>
              <span>Wishlist</span>
            </div>
          </div>
        </div>

        <!-- PROFILE FORM -->
        <div class="account-card" id="profile">
          <div class="card-header">
            <h3><i class="fa fa-user-edit"></i> Edit Profile</h3>
          </div>

          <?php if ($message): ?>
          <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fa fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo sanitize($message); ?>
          </div>
          <?php endif; ?>

          <form method="POST" action="">
            <?php echo csrfField(); ?>
            <input type="hidden" name="update_profile" value="1" />

            <div class="form-row">
              <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo sanitize($customer['full_name']); ?>" required />
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo sanitize($customer['email']); ?>" disabled />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo sanitize($customer['phone']); ?>" />
              </div>
              <div class="form-group">
                <label>Wedding Date</label>
                <input type="date" name="wedding_date" value="<?php echo $customer['wedding_date'] ? $customer['wedding_date'] : ''; ?>" />
              </div>
            </div>

            <div class="form-group">
              <label>Address</label>
              <textarea name="address" rows="3"><?php echo sanitize($customer['address']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save"></i> Save Changes
            </button>
          </form>
        </div>

        <!-- RECENT ORDERS -->
        <div class="account-card">
          <div class="card-header">
            <h3><i class="fa fa-box"></i> Recent Orders</h3>
            <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
          </div>

          <?php if (!empty($orders)): ?>
          <div class="orders-list">
            <?php foreach ($orders as $order): ?>
            <div class="order-item">
              <div class="order-info">
                <strong>#<?php echo $order['id']; ?></strong>
                <span><?php echo formatDate($order['created_at']); ?></span>
              </div>
              <div class="order-status">
                <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
              </div>
              <div class="order-total">
                <?php echo formatPrice($order['total']); ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p class="empty-text">No orders yet. <a href="products.php">Start shopping</a>.</p>
          <?php endif; ?>
        </div>

        <!-- UPCOMING APPOINTMENTS -->
        <div class="account-card">
          <div class="card-header">
            <h3><i class="fa fa-calendar"></i> Upcoming Appointments</h3>
            <a href="bookings.php" class="btn btn-outline btn-sm">View All</a>
          </div>

          <?php if (!empty($appointments)): ?>
          <div class="appointments-list">
            <?php foreach ($appointments as $appt): ?>
            <div class="appointment-item">
              <div class="appointment-date">
                <span class="day"><?php echo date('d', strtotime($appt['booking_date'])); ?></span>
                <span class="month"><?php echo date('M', strtotime($appt['booking_date'])); ?></span>
              </div>
              <div class="appointment-info">
                <strong><?php echo sanitize($appt['service_type']); ?></strong>
                <span><?php echo date('h:i A', strtotime($appt['booking_time'])); ?> &middot; <?php echo sanitize($appt['status']); ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p class="empty-text">No appointments. <a href="booking.php">Book one now</a>.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 35vh;
  background: linear-gradient(135deg, rgba(30,20,30,0.7), rgba(60,30,50,0.5)), url("happytimes.jpeg");
  background-size: cover; background-position: center;
  display: flex; align-items: center; position: relative;
}
.page-hero-content { padding: 100px 0 40px; max-width: 700px; text-align: center; margin: 0 auto; }
.page-label {
  display: inline-block; color: var(--accent); font-size: 0.82rem;
  letter-spacing: 3px; text-transform: uppercase; margin-bottom: 16px;
  font-weight: 600;
}
.page-hero h1 {
  font-size: clamp(2.5rem, 5vw, 4rem); color: #fff;
  line-height: 1.1; margin-bottom: 16px;
}
.page-sub {
  font-family: 'Cormorant Garamond', serif; font-size: 1.2rem;
  color: rgba(255,255,255,0.8); max-width: 500px; margin: 0 auto;
  line-height: 1.6;
}

/* ACCOUNT LAYOUT */
.account-layout {
  display: grid; grid-template-columns: 280px 1fr;
  gap: 32px; align-items: start;
}

/* SIDEBAR */
.account-sidebar { position: sticky; top: 100px; }
.profile-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; padding: 32px 24px; text-align: center; margin-bottom: 20px;
}
.profile-avatar {
  width: 90px; height: 90px; border-radius: 50%;
  margin: 0 auto 16px; overflow: hidden;
  border: 3px solid var(--accent);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.avatar-fallback {
  width: 100%; height: 100%;
  background: linear-gradient(135deg, var(--accent), var(--primary));
  color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 2rem; font-weight: 700;
}
.profile-card h3 { font-size: 1.1rem; margin-bottom: 4px; }
.profile-card p { font-size: 0.85rem; color: var(--text-lt); }

.account-nav {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; overflow: hidden;
}
.account-nav a {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 20px; color: var(--text-lt); font-size: 0.9rem;
  border-bottom: 1px solid var(--border); transition: all 0.3s;
}
.account-nav a:last-child { border-bottom: none; }
.account-nav a:hover, .account-nav a.active {
  background: rgba(201,168,76,0.1); color: var(--accent);
}
.account-nav a i { width: 20px; }
.nav-badge {
  margin-left: auto; background: var(--accent); color: #fff;
  padding: 2px 8px; border-radius: 50px; font-size: 0.75rem; font-weight: 600;
}

/* MAIN CONTENT */
.account-main { min-width: 0; }

/* STATS */
.stats-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 20px; margin-bottom: 32px;
}
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 24px; display: flex;
  align-items: center; gap: 16px;
}
.stat-icon {
  width: 48px; height: 48px; border-radius: 12px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.2rem;
}
.stat-info strong { font-size: 1.6rem; display: block; line-height: 1; }
.stat-info span { font-size: 0.85rem; color: var(--text-lt); }

/* CARDS */
.account-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; padding: 28px; margin-bottom: 24px;
}
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
}
.card-header h3 { font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
.card-header h3 i { color: var(--accent); }

.alert {
  padding: 14px 18px; border-radius: 12px; margin-bottom: 20px;
  font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
}
.alert-success { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 18px; }
.form-group label {
  display: block; font-size: 0.85rem; font-weight: 500;
  margin-bottom: 8px; color: var(--text);
}
.form-group input, .form-group textarea, .form-group select {
  width: 100%; padding: 12px 16px; border: 1.5px solid var(--border);
  border-radius: 10px; background: var(--bg); font-size: 0.9rem;
  color: var(--text); outline: none; transition: border-color 0.3s;
}
.form-group input:focus, .form-group textarea:focus { border-color: var(--accent); }
.form-group input:disabled { background: var(--bg); opacity: 0.6; }

.empty-text { color: var(--text-lt); font-size: 0.9rem; }
.empty-text a { color: var(--accent); }

/* ORDERS */
.orders-list { display: flex; flex-direction: column; gap: 12px; }
.order-item {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px; background: var(--bg); border-radius: 12px;
}
.order-info strong { display: block; font-size: 0.95rem; }
.order-info span { font-size: 0.8rem; color: var(--text-lt); }
.status-badge {
  padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600;
}
.status-pending { background: rgba(245,158,11,0.1); color: #f59e0b; }
.status-processing { background: rgba(59,130,246,0.1); color: #3b82f6; }
.status-completed { background: rgba(16,185,129,0.1); color: #10b981; }
.status-cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }
.order-total { font-weight: 600; color: var(--accent); }

/* APPOINTMENTS */
.appointments-list { display: flex; flex-direction: column; gap: 12px; }
.appointment-item {
  display: flex; align-items: center; gap: 16px;
  padding: 16px; background: var(--bg); border-radius: 12px;
}
.appointment-date {
  width: 56px; height: 56px; border-radius: 12px;
  background: linear-gradient(135deg, var(--accent), var(--primary));
  color: #fff; display: flex; flex-direction: column;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.appointment-date .day { font-size: 1.2rem; font-weight: 700; line-height: 1; }
.appointment-date .month { font-size: 0.7rem; text-transform: uppercase; }
.appointment-info strong { display: block; font-size: 0.95rem; }
.appointment-info span { font-size: 0.8rem; color: var(--text-lt); }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .account-layout { grid-template-columns: 1fr; }
  .account-sidebar { position: static; }
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
  .stats-grid { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
  .page-hero-content { padding: 80px 20px 30px; }
}
</style>

<?php require_once 'footer.php'; ?>