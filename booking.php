<?php
$pageTitle = 'Book Appointment';
$pageDesc = 'Schedule your personalized bridal consultation at Witad Bridal Collection.';
require_once 'header.php';

// Get settings
$settings = array();
$settingsRows = fetchAll("SELECT setting_key, setting_value FROM settings");
foreach ($settingsRows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$bookingStart = (isset($settings['booking_start_time']) ? $settings['booking_start_time'] : '08:00');
$bookingEnd = (isset($settings['booking_end_time']) ? $settings['booking_end_time'] : '19:00');
$bookingInterval = intval((isset($settings['booking_interval']) ? $settings['booking_interval'] : 60));
$bookingDaysAhead = intval((isset($settings['booking_days_ahead']) ? $settings['booking_days_ahead'] : 30));

// Get current month/year or from URL
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month/year
if ($month < 1 || $month > 12) $month = date('n');
if ($year < date('Y') || $year > date('Y') + 1) $year = date('Y');

// Navigation
$prevMonth = $month == 1 ? 12 : $month - 1;
$prevYear = $month == 1 ? $year - 1 : $year;
$nextMonth = $month == 12 ? 1 : $month + 1;
$nextYear = $month == 12 ? $year + 1 : $year;

// Calendar data
$firstDay = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDay);
$startDayOfWeek = date('w', $firstDay); // 0 = Sunday
$monthName = date('F Y', $firstDay);

// Get booked slots for this month
$bookedSlots = array();
$bookings = fetchAll(
    "SELECT booking_date, booking_time, status FROM bookings WHERE booking_date BETWEEN ? AND ? AND status != 'cancelled'",
    "ss",
    array("$year-" . sprintf("%02d", $month) . "-01", "$year-" . sprintf("%02d", $month) . "-$daysInMonth")
);
foreach ($bookings as $b) {
    $bookedSlots[$b['booking_date']][] = $b['booking_time'];
}

// Generate time slots
function getTimeSlots($date, $start, $end, $interval, $booked) {
    $slots = array();
    $current = strtotime($date . ' ' . $start);
    $endTime = strtotime($date . ' ' . $end);
    $now = time();

    while ($current < $endTime) {
        $slotTime = date('H:i', $current);
        $slotDateTime = strtotime($date . ' ' . $slotTime);
        $isBooked = isset($booked[$date]) && in_array($slotTime . ':00', $booked[$date]);
        $isPast = $slotDateTime < $now;

        $slots[] = array(
            'time' => $slotTime,
            'available' => !$isBooked && !$isPast,
            'booked' => $isBooked,
            'past' => $isPast
        );
        $current += $interval * 60;
    }
    return $slots;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {
    if (!verifyCsrf((isset($_POST['csrf_token']) ? $_POST['csrf_token'] : ''))) {
        setFlash('error', 'Invalid request. Please try again.');
        header('Location: booking.php');
        exit;
    }

    $fullName = sanitize((isset($_POST['full_name']) ? $_POST['full_name'] : ''));
    $phone = sanitize((isset($_POST['phone']) ? $_POST['phone'] : ''));
    $email = sanitize((isset($_POST['email']) ? $_POST['email'] : ''));
    $weddingDate = (isset($_POST['wedding_date']) ? $_POST['wedding_date'] : null);
    $serviceNeeded = sanitize((isset($_POST['service_needed']) ? $_POST['service_needed'] : 'General Inquiry'));
    $notes = sanitize((isset($_POST['notes']) ? $_POST['notes'] : ''));
    $bookingDate = (isset($_POST['booking_date']) ? $_POST['booking_date'] : '');
    $bookingTime = (isset($_POST['booking_time']) ? $_POST['booking_time'] : '');

    if (empty($fullName) || empty($phone) || empty($bookingDate) || empty($bookingTime)) {
        setFlash('error', 'Please fill in all required fields.');
    } else {
        // Check if slot is still available
        $existing = fetchOne(
            "SELECT id FROM bookings WHERE booking_date = ? AND booking_time = ? AND status != 'cancelled'",
            "ss", array($bookingDate, $bookingTime)
        );

        if ($existing) {
            setFlash('error', 'This time slot has just been booked. Please select another.');
        } else {
            $customerId = isLoggedIn() ? $_SESSION['customer_id'] : null;
            query(
                "INSERT INTO bookings (customer_id, full_name, phone, email, wedding_date, service_needed, notes, booking_date, booking_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
                "issssssss",
                array($customerId, $fullName, $phone, $email, $weddingDate, $serviceNeeded, $notes, $bookingDate, $bookingTime)
            );

            $bookingId = insertId();

            // Send confirmation email
            $emailBody = emailTemplate('Appointment Confirmation', '
                <h2>Your Appointment is Confirmed!</h2>
                <p>Hello ' . $fullName . ',</p>
                <p>Thank you for booking an appointment with Witad Bridal Collection. Here are your details:</p>
                <div style="background:#f9f9f9;padding:20px;border-radius:12px;margin:20px 0;">
                    <p><strong>Date:</strong> ' . formatDate($bookingDate) . '</p>
                    <p><strong>Time:</strong> ' . date('h:i A', strtotime($bookingTime)) . '</p>
                    <p><strong>Service:</strong> ' . $serviceNeeded . '</p>
                    <p><strong>Location:</strong> ' . ((isset($settings['contact_address']) ? $settings['contact_address'] : 'Kabwohe-Sheema, Uganda')) . '</p>
                </div>
                <p>We look forward to seeing you! If you need to reschedule, please contact us at ' . ((isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256 750 900 134')) . '.</p>
                <a href="' . $canonical . '" class="btn">View Appointment</a>
            ');

            if ($email) {
                sendEmail($email, 'Appointment Confirmation - Witad Bridal', $emailBody);
            }

            setFlash('success', 'Your appointment has been booked for ' . formatDate($bookingDate) . ' at ' . date('h:i A', strtotime($bookingTime)) . '!');
            header('Location: booking-confirmation.php?id=' . $bookingId);
            exit;
        }
    }
}
?>

