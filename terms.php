<?php
$pageTitle = 'Terms of Service';
$pageDesc = 'Read the Terms of Service for Witad Bridal Collection. Understand our policies for bridal gown sales, rentals, appointments, and services in Kabwohe-Sheema, Uganda.';
$canonical = 'https://witadbridal.com/terms';
require_once 'header.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Legal</span>
      <h1>Terms of Service</h1>
      <p class="page-sub">Please read these terms carefully before using our services.</p>
    </div>
  </div>
</section>

<!-- TERMS CONTENT -->
<section class="section">
  <div class="container">
    <div class="legal-layout">
      <!-- SIDEBAR NAV -->
      <aside class="legal-sidebar">
        <div class="legal-nav">
          <h4>Legal</h4>
          <ul>
            <li><a href="terms.php" class="active"><i class="fa fa-file-contract"></i> Terms of Service</a></li>
            <li><a href="privacy.php"><i class="fa fa-shield-alt"></i> Privacy Policy</a></li>
            <li><a href="contact.php"><i class="fa fa-envelope"></i> Contact Us</a></li>
          </ul>
        </div>
        <div class="legal-nav contact-card">
          <h4>Questions?</h4>
          <p>If you have any questions about our terms, please contact us.</p>
          <a href="contact.php" class="btn btn-primary btn-sm" style="width:100%;">
            <i class="fa fa-phone"></i> Contact Us
          </a>
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <article class="legal-content">
        <div class="legal-section">
          <h2>1. Introduction</h2>
          <p>Welcome to Witad Bridal Collection. These Terms of Service govern your use of our website, products, and services. By accessing or using our services, you agree to be bound by these terms.</p>
          <p>Witad Bridal Collection is a bridal boutique located in Kabwohe-Sheema, Uganda, specializing in Mushanana, bridal accessories, and related services.</p>
        </div>

        <div class="legal-section">
          <h2>2. Services</h2>
          <p>We offer the following services:</p>
          <ul>
            <li><strong>Bridal Gown Sales</strong> - Purchase of new and designer Mushanana</li>
            <li><strong>Bridal Gown Rentals</strong> - Short-term rental of select Mushanana</li>
            <li><strong>Bridal Styling</strong> - Professional consultation and styling services</li>
            <li><strong>Bridesmaids Dresses</strong> - Coordinated bridal party attire</li>
            <li><strong>Bridal Accessories</strong> - Veils, jewelry, shoes, and finishing touches</li>
            <li><strong>Alterations & Fittings</strong> - Custom tailoring and adjustments</li>
          </ul>
        </div>

        <div class="legal-section">
          <h2>3. Appointments & Bookings</h2>
          <p>All bridal consultations and fittings require advance booking. We recommend scheduling appointments at least 2 weeks in advance, especially during peak wedding season.</p>
          <p>Cancellations must be made at least 24 hours before the scheduled appointment. Late cancellations may incur a fee.</p>
        </div>

        <div class="legal-section">
          <h2>4. Payments & Deposits</h2>
          <p>A non-refundable deposit of 50% is required to secure any gown purchase or rental. The remaining balance is due before delivery or pickup.</p>
          <p>We accept the following payment methods:</p>
          <ul>
            <li>Cash</li>
            <li>Mobile Money (MTN, Airtel)</li>
            <li>Bank Transfer</li>
            <li>Credit/Debit Cards</li>
          </ul>
        </div>

        <div class="legal-section">
          <h2>5. Gown Rentals</h2>
          <p>Rental gowns must be returned within the agreed rental period. Late returns will incur additional daily charges.</p>
          <p>Renters are responsible for any damage beyond normal wear and tear. A security deposit may be required for high-value rentals.</p>
          <p>All rental gowns are professionally cleaned before and after each rental.</p>
        </div>

        <div class="legal-section">
          <h2>6. Alterations & Returns</h2>
          <p>Alterations are included in the purchase price for standard adjustments. Complex modifications may incur additional charges.</p>
          <p>Due to the custom nature of bridal wear, all sales are final. Gowns may be exchanged for store credit within 7 days of purchase, provided they are unworn and in original condition.</p>
        </div>

        <div class="legal-section">
          <h2>7. Intellectual Property</h2>
          <p>All content on this website, including images, text, and designs, is the property of Witad Bridal Collection and may not be used without permission.</p>
        </div>

        <div class="legal-section">
          <h2>8. Limitation of Liability</h2>
          <p>Witad Bridal Collection shall not be liable for any indirect, incidental, or consequential damages arising from the use of our services.</p>
        </div>

        <div class="legal-section">
          <h2>9. Changes to Terms</h2>
          <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to this page.</p>
        </div>

        <div class="legal-section">
          <h2>10. Contact Information</h2>
          <p>For questions about these terms, please contact us:</p>
          <ul>
            <li><strong>Email:</strong> info@witadbridal.com</li>
            <li><strong>Phone:</strong> +256 750 900 134</li>
            <li><strong>Address:</strong> Kabwohe-Sheema, Uganda</li>
          </ul>
        </div>

        <div class="legal-date">
          <p><strong>Last Updated:</strong> July 2025</p>
        </div>
      </article>
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

/* LEGAL LAYOUT */
.legal-layout {
  display: grid; grid-template-columns: 260px 1fr;
  gap: 48px; align-items: start;
}
.legal-sidebar { position: sticky; top: 100px; height: fit-content; }
.legal-nav {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 24px; margin-bottom: 20px;
}
.legal-nav h4 { font-size: 1rem; margin-bottom: 16px; }
.legal-nav ul { list-style: none; }
.legal-nav li { margin-bottom: 8px; }
.legal-nav a {
  font-size: 0.88rem; color: var(--text-lt); display: flex;
  align-items: center; gap: 10px; padding: 8px 12px;
  border-radius: 8px; transition: all 0.3s;
}
.legal-nav a:hover, .legal-nav a.active {
  background: rgba(201,168,76,0.1); color: var(--accent);
}
.legal-nav a i { font-size: 0.85rem; width: 18px; }
.contact-card p { font-size: 0.85rem; color: var(--text-lt); margin-bottom: 16px; line-height: 1.6; }

/* LEGAL CONTENT */
.legal-content { min-width: 0; }
.legal-section {
  margin-bottom: 40px; padding-bottom: 40px;
  border-bottom: 1px solid var(--border);
}
.legal-section:last-of-type { border-bottom: none; }
.legal-section h2 {
  font-size: 1.3rem; margin-bottom: 16px; color: var(--text);
}
.legal-section p {
  color: var(--text-lt); font-size: 0.95rem; line-height: 1.85;
  margin-bottom: 12px;
}
.legal-section ul { margin: 12px 0; padding-left: 20px; }
.legal-section li {
  color: var(--text-lt); font-size: 0.92rem; line-height: 1.8;
  margin-bottom: 6px;
}
.legal-section li strong { color: var(--text); }
.legal-date {
  background: rgba(201,168,76,0.05); border: 1px solid rgba(201,168,76,0.15);
  border-radius: 12px; padding: 20px 24px; margin-top: 20px;
}
.legal-date p { margin: 0; font-size: 0.9rem; color: var(--text-lt); }

/* RESPONSIVE */
@media (max-width: 768px) {
  .legal-layout { grid-template-columns: 1fr; }
  .legal-sidebar { position: static; }
  .page-hero-content { padding: 80px 20px 30px; }
}
</style>

<?php require_once 'footer.php'; ?>