<?php
$pageTitle = 'Where Dreams Meet Elegance';
$pageDesc = 'Uganda\'s premier bridal destination. Discover stunning Mushanana, accessories, and personalized bridal services in Kabwohe-Sheema.';
$canonical = 'https://witadbridal.com/';
require_once 'header.php';

// Get featured products
$featuredProducts = fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.status = 'active' LIMIT 4");

// Get testimonials
$testimonials = fetchAll("SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order LIMIT 3");

// Get blog posts
$blogPosts = fetchAll("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 2");

// Get gallery images
$galleryImages = fetchAll("SELECT * FROM gallery_images WHERE status = 'active' ORDER BY sort_order LIMIT 7");
?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">
        <i class="fa fa-crown"></i>
        Uganda's Premier Bridal Destination
      </div>
      <h1>Where Dreams<br>Meet <em>Elegance</em></h1>
      <p class="hero-sub">
        Discover stunning bridal gowns, exquisite accessories, and personalized bridal services designed to make your special day unforgettable.
      </p>
      <div class="hero-btns">
        <a class="btn btn-primary btn-lg" href="products.php">
          <i class="fa fa-shopping-bag"></i> Shop Collection
        </a>
        <a class="btn btn-outline" href="booking.php">
          <i class="fa fa-calendar"></i> Book Appointment
        </a>
      </div>
    </div>
  </div>
  <div class="hero-scroll">
    <span>Scroll</span>
    <i class="fa fa-chevron-down"></i>
  </div>
</section>

<!-- TRUST BAR -->
<div class="trust-bar">
  <div class="container">
    <div class="trust-items">
      <div class="trust-item"><i class="fa fa-gem"></i><div><strong>500+</strong><span>Happy Brides</span></div></div>
      <div class="trust-item"><i class="fa fa-star"></i><div><strong>5-Star</strong><span>Rated Service</span></div></div>
      <div class="trust-item"><i class="fa fa-crown"></i><div><strong>Premium</strong><span>Gown Collections</span></div></div>
      <div class="trust-item"><i class="fa fa-heart"></i><div><strong>Personalized</strong><span>Styling Experience</span></div></div>
      <div class="trust-item"><i class="fa fa-scissors"></i><div><strong>Expert</strong><span>Alterations & Fittings</span></div></div>
    </div>
  </div>
</div>

