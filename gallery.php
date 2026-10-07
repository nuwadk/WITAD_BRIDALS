<?php
$pageTitle = 'Gallery';
$pageDesc = 'Browse our stunning bridal gallery featuring real weddings, gown showcases, and behind-the-scenes moments from Witad Bridal Collection in Kabwohe-Sheema, Uganda.';
$canonical = 'https://witadbridal.com/gallery';
require_once 'header.php';

// ============================================================
// ADD YOUR IMAGES HERE — Upload to images/gallery/ first
// ============================================================
$galleryImages = array(
    array(
        'image_path' => '7520b202bc3d5c5ff569a0f9b8ff6af8.png',
        'title'      => 'Elegant A-Line Gown',
        'category'   => 'party'
    ),
    array(
        'image_path' => 'WhatsApp Image 2026-06-22 at 08.27.06 (1).jpeg',
        'title'      => 'Royal Ballgown Fitting',
        'category'   => 'fittings'
    ),
    array(
        'image_path' => 'mushanana, Kinyarwanda….jpeg',
        'title'      => 'Sheema Garden Giveaway',
        'category'   => 'giveaways'
    ),
    array(
        'image_path' => 'WhatsApp Image 2026-06-01 at 16.20.45 (3).jpeg',
        'title'      => 'Behind the Scenes',
        'category'   => 'behind-the-scenes'
    ),
    array(
        'image_path' => 'accessories 2.jpeg',
        'title'      => 'Handcrafted Veils & Accessories',
        'category'   => 'accessories'
    ),
    array(
        'image_path' => 'mushana9.jpeg',
        'title'      => 'Traditional Bridal Attire',
        'category'   => 'traditional'
    ),
    array(
        'image_path' => 'reception4.jpeg',
        'title'      => 'Reception Glamour',
        'category'   => 'okuhingira'
    ),
    array(
        'image_path' => 'mushanana3.jpeg',
        'title'      => 'The Fitting Experience',
        'category'   => 'fittings'
    ),
    // ADD MORE IMAGES HERE...
);

// ---- FILTER LOGIC ----
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter != 'all') {
    $filtered = array();
    foreach ($galleryImages as $img) {
        if ($img['category'] == $filter) {
            $filtered[] = $img;
        }
    }
    $galleryImages = $filtered;
}

// ---- GET DISTINCT CATEGORIES FOR FILTER BUTTONS ----
$categories = array();
$seen = array();
foreach ($galleryImages as $img) {
    if (!empty($img['category']) && !isset($seen[$img['category']])) {
        $seen[$img['category']] = true;
        $categories[] = array('category' => $img['category']);
    }
}
// Sort categories alphabetically
usort($categories, function($a, $b) {
    return strcmp($a['category'], $b['category']);
});
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Our Portfolio</span>
      <h1>Bridal Gallery</h1>
      <p class="page-sub">Every gown tells a story. Browse our collection of beautiful moments, stunning brides, and unforgettable weddings.</p>
    </div>
  </div>
</section>

<!-- GALLERY SECTION -->
<section class="section">
  <div class="container">
    <div class="centered">
      <span class="section-label">A Glimpse of Elegance</span>
      <h2 class="section-title">Moments of Magic</h2>
      <div class="divider center"></div>
      <p class="section-desc">From the first fitting to the final walk down the aisle, capture the beauty of every bridal journey.</p>
    </div>

    <!-- FILTER TABS -->
    <div class="gallery-filters">
      <a href="gallery.php" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">All</a>
      <?php foreach ($categories as $cat): ?>
      <a href="gallery.php?filter=<?php echo urlencode($cat['category']); ?>" class="filter-btn <?php echo $filter == $cat['category'] ? 'active' : ''; ?>">
        <?php echo ucfirst(htmlspecialchars($cat['category'])); ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- GALLERY MASONRY -->
    <?php if (!empty($galleryImages)): ?>
    <div class="gallery-masonry" id="galleryMasonry">
      <?php foreach ($galleryImages as $index => $img): ?>
      <div class="gal-item <?php echo $index % 5 == 0 || $index % 5 == 3 ? 'tall' : ''; ?>" data-cat="<?php echo htmlspecialchars($img['category']); ?>" onclick="openLightbox('<?php echo $img['image_path']; ?>', '<?php echo htmlspecialchars($img['title'], ENT_QUOTES); ?>')">
        <img src="<?php echo $img['image_path']; ?>" alt="<?php echo htmlspecialchars($img['title']); ?>" />
        <div class="gal-overlay">
          <div class="gal-overlay-content">
            <h4><?php echo htmlspecialchars($img['title']); ?></h4>
            <span><?php echo ucfirst(htmlspecialchars($img['category'])); ?></span>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state" style="text-align:center;padding:80px 20px;">
      <i class="fa fa-images" style="font-size:4rem;color:var(--border);margin-bottom:20px;"></i>
      <h3 style="font-size:1.4rem;margin-bottom:10px;">No Images Yet</h3>
      <p style="color:var(--text-lt);">Our gallery is being updated. Check back soon for stunning bridal moments.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <span class="lightbox-close" onclick="closeLightbox()"><i class="fa fa-times"></i></span>
  <img id="lightboxImg" src="" alt="Gallery Image" onclick="event.stopPropagation()" />
  <div class="lightbox-caption" id="lightboxCaption"></div>
