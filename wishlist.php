<?php
$pageTitle = 'My Wishlist';
$pageDesc = 'Save your favorite bridal gowns and accessories to your wishlist at Witad Bridal Collection.';
$canonical = 'https://witadbridal.com/wishlist';
require_once 'header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php?redirect=wishlist.php');
    exit;
}

$customerId = $_SESSION['customer_id'];

// Handle remove from wishlist
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    query("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?", "ii", array($customerId, $_GET['remove']));
    setFlash('success', 'Item removed from wishlist.');
    header('Location: wishlist.php');
    exit;
}

// Get wishlist items
$wishlistItems = fetchAll("SELECT w.*, p.name, p.price, p.sale_price, p.image, p.slug, p.rental_price, c.name as category_name FROM wishlist w JOIN products p ON w.product_id = p.id JOIN categories c ON p.category_id = c.id WHERE w.customer_id = ? ORDER BY w.created_at DESC", "i", array($customerId));

// Get wishlist count for sidebar
$wishlistCount = count($wishlistItems);
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Favorites</span>
      <h1>My Wishlist</h1>
      <p class="page-sub">Your curated collection of dream gowns and accessories, saved for later.</p>
    </div>
  </div>
</section>

<!-- WISHLIST CONTENT -->
<section class="section">
  <div class="container">
    <?php if (!empty($wishlistItems)): ?>
    <div class="wishlist-layout">
      <!-- WISHLIST ITEMS -->
      <div class="wishlist-main">
        <div class="wishlist-header">
          <h2><?php echo $wishlistCount; ?> item<?php echo $wishlistCount > 1 ? 's' : ''; ?> saved</h2>
          <a href="products.php" class="btn btn-outline btn-sm"><i class="fa fa-shopping-bag"></i> Continue Shopping</a>
        </div>

        <div class="wishlist-grid">
          <?php foreach ($wishlistItems as $item): 
            $price = $item['sale_price'] ? $item['sale_price'] : $item['price'];
            $isSale = $item['sale_price'] ? true : false;
          ?>
          <div class="wishlist-card">
            <div class="wishlist-image">
              <img src="<?php echo $item['image']; ?>" alt="<?php echo sanitize($item['name']); ?>" />
              <?php if ($isSale): ?><span class="wishlist-badge sale">Sale</span><?php endif; ?>
              <?php if ($item['rental_price']): ?><span class="wishlist-badge rent">For Rent</span><?php endif; ?>
              <a href="wishlist.php?remove=<?php echo $item['product_id']; ?>" class="wishlist-remove" title="Remove from wishlist" onclick="return confirm('Remove this item from your wishlist?')">
                <i class="fa fa-times"></i>
              </a>
            </div>
            <div class="wishlist-body">
              <span class="wishlist-category"><?php echo $item['category_name']; ?></span>
              <h3><a href="product-detail.php?slug=<?php echo $item['slug']; ?>"><?php echo sanitize($item['name']); ?></a></h3>
              <div class="wishlist-price">
                <?php if ($isSale): ?><span class="old-price"><?php echo formatPrice($item['price']); ?></span><?php endif; ?>
                <span class="price"><?php echo formatPrice($price); ?></span>
                <?php if ($item['rental_price']): ?><span class="rental-price">Rent: <?php echo formatPrice($item['rental_price']); ?></span><?php endif; ?>
              </div>
              <div class="wishlist-actions">
                <a href="product-detail.php?slug=<?php echo $item['slug']; ?>" class="btn btn-primary btn-sm">View Details</a>
                <button onclick="addToCartAjax(<?php echo $item['product_id']; ?>)" class="btn btn-outline btn-sm">
                  <i class="fa fa-shopping-bag"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- SIDEBAR -->
      <aside class="wishlist-sidebar">
        <div class="sidebar-card">
          <h4><i class="fa fa-heart"></i> Wishlist Summary</h4>
          <div class="summary-row">
            <span>Items Saved</span>
            <strong><?php echo $wishlistCount; ?></strong>
          </div>
          <div class="summary-row">
            <span>Recently Added</span>
            <strong><?php echo !empty($wishlistItems) ? formatDate($wishlistItems[0]['created_at']) : '-'; ?></strong>
          </div>
          <a href="products.php" class="btn btn-primary" style="width:100%;margin-top:16px;">
            <i class="fa fa-shopping-bag"></i> Browse More
          </a>
        </div>

        <div class="sidebar-card sidebar-tip">
          <i class="fa fa-lightbulb"></i>
          <h4>Tip</h4>
          <p>Items in your wishlist are saved for 30 days. Book an appointment to try them on!</p>
          <a href="booking.php" class="btn btn-outline btn-sm" style="width:100%;">
            <i class="fa fa-calendar"></i> Book Appointment
          </a>
        </div>
      </aside>
    </div>

    <?php else: ?>
    <!-- EMPTY WISHLIST -->
    <div class="empty-wishlist">
      <div class="empty-icon">
        <i class="fa fa-heart"></i>
      </div>
      <h2>Your Wishlist is Empty</h2>
      <p>Browse our collection and save your favorite gowns and accessories to your wishlist.</p>
      <div class="empty-actions">
        <a href="products.php" class="btn btn-primary btn-lg">
          <i class="fa fa-shopping-bag"></i> Explore Collection
        </a>
        <a href="gallery.php" class="btn btn-outline">
          <i class="fa fa-images"></i> View Gallery
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- RECOMMENDED -->
<section class="section recommended-section">
  <div class="container">
    <div class="centered">
      <span class="section-label">You Might Also Like</span>
      <h2 class="section-title" style="color:#fff;">Recommended For You</h2>
      <div class="divider center"></div>
    </div>
    <div class="recommended-grid">
      <?php 
      $recommended = fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' ORDER BY RAND() LIMIT 4");
      foreach ($recommended as $product): 
        $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
      ?>
      <div class="recommended-card">
        <img src="<?php echo $product['image']; ?>" alt="<?php echo sanitize($product['name']); ?>" />
        <div class="recommended-body">
          <span class="recommended-cat"><?php echo $product['category_name']; ?></span>
          <h4><a href="product-detail.php?slug=<?php echo $product['slug']; ?>"><?php echo sanitize($product['name']); ?></a></h4>
          <span class="recommended-price"><?php echo formatPrice($price); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