<!-- FEATURED PRODUCTS -->
<section class="section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Curated For You</span>
      <h2 class="section-title">Featured Collection</h2>
      <div class="divider center"></div>
      <p class="section-desc">Handpicked gowns and accessories for the discerning bride.</p>
    </div>
    <div class="products-grid">
      <?php foreach ($featuredProducts as $product): 
        $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
        $isSale = $product['sale_price'] ? true : false;
      ?>
      <div class="product-card">
        <div class="product-image">
          <img src="<?php echo $product['image']; ?>" alt="<?php echo sanitize($product['name']); ?>" />
          <?php if ($isSale): ?>
          <span class="product-badge sale">Sale</span>
          <?php endif; ?>
          <?php if ($product['rental_price']): ?>
          <span class="product-badge rent">For Rent</span>
          <?php endif; ?>
          <div class="product-overlay">
            <a href="product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn btn-primary btn-sm">View Details</a>
            <button onclick="addToCartAjax(<?php echo $product['id']; ?>)" class="btn btn-outline btn-sm" style="margin-top:8px;">
              <i class="fa fa-shopping-bag"></i> Add to Cart
            </button>
          </div>
        </div>
        <div class="product-info">
          <span class="product-category"><?php echo $product['category_name']; ?></span>
          <h3 class="product-name"><a href="product-detail.php?slug=<?php echo $product['slug']; ?>"><?php echo sanitize($product['name']); ?></a></h3>
          <div class="product-price">
            <?php if ($isSale): ?>
            <span class="old-price"><?php echo formatPrice($product['price']); ?></span>
            <?php endif; ?>
            <span class="price"><?php echo formatPrice($price); ?></span>
            <?php if ($product['rental_price']): ?>
            <span class="rental-price">Rent: <?php echo formatPrice($product['rental_price']); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a class="btn btn-outline-dark" href="products.php">
        <i class="fa fa-arrow-right"></i> View All Collection
      </a>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section why-section">
  <div class="container">
    <div class="why-grid">
      <div class="why-image-wrap">
        <img src="mushanana3.jpeg" alt="Bride in elegant gown" />
        <div class="why-badge"><strong>500+</strong><span>HAPPY<br>BRIDES</span></div>
      </div>
      <div>
        <span class="section-label">Why Choose Us</span>
        <h2 class="section-title">The Witad Bridal Difference</h2>
        <div class="divider"></div>
        <p style="color:var(--text-lt);font-size:0.95rem;line-height:1.85;">
          At Witad Bridal Collection, we believe every bride deserves to feel extraordinary. Our experienced team combines expertise, warmth, and a deep passion for bridal fashion to ensure your experience is as beautiful as the gown you'll wear.
        </p>
        <div class="why-features">
          <div class="why-feature">
            <div class="why-feature-icon"><i class="fa fa-star"></i></div>
            <div><h4>Curated Collections</h4><p>Handpicked gowns from top designers for every style.</p></div>
          </div>
          <div class="why-feature">
            <div class="why-feature-icon"><i class="fa fa-user-tie"></i></div>
            <div><h4>Expert Consultants</h4><p>Personalized guidance from our bridal specialists.</p></div>
          </div>
          <div class="why-feature">
            <div class="why-feature-icon"><i class="fa fa-scissors"></i></div>
            <div><h4>Perfect Fit</h4><p>Professional alterations for your ideal silhouette.</p></div>
          </div>
          <div class="why-feature">
            <div class="why-feature-icon"><i class="fa fa-heart"></i></div>
            <div><h4>Memorable Experience</h4><p>Stress-free, joyful, and unforgettable bridal journey.</p></div>
          </div>
        </div>
        <div style="margin-top:36px;">
          <a class="btn btn-primary" href="about.php"><i class="fa fa-info-circle"></i> Our Story</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- SERVICES -->
<section class="section">
  <div class="container">
    <div class="centered">
      <span class="section-label">What We Offer</span>
      <h2 class="section-title">Our Signature Services</h2>
      <div class="divider center"></div>
      <p class="section-desc">From the perfect gown to the finishing touch — we're with you every step of your bridal journey.</p>
    </div>
    <div class="services-grid">
      <div class="service-card">
        <div class="service-icon"><i class="fa fa-star"></i></div>
        <h3>Bridal Gown Sales</h3>
        <p>Explore a curated range of luxurious Mushanana designed for every style, body type, and dream aesthetic.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa fa-tag"></i></div>
        <h3>Bridal Gown Rentals</h3>
        <p>Affordable and elegant rental options for brides seeking sophistication without compromise on beauty.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa fa-magic"></i></div>
        <h3>Bridal Styling</h3>
        <p>Professional consultations to help you achieve your desired wedding-day look with expert guidance.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa fa-heart"></i></div>
        <h3>Bridesmaids Dresses</h3>
        <p>Beautiful coordinated dresses in various colors and designs to complement your special day perfectly.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa fa-gem"></i></div>
        <h3>Bridal Accessories</h3>
        <p>Veils, tiaras, jewelry, shoes, and every finishing touch to complete your perfect bridal look.</p>
      </div>
      <div class="service-card">
        <div class="service-icon"><i class="fa fa-scissors"></i></div>
        <h3>Alterations & Fittings</h3>
        <p>Expert adjustments ensuring your gown fits flawlessly and feels comfortable throughout your big day.</p>
      </div>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a class="btn btn-outline-dark" href="services.php"><i class="fa fa-arrow-right"></i> View All Services</a>
    </div>
  </div>
