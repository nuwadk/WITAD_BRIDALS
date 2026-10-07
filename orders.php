<?php
$pageTitle = 'My Orders';
$pageDesc = 'View your order history at Witad Bridal Collection.';
$canonical = 'https://witadbridal.com/orders';
require_once 'header.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=orders.php');
    exit;
}

$customerId = $_SESSION['customer_id'];

// Get all orders
$orders = fetchAll("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC", "i", array($customerId));
?>

<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Orders</span>
      <h1>My Orders</h1>
      <p class="page-sub">Track your purchases and rentals.</p>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="account-layout">
      <aside class="account-sidebar">
        <div class="profile-card">
          <div class="profile-avatar">
            <div class="avatar-fallback"><?php echo initials($_SESSION['customer_name']); ?></div>
          </div>
          <h3><?php echo sanitize($_SESSION['customer_name']); ?></h3>
        </div>
        <nav class="account-nav">
          <a href="account.php"><i class="fa fa-user"></i> Profile</a>
          <a href="orders.php" class="active"><i class="fa fa-box"></i> Orders</a>
          <a href="bookings.php"><i class="fa fa-calendar"></i> Appointments</a>
          <a href="wishlist.php"><i class="fa fa-heart"></i> Wishlist</a>
          <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Logout</a>
        </nav>
      </aside>

      <div class="account-main">
        <div class="account-card">
          <div class="card-header">
            <h3><i class="fa fa-box"></i> Order History</h3>
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
              <div class="order-total"><?php echo formatPrice($order['total']); ?></div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p class="empty-text">No orders yet. <a href="products.php">Start shopping</a>.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
.page-hero { min-height: 35vh; background: linear-gradient(135deg, rgba(30,20,30,0.7), rgba(60,30,50,0.5)), url("happytimes.jpeg"); background-size: cover; background-position: center; display: flex; align-items: center; }
.page-hero-content { padding: 100px 0 40px; max-width: 700px; text-align: center; margin: 0 auto; }
.page-label { display: inline-block; color: var(--accent); font-size: 0.82rem; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 16px; font-weight: 600; }
.page-hero h1 { font-size: clamp(2.5rem, 5vw, 4rem); color: #fff; line-height: 1.1; margin-bottom: 16px; }
.page-sub { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; color: rgba(255,255,255,0.8); max-width: 500px; margin: 0 auto; line-height: 1.6; }

.account-layout { display: grid; grid-template-columns: 280px 1fr; gap: 32px; align-items: start; }
.account-sidebar { position: sticky; top: 100px; }
.profile-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 32px 24px; text-align: center; margin-bottom: 20px; }
.profile-avatar { width: 90px; height: 90px; border-radius: 50%; margin: 0 auto 16px; overflow: hidden; border: 3px solid var(--accent); }
.avatar-fallback { width: 100%; height: 100%; background: linear-gradient(135deg, var(--accent), var(--primary)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; }
.profile-card h3 { font-size: 1.1rem; margin-bottom: 4px; }
.account-nav { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; overflow: hidden; }
.account-nav a { display: flex; align-items: center; gap: 12px; padding: 14px 20px; color: var(--text-lt); font-size: 0.9rem; border-bottom: 1px solid var(--border); transition: all 0.3s; }
.account-nav a:last-child { border-bottom: none; }
.account-nav a:hover, .account-nav a.active { background: rgba(201,168,76,0.1); color: var(--accent); }
.account-nav a i { width: 20px; }

.account-main { min-width: 0; }
.account-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 28px; margin-bottom: 24px; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--border); }
.card-header h3 { font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
.card-header h3 i { color: var(--accent); }

.orders-list { display: flex; flex-direction: column; gap: 12px; }
.order-item { display: flex; align-items: center; justify-content: space-between; padding: 16px; background: var(--bg); border-radius: 12px; }
.order-info strong { display: block; font-size: 0.95rem; }
.order-info span { font-size: 0.8rem; color: var(--text-lt); }
.status-badge { padding: 4px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
.status-pending { background: rgba(245,158,11,0.1); color: #f59e0b; }
.status-processing { background: rgba(59,130,246,0.1); color: #3b82f6; }
.status-completed { background: rgba(16,185,129,0.1); color: #10b981; }
.status-cancelled { background: rgba(239,68,68,0.1); color: #ef4444; }
.order-total { font-weight: 600; color: var(--accent); }
.empty-text { color: var(--text-lt); font-size: 0.9rem; }
.empty-text a { color: var(--accent); }

@media (max-width: 1024px) { .account-layout { grid-template-columns: 1fr; } .account-sidebar { position: static; } }
@media (max-width: 768px) { .page-hero-content { padding: 80px 20px 30px; } }
</style>

<?php require_once 'footer.php'; ?>