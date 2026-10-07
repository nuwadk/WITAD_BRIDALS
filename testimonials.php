<?php
$pageTitle = 'Testimonials';
$pageDesc = 'Read what our happy brides say about Witad Bridal Collection. Real reviews from real brides in Kabwohe-Sheema, Uganda.';
$canonical = 'https://witadbridal.com/testimonials';
require_once 'header.php';

// Get all testimonials
$testimonials = fetchAll("SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order, created_at DESC");

// Calculate average rating
$avgResult = fetchOne("SELECT AVG(rating) as avg_rating FROM testimonials WHERE status = 'active'");
$avgRating = $avgResult ? round($avgResult['avg_rating'], 1) : 0;

// Get total count
$countResult = fetchOne("SELECT COUNT(*) as total FROM testimonials WHERE status = 'active'");
$totalCount = $countResult ? $countResult['total'] : 0;
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Bride Stories</span>
      <h1>Words From Our Brides</h1>
      <p class="page-sub">Real stories, real emotions, real love. Hear what our brides have to say about their experience with us.</p>

      <div class="hero-stats">
        <div class="hero-stat">
          <strong><?php echo $avgRating; ?></strong>
          <span>Average Rating</span>
          <div class="hero-stars"><?php echo str_repeat('&#9733;', floor($avgRating)); ?></div>
        </div>
        <div class="hero-stat">
          <strong><?php echo $totalCount; ?>+</strong>
          <span>Happy Brides</span>
        </div>
        <div class="hero-stat">
          <strong>100%</strong>
          <span>Would Recommend</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TESTIMONIALS GRID -->
<section class="section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Real Reviews</span>
      <h2 class="section-title">What Brides Are Saying</h2>
      <div class="divider center"></div>
    </div>

    <?php if (!empty($testimonials)): ?>
    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testi-card">
        <div class="testi-quote-mark">"</div>
        <div class="testi-stars"><?php echo str_repeat('&#9733;', $t['rating']); ?></div>
        <p class="testi-quote">"<?php echo sanitize($t['content']); ?>"</p>
        <div class="testi-author">
          <?php if (isset($t['image']) && $t['image']): ?>
          <img src="<?php echo $t['image']; ?>" alt="<?php echo sanitize($t['customer_name']); ?>" class="testi-avatar" />
          <?php else: ?>
          <div class="testi-avatar" style="background:linear-gradient(135deg,var(--accent),var(--primary));color:#fff;">
            <?php echo initials($t['customer_name']); ?>
          </div>
          <?php endif; ?>
          <div class="testi-info">
            <div class="testi-name"><?php echo sanitize($t['customer_name']); ?></div>
            <div class="testi-location">
              <?php if (isset($t['location']) && $t['location']): ?><i class="fa fa-map-marker-alt"></i> <?php echo sanitize($t['location']); ?><?php endif; ?>
              <?php if (isset($t['wedding_date']) && $t['wedding_date']): ?> &middot; <?php echo formatDate($t['wedding_date']); ?><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state" style="text-align:center;padding:80px 20px;">
      <i class="fa fa-star" style="font-size:4rem;color:var(--border);margin-bottom:20px;"></i>
      <h3 style="font-size:1.4rem;margin-bottom:10px;">No Reviews Yet</h3>
      <p style="color:var(--text-lt);">Be the first bride to share your experience with us!</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- WRITE A REVIEW -->
<section class="section review-section">
  <div class="container">
    <div class="review-layout">
      <div class="review-content">
        <span class="section-label">Share Your Story</span>
        <h2 class="section-title" style="color:#fff;">Share Your Experience</h2>
        <div class="divider"></div>
        <p style="color:rgba(255,255,255,0.7);">Your feedback helps future brides make the right choice. Tell us about your journey with Witad Bridal.</p>
        <div class="review-benefits">
          <div class="review-benefit"><i class="fa fa-check"></i> Help other brides decide</div>
          <div class="review-benefit"><i class="fa fa-check"></i> Share your love story</div>
          <div class="review-benefit"><i class="fa fa-check"></i> Get featured on our site</div>
        </div>
      </div>
      <div class="review-form-card">
        <h3>Write a Review</h3>
        <form method="POST" action="testimonial-submit.php">
          <?php echo csrfField(); ?>
          <div class="form-group">
            <label>Your Name</label>
            <input type="text" name="customer_name" placeholder="Full name" required />
          </div>
          <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" placeholder="City, Uganda" />
          </div>
          <div class="form-group">
            <label>Rating</label>
            <div class="star-rating">
              <input type="radio" name="rating" value="5" id="star5" checked /><label for="star5">&#9733;</label>
              <input type="radio" name="rating" value="4" id="star4" /><label for="star4">&#9733;</label>
              <input type="radio" name="rating" value="3" id="star3" /><label for="star3">&#9733;</label>
              <input type="radio" name="rating" value="2" id="star2" /><label for="star2">&#9733;</label>
              <input type="radio" name="rating" value="1" id="star1" /><label for="star1">&#9733;</label>
            </div>
          </div>
          <div class="form-group">
            <label>Your Review</label>
            <textarea name="content" rows="4" placeholder="Share your experience..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;">
            <i class="fa fa-paper-plane"></i> Submit Review
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section" style="padding:90px 0;">
  <div class="cta-banner">
    <span class="section-label" style="display:block;margin-bottom:8px;">Ready to Begin?</span>
    <h2>Your Story Starts Here</h2>
    <p>Book your appointment today and become our next happy bride.</p>
    <div class="cta-btns">
      <a class="btn btn-primary btn-lg" href="booking.php">
        <i class="fa fa-calendar-check"></i> Book Appointment
      </a>
      <a class="btn btn-outline" href="products.php">
        <i class="fa fa-shopping-bag"></i> View Collection
      </a>
    </div>
  </div>