<div class="breadcrumbs">
  <div class="container">
    <h1>Book Appointment</h1>
    <div class="crumb">
      <a href="index.php">Home</a> <i class="fa fa-chevron-right"></i>
      <span>Book Appointment</span>
    </div>
  </div>
</div>

<section class="section" style="padding-top:60px;">
  <div class="container">
    <div class="booking-layout">
      <!-- CALENDAR -->
      <div class="calendar-section">
        <div class="calendar-header">
          <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="cal-nav"><i class="fa fa-chevron-left"></i></a>
          <h2><?php echo $monthName; ?></h2>
          <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="cal-nav"><i class="fa fa-chevron-right"></i></a>
        </div>

        <div class="calendar-grid">
          <div class="cal-day-label">Sun</div>
          <div class="cal-day-label">Mon</div>
          <div class="cal-day-label">Tue</div>
          <div class="cal-day-label">Wed</div>
          <div class="cal-day-label">Thu</div>
          <div class="cal-day-label">Fri</div>
          <div class="cal-day-label">Sat</div>

          <?php
          // Empty cells before first day
          for ($i = 0; $i < $startDayOfWeek; $i++) {
              echo '<div class="cal-day empty"></div>';
          }

          // Days
          $today = date('Y-m-d');
          $maxDate = date('Y-m-d', strtotime("+$bookingDaysAhead days"));

          for ($day = 1; $day <= $daysInMonth; $day++) {
              $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
              $isToday = $date == $today;
              $isPast = $date < $today;
              $isFuture = $date > $maxDate;
              $hasBookings = isset($bookedSlots[$date]);
              $slots = !$isPast && !$isFuture ? getTimeSlots($date, $bookingStart, $bookingEnd, $bookingInterval, $bookedSlots) : array();
              $availableSlots = array_filter($slots, create_function('$s', 'return $s["available"];'));
              $isSelectable = !$isPast && !$isFuture && count($availableSlots) > 0;

              $classes = array('cal-day');
              if ($isToday) $classes[] = 'today';
              if ($isPast) $classes[] = 'past';
              if ($isFuture) $classes[] = 'future';
              if ($isSelectable) $classes[] = 'selectable';
              if ($hasBookings) $classes[] = 'has-bookings';
              ?>
              <div class="<?php echo implode(' ', $classes); ?>" 
                   data-date="<?php echo $date; ?>"
                   <?php if ($isSelectable): ?>onclick="selectDate('<?php echo $date; ?>')"<?php endif; ?>>
                <span class="day-num"><?php echo $day; ?></span>
                <?php if ($hasBookings): ?>
                <span class="booking-dot"></span>
                <?php endif; ?>
                <?php if ($isToday): ?>
                <span class="today-label">Today</span>
                <?php endif; ?>
              </div>
              <?php
          }
          ?>
        </div>

        <div class="calendar-legend">
          <span><span class="dot available"></span> Available</span>
          <span><span class="dot booked"></span> Booked</span>
          <span><span class="dot past"></span> Past</span>
          <span><span class="dot today"></span> Today</span>
        </div>
      </div>

      <!-- BOOKING FORM -->
      <div class="booking-form-section">
        <div class="form-card" id="bookingForm">
          <h3 style="font-size:1.4rem;margin-bottom:6px;">
            <i class="fa fa-calendar-check" style="color:var(--accent);"></i> Book Your Visit
          </h3>
          <p style="font-size:0.87rem;color:var(--text-lt);margin-bottom:28px;">
            Select a date on the calendar, then choose your preferred time slot.
          </p>

          <div id="selectedDateDisplay" style="display:none;margin-bottom:20px;padding:14px;background:linear-gradient(135deg,rgba(201,168,76,0.1),rgba(244,167,185,0.1));border-radius:12px;border:1px solid var(--accent-lt);">
            <strong style="color:var(--accent);"><i class="fa fa-calendar"></i> Selected Date:</strong>
            <span id="dateText" style="font-weight:600;"></span>
          </div>

          <form method="POST" action="" id="appointmentForm">
            <?php echo csrfField(); ?>
            <input type="hidden" name="booking_date" id="bookingDate" required />
            <input type="hidden" name="booking_time" id="bookingTime" required />

            <div id="timeSlots" style="display:none;margin-bottom:24px;">
              <label style="font-size:0.85rem;font-weight:600;margin-bottom:10px;display:block;">Select Time</label>
              <div class="time-slots-grid" id="timeSlotsGrid"></div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" placeholder="Your full name" required 
                  value="<?php echo isLoggedIn() ? sanitize((isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : '')) : ''; ?>" />
              </div>
              <div class="form-group">
                <label>Phone Number *</label>
                <input type="tel" name="phone" placeholder="+256 XXX XXX XXX" required
                  value="<?php echo isLoggedIn() ? sanitize((isset($_SESSION['customer_phone']) ? $_SESSION['customer_phone'] : '')) : ''; ?>" />
              </div>
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input type="email" name="email" placeholder="your@email.com"
                value="<?php echo isLoggedIn() ? sanitize((isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : '')) : ''; ?>" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Wedding Date</label>
                <input type="date" name="wedding_date" />
              </div>
              <div class="form-group">
                <label>Service Needed</label>
                <select name="service_needed">
                  <option value="">Select a service...</option>
                  <option value="Bridal Gown Sales">Bridal Gown Sales</option>
                  <option value="Bridal Gown Rental">Bridal Gown Rental</option>
                  <option value="Bridal Styling">Bridal Styling</option>
                  <option value="Bridesmaids Dresses">Bridesmaids Dresses</option>
                  <option value="Accessories">Accessories</option>
                  <option value="Alterations & Fittings">Alterations & Fittings</option>
                  <option value="General Inquiry">General Inquiry</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Additional Notes</label>
              <textarea name="notes" rows="3" placeholder="Tell us about your wedding vision..."></textarea>
            </div>

            <button type="submit" name="book_appointment" class="submit-btn" id="submitBtn" disabled>
              <i class="fa fa-calendar-check"></i> Confirm Appointment
            </button>
          </form>
        </div>

        <div class="booking-info">
          <div class="info-card">
            <div class="info-card-icon"><i class="fa fa-clock"></i></div>
            <div>
              <h4>Working Hours</h4>
              <p><?php echo nl2br((isset($settings['business_hours']) ? $settings['business_hours'] : "Mon – Fri: 8:00 AM – 7:00 PM
Sat: 9:00 AM – 6:00 PM
Sun: By Appointment")); ?></p>
            </div>
          </div>
          <div class="info-card">
            <div class="info-card-icon"><i class="fa fa-map-marker-alt"></i></div>
            <div>
              <h4>Our Location</h4>
              <p><?php echo (isset($settings['contact_address']) ? $settings['contact_address'] : 'Kabwohe-Sheema, Uganda'); ?></p>
            </div>
          </div>
          <div class="info-card">
            <div class="info-card-icon"><i class="fa fa-phone"></i></div>
            <div>
              <h4>Need Help?</h4>
              <p><a href="tel:<?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256750900134'); ?>"><?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256 750 900 134'); ?></a></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Pre-loaded booked slots data
const bookedSlots = <?php echo json_encode($bookedSlots); ?>;
const bookingStart = '<?php echo $bookingStart; ?>';
const bookingEnd = '<?php echo $bookingEnd; ?>';
const bookingInterval = <?php echo $bookingInterval; ?>;

function selectDate(date) {
    document.getElementById('bookingDate').value = date;
    document.getElementById('dateText').textContent = new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('selectedDateDisplay').style.display = 'block';

    // Generate time slots
    const slotsContainer = document.getElementById('timeSlotsGrid');
    slotsContainer.innerHTML = '';

    const booked = bookedSlots[date] || [];
    let current = new Date(date + 'T' + bookingStart);
    const end = new Date(date + 'T' + bookingEnd);
    const now = new Date();
    let hasAvailable = false;

    while (current < end) {
        const timeStr = current.toTimeString().slice(0, 5);
        const isBooked = booked.indexOf(timeStr + ':00') !== -1;
        const isPast = current < now;
        const available = !isBooked && !isPast;

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'time-slot' + (available ? '' : ' disabled') + (isBooked ? ' booked' : '');
        btn.textContent = current.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

        if (available) {
            hasAvailable = true;
            btn.onclick = function() {
                document.querySelectorAll('.time-slot').forEach(function(s) { s.classList.remove('selected'); });
                this.classList.add('selected');
                document.getElementById('bookingTime').value = timeStr + ':00';
                document.getElementById('submitBtn').disabled = false;
            };
        }

        slotsContainer.appendChild(btn);
        current.setMinutes(current.getMinutes() + bookingInterval);
    }

    document.getElementById('timeSlots').style.display = 'block';

    if (!hasAvailable) {
        slotsContainer.innerHTML = '<p style="color:var(--text-lt);text-align:center;padding:20px;">No available slots for this date. Please select another date.</p>';
    }

    // Scroll to form on mobile
    if (window.innerWidth < 768) {
        document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<style>
.booking-layout {
  display: grid; grid-template-columns: 1fr 420px;
  gap: 40px; align-items: start;
}
.calendar-section { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 28px; }
.calendar-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 24px;
}
.calendar-header h2 { font-size: 1.4rem; }
.cal-nav {
  width: 40px; height: 40px; border-radius: 10px;
  background: var(--bg); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--text); transition: all 0.3s; cursor: pointer;
}
.cal-nav:hover { background: var(--accent); border-color: var(--accent); color: #fff; }
.calendar-grid {
  display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;
}
.cal-day-label {
  text-align: center; font-size: 0.75rem; font-weight: 600;
  color: var(--text-lt); padding: 10px 0; text-transform: uppercase; letter-spacing: 1px;
}
.cal-day {
  aspect-ratio: 1; display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  border-radius: 12px; position: relative; cursor: default;
  background: var(--bg); border: 1.5px solid transparent;
  transition: all 0.3s;
}
.cal-day.empty { background: transparent; }
.cal-day.past { color: var(--text-lt); opacity: 0.5; }
.cal-day.future { color: var(--text-lt); opacity: 0.5; }
.cal-day.selectable {
  cursor: pointer; background: var(--bg);
  border-color: var(--border);
}
.cal-day.selectable:hover {
  background: linear-gradient(135deg, rgba(201,168,76,0.15), rgba(244,167,185,0.1));
  border-color: var(--accent); transform: scale(1.05);
}
.cal-day.today { background: linear-gradient(135deg, var(--accent), var(--accent-dk)); color: #fff; }
.cal-day.today.selectable:hover { color: #fff; }
.cal-day.has-bookings.selectable .booking-dot { background: var(--primary); }
.cal-day .day-num { font-size: 0.95rem; font-weight: 600; }
.cal-day .today-label { font-size: 0.6rem; margin-top: 2px; }
.cal-day .booking-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: var(--accent); position: absolute; bottom: 6px;
}
.calendar-legend {
  display: flex; gap: 20px; margin-top: 20px; justify-content: center;
  flex-wrap: wrap;
}
.calendar-legend span { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--text-lt); }
.calendar-legend .dot { width: 10px; height: 10px; border-radius: 50%; }
.calendar-legend .dot.available { background: var(--bg); border: 1.5px solid var(--border); }
.calendar-legend .dot.booked { background: var(--primary); }
.calendar-legend .dot.past { background: var(--text-lt); opacity: 0.5; }
.calendar-legend .dot.today { background: var(--accent); }

.time-slots-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;
}
.time-slot {
  padding: 10px; border: 1.5px solid var(--border);
  border-radius: 10px; background: var(--bg);
  font-family: 'Poppins', sans-serif; font-size: 0.82rem;
  font-weight: 500; cursor: pointer; transition: all 0.3s;
}
.time-slot:hover:not(.disabled) {
  border-color: var(--accent); background: rgba(201,168,76,0.1);
}
.time-slot.selected {
  background: var(--accent); border-color: var(--accent); color: #fff;
}
.time-slot.disabled {
  opacity: 0.4; cursor: not-allowed; text-decoration: line-through;
}
.time-slot.booked { background: rgba(244,167,185,0.2); border-color: var(--primary); color: var(--primary); }

.booking-form-section { position: sticky; top: 100px; }
.booking-info { margin-top: 24px; }
.booking-info .info-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 20px; margin-bottom: 12px;
  display: flex; gap: 14px; align-items: flex-start;
}
.booking-info .info-card-icon {
  width: 42px; height: 42px; border-radius: 12px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1rem; flex-shrink: 0;
}
.booking-info .info-card h4 { font-size: 0.95rem; margin-bottom: 4px; }
.booking-info .info-card p { font-size: 0.85rem; color: var(--text-lt); line-height: 1.6; }

/* FORM STYLES */
.form-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 24px; padding: 36px;
  box-shadow: 0 8px 40px var(--shadow);
}
.form-group { margin-bottom: 18px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-card label {
  display: block; font-size: 0.83rem; font-weight: 500;
  color: var(--text); margin-bottom: 6px;
}
.form-card input, .form-card select, .form-card textarea {
  width: 100%; padding: 12px 16px;
  border: 1.5px solid var(--border); border-radius: 12px;
  background: var(--bg); font-family: 'Poppins', sans-serif;
  font-size: 0.88rem; color: var(--text);
  transition: border-color 0.3s, box-shadow 0.3s; outline: none;
}
.form-card input:focus, .form-card select:focus, .form-card textarea:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
}
.form-card textarea { resize: vertical; min-height: 80px; }
.submit-btn {
  width: 100%; padding: 15px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dk));
  color: #fff; border: none; border-radius: 50px;
  font-family: 'Poppins', sans-serif; font-size: 1rem;
  font-weight: 600; cursor: pointer; letter-spacing: 0.5px;
  transition: all 0.3s; box-shadow: 0 4px 18px rgba(201,168,76,0.4);
}
.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px); box-shadow: 0 8px 28px rgba(201,168,76,0.5);
}
.submit-btn:disabled {
  opacity: 0.5; cursor: not-allowed;
}

@media (max-width: 1024px) {
  .booking-layout { grid-template-columns: 1fr; }
  .booking-form-section { position: static; }
}
@media (max-width: 768px) {
  .form-row { grid-template-columns: 1fr; }
  .time-slots-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<?php require_once 'footer.php'; ?>