</section>

<!-- GALLERY PREVIEW -->
<section class="section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Our Portfolio</span>
      <h2 class="section-title">A Glimpse of Elegance</h2>
      <div class="divider center"></div>
      <p class="section-desc">Every gown tells a story. Browse a selection of our most beloved bridal looks.</p>
    </div>
    <div class="gallery-preview">
      <?php foreach ($galleryImages as $index => $img): ?>
      <div class="gal-item <?php echo $index == 0 || $index == 3 ? 'tall' : ''; ?>" onclick="openLightbox('<?php echo $img['image']; ?>')">
        <img src="<?php echo $img['image']; ?>" alt="<?php echo sanitize($img['title']); ?>" />
        <div class="gal-overlay"><?php echo sanitize($img['title']); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a class="btn btn-outline-dark" href="gallery.php"><i class="fa fa-images"></i> View Full Gallery</a>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section testimonials-section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Bride Stories</span>
      <h2 class="section-title" style="color:#fff;">Words From Our Brides</h2>
      <div class="divider center"></div>
    </div>
    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testi-card">
        <div class="testi-stars"><?php echo str_repeat('★', $t['rating']); ?></div>
        <p class="testi-quote">"<?php echo sanitize($t['content']); ?>"</p>
        <div class="testi-author">
          <img src="<?php echo $t['image'] ?: 'https://via.placeholder.com/50'; ?>" alt="<?php echo sanitize($t['customer_name']); ?>" class="testi-avatar" />
          <div>
            <div class="testi-name"><?php echo sanitize($t['customer_name']); ?></div>
            <div class="testi-date"><?php echo $t['location'] ? sanitize($t['location']) : ''; ?> <?php echo $t['wedding_date'] ? '· ' . formatDate($t['wedding_date']) : ''; ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:44px;">
      <a class="btn" style="background:rgba(255,255,255,0.1);border:1.5px solid rgba(255,255,255,0.3);color:#fff;padding:12px 32px;border-radius:50px;" href="testimonials.php">
        <i class="fa fa-star"></i> Read All Reviews
      </a>
    </div>
  </div>
</section>

<!-- BLOG PREVIEW -->
<section class="section">
  <div class="container">
    <div class="centered">
      <span class="section-label">Inspiration & Tips</span>
      <h2 class="section-title">From The Journal</h2>
      <div class="divider center"></div>
      <p class="section-desc">Stories, trends, and guidance for the modern bride.</p>
    </div>
    <div class="blog-grid">
      <?php foreach ($blogPosts as $post): ?>
      <div class="blog-card">
        <img src="<?php echo $post['image']; ?>" alt="<?php echo sanitize($post['title']); ?>" />
        <div class="blog-card-body">
          <span class="blog-cat"><?php echo $post['category']; ?></span>
          <h3><a href="blog-post.php?slug=<?php echo $post['slug']; ?>"><?php echo sanitize($post['title']); ?></a></h3>
          <p><?php echo sanitize($post['excerpt']); ?></p>
          <div class="blog-meta">
            <span><i class="fa fa-calendar"></i> <?php echo formatDate($post['published_at']); ?></span>
            <span><i class="fa fa-eye"></i> <?php echo $post['views']; ?> views</span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px;">
      <a class="btn btn-outline-dark" href="blog.php"><i class="fa fa-book-open"></i> Read All Articles</a>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<section class="section" style="padding:90px 0;">
  <div class="cta-banner">
    <span class="section-label" style="display:block;margin-bottom:8px;">Begin Your Journey</span>
    <h2>Your Perfect Gown is Waiting</h2>
    <p>Book a personalized consultation with our bridal experts and take the first step toward your dream wedding day.</p>
    <div class="cta-btns">
      <a class="btn btn-primary btn-lg" href="booking.php">
        <i class="fa fa-calendar-check"></i> Book Appointment
      </a>
      <a class="btn btn-outline" href="products.php">
        <i class="fa fa-shopping-bag"></i> Shop Collection
      </a>
    </div>
  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <span class="lightbox-close" onclick="closeLightbox()"><i class="fa fa-times"></i></span>
  <img id="lightboxImg" src="" alt="Gallery Image" onclick="event.stopPropagation()" />
