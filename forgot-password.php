<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
$pageTitle = 'Forgot Password';
$pageDesc = 'Reset your Witad Bridal Collection account password.';
require_once 'header.php';

$step = 'request'; // request | sent | reset
$token = isset($_GET['token']) ? sanitize($_GET['token']) : '';
$email = '';

// Step 1: Handle token verification (reset form)
if (!empty($token)) {
    $reset = fetchOne("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0", "s", array($token));
    if ($reset) {
        $step = 'reset';
        $email = $reset['email'];
    } else {
        setFlash('error', 'Invalid or expired reset link. Please request a new one.');
        $step = 'request';
    }
}

// Step 2: Handle password reset submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password']) && $step == 'reset') {
    if (!verifyCsrf((isset($_POST['csrf_token']) ? $_POST['csrf_token'] : ''))) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        $newPassword = (isset($_POST['new_password']) ? $_POST['new_password'] : '');
        $confirmPassword = (isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '');

        if (strlen($newPassword) < 6) {
            setFlash('error', 'Password must be at least 6 characters long.');
        } elseif ($newPassword !== $confirmPassword) {
            setFlash('error', 'Passwords do not match.');
        } else {
            // Hash password and update
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            query("UPDATE customers SET password = ? WHERE email = ?", "ss", array($hashedPassword, $email));
            
            // Mark token as used
            query("UPDATE password_resets SET used = 1 WHERE token = ?", "s", array($token));
            
            setFlash('success', 'Your password has been reset successfully. Please log in with your new password.');
            header('Location: login.php');
            exit;
        }
    }
}

// Step 3: Handle reset request submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_reset']) && $step == 'request') {
    if (!verifyCsrf((isset($_POST['csrf_token']) ? $_POST['csrf_token'] : ''))) {
        setFlash('error', 'Invalid request. Please try again.');
    } else {
        $requestEmail = sanitize((isset($_POST['email']) ? $_POST['email'] : ''));
        
        if (empty($requestEmail)) {
            setFlash('error', 'Please enter your email address.');
        } else {
            $customer = fetchOne("SELECT id, email, full_name FROM customers WHERE email = ?", "s", array($requestEmail));
            
            if ($customer) {
                // Generate token
                $resetToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token
                query("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())", "sss", array($customer['email'], $resetToken, $expiresAt));
                
                // Send reset email
                $resetLink = $canonical . 'forgot-password.php?token=' . $resetToken;
                $emailBody = emailTemplate('Password Reset Request', '
                    <h2>Password Reset Request</h2>
                    <p>Hello ' . sanitize($customer['full_name']) . ',</p>
                    <p>We received a request to reset your password for your Witad Bridal Collection account.</p>
                    <p>Click the button below to reset your password. This link will expire in 1 hour.</p>
                    <div style="text-align:center;margin:28px 0;">
                        <a href="' . $resetLink . '" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#c9a84c,#a88a3a);color:#fff;text-decoration:none;border-radius:50px;font-weight:600;">Reset Password</a>
                    </div>
                    <p style="font-size:0.85rem;color:#888;">If you did not request this, you can safely ignore this email. Your password will not be changed.</p>
                    <p style="font-size:0.85rem;color:#888;word-break:break-all;">Or copy this link: ' . $resetLink . '</p>
                ');
                
                sendEmail($customer['email'], 'Password Reset - Witad Bridal Collection', $emailBody);
            }
            
            // Always show success to prevent email enumeration
            $step = 'sent';
        }
    }
}
?>

<div class="breadcrumbs">
  <div class="container">
    <h1><?php echo $step == 'reset' ? 'Reset Password' : 'Forgot Password'; ?></h1>
    <div class="crumb">
      <a href="index.php">Home</a> <i class="fa fa-chevron-right"></i>
      <span><?php echo $step == 'reset' ? 'Reset Password' : 'Forgot Password'; ?></span>
    </div>
  </div>
</div>

