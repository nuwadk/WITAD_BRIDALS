<?php
$pageTitle = 'Newsletter';
$pageDesc = 'Subscribe to the Witad Bridal Collection newsletter for the latest bridal trends, tips, and exclusive offers.';
$canonical = 'https://witadbridal.com/newsletter';
require_once 'header.php';

$message = '';
$messageType = '';

// Process subscription
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrf($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        if (empty($email)) {
            $message = 'Please enter your email address.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            // Check if email already exists
            $existing = fetchOne("SELECT id FROM newsletter_subscribers WHERE email = ?", "s", array($email));

            if ($existing) {
                $message = 'You are already subscribed to our newsletter!';
                $messageType = 'info';
            } else {
                // Insert subscriber using query() function from db.php
                $stmt = query("INSERT INTO newsletter_subscribers (email, status, subscribed_at) VALUES (?, 'active', NOW())", "s", array($email));

                if ($stmt) {
                    // Send welcome email
                    $subject = 'Welcome to Witad Bridal Collection';
                    $body = emailTemplate('Welcome to Our Newsletter', '
                        <p>Dear Subscriber,</p>
                        <p>Thank you for subscribing to the Witad Bridal Collection newsletter! We are thrilled to have you join our community of brides and wedding enthusiasts.</p>
                        <p>Here is what you can expect:</p>
                        <ul>
                            <li>Exclusive bridal trends and styling tips</li>
                            <li>Early access to new collections</li>
                            <li>Special offers and promotions</li>
                            <li>Real wedding stories and inspiration</li>
                        </ul>
                        <p>Stay tuned for our next update, and feel free to reach out if you have any questions.</p>
                        <p>With love,<br>The Witad Bridal Team</p>
                        <a href="https://witadbridal.com" class="btn">Visit Our Website</a>
                    ');
                    sendEmail($email, $subject, $body);

                    $message = 'Thank you for subscribing! Check your inbox for a welcome email.';
                    $messageType = 'success';
                } else {
                    $message = 'Something went wrong. Please try again later.';
                    $messageType = 'error';
                }
            }
        }
    }
}

// Get subscriber count
$subscriberCount = fetchOne("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
$totalSubscribers = $subscriberCount ? $subscriberCount['total'] : 0;
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Stay Connected</span>
      <h1>The Bridal Journal</h1>
      <p class="page-sub">Join thousands of brides who trust us for the latest trends, tips, and exclusive offers.</p>
    </div>
  </div>
</section>

<!-- NEWSLETTER SECTION -->
<section class="section">
  <div class="container">
    <div class="newsletter-layout">
      <!-- LEFT: Info -->
      <div class="newsletter-info">
        <span class="section-label">Why Subscribe?</span>
        <h2 class="section-title">Be the First to Know</h2>
        <div class="divider"></div>
        <p>Get exclusive access to bridal trends, styling advice, and special promotions delivered straight to your inbox.</p>

        <div class="benefits-list">
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-gem"></i></div>
            <div>
              <h4>Exclusive Trends</h4>
              <p>Be the first to discover the latest bridal fashion trends and collections.</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-tag"></i></div>
            <div>
              <h4>Special Offers</h4>
              <p>Receive subscriber-only discounts and early access to sales.</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-heart"></i></div>
            <div>
              <h4>Real Stories</h4>
              <p>Get inspired by real wedding stories and bride testimonials.</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon"><i class="fa fa-calendar"></i></div>
            <div>
              <h4>Event Invites</h4>
              <p>Get invited to exclusive trunk shows and bridal events.</p>
            </div>
          </div>
        </div>

        <div class="subscriber-count">
          <i class="fa fa-users"></i>
          <strong><?php echo number_format($totalSubscribers); ?></strong> brides already subscribed
        </div>
      </div>

      <!-- RIGHT: Form -->
      <div class="newsletter-form-card">
        <div class="form-header">
          <i class="fa fa-envelope-open"></i>
          <h3>Subscribe Now</h3>
          <p>Join our community and never miss an update.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
          <i class="fa fa-<?php echo $messageType == 'success' ? 'check-circle' : ($messageType == 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
          <?php echo sanitize($message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrfField(); ?>
          <div class="form-group">
            <label for="email"><i class="fa fa-envelope"></i> Email Address</label>
            <input type="email" id="email" name="email" placeholder="your@email.com" required />
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
            <i class="fa fa-paper-plane"></i> Subscribe to Newsletter
          </button>
        </form>

        <p class="form-note">
          <i class="fa fa-lock"></i> We respect your privacy. Unsubscribe at any time.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- PAST ISSUES PREVIEW -->
<section class="section past-issues-section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Past Issues</span>
      <h2 class="section-title" style="color:#fff;">What You Have Missed</h2>
      <div class="divider center"></div>
    </div>
    <div class="issues-grid">
      <div class="issue-card">
        <div class="issue-date">June 2025</div>
        <h4>Summer Bridal Trends</h4>
        <p>Discover the hottest trends for summer weddings this year.</p>
        <span class="issue-tag">Trends</span>
      </div>
      <div class="issue-card">
        <div class="issue-date">May 2025</div>
        <h4>Accessorizing Your Gown</h4>
        <p>The ultimate guide to choosing the perfect bridal accessories.</p>
        <span class="issue-tag">Styling</span>
      </div>
      <div class="issue-card">
        <div class="issue-date">April 2025</div>
        <h4>Real Bride: Sarah's Story</h4>
        <p>How Sarah found her dream gown at Witad Bridal.</p>
        <span class="issue-tag">Stories</span>
      </div>
    </div>
  </div>
</section>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 45vh;
  background: linear-gradient(135deg, rgba(30,20,30,0.7), rgba(60,30,50,0.5)), url("happytimes.jpeg");
  background-size: cover; background-position: center;
  display: flex; align-items: center; position: relative;
}
.page-hero-content { padding: 120px 0 60px; max-width: 700px; text-align: center; margin: 0 auto; }
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

/* NEWSLETTER LAYOUT */
.newsletter-layout {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; align-items: start;
}
.newsletter-info p {
  color: var(--text-lt); font-size: 0.95rem; line-height: 1.8;
  margin-bottom: 32px;
}
.benefits-list { margin-bottom: 36px; }
.benefit-item {
  display: flex; align-items: flex-start; gap: 16px;
  margin-bottom: 20px;
}
.benefit-icon {
  width: 48px; height: 48px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  border-radius: 12px; display: flex;
  align-items: center; justify-content: center;
  color: #fff; font-size: 1.1rem; flex-shrink: 0;
}
.benefit-item h4 { font-size: 1rem; margin-bottom: 4px; }
.benefit-item p { font-size: 0.85rem; color: var(--text-lt); margin: 0; }
.subscriber-count {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.2);
  padding: 14px 24px; border-radius: 12px; font-size: 0.9rem;
  color: var(--text-lt);
}
.subscriber-count i { color: var(--accent); font-size: 1.2rem; }
.subscriber-count strong { color: var(--accent); font-size: 1.3rem; margin-right: 4px; }

