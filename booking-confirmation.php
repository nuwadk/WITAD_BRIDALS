<?php
$pageTitle = 'Appointment Confirmed';
$pageDesc = 'Your bridal appointment has been confirmed at Witad Bridal Collection.';
require_once 'header.php';

$bookingId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$bookingId) {
    header('Location: booking.php');
    exit;
}

// Get booking details
$booking = fetchOne("SELECT * FROM bookings WHERE id = ?", "i", array($bookingId));

if (!$booking) {
    setFlash('error', 'Booking not found.');
    header('Location: booking.php');
    exit;
}

// Get settings
$settings = array();
$settingsRows = fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="breadcrumbs">
  <div class="container">
    <h1>Appointment Confirmed</h1>
    <div class="crumb">
      <a href="index.php">Home</a> <i class="fa fa-chevron-right"></i>
      <a href="booking.php">Book Appointment</a> <i class="fa fa-chevron-right"></i>
      <span>Confirmation</span>
    </div>
  </div>
</div>

<section class="section" style="padding-top:60px;">
  <div class="container">
    <div class="confirmation-wrapper">
      
      <div class="confirmation-card">
        <div class="confirmation-icon">
          <i class="fa fa-check-circle"></i>
        </div>
        <h2>Your Appointment is Booked!</h2>
        <p class="confirmation-sub">Thank you for choosing Witad Bridal Collection. We look forward to making your bridal journey unforgettable.</p>
        
        <div class="booking-details">
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-hashtag"></i> Booking ID</div>
            <div class="detail-value">#<?php echo str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-user"></i> Name</div>
            <div class="detail-value"><?php echo sanitize($booking['full_name']); ?></div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-phone"></i> Phone</div>
            <div class="detail-value"><?php echo sanitize($booking['phone']); ?></div>
          </div>
          <?php if ($booking['email']): ?>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-envelope"></i> Email</div>
            <div class="detail-value"><?php echo sanitize($booking['email']); ?></div>
          </div>
          <?php endif; ?>
          <div class="detail-row highlight">
            <div class="detail-label"><i class="fa fa-calendar"></i> Date</div>
            <div class="detail-value"><?php echo formatDate($booking['booking_date']); ?></div>
          </div>
          <div class="detail-row highlight">
            <div class="detail-label"><i class="fa fa-clock"></i> Time</div>
            <div class="detail-value"><?php echo date('h:i A', strtotime($booking['booking_time'])); ?></div>
          </div>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-star"></i> Service</div>
            <div class="detail-value"><?php echo sanitize($booking['service_needed']); ?></div>
          </div>
          <?php if ($booking['wedding_date']): ?>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-heart"></i> Wedding Date</div>
            <div class="detail-value"><?php echo formatDate($booking['wedding_date']); ?></div>
          </div>
          <?php endif; ?>
          <?php if ($booking['notes']): ?>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-comment"></i> Notes</div>
            <div class="detail-value"><?php echo nl2br(sanitize($booking['notes'])); ?></div>
          </div>
          <?php endif; ?>
          <div class="detail-row">
            <div class="detail-label"><i class="fa fa-info-circle"></i> Status</div>
            <div class="detail-value"><span class="status-badge status-pending">Pending</span></div>
          </div>
        </div>
        
        <div class="confirmation-actions">
          <a href="bookings.php" class="btn btn-primary"><i class="fa fa-calendar"></i> My Appointments</a>
          <a href="index.php" class="btn btn-outline"><i class="fa fa-home"></i> Back to Home</a>
        </div>
        
        <div class="confirmation-note">
          <i class="fa fa-info-circle"></i>
          <p>A confirmation email has been sent to your email address. If you need to reschedule or cancel, please contact us at <strong><?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256 750 900 134'); ?></strong>.</p>
        </div>
      </div>
      
      <div class="confirmation-sidebar">
        <div class="info-card">
          <div class="info-card-icon"><i class="fa fa-map-marker-alt"></i></div>
          <div>
            <h4>Visit Us</h4>
            <p><?php echo nl2br((isset($settings['contact_address']) ? $settings['contact_address'] : "Kabwohe-Sheema, Uganda")); ?></p>
          </div>
        </div>
        <div class="info-card">
          <div class="info-card-icon"><i class="fa fa-phone"></i></div>
          <div>
            <h4>Call Us</h4>
            <p><a href="tel:<?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256750900134'); ?>"><?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256 750 900 134'); ?></a></p>
          </div>
        </div>
        <div class="info-card">
          <div class="info-card-icon"><i class="fa fa-envelope"></i></div>
          <div>
            <h4>Email Us</h4>
            <p><a href="mailto:<?php echo (isset($settings['contact_email']) ? $settings['contact_email'] : 'info@witadbridal.com'); ?>"><?php echo (isset($settings['contact_email']) ? $settings['contact_email'] : 'info@witadbridal.com'); ?></a></p>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</section>