</div>

<script>
function addToCartAjax(productId) {
  fetch('cart-action.php?action=add&id=' + productId)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Update cart badge
        const badges = document.querySelectorAll('.nav-icon .badge');
        badges.forEach(b => b.textContent = data.count);
        alert('Added to cart!');
      }
    });
}
</script>

<style>
/* HERO */
.hero {
  min-height: 100vh;
  background: linear-gradient(135deg, rgba(30,20,30,0.5), rgba(60,30,50,0.3)), url("happytimes.jpeg");
  background-size: cover; background-position: center;
  display: flex; align-items: center; position: relative; overflow: hidden;
}
.hero::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0;
  height: 60px; background: linear-gradient(transparent, var(--bg));
}
.hero-content { padding-top: 80px; max-width: 700px; }
.hero-badge {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(59,58,58,0.12); border: 1px solid rgba(201,168,76,0.5);
  backdrop-filter: blur(8px); padding: 8px 20px; border-radius: 50px;
  color: var(--accent-lt); font-size: 0.82rem; letter-spacing: 2px;
  text-transform: uppercase; margin-bottom: 28px;
}
.hero h1 {
  font-size: clamp(2.8rem, 7vw, 5rem); color: #fff;
  line-height: 1.1; margin-bottom: 10px;
}
.hero h1 em { color: var(--accent-lt); font-style: italic; }
.hero-sub {
  font-family: 'Cormorant Garamond', serif; font-size: 1.35rem;
  color: rgba(255,255,255,0.85); margin-bottom: 40px;
  max-width: 560px; line-height: 1.6;
}
.hero-btns { display: flex; gap: 16px; flex-wrap: wrap; }
.hero-scroll {
  position: absolute; bottom: 40px; left: 50%;
  transform: translateX(-50%); color: rgba(255,255,255,0.6);
  font-size: 0.78rem; letter-spacing: 2px; text-transform: uppercase;
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  animation: bounce 2s infinite;
}
.hero-scroll i { font-size: 1.2rem; }
@keyframes bounce {
  0%,100% { transform: translateX(-50%) translateY(0); }
  50% { transform: translateX(-50%) translateY(6px); }
}