<section class="section" style="padding:80px 0;">
  <div class="container">
    <div class="auth-wrapper">
      
      <?php if ($step == 'request'): ?>
      <!-- Request Reset Form -->
      <div class="auth-card">
        <div class="auth-header">
          <div class="auth-icon"><i class="fa fa-lock"></i></div>
          <h2>Forgot Your Password?</h2>
          <p>Enter your email address and we'll send you a link to reset your password.</p>
        </div>
        
        <form method="POST" action="">
          <?php echo csrfField(); ?>
          
          <div class="form-group">
            <label>Email Address</label>
            <div class="input-icon-wrap">
              <i class="fa fa-envelope"></i>
              <input type="email" name="email" placeholder="your@email.com" required autofocus />
            </div>
          </div>
          
          <button type="submit" name="request_reset" class="auth-btn">
            <i class="fa fa-paper-plane"></i> Send Reset Link
          </button>
        </form>
        
        <div class="auth-footer">
          <p>Remember your password? <a href="login.php">Log In</a></p>
        </div>
      </div>
      
      <?php elseif ($step == 'sent'): ?>
      <!-- Email Sent Confirmation -->
      <div class="auth-card" style="text-align:center;">
        <div class="auth-header">
          <div class="auth-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
            <i class="fa fa-envelope-open-text"></i>
          </div>
          <h2>Check Your Email</h2>
          <p>If an account exists with that email address, we've sent password reset instructions. Please check your inbox and spam folder.</p>
        </div>
        
        <div class="auth-footer">
          <p>Didn't receive it? <a href="forgot-password.php">Try Again</a></p>
          <p><a href="login.php"><i class="fa fa-arrow-left"></i> Back to Login</a></p>
        </div>
      </div>
      
      <?php elseif ($step == 'reset'): ?>
      <!-- Reset Password Form -->
      <div class="auth-card">
        <div class="auth-header">
          <div class="auth-icon"><i class="fa fa-key"></i></div>
          <h2>Create New Password</h2>
          <p>Enter your new password below.</p>
        </div>
        
        <form method="POST" action="">
          <?php echo csrfField(); ?>
          <input type="hidden" name="token" value="<?php echo sanitize($token); ?>" />
          
          <div class="form-group">
            <label>New Password</label>
            <div class="input-icon-wrap">
              <i class="fa fa-lock"></i>
              <input type="password" name="new_password" placeholder="Min. 6 characters" required minlength="6" />
            </div>
          </div>
          
          <div class="form-group">
            <label>Confirm Password</label>
            <div class="input-icon-wrap">
              <i class="fa fa-lock"></i>
              <input type="password" name="confirm_password" placeholder="Repeat your password" required minlength="6" />
            </div>
          </div>
          
          <button type="submit" name="reset_password" class="auth-btn">
            <i class="fa fa-check-circle"></i> Reset Password
          </button>
        </form>
      </div>
      <?php endif; ?>
      
    </div>
  </div>
</section>

<style>
.auth-wrapper {
  max-width: 460px;
  margin: 0 auto;
}
.auth-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 24px;
  padding: 40px 36px;
  box-shadow: 0 8px 40px var(--shadow);
}
.auth-header {
  text-align: center;
  margin-bottom: 32px;
}
.auth-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--accent), var(--accent-dk));
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 1.6rem;
  color: #fff;
}
.auth-header h2 {
  font-size: 1.4rem;
  margin-bottom: 8px;
}
.auth-header p {
  font-size: 0.88rem;
  color: var(--text-lt);
  line-height: 1.6;
}
.form-group {
  margin-bottom: 20px;
}
.form-group label {
  display: block;
  font-size: 0.83rem;
  font-weight: 500;
  color: var(--text);
  margin-bottom: 6px;
}
.input-icon-wrap {
  position: relative;
}
.input-icon-wrap i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-lt);
  font-size: 0.9rem;
}
.input-icon-wrap input {
  width: 100%;
  padding: 12px 14px 12px 42px;
  border: 1.5px solid var(--border);
  border-radius: 12px;
  background: var(--bg);
  font-family: 'Poppins', sans-serif;
  font-size: 0.88rem;
  color: var(--text);
  transition: border-color 0.3s, box-shadow 0.3s;
  outline: none;
}
.input-icon-wrap input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}
.auth-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dk));
  color: #fff;
  border: none;
  border-radius: 50px;
  font-family: 'Poppins', sans-serif;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  letter-spacing: 0.5px;
  transition: all 0.3s;
  box-shadow: 0 4px 18px rgba(201,168,76,0.4);
  margin-top: 8px;
}
.auth-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(201,168,76,0.5);
}
.auth-footer {
  text-align: center;
  margin-top: 28px;
  padding-top: 24px;
  border-top: 1px solid var(--border);
}
.auth-footer p {
  font-size: 0.85rem;
  color: var(--text-lt);
  margin-bottom: 8px;
}
.auth-footer a {
  color: var(--accent);
  font-weight: 600;
  text-decoration: none;
}
.auth-footer a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .auth-card {
    padding: 32px 24px;
  }
}
</style>

<?php require_once 'footer.php'; ?>