function addToCartAjax(productId) {
  fetch('cart-action.php?action=add&id=' + productId)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.querySelectorAll('.nav-icon .badge').forEach(b => b.textContent = data.count);
        alert('Added to cart!');
      }
    });
}
</script>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 40vh;
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

/* WISHLIST LAYOUT */
.wishlist-layout {
  display: grid; grid-template-columns: 1fr 300px;
  gap: 40px;
}
.wishlist-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 28px; padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
}
.wishlist-header h2 { font-size: 1.3rem; }

/* WISHLIST GRID */
.wishlist-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 24px;
}
.wishlist-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 18px; overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}
.wishlist-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px var(--shadow); }
.wishlist-image { position: relative; height: 280px; overflow: hidden; }
.wishlist-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.wishlist-card:hover .wishlist-image img { transform: scale(1.05); }
.wishlist-badge {
  position: absolute; top: 12px; left: 12px;
  padding: 4px 12px; border-radius: 50px;
  font-size: 0.72rem; font-weight: 600; letter-spacing: 0.5px;
}
.wishlist-badge.sale { background: var(--error); color: #fff; }
.wishlist-badge.rent { background: var(--accent); color: #fff; }
.wishlist-remove {
  position: absolute; top: 12px; right: 12px;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(255,255,255,0.9); color: var(--error);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; transition: all 0.3s; cursor: pointer;
}
.wishlist-remove:hover { background: var(--error); color: #fff; }
.wishlist-body { padding: 20px; }
.wishlist-category {
  font-size: 0.75rem; color: var(--accent); font-weight: 600;
  letter-spacing: 1px; text-transform: uppercase;
}
.wishlist-body h3 { font-size: 1rem; margin: 6px 0 10px; }
.wishlist-body h3 a { color: var(--text); transition: color 0.3s; }
.wishlist-body h3 a:hover { color: var(--accent); }
.wishlist-price { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
.wishlist-price .price { font-size: 1.05rem; font-weight: 600; color: var(--accent); }
.wishlist-price .old-price { font-size: 0.85rem; color: var(--text-lt); text-decoration: line-through; }
.wishlist-price .rental-price { font-size: 0.78rem; color: var(--primary); font-weight: 500; }
.wishlist-actions { display: flex; gap: 8px; }
.wishlist-actions .btn { flex: 1; text-align: center; }

/* SIDEBAR */
.wishlist-sidebar { position: sticky; top: 100px; height: fit-content; }
.sidebar-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 24px; margin-bottom: 20px;
}
.sidebar-card h4 {
  font-size: 0.95rem; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.sidebar-card h4 i { color: var(--accent); }
.summary-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 10px 0; border-bottom: 1px solid var(--border);
  font-size: 0.88rem;
}
.summary-row:last-child { border-bottom: none; }
.summary-row span { color: var(--text-lt); }
.summary-row strong { color: var(--text); }
.sidebar-tip { text-align: center; }
.sidebar-tip i { font-size: 2rem; color: var(--accent); margin-bottom: 12px; display: block; }
.sidebar-tip h4 { justify-content: center; }
.sidebar-tip p { font-size: 0.85rem; color: var(--text-lt); margin-bottom: 16px; line-height: 1.6; }

/* EMPTY WISHLIST */
.empty-wishlist {
  text-align: center; padding: 80px 20px;
}
.empty-icon {
  width: 100px; height: 100px; border-radius: 50%;
  background: linear-gradient(135deg, rgba(244,167,185,0.2), rgba(201,168,76,0.15));
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 28px; font-size: 2.5rem; color: var(--accent);
}
.empty-wishlist h2 { font-size: 1.6rem; margin-bottom: 12px; }
.empty-wishlist p { color: var(--text-lt); margin-bottom: 28px; max-width: 400px; margin-left: auto; margin-right: auto; }
.empty-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }

/* RECOMMENDED */
.recommended-section {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  position: relative; overflow: hidden;
}
.recommended-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 24px; margin-top: 52px;
}
.recommended-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(201,168,76,0.15);
  border-radius: 16px; overflow: hidden;
  transition: transform 0.3s;
}
.recommended-card:hover { transform: translateY(-6px); }
.recommended-card img { width: 100%; height: 180px; object-fit: cover; }
.recommended-body { padding: 16px; }
.recommended-cat {
  font-size: 0.72rem; font-weight: 600; color: var(--accent);
  letter-spacing: 1.5px; text-transform: uppercase; display: block; margin-bottom: 6px;
}
.recommended-body h4 { font-size: 0.95rem; margin-bottom: 8px; }
.recommended-body h4 a { color: #fff; transition: color 0.3s; }
.recommended-body h4 a:hover { color: var(--accent); }
.recommended-price { font-size: 0.95rem; color: var(--accent); font-weight: 600; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .wishlist-layout { grid-template-columns: 1fr; }
  .wishlist-sidebar { position: static; }
  .recommended-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .recommended-grid { grid-template-columns: 1fr 1fr; }
  .wishlist-grid { grid-template-columns: 1fr; }
  .page-hero-content { padding: 100px 20px 40px; }
}
</style>

<?php require_once 'footer.php'; ?>