<style>
.confirmation-wrapper {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 40px;
  align-items: start;
  max-width: 1000px;
  margin: 0 auto;
}
.confirmation-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 24px;
  padding: 48px 40px;
  text-align: center;
  box-shadow: 0 8px 40px var(--shadow);
}
.confirmation-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #10b981, #059669);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  font-size: 2.5rem;
  color: #fff;
  box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
}
.confirmation-card h2 {
  font-size: 1.8rem;
  margin-bottom: 8px;
}
.confirmation-sub {
  color: var(--text-lt);
  font-size: 0.95rem;
  max-width: 480px;
  margin: 0 auto 32px;
  line-height: 1.6;
}
.booking-details {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 8px 0;
  margin-bottom: 32px;
  text-align: left;
}
.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 24px;
  border-bottom: 1px solid var(--border);
}
.detail-row:last-child {
  border-bottom: none;
}
.detail-row.highlight {
  background: linear-gradient(135deg, rgba(201,168,76,0.06), rgba(244,167,185,0.04));
}
.detail-row.highlight .detail-value {
  color: var(--accent);
  font-weight: 600;
}
.detail-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.85rem;
  color: var(--text-lt);
}
.detail-label i {
  color: var(--accent);
  width: 16px;
}
.detail-value {
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text);
}
.status-badge {
  padding: 4px 14px;
  border-radius: 50px;
  font-size: 0.75rem;
  font-weight: 600;
}
.status-pending {
  background: rgba(245,158,11,0.1);
  color: #f59e0b;
}
.confirmation-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 28px;
}
.confirmation-note {
  background: linear-gradient(135deg, rgba(59,130,246,0.06), rgba(59,130,246,0.02));
  border: 1px solid rgba(59,130,246,0.15);
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  gap: 12px;
  align-items: flex-start;
  text-align: left;
}
.confirmation-note i {
  color: #3b82f6;
  font-size: 1.1rem;
  margin-top: 2px;
  flex-shrink: 0;
}
.confirmation-note p {
  font-size: 0.85rem;
  color: var(--text-lt);
  line-height: 1.6;
  margin: 0;
}

.confirmation-sidebar {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.confirmation-sidebar .info-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  gap: 14px;
  align-items: flex-start;
}
.confirmation-sidebar .info-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.confirmation-sidebar .info-card h4 {
  font-size: 0.95rem;
  margin-bottom: 4px;
}
.confirmation-sidebar .info-card p {
  font-size: 0.85rem;
  color: var(--text-lt);
  line-height: 1.6;
  margin: 0;
}
.confirmation-sidebar .info-card a {
  color: var(--accent);
  text-decoration: none;
}

@media (max-width: 768px) {
  .confirmation-wrapper {
    grid-template-columns: 1fr;
  }
  .confirmation-card {
    padding: 32px 20px;
  }
  .confirmation-actions {
    flex-direction: column;
  }
  .detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }
}
</style>

<?php require_once 'footer.php'; ?>