</section>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 55vh;
  background: linear-gradient(135deg, rgba(30,20,30,0.75), rgba(60,30,50,0.6)), url("happytimes.jpeg");
  background-size: cover; background-position: center;
  display: flex; align-items: center; position: relative;
}
.page-hero-content { padding: 120px 0 60px; max-width: 800px; text-align: center; margin: 0 auto; }
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
  color: rgba(255,255,255,0.8); max-width: 500px; margin: 0 auto 32px;
  line-height: 1.6;
}
.hero-stats {
  display: flex; justify-content: center; gap: 48px;
  margin-top: 36px; flex-wrap: wrap;
}
.hero-stat { text-align: center; }
.hero-stat strong {
  font-family: 'Playfair Display', serif; font-size: 2.5rem;
  color: var(--accent); display: block; line-height: 1;
}
.hero-stat span {
  font-size: 0.85rem; color: rgba(255,255,255,0.6);
  letter-spacing: 1px; display: block; margin-top: 6px;
}
.hero-stars { color: var(--accent); font-size: 1rem; margin-top: 6px; }

/* TESTIMONIALS GRID */
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 28px; margin-top: 52px;
}
.testi-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; padding: 36px 30px; position: relative;
  transition: transform 0.3s, box-shadow 0.3s;
}
.testi-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px var(--shadow); }
.testi-quote-mark {
  font-family: 'Playfair Display', serif; font-size: 4rem;
  color: rgba(201,168,76,0.15); line-height: 1;
  position: absolute; top: 16px; right: 24px;
}
.testi-stars { color: var(--accent); font-size: 0.95rem; margin-bottom: 16px; }
.testi-quote {
  font-family: 'Cormorant Garamond', serif; font-style: italic;
  font-size: 1.05rem; color: var(--text-lt); line-height: 1.8;
  margin-bottom: 24px; position: relative; z-index: 1;
}
.testi-author { display: flex; align-items: center; gap: 14px; }
.testi-avatar {
  width: 52px; height: 52px; border-radius: 50%;
  object-fit: cover; border: 2px solid var(--accent);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 1rem; flex-shrink: 0;
}
.testi-info { min-width: 0; }
.testi-name { font-weight: 600; color: var(--text); font-size: 0.95rem; }
.testi-location {
  font-size: 0.78rem; color: var(--text-lt); margin-top: 2px;
}
.testi-location i { color: var(--accent); margin-right: 4px; }

/* REVIEW SECTION */
.review-section {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  position: relative; overflow: hidden;
}
.review-layout {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; align-items: center;
}
.review-content p { margin-bottom: 28px; }
.review-benefits { display: flex; flex-direction: column; gap: 12px; }
.review-benefit {
  display: flex; align-items: center; gap: 10px;
  color: rgba(255,255,255,0.7); font-size: 0.9rem;
}
.review-benefit i { color: var(--accent); font-size: 0.8rem; }
.review-form-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(201,168,76,0.15);
  border-radius: 20px; padding: 40px;
}
.review-form-card h3 { color: #fff; font-size: 1.3rem; margin-bottom: 24px; }
.form-group { margin-bottom: 18px; }
.form-group label {
  display: block; color: rgba(255,255,255,0.7); font-size: 0.85rem;
  margin-bottom: 8px; font-weight: 500;
}
.form-group input, .form-group textarea {
  width: 100%; padding: 12px 16px;
  border: 1.5px solid rgba(255,255,255,0.15);
  border-radius: 10px; background: rgba(255,255,255,0.05);
  color: #fff; font-size: 0.9rem; outline: none;
  transition: border-color 0.3s;
}
.form-group input:focus, .form-group textarea:focus { border-color: var(--accent); }
.form-group textarea { resize: vertical; }

/* STAR RATING */
.star-rating {
  display: flex; flex-direction: row-reverse; justify-content: flex-end;
  gap: 4px;
}
.star-rating input { display: none; }
.star-rating label {
  font-size: 1.8rem; color: rgba(255,255,255,0.2); cursor: pointer;
  transition: color 0.2s;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label { color: var(--accent); }

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
  .review-layout { grid-template-columns: 1fr; }
  .hero-stats { gap: 24px; }
  .hero-stat strong { font-size: 2rem; }
}
@media (max-width: 768px) {
  .testimonials-grid { grid-template-columns: 1fr; }
  .cta-banner { padding: 52px 28px; margin: 0 12px; }
  .page-hero-content { padding: 100px 20px 40px; }
  .review-form-card { padding: 28px 20px; }
}
</style>

<?php require_once 'footer.php'; ?>