/* TRUST BAR */
.trust-bar {
  background: linear-gradient(135deg, var(--text) 0%, #2a1a25 100%);
  padding: 28px 0;
}
.trust-items {
  display: flex; justify-content: center; gap: 60px; flex-wrap: wrap;
}
.trust-item {
  display: flex; align-items: center; gap: 12px;
  color: rgba(255,255,255,0.9);
}
.trust-item i { color: var(--accent); font-size: 1.4rem; }
.trust-item strong {
  font-family: 'Playfair Display', serif; font-size: 1.1rem;
  display: block; line-height: 1;
}
.trust-item span {
  font-size: 0.78rem; color: rgba(255,255,255,0.55); letter-spacing: 1px;
}

/* PRODUCTS */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 28px; margin-top: 52px;
}
.product-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; overflow: hidden;
  transition: all 0.35s ease;
}
.product-card:hover {
  transform: translateY(-6px); box-shadow: 0 20px 50px var(--shadow);
}
.product-image {
  position: relative; height: 320px; overflow: hidden;
}
.product-image img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.5s ease;
}
.product-card:hover .product-image img { transform: scale(1.05); }
.product-badge {
  position: absolute; top: 12px; left: 12px;
  padding: 4px 12px; border-radius: 50px;
  font-size: 0.72rem; font-weight: 600; letter-spacing: 0.5px;
}
.product-badge.sale { background: var(--error); color: #fff; }
.product-badge.rent { background: var(--accent); color: #fff; }
.product-overlay {
  position: absolute; inset: 0; background: rgba(30,20,30,0.6);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.3s; gap: 8px;
}
.product-card:hover .product-overlay { opacity: 1; }
.product-info { padding: 20px; }
.product-category {
  font-size: 0.75rem; color: var(--accent);
  font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
}
.product-name {
  font-size: 1.05rem; margin: 6px 0 10px;
  font-family: 'Playfair Display', serif;
}
.product-name a { color: var(--text); transition: color 0.3s; }
.product-name a:hover { color: var(--accent); }
.product-price { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.product-price .price {
  font-size: 1.1rem; font-weight: 600; color: var(--accent);
}
.product-price .old-price {
  font-size: 0.9rem; color: var(--text-lt); text-decoration: line-through;
}
.product-price .rental-price {
  font-size: 0.78rem; color: var(--primary); font-weight: 500;
}

/* WHY SECTION */
.why-section {
  background: linear-gradient(135deg, rgba(244,167,185,0.06), rgba(201,168,76,0.04));
}
.why-grid {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; align-items: center;
}
.why-image-wrap { position: relative; }
.why-image-wrap img {
  width: 100%; height: 560px; object-fit: cover; border-radius: 24px;
}
.why-badge {
  position: absolute; bottom: -20px; right: -20px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dk));
  color: #fff; width: 130px; height: 130px; border-radius: 50%;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  text-align: center; box-shadow: 0 8px 30px rgba(201,168,76,0.4);
}
.why-badge strong {
  font-family: 'Playfair Display', serif; font-size: 2rem; line-height: 1;
}
.why-badge span { font-size: 0.7rem; letter-spacing: 1px; opacity: 0.9; }
.why-features {
  display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 36px;
}
.why-feature {
  display: flex; align-items: flex-start; gap: 14px;
}
.why-feature-icon {
  width: 42px; height: 42px;
  background: linear-gradient(135deg, var(--primary), var(--accent-lt));
  border-radius: 12px; display: flex;
  align-items: center; justify-content: center;
  color: #fff; font-size: 1rem; flex-shrink: 0;
}
.why-feature h4 { font-size: 0.95rem; margin-bottom: 4px; }
.why-feature p { font-size: 0.82rem; color: var(--text-lt); line-height: 1.5; }

/* SERVICES */
.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 28px; margin-top: 52px;
}
.service-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 20px; padding: 36px 30px;
  transition: all 0.35s ease; position: relative; overflow: hidden;
}
.service-card::before {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px; background: linear-gradient(90deg, var(--primary), var(--accent));
  transform: scaleX(0); transform-origin: left;
  transition: transform 0.4s ease;
}
.service-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px var(--shadow); }
.service-card:hover::before { transform: scaleX(1); }
.service-icon {
  width: 64px; height: 64px;
  background: linear-gradient(135deg, rgba(244,167,185,0.2), rgba(201,168,76,0.15));
  border-radius: 16px; display: flex;
  align-items: center; justify-content: center;
  font-size: 1.6rem; color: var(--accent); margin-bottom: 20px;
}
.service-card h3 { font-size: 1.2rem; margin-bottom: 10px; }
.service-card p { font-size: 0.9rem; color: var(--text-lt); line-height: 1.7; }

/* GALLERY */
.gallery-preview {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-template-rows: 280px 280px;
  gap: 14px; margin-top: 52px;
}
.gal-item {
  position: relative; overflow: hidden;
  border-radius: 16px; cursor: pointer;
}
.gal-item.tall { grid-row: 1 / 3; }
.gal-item img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s ease;
}
.gal-item:hover img { transform: scale(1.08); }
.gal-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(30,20,30,0.6), transparent);
  opacity: 0; transition: opacity 0.3s;
  display: flex; align-items: flex-end; padding: 20px;
  color: #fff; font-family: 'Cormorant Garamond', serif;
  font-size: 1.1rem; font-style: italic;
}
.gal-item:hover .gal-overlay { opacity: 1; }

