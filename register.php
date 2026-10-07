<?php
$pageTitle = 'Register';
$pageDesc = 'Create your Witad Bridal Collection account to save favorites, book appointments, and enjoy exclusive member benefits.';
$canonical = 'https://witadbridal.com/register';
require_once 'header.php';

$message = '';
$messageType = '';

// Process registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrf($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        // Validation
        if (empty($name) || empty($email) || empty($password)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters long.';
            $messageType = 'error';
        } elseif ($password !== $confirmPassword) {
            $message = 'Passwords do not match.';
            $messageType = 'error';
        } else {
            // Check if email already exists
            $existing = fetchOne("SELECT id FROM customers WHERE email = ?", "s", array($email));

            if ($existing) {
                $message = 'An account with this email already exists. Please sign in instead.';
                $messageType = 'error';
            } else {
                // Hash password and insert
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = query("INSERT INTO customers (full_name, email, phone, password, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())", "ssss", array($name, $email, $phone, $hashedPassword));

                if ($stmt) {
                    $newId = insertId();

                    // Auto login after registration
                    $_SESSION['customer_id'] = $newId;
                    $_SESSION['customer_name'] = $name;
                    $_SESSION['customer_email'] = $email;

                    // Send welcome email
                    $subject = 'Welcome to Witad Bridal Collection';
                    $body = emailTemplate('Welcome to Witad Bridal', '
                        <p>Dear ' . sanitize($name) . ',</p>
                        <p>Welcome to the Witad Bridal Collection family! Your account has been successfully created.</p>
                        <p>With your account, you can:</p>
                        <ul>
                            <li>Save your favorite gowns to your wishlist</li>
                            <li>Book appointments for fittings and consultations</li>
                            <li>Track your orders and alterations</li>
                            <li>Receive exclusive offers and updates</li>
                        </ul>
                        <p>We look forward to helping you find your dream gown!</p>
                        <p>With love,<br>The Witad Bridal Team</p>
                        <a href="https://witadbridal.com/products.php" class="btn">Start Shopping</a>
                    ');
                    sendEmail($email, $subject, $body);

                    setFlash('success', 'Welcome to Witad Bridal! Your account has been created.');
                    header('Location: index.php');
                    exit;
                } else {
                    $message = 'Something went wrong. Please try again later.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Join Us</span>
      <h1>Create Account</h1>
      <p class="page-sub">Become a member and enjoy exclusive bridal benefits, personalized styling, and more.</p>
    </div>
  </div>
</section>

<!-- REGISTER SECTION -->
<section class="section">
  <div class="container">
    <div class="auth-layout">
      <!-- REGISTER FORM -->
      <div class="auth-card">
        <div class="auth-header">
          <i class="fa fa-user-plus"></i>
          <h2>Create Account</h2>
          <p>Fill in your details to get started.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
          <i class="fa fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
          <?php echo sanitize($message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrfField(); ?>

          <div class="form-group">
            <label for="name"><i class="fa fa-user"></i> Full Name</label>
            <input type="text" id="name" name="name" placeholder="Your full name" required value="<?php echo isset($_POST['name']) ? sanitize($_POST['name']) : ''; ?>" />
          </div>

          <div class="form-group">
            <label for="email"><i class="fa fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" />
          </div>

          <div class="form-group">
            <label for="phone"><i class="fa fa-phone"></i> Phone Number (Optional)</label>
            <input type="tel" id="phone" name="phone" placeholder="+256 700 000 000" value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>" />
          </div>

          <div class="form-group">
            <label for="password"><i class="fa fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" placeholder="At least 6 characters" required minlength="6" />
          </div>

          <div class="form-group">
            <label for="confirm_password"><i class="fa fa-lock"></i> Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required />
          </div>

          <div class="form-options">
            <label class="terms">
              <input type="checkbox" name="terms" required /> I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a>
            </label>
          </div>

          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
            <i class="fa fa-user-plus"></i> Create Account
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
          Already have an account? <a href="login.php">Sign in</a>
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
  font-size: clamp(2.5rem, 5vw, 4rem); color: #fff;
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

.form-group { margin-bottom: 18px; }
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
  margin-bottom: 24px; font-size: 0.85rem;
}
.terms {
  display: flex; align-items: flex-start; gap: 8px;
  color: var(--text-lt); cursor: pointer; line-height: 1.5;
}
.terms input { margin-top: 3px; accent-color: var(--accent); }
.terms a { color: var(--accent); }

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
  color: #fff; font-size: 1.1rem; flex-shrink: 0;
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