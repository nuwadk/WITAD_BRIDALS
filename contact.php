<?php
$pageTitle = 'Contact Us';
$pageDesc = 'Get in touch with Witad Bridal Collection. Visit us in Kabwohe-Sheema, Uganda, or reach out via phone, email, or WhatsApp for bridal appointments and inquiries.';
$canonical = 'https://witadbridal.com/contact';
require_once 'header.php';

$message = '';
$messageType = '';

// Process contact form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrf($_POST['csrf_token'])) {
        $message = 'Invalid request. Please try again.';
        $messageType = 'error';
    } else {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $msgBody = isset($_POST['message']) ? trim($_POST['message']) : '';

        if (empty($name) || empty($email) || empty($subject) || empty($msgBody)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        } else {
            // Store in database
            $stmt = query("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'new', NOW())", "sssss", array($name, $email, $phone, $subject, $msgBody));

            if ($stmt) {
                // Send notification email to admin
                $adminSubject = 'New Contact Message: ' . $subject;
                $adminBody = emailTemplate('New Contact Message', '
                    <p><strong>Name:</strong> ' . sanitize($name) . '</p>
                    <p><strong>Email:</strong> ' . sanitize($email) . '</p>
                    <p><strong>Phone:</strong> ' . sanitize($phone) . '</p>
                    <p><strong>Subject:</strong> ' . sanitize($subject) . '</p>
                    <p><strong>Message:</strong></p>
                    <p>' . nl2br(sanitize($msgBody)) . '</p>
                ');
                sendEmail('info@witadbridal.com', $adminSubject, $adminBody);

                // Send confirmation to user
                $userSubject = 'Thank you for contacting Witad Bridal';
                $userBody = emailTemplate('Message Received', '
                    <p>Dear ' . sanitize($name) . ',</p>
                    <p>Thank you for reaching out to Witad Bridal Collection. We have received your message and will get back to you within 24 hours.</p>
                    <p><strong>Your message:</strong></p>
                    <p><em>' . nl2br(sanitize($msgBody)) . '</em></p>
                    <p>Best regards,<br>The Witad Bridal Team</p>
                ');
                sendEmail($email, $userSubject, $userBody);

                $message = 'Thank you for your message! We will get back to you soon.';
                $messageType = 'success';
            } else {
                $message = 'Something went wrong. Please try again later.';
                $messageType = 'error';
            }
        }
    }
}

// Get settings for contact info
$settings = fetchAll("SELECT setting_key, setting_value FROM settings");
$settingsMap = array();
foreach ($settings as $s) {
    $settingsMap[$s['setting_key']] = $s['setting_value'];
}
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Get In Touch</span>
      <h1>Contact Us</h1>
      <p class="page-sub">We would love to hear from you. Reach out for appointments, inquiries, or just to say hello.</p>
    </div>
  </div>
</section>

<!-- CONTACT SECTION -->
<section class="section">
  <div class="container">
    <div class="contact-layout">
      <!-- CONTACT INFO -->
      <div class="contact-info">
        <span class="section-label">Our Details</span>
        <h2 class="section-title">Let's Connect</h2>
        <div class="divider"></div>
        <p>Whether you are planning your wedding or just exploring options, our team is here to help you every step of the way.</p>

        <div class="contact-details">
          <div class="contact-detail">
            <div class="detail-icon"><i class="fa fa-map-marker-alt"></i></div>
            <div class="detail-content">
              <h4>Visit Us</h4>
              <p><?php echo isset($settingsMap['contact_address']) ? sanitize($settingsMap['contact_address']) : 'Kabwohe-Sheema, Uganda'; ?></p>
            </div>
          </div>
          <div class="contact-detail">
            <div class="detail-icon"><i class="fa fa-phone"></i></div>
            <div class="detail-content">
              <h4>Call Us</h4>
              <p><a href="tel:<?php echo isset($settingsMap['contact_phone']) ? $settingsMap['contact_phone'] : '+256750900134'; ?>"><?php echo isset($settingsMap['contact_phone']) ? $settingsMap['contact_phone'] : '+256 750 900 134'; ?></a></p>
            </div>
          </div>
          <div class="contact-detail">
            <div class="detail-icon"><i class="fa fa-envelope"></i></div>
            <div class="detail-content">
              <h4>Email Us</h4>
              <p><a href="mailto:<?php echo isset($settingsMap['contact_email']) ? $settingsMap['contact_email'] : 'info@witadbridal.com'; ?>"><?php echo isset($settingsMap['contact_email']) ? $settingsMap['contact_email'] : 'info@witadbridal.com'; ?></a></p>
            </div>
          </div>
          <div class="contact-detail">
            <div class="detail-icon"><i class="fa fa-clock"></i></div>
            <div class="detail-content">
              <h4>Business Hours</h4>
              <p><?php echo nl2br(isset($settingsMap['business_hours']) ? sanitize($settingsMap['business_hours']) : "Mon-Fri: 8AM - 7PM
Sat: 9AM - 6PM
Sun: By Appointment"); ?></p>
            </div>
          </div>
        </div>

        <div class="contact-social">
          <span>Follow Us:</span>
          <a href="<?php echo isset($settingsMap['facebook_url']) ? $settingsMap['facebook_url'] : '#'; ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?php echo isset($settingsMap['instagram_url']) ? $settingsMap['instagram_url'] : '#'; ?>" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="<?php echo isset($settingsMap['tiktok_url']) ? $settingsMap['tiktok_url'] : '#'; ?>" target="_blank" title="TikTok"><i class="fab fa-tiktok"></i></a>
          <a href="https://wa.me/<?php echo isset($settingsMap['whatsapp_number']) ? $settingsMap['whatsapp_number'] : '256750900134'; ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <!-- CONTACT FORM -->
      <div class="contact-form-card">
        <div class="form-header">
          <i class="fa fa-paper-plane"></i>
          <h3>Send a Message</h3>
          <p>Fill in the form below and we will respond within 24 hours.</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
          <i class="fa fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
          <?php echo sanitize($message); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
          <?php echo csrfField(); ?>
          <div class="form-row">
            <div class="form-group">
              <label for="name"><i class="fa fa-user"></i> Full Name *</label>
              <input type="text" id="name" name="name" placeholder="Your name" required />
            </div>
            <div class="form-group">
              <label for="email"><i class="fa fa-envelope"></i> Email *</label>
              <input type="email" id="email" name="email" placeholder="your@email.com" required />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="phone"><i class="fa fa-phone"></i> Phone</label>
              <input type="tel" id="phone" name="phone" placeholder="+256 700 000 000" />
            </div>
            <div class="form-group">
              <label for="subject"><i class="fa fa-tag"></i> Subject *</label>
              <select id="subject" name="subject" required>
                <option value="">Select a subject</option>
                <option value="General Inquiry">General Inquiry</option>
                <option value="Book Appointment">Book Appointment</option>
                <option value="Gown Inquiry">Gown Inquiry</option>
                <option value="Rental Question">Rental Question</option>
                <option value="Alterations">Alterations</option>
                <option value="Feedback">Feedback</option>
                <option value="Other">Other</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="message"><i class="fa fa-comment"></i> Message *</label>
            <textarea id="message" name="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">
            <i class="fa fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- MAP SECTION -->
<section class="section map-section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Find Us</span>
      <h2 class="section-title" style="color:#fff;">Visit Our Boutique</h2>
      <div class="divider center"></div>
      <p style="color:rgba(255,255,255,0.7);max-width:600px;margin:0 auto;">Come experience the magic in person. We are located in the heart of Kabwohe-Sheema.</p>
    </div>
    <div class="map-container">
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15959.2!2d30.3!3d-0.6!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMMKwMzYnMDAuMCJTIDMwwrAxOCcwMC4wIkU!5e0!3m2!1sen!2sug!4v1" 
        width="100%" 
        height="400" 
        style="border:0; border-radius:20px;" 
        allowfullscreen="" 
        loading="lazy">
      </iframe>
    </div>
  </div>
</section>

<!-- FAQ PREVIEW -->
<section class="section" style="padding:90px 0;">
  <div class="container">
    <div class="cta-banner">
      <span class="section-label" style="display:block;margin-bottom:8px;">Have Questions?</span>
      <h2>Check Our FAQ</h2>
      <p>Find answers to commonly asked questions about our services, appointments, and more.</p>
      <div class="cta-btns">
        <a class="btn btn-primary btn-lg" href="faq.php">
          <i class="fa fa-question-circle"></i> View FAQ
        </a>
        <a class="btn btn-outline" href="booking.php">
          <i class="fa fa-calendar"></i> Book Appointment
        </a>
      </div>
    </div>
  </div>
</section>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 40vh;
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

/* CONTACT LAYOUT */
.contact-layout {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; align-items: start;
}
.contact-info p {
  color: var(--text-lt); font-size: 0.95rem; line-height: 1.8;
  margin-bottom: 32px;
}
.contact-details { margin-bottom: 32px; }
.contact-detail {
  display: flex; align-items: flex-start; gap: 16px;
  margin-bottom: 24px;
}
.detail-icon {
  width: 52px; height: 52px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  border-radius: 14px; display: flex;
  align-items: center; justify-content: center;
  color: #fff; font-size: 1.2rem; flex-shrink: 0;
}
.detail-content h4 { font-size: 1rem; margin-bottom: 4px; }
.detail-content p { font-size: 0.88rem; color: var(--text-lt); margin: 0; line-height: 1.5; }
.detail-content a { color: var(--accent); transition: color 0.3s; }
.detail-content a:hover { color: var(--accent-dk); }
.contact-social {
  display: flex; align-items: center; gap: 12px;
}
.contact-social span { font-size: 0.9rem; color: var(--text-lt); margin-right: 4px; }
.contact-social a {
  width: 42px; height: 42px; border-radius: 50%;
  background: var(--bg); border: 1.5px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-lt); font-size: 1rem; transition: all 0.3s;
}
.contact-social a:hover {
  background: var(--accent); border-color: var(--accent); color: #fff;
}

/* CONTACT FORM */
.contact-form-card {
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

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { margin-bottom: 18px; }
.form-group label {
  display: block; font-size: 0.88rem; font-weight: 500;
  margin-bottom: 8px; color: var(--text);
}
.form-group label i { color: var(--accent); margin-right: 6px; }
.form-group input, .form-group select, .form-group textarea {
  width: 100%; padding: 14px 18px; border: 1.5px solid var(--border);
  border-radius: 12px; background: var(--bg); font-size: 0.95rem;
  color: var(--text); outline: none; transition: border-color 0.3s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--accent); }
.form-group select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; }
.form-group textarea { resize: vertical; }

/* MAP SECTION */
.map-section {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  position: relative; overflow: hidden;
}
.map-container {
  margin-top: 48px; border-radius: 20px; overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}
.map-container iframe { display: block; }

/* CTA BANNER */
.cta-banner {
  background: linear-gradient(135deg, rgba(244,167,185,0.15), rgba(201,168,76,0.1)), var(--bg-card);
  border-radius: 28px; padding: 72px 60px;
  text-align: center; margin: 0 24px;
  border: 1px solid var(--border);
}
.cta-banner h2 { font-size: clamp(2rem, 4vw, 2.8rem); margin-bottom: 16px; }
.cta-banner p { color: var(--text-lt); font-size: 1rem; max-width: 520px; margin: 0 auto 36px; }
.cta-btns { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .contact-layout { grid-template-columns: 1fr; }
  .form-row { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .contact-form-card { padding: 32px 24px; }
  .cta-banner { padding: 52px 28px; margin: 0 12px; }
  .page-hero-content { padding: 80px 20px 30px; }
}
</style>

<?php require_once 'footer.php'; ?>