/* TESTIMONIALS */
.testimonials-section {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  position: relative; overflow: hidden;
}
.testimonials-section::before {
  content: '"'; font-family: 'Playfair Display', serif;
  font-size: 300px; color: rgba(201,168,76,0.06);
  position: absolute; top: -60px; left: 40px;
  line-height: 1; pointer-events: none;
}
.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 28px; margin-top: 50px;
}
.testi-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(201,168,76,0.2);
  border-radius: 20px; padding: 36px 30px;
  backdrop-filter: blur(8px); transition: transform 0.3s;
}
.testi-card:hover { transform: translateY(-4px); }
.testi-stars { color: var(--accent); font-size: 0.9rem; margin-bottom: 18px; }
.testi-quote {
  font-family: 'Cormorant Garamond', serif; font-style: italic;
  font-size: 1.1rem; color: rgba(255,255,255,0.88);
  line-height: 1.8; margin-bottom: 24px;
}
.testi-author { display: flex; align-items: center; gap: 14px; }
.testi-avatar {
  width: 50px; height: 50px; border-radius: 50%;
  object-fit: cover; border: 2px solid var(--accent);
}
.testi-name { font-weight: 600; color: #fff; font-size: 0.95rem; }
.testi-date { font-size: 0.78rem; color: rgba(255,255,255,0.45); margin-top: 2px; }

/* BLOG */
.blog-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 28px; margin-top: 52px;
}
.blog-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 18px; overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}
.blog-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px var(--shadow); }
.blog-card img { width: 100%; height: 220px; object-fit: cover; }
.blog-card-body { padding: 24px; }
.blog-cat {
  font-size: 0.72rem; font-weight: 600; color: var(--accent);
  letter-spacing: 1.5px; text-transform: uppercase;
}
.blog-card h3 { font-size: 1.1rem; margin: 10px 0; line-height: 1.4; }
.blog-card h3 a { color: var(--text); transition: color 0.3s; }
.blog-card h3 a:hover { color: var(--accent); }
.blog-card p { font-size: 0.87rem; color: var(--text-lt); line-height: 1.7; }
.blog-meta {
  font-size: 0.8rem; color: var(--text-lt); margin-top: 14px;
  display: flex; gap: 16px;
}
.blog-meta i { color: var(--accent); margin-right: 4px; }

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

/* LIGHTBOX */
.lightbox {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.92); z-index: 2000;
  align-items: center; justify-content: center;
}
.lightbox.open { display: flex; }
.lightbox img { max-width: 90vw; max-height: 88vh; border-radius: 12px; object-fit: contain; }
.lightbox-close {
  position: absolute; top: 24px; right: 28px;
  color: #fff; font-size: 2rem; cursor: pointer; opacity: 0.7;
  transition: opacity 0.2s;
}
.lightbox-close:hover { opacity: 1; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .gallery-preview { grid-template-columns: repeat(3, 1fr); grid-template-rows: auto; }
  .gal-item.tall { grid-row: auto; }
  .why-badge { right: 0; bottom: -10px; }
  .blog-grid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  .why-grid { grid-template-columns: 1fr; }
  .why-image-wrap img { height: 340px; }
  .why-features { grid-template-columns: 1fr; }
  .gallery-preview { grid-template-columns: 1fr 1fr; }
  .hero-btns { flex-direction: column; }
  .cta-banner { padding: 52px 28px; margin: 0 12px; }
  .trust-items { gap: 28px; flex-direction: column; align-items: center; }
}
</style>

<?php require_once 'footer.php'; ?>
