<!-- FOOTER -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="logo" style="margin-bottom:18px;">
            <span class="logo-name" style="color:#fff;font-size:1.7rem;">Witad</span>
            <span class="logo-tag">Bridal Collection</span>
          </div>
          <p>Uganda's premier bridal destination — where every bride finds her perfect look, crafted with elegance, quality, and heart.</p>
          <div class="footer-social">
            <a href="<?php echo (isset($settings['facebook_url']) ? $settings['facebook_url'] : '#'); ?>" title="Facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="<?php echo (isset($settings['instagram_url']) ? $settings['instagram_url'] : '#'); ?>" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="<?php echo (isset($settings['tiktok_url']) ? $settings['tiktok_url'] : '#'); ?>" title="TikTok" target="_blank"><i class="fab fa-tiktok"></i></a>
            <a href="https://wa.me/<?php echo (isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : ''); ?>" title="WhatsApp" target="_blank"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
        <div class="footer-col">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="index.php"><i class="fa fa-angle-right"></i>Home</a></li>
            <li><a href="about.php"><i class="fa fa-angle-right"></i>About Us</a></li>
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Services</a></li>
            <li><a href="products.php"><i class="fa fa-angle-right"></i>Collection</a></li>
            <li><a href="gallery.php"><i class="fa fa-angle-right"></i>Gallery</a></li>
            <li><a href="blog.php"><i class="fa fa-angle-right"></i>Journal</a></li>
            <li><a href="testimonials.php"><i class="fa fa-angle-right"></i>Reviews</a></li>
            <li><a href="faq.php"><i class="fa fa-angle-right"></i>FAQ</a></li>
            <li><a href="contact.php"><i class="fa fa-angle-right"></i>Contact</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Our Services</h4>
          <ul class="footer-links">
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Bridal Gown Sales</a></li>
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Gown Rentals</a></li>
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Bridal Styling</a></li>
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Bridesmaids Dresses</a></li>
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Bridal Accessories</a></li>
            <li><a href="services.php"><i class="fa fa-angle-right"></i>Alterations & Fittings</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Contact Us</h4>
          <div class="footer-contact-item">
            <i class="fa fa-map-marker-alt"></i>
            <span><?php echo (isset($settings['contact_address']) ? $settings['contact_address'] : 'Kabwohe-Sheema, Uganda'); ?></span>
          </div>
          <div class="footer-contact-item">
            <i class="fa fa-phone"></i>
            <span><a href="tel:<?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : ''); ?>"><?php echo (isset($settings['contact_phone']) ? $settings['contact_phone'] : '+256 750 900 134'); ?></a></span>
          </div>
          <div class="footer-contact-item">
            <i class="fa fa-envelope"></i>
            <span><a href="mailto:<?php echo (isset($settings['contact_email']) ? $settings['contact_email'] : ''); ?>"><?php echo (isset($settings['contact_email']) ? $settings['contact_email'] : 'info@witadbridal.com'); ?></a></span>
          </div>
          <div class="footer-contact-item">
            <i class="fa fa-clock"></i>
            <span><?php echo nl2br((isset($settings['business_hours']) ? $settings['business_hours'] : "Mon-Fri: 8AM-7PM\nSat: 9AM-6PM\nSun: By Appointment")); ?></span>
          </div>
          <div style="margin-top:24px;">
            <h4 style="font-size:0.9rem;margin-bottom:12px;">Newsletter</h4>
            <form method="POST" action="newsletter.php" style="display:flex;gap:8px;">
              <?php echo csrfField(); ?>
              <input type="email" name="email" placeholder="Your email" required
                style="flex:1;padding:10px 14px;border:1px solid rgba(255,255,255,0.2);border-radius:8px;background:rgba(255,255,255,0.05);color:#fff;font-size:0.85rem;outline:none;" />
              <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 16px;">
                <i class="fa fa-paper-plane"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <a href="index.php">Witad Bridal Collection</a>. All rights reserved. Made with <i class="fa fa-heart" style="color:var(--primary);"></i> in Uganda.</p>
        <div class="footer-bottom-links">
          <a href="privacy.php">Privacy Policy</a>
          <a href="terms.php">Terms of Service</a>
          <a href="contact.php">Book Appointment</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- FLOATING WHATSAPP -->
  <a href="https://wa.me/<?php echo (isset($settings['whatsapp_number']) ? $settings['whatsapp_number'] : '256750900134'); ?>" class="fab-whatsapp" target="_blank" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- BACK TO TOP -->
  <div class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fa fa-arrow-up"></i>
  </div>

  <script>
    /* NAVBAR SCROLL */
    window.addEventListener('scroll', () => {
      const nav = document.getElementById('navbar');
      nav.classList.toggle('scrolled', window.scrollY > 60);
      const bt = document.getElementById('backTop');
      if (bt) bt.classList.toggle('visible', window.scrollY > 400);
    });

    /* MOBILE MENU */
    function openMobile() { document.getElementById('mobileMenu').classList.add('open'); }
    function closeMobile() { document.getElementById('mobileMenu').classList.remove('open'); }

    /* LIGHTBOX */
    function openLightbox(src) {
      const lb = document.getElementById('lightbox');
      if (!lb) return;
      document.getElementById('lightboxImg').src = src;
      lb.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
      const lb = document.getElementById('lightbox');
      if (lb) {
        lb.classList.remove('open');
        document.body.style.overflow = '';
      }
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

    /* FAQ ACCORDION */
    function toggleFaq(btn) {
      const item = btn.parentElement;
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    }

    /* GALLERY FILTER */
    function filterGallery(cat, btn) {
      document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.querySelectorAll('#galleryMasonry .gal-item').forEach(item => {
        if (cat === 'all' || item.dataset.cat === cat) {
          item.style.display = '';
          item.style.animation = 'fadeIn .4s ease';
        } else {
          item.style.display = 'none';
        }
      });
    }
  </script>

  <style>
    footer {
      background: linear-gradient(160deg, #1e1018 0%, #2a1a25 50%, #1a1020 100%);
      color: #fff; padding: 80px 0 0;
    }
    .footer-grid {
      display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr;
      gap: 50px; padding-bottom: 60px;
    }
    .footer-brand p {
      font-size: 0.87rem; color: rgba(255,255,255,0.55);
      line-height: 1.8; margin: 18px 0 24px; max-width: 280px;
    }
    .footer-social { display: flex; gap: 12px; }
    .footer-social a {
      width: 40px; height: 40px; border-radius: 10px;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.1);
      display: flex; align-items: center; justify-content: center;
      color: rgba(255,255,255,0.7); font-size: 1rem;
      transition: all 0.3s;
    }
    .footer-social a:hover {
      background: var(--accent); border-color: var(--accent); color: #fff;
    }
    .footer-col h4 {
      font-size: 1rem; color: #fff; margin-bottom: 22px;
      position: relative; padding-bottom: 12px;
    }
    .footer-col h4::after {
      content: ''; position: absolute; bottom: 0; left: 0;
      width: 36px; height: 2px; background: var(--accent);
    }
    .footer-links { list-style: none; }
    .footer-links li { margin-bottom: 12px; }
    .footer-links a {
      font-size: 0.87rem; color: rgba(255,255,255,0.55);
      transition: color 0.3s; display: flex; align-items: center; gap: 8px;
    }
    .footer-links a:hover { color: var(--accent); }
    .footer-links i { font-size: 0.65rem; color: var(--accent); }
    .footer-contact-item {
      display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px;
    }
    .footer-contact-item i {
      color: var(--accent); font-size: 1rem; margin-top: 2px; width: 18px;
    }
    .footer-contact-item span {
      font-size: 0.85rem; color: rgba(255,255,255,0.55); line-height: 1.6;
    }
    .footer-contact-item a {
      color: rgba(255,255,255,0.55); transition: color 0.3s;
    }
    .footer-contact-item a:hover { color: var(--accent); }
    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.08);
      padding: 24px 0; display: flex;
      justify-content: space-between; align-items: center;
      flex-wrap: wrap; gap: 16px;
    }
    .footer-bottom p { font-size: 0.82rem; color: rgba(255,255,255,0.35); }
    .footer-bottom a { color: var(--accent); }
    .footer-bottom-links { display: flex; gap: 24px; }
    .footer-bottom-links a {
      font-size: 0.82rem; color: rgba(255,255,255,0.35); transition: color 0.3s;
    }
    .footer-bottom-links a:hover { color: var(--accent); }

    .fab-whatsapp {
      position: fixed; bottom: 32px; right: 32px;
      width: 58px; height: 58px; background: #25d366;
      border-radius: 50%; display: flex;
      align-items: center; justify-content: center;
      color: #fff; font-size: 1.6rem;
      box-shadow: 0 6px 24px rgba(37,211,102,0.45);
      z-index: 900; transition: transform 0.3s, box-shadow 0.3s;
    }
    .fab-whatsapp:hover {
      transform: scale(1.1); box-shadow: 0 10px 32px rgba(37,211,102,0.55);
    }
    .back-top {
      position: fixed; bottom: 32px; right: 100px;
      width: 46px; height: 46px; background: var(--accent);
      border-radius: 50%; display: flex;
      align-items: center; justify-content: center;
      color: #fff; font-size: 1.1rem; z-index: 900;
      opacity: 0; transform: translateY(20px);
      transition: all 0.3s; cursor: pointer;
    }
    .back-top.visible { opacity: 1; transform: translateY(0); }

    @media (max-width: 1024px) {
      .footer-grid { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 768px) {
      .footer-grid { grid-template-columns: 1fr; gap: 36px; }
      .footer-bottom { flex-direction: column; text-align: center; }
    }
  </style>
</body>
</html>