/* FORM CARD */
.newsletter-form-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 24px; padding: 48px;
  box-shadow: 0 20px 60px var(--shadow);
}
.form-header {
  text-align: center; margin-bottom: 32px;
}
.form-header i {
  font-size: 3rem; color: var(--accent); margin-bottom: 16px;
  display: block;
}
.form-header h3 { font-size: 1.4rem; margin-bottom: 8px; }
.form-header p { color: var(--text-lt); font-size: 0.9rem; }

.alert {
  padding: 14px 18px; border-radius: 12px; margin-bottom: 20px;
  font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
}
.alert-success { background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16,185,129,0.2); }
.alert-error { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.2); }
.alert-info { background: rgba(59,130,246,0.1); color: #3b82f6; border: 1px solid rgba(59,130,246,0.2); }

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
.form-note {
  text-align: center; font-size: 0.8rem; color: var(--text-lt);
  margin-top: 20px;
}
.form-note i { margin-right: 4px; }

/* PAST ISSUES */
.past-issues-section {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  position: relative; overflow: hidden;
}
.issues-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 28px; margin-top: 52px;
}
.issue-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(201,168,76,0.15);
  border-radius: 18px; padding: 32px 28px;
  transition: transform 0.3s;
}
.issue-card:hover { transform: translateY(-6px); }
.issue-date {
  font-size: 0.78rem; color: var(--accent);
  font-weight: 600; letter-spacing: 1.5px;
  text-transform: uppercase; margin-bottom: 12px;
}
.issue-card h4 { color: #fff; font-size: 1.1rem; margin-bottom: 10px; }
.issue-card p { color: rgba(255,255,255,0.55); font-size: 0.85rem; line-height: 1.6; margin-bottom: 16px; }
.issue-tag {
  display: inline-block; background: rgba(201,168,76,0.15);
  color: var(--accent); padding: 4px 12px;
  border-radius: 50px; font-size: 0.72rem;
  font-weight: 600; letter-spacing: 0.5px;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
  .newsletter-layout { grid-template-columns: 1fr; }
  .issues-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .issues-grid { grid-template-columns: 1fr; }
  .newsletter-form-card { padding: 32px 24px; }
  .page-hero-content { padding: 100px 20px 40px; }
}
</style>

<?php require_once 'footer.php'; ?>