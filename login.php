<?php
$pageTitle = 'Login';
$pageDesc = 'Sign in to your Witad Bridal Collection account to access your wishlist, orders, and appointments.';
$canonical = 'https://witadbridal.com/login';
require_once 'header.php';

$message = '';
$messageType = '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Process login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrf($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($email) || empty($password)) {
            $message = 'Please enter both email and password.';
            $messageType = 'error';
        } else {
            $user = fetchOne("SELECT id, full_name as name, email, password FROM customers WHERE email = ? AND status = 'active'", "s", array($email));

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_email'] = $user['email'];

                header('Location: ' . $redirect);
                exit;
            } else {
                $message = 'Invalid email or password.';
                $messageType = 'error';
            }
        }
    }
}
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Welcome Back</span>
      <h1>Sign In</h1>
      <p class="page-sub">Access your account to manage your wishlist, appointments, and orders.</p>
    </div>
  </div>
</section>

<!-- LOGIN SECTION -->
<section class="section">
  <div class="container">
    <div class="auth-layout">
      <!-- LOGIN FORM -->
      <div class="auth-card">
        <div class="auth-header">
          <i class="fa fa-user-circle"></i>
          <h2>Sign In</h2>
          <p>Enter your credentials to access your account.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
          <i class="fa fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
          <?php echo sanitize($message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrfField(); ?>
          <input type="hidden" name="redirect" value="<?php echo sanitize($redirect); ?>" />

          <div class="form-group">
            <label for="email"><i class="fa fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required />
          </div>

          <div class="form-group">
            <label for="password"><i class="fa fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" placeholder="Your password" required />
          </div>

          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" name="remember" /> Remember me
            </label>
            <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
            <i class="fa fa-sign-in-alt"></i> Sign In
          </button>
        </form>

        <div class="auth-divider">
          <span>or</span>
        </div>

        <div class="auth-social">
          <a href="#" class="social-btn google"><i class="fab fa-google"></i> Google</a>
          <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i> Facebook</a>
        </div>

        <p class="auth-footer">
          Don't have an account? <a href="register.php">Create one</a>
        </p>
      </div>

      <!-- BENEFITS SIDE -->
      <div class="auth-benefits">
        <span class="section-label">Member Benefits</span>
        <h2 class="section-title" style="color:#fff;">Why Join Witad?</h2>
        <div class="divider"></div>

        <div class="benefits-list">
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-heart"></i></div>
            <div>
              <h4>Save Favorites</h4>
              <p>Build your dream wishlist and share it with family and friends.</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-calendar-check"></i></div>
            <div>
              <h4>Book Appointments</h4>
              <p>Schedule fittings and consultations at your convenience.</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-box"></i></div>
            <div>
              <h4>Track Orders</h4>
              <p>Monitor your gown orders and alterations in real time.</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-tag"></i></div>
            <div>
              <h4>Exclusive Offers</h4>
              <p>Get member-only discounts and early access to new collections.</p>
            </div>
          </div>
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
  font-size: clamp(2.5rem, 5vw, 4rem); color: #f9f8f8;
  line-height: 1.1; margin-bottom: 16px;
}
.page-sub {
  font-family: 'Cormorant Garamond', serif; font-size: 1.2rem;
  color: rgba(255,255,255,0.8); max-width: 500px; margin: 0 auto;
  line-height: 1.6;
}

/* AUTH LAYOUT */
.auth-layout {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; align-items: center;
  max-width: 1000px; margin: 0 auto;
}

/* AUTH CARD */
.auth-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 24px; padding: 48px;
  box-shadow: 0 20px 60px var(--shadow);
}
.auth-header {
  text-align: center; margin-bottom: 32px;
}
.auth-header i {
  font-size: 3.5rem; color: var(--accent); margin-bottom: 16px;
  display: block;
}
.auth-header h2 { font-size: 1.5rem; margin-bottom: 8px; }
.auth-header p { color: var(--text-lt); font-size: 0.9rem; }

.alert {
  padding: 14px 18px; border-radius: 12px; margin-bottom: 20px;
  font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
}
.alert-success { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }

.form-group { margin-bottom: 20px; }
.form-group label {
  display: block; font-size: 0.88rem; font-weight: 500;
  margin-bottom: 8px; color: var(--text);
}
.form-group label i { color: var(--accent); margin-right: 6px; }
.form-group input {
  width: 100%; padding: 14px 18px; border: 1.5px solid var(--border);
  border-radius: 12px; background: var(--bg); font-size: 0.95rem;
  color: var(--text); outline: none; transition: border-color 0.3s;
}
.form-group input:focus { border-color: var(--accent); }

.form-options {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px; font-size: 0.85rem;
}
.remember-me {
  display: flex; align-items: center; gap: 6px;
  color: var(--text-lt); cursor: pointer;
}
.remember-me input { accent-color: var(--accent); }
.forgot-link { color: var(--accent); transition: color 0.3s; }
.forgot-link:hover { color: var(--accent-dk); }

.auth-divider {
  display: flex; align-items: center; gap: 16px;
  margin: 28px 0; color: var(--text-lt); font-size: 0.85rem;
}
.auth-divider::before, .auth-divider::after {
  content: ''; flex: 1; height: 1px; background: var(--border);
}

.auth-social {
  display: flex; gap: 12px; margin-bottom: 24px;
}
.social-btn {
  flex: 1; display: flex; align-items: center; justify-content: center;
  gap: 8px; padding: 12px; border-radius: 10px;
  border: 1.5px solid var(--border); background: var(--bg);
  color: var(--text); font-size: 0.88rem; font-weight: 500;
  transition: all 0.3s;
}
.social-btn:hover { background: var(--border); }
.social-btn.google i { color: #ea4335; }
.social-btn.facebook i { color: #1877f2; }

.auth-footer {
  text-align: center; font-size: 0.9rem; color: var(--text-lt);
}
.auth-footer a { color: var(--accent); font-weight: 600; }

/* BENEFITS SIDE */
.auth-benefits {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  border-radius: 24px; padding: 48px;
  border: 1px solid rgba(201,168,76,0.15);
}
.auth-benefits p { color: rgba(255,255,255,0.7); margin-bottom: 32px; }
.benefits-list { display: flex; flex-direction: column; gap: 20px; }
.benefit-item {
  display: flex; align-items: flex-start; gap: 16px;
}
.benefit-icon {
  width: 48px; height: 48px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  border-radius: 12px; display: flex;
  align-items: center; justify-content: center;
  color: #f8f6f6; font-size: 1.1rem; flex-shrink: 0;
}
.benefit-item h4 { color: #fff; font-size: 1rem; margin-bottom: 4px; }
.benefit-item p { color: rgba(255,255,255,0.55); font-size: 0.85rem; margin: 0; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .auth-layout { grid-template-columns: 1fr; max-width: 500px; }
  .auth-benefits { display: none; }
}
@media (max-width: 768px) {
  .auth-card { padding: 32px 24px; }
  .page-hero-content { padding: 80px 20px 30px; }
}
</style>

<?php require_once 'footer.php'; ?>