</div>

<!-- CTA -->
<section class="section" style="padding:90px 0;">
  <div class="cta-banner">
    <span class="section-label" style="display:block;margin-bottom:8px;">Be Part of Our Story</span>
    <h2>Share Your Bridal Moment</h2>
    <p>Had your dream wedding in a Witad gown? We would love to feature your beautiful story in our gallery.</p>
    <div class="cta-btns">
      <a class="btn btn-primary btn-lg" href="contact.php">
        <i class="fa fa-envelope"></i> Submit Your Photos
      </a>
      <a class="btn btn-outline" href="booking.php">
        <i class="fa fa-calendar"></i> Book a Session
      </a>
    </div>
  </div>
</section>

<!-- LIGHTBOX JAVASCRIPT -->
<script>
function openLightbox(src, caption) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxCaption').textContent = caption || '';
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
}
</script>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 50vh;
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

/* GALLERY FILTERS */
.gallery-filters {
  display: flex; justify-content: center; gap: 12px;
  flex-wrap: wrap; margin-bottom: 40px;
}
.filter-btn {
  padding: 10px 24px; border-radius: 50px;
  border: 1.5px solid var(--border); background: var(--bg-card);
  color: var(--text-lt); font-size: 0.88rem; font-weight: 500;
  transition: all 0.3s; cursor: pointer; text-decoration: none;
  display: inline-block;
}
.filter-btn:hover, .filter-btn.active {
  background: var(--accent); color: #fff; border-color: var(--accent);
}

/* GALLERY MASONRY */
.gallery-masonry {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  grid-auto-rows: 280px;
  gap: 16px;
}
.gal-item {
  position: relative; overflow: hidden;
  border-radius: 16px; cursor: pointer;
  grid-row: span 1;
}
.gal-item.tall { grid-row: span 2; }
.gal-item img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s ease;
}
.gal-item:hover img { transform: scale(1.08); }
.gal-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(30,20,30,0.75) 0%, rgba(30,20,30,0.2) 50%, transparent 100%);
  opacity: 0; transition: opacity 0.35s ease;
  display: flex; align-items: flex-end; padding: 24px;
}
.gal-item:hover .gal-overlay { opacity: 1; }
.gal-overlay-content h4 {
  color: #fff; font-size: 1.1rem; margin-bottom: 4px;
  font-family: 'Playfair Display', serif;
}
.gal-overlay-content span {
  color: var(--accent-lt); font-size: 0.8rem;
  letter-spacing: 1.5px; text-transform: uppercase;
}

/* LIGHTBOX */
.lightbox {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.92); z-index: 2000;
  align-items: center; justify-content: center;
  flex-direction: column; padding: 40px;
}
.lightbox.open { display: flex; }
.lightbox img { max-width: 85vw; max-height: 80vh; border-radius: 12px; object-fit: contain; }
.lightbox-close {
  position: absolute; top: 24px; right: 28px;
  color: #fff; font-size: 2rem; cursor: pointer; opacity: 0.7;
  transition: opacity 0.2s; z-index: 2001;
}
.lightbox-close:hover { opacity: 1; }
.lightbox-caption {
  color: rgba(255,255,255,0.8); margin-top: 16px;
  font-family: 'Cormorant Garamond', serif; font-size: 1.2rem;
}

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
  .gallery-masonry { grid-template-columns: repeat(3, 1fr); }
  .gal-item.tall { grid-row: span 1; }
}
@media (max-width: 768px) {
  .gallery-masonry { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 200px; }
  .cta-banner { padding: 52px 28px; margin: 0 12px; }
  .page-hero-content { padding: 100px 20px 40px; }
  .gallery-filters { gap: 8px; }
  .filter-btn { padding: 8px 16px; font-size: 0.8rem; }
}
</style>

<?php require_once 'footer.php'; ?>