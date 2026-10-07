<?php
require_once 'functions.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
if (!$slug) {
    header('Location: products.php');
    exit;
}

// PHP 5.4 compatible - use array() instead of []
$product = fetchOne("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.slug = ? AND p.status = 'active'", "s", array($slug));

if (!$product) {
    header('Location: products.php');
    exit;
}

// Increment views
query("UPDATE products SET views = views + 1 WHERE id = ?", "i", array($product['id']));

// Get related products
$related = fetchAll("SELECT p.*, c.name as category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' ORDER BY RAND() LIMIT 4", "ii", array($product['category_id'], $product['id']));

// Get product images
$images = fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order", "i", array($product['id']));

$pageTitle = $product['name'];
$pageDesc = $product['short_desc'];
$canonical = 'https://witadbridal.com/product-detail.php?slug=' . $slug;
require_once 'header.php';

$price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
$isSale = $product['sale_price'] ? true : false;

// PHP 5.4 compatible - use isset() instead of ?? or ?:
$sizes = isset($product['sizes']) && $product['sizes'] ? explode(',', $product['sizes']) : array();
$colors = isset($product['colors']) && $product['colors'] ? explode(',', $product['colors']) : array();
?>

<!-- BREADCRUMBS -->
<div class="breadcrumbs">
  <div class="container">
    <h1><?php echo sanitize($product['name']); ?></h1>
    <div class="crumb">
      <a href="index.php">Home</a> <i class="fa fa-chevron-right"></i>
      <a href="products.php">Collection</a> <i class="fa fa-chevron-right"></i>
      <a href="products.php?category=<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a> <i class="fa fa-chevron-right"></i>
      <span><?php echo sanitize($product['name']); ?></span>
    </div>
  </div>
</div>

<section class="section" style="padding-top:60px;">
  <div class="container">
    <div class="product-detail-layout">
      <!-- IMAGES -->
      <div class="product-gallery">
        <div class="main-image">
          <?php 
          $mainImg = isset($product['image']) && $product['image'] ? $product['image'] : 'assets/images/placeholder-product.jpg';
          ?>
          <img id="mainImage" src="<?php echo $mainImg; ?>" alt="<?php echo sanitize($product['name']); ?>" />
          <?php if ($isSale): ?><span class="product-badge sale">Sale</span><?php endif; ?>
        </div>
        <?php if (!empty($images)): ?>
        <div class="thumbnail-list">
          <div class="thumb active" onclick="changeImage('<?php echo $mainImg; ?>', this)">
            <img src="<?php echo $mainImg; ?>" alt="Main" />
          </div>
          <?php foreach ($images as $img): ?>
          <div class="thumb" onclick="changeImage('<?php echo $img['image']; ?>', this)">
            <img src="<?php echo $img['image']; ?>" alt="Gallery" />
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- DETAILS -->
      <div class="product-details">
        <span class="product-category-tag"><?php echo $product['category_name']; ?></span>
        <h1 class="product-title"><?php echo sanitize($product['name']); ?></h1>

        <div class="product-rating">
          <span class="stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
          <span class="reviews">(12 reviews)</span>
        </div>

        <div class="product-price-large">
          <?php if ($isSale): ?>
          <span class="old-price-large"><?php echo formatPrice($product['price']); ?></span>
          <?php endif; ?>
          <span class="current-price"><?php echo formatPrice($price); ?></span>
          <?php if ($product['rental_price']): ?>
          <span class="rental-option">or Rent for <?php echo formatPrice($product['rental_price']); ?></span>
          <?php endif; ?>
        </div>

        <p class="product-short-desc"><?php echo sanitize($product['short_desc']); ?></p>

        <form method="POST" action="cart-action.php" id="addToCartForm">
          <?php echo csrfField(); ?>
          <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
          <input type="hidden" name="action" value="add" />

          <?php if (!empty($sizes)): ?>
          <div class="option-group">
            <label>Size</label>
            <div class="size-options">
              <?php foreach ($sizes as $size): ?>
              <label class="size-option">
                <input type="radio" name="size" value="<?php echo trim($size); ?>" required />
                <span><?php echo trim($size); ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($colors)): ?>
          <div class="option-group">
            <label>Color</label>
            <div class="color-options">
              <?php foreach ($colors as $color): ?>
              <label class="color-option" title="<?php echo trim($color); ?>">
                <input type="radio" name="color" value="<?php echo trim($color); ?>" required />
                <span style="background:<?php echo strtolower(trim($color)); ?>"></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="option-group">
            <label>Type</label>
            <div class="type-options">
              <label class="type-option">
                <input type="radio" name="type" value="purchase" checked />
                <span><i class="fa fa-shopping-bag"></i> Purchase</span>
              </label>
              <?php if ($product['rental_price']): ?>
              <label class="type-option">
                <input type="radio" name="type" value="rental" />
                <span><i class="fa fa-tag"></i> Rent</span>
              </label>
              <?php endif; ?>
            </div>
          </div>

          <div class="option-group">
            <label>Quantity</label>
            <div class="qty-selector">
              <button type="button" onclick="adjustQty(-1)">-</button>
              <input type="number" name="qty" value="1" min="1" max="5" id="qtyInput" />
              <button type="button" onclick="adjustQty(1)">+</button>
            </div>
          </div>

          <div class="product-actions">
            <button type="submit" class="btn btn-primary btn-lg" style="flex:1;">
              <i class="fa fa-shopping-bag"></i> Add to Cart
            </button>
            <button type="button" onclick="addToWishlist(<?php echo $product['id']; ?>)" class="btn btn-outline btn-lg" style="width:56px;padding:0;justify-content:center;">
              <i class="fa fa-heart"></i>
            </button>
          </div>
        </form>

        <div class="product-meta">
          <div class="meta-item"><i class="fa fa-check-circle"></i> <span>Expert alterations included</span></div>
          <div class="meta-item"><i class="fa fa-truck"></i> <span>Free pickup from studio</span></div>
          <div class="meta-item"><i class="fa fa-undo"></i> <span>7-day return policy</span></div>
          <div class="meta-item"><i class="fa fa-shield-alt"></i> <span>Authentic designer pieces</span></div>
        </div>

        <div class="product-share">
          <span>Share:</span>
          <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical); ?>" target="_blank"><i class="fab fa-facebook-f"></i></a>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($canonical); ?>&text=<?php echo urlencode($product['name']); ?>" target="_blank"><i class="fab fa-twitter"></i></a>
          <a href="https://wa.me/?text=<?php echo urlencode($product['name'] . ' - ' . $canonical); ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
          <a href="mailto:?subject=<?php echo urlencode('Check out: ' . $product['name']); ?>&body=<?php echo urlencode($canonical); ?>"><i class="fa fa-envelope"></i></a>
        </div>
      </div>
    </div>

    <!-- TABS -->
    <div class="product-tabs">
      <div class="tab-buttons">
        <button class="tab-btn active" onclick="showTab('desc', this)">Description</button>
        <button class="tab-btn" onclick="showTab('details', this)">Details</button>
        <button class="tab-btn" onclick="showTab('shipping', this)">Shipping &amp; Returns</button>
        <button class="tab-btn" onclick="showTab('reviews', this)">Reviews</button>
      </div>
      <div class="tab-content">
        <div id="tab-desc" class="tab-panel active">
          <div class="tab-inner">
            <?php echo nl2br(sanitize($product['description'])); ?>
          </div>
        </div>
        <div id="tab-details" class="tab-panel">
          <div class="tab-inner">
            <table class="details-table">
              <tr><td>Material</td><td><?php echo isset($product['material']) && $product['material'] ? $product['material'] : 'Premium fabric'; ?></td></tr>
              <tr><td>Style</td><td><?php echo isset($product['style']) && $product['style'] ? $product['style'] : 'Classic'; ?></td></tr>
              <tr><td>Available Sizes</td><td><?php echo isset($product['sizes']) && $product['sizes'] ? $product['sizes'] : 'Contact us'; ?></td></tr>
              <tr><td>Available Colors</td><td><?php echo isset($product['colors']) && $product['colors'] ? $product['colors'] : 'As shown'; ?></td></tr>
              <tr><td>Category</td><td><?php echo $product['category_name']; ?></td></tr>
            </table>
          </div>
        </div>
        <div id="tab-shipping" class="tab-panel">
          <div class="tab-inner">
            <h4>Pickup Information</h4>
            <p>All gowns are available for pickup at our studio in Kabwohe-Sheema. We recommend scheduling a fitting appointment when you pick up your gown.</p>
            <h4>Alterations</h4>
            <p>Basic alterations are included with your purchase. Complex alterations may incur additional charges. Our expert seamstresses will ensure your gown fits perfectly.</p>
            <h4>Return Policy</h4>
            <p>Unworn gowns with tags attached can be returned within 7 days of pickup. Rental gowns must be returned in the condition specified in your rental agreement.</p>
          </div>
        </div>
        <div id="tab-reviews" class="tab-panel">
          <div class="tab-inner">
            <p style="color:var(--text-lt);">Reviews coming soon. Be the first to review this gown!</p>
          </div>
        </div>
      </div>
    </div>

    <!-- RELATED PRODUCTS -->
    <?php if (!empty($related)): ?>
    <div class="related-products">
      <div class="centered" style="margin-bottom:40px;">
        <span class="section-label">You May Also Like</span>
        <h2 class="section-title">Related Gowns</h2>
        <div class="divider center"></div>
      </div>
      <div class="products-grid">
        <?php foreach ($related as $r):
          $rPrice = $r['sale_price'] ? $r['sale_price'] : $r['price'];
          $rImg = isset($r['image']) && $r['image'] ? $r['image'] : 'assets/images/placeholder-product.jpg';
        ?>
        <div class="product-card">
          <div class="product-image">
            <img src="<?php echo $rImg; ?>" alt="<?php echo sanitize($r['name']); ?>" />
            <div class="product-overlay">
              <a href="product-detail.php?slug=<?php echo $r['slug']; ?>" class="btn btn-primary btn-sm">View Details</a>
            </div>
          </div>
          <div class="product-info">
            <span class="product-category"><?php echo $r['category_name']; ?></span>
            <h3 class="product-name"><a href="product-detail.php?slug=<?php echo $r['slug']; ?>"><?php echo sanitize($r['name']); ?></a></h3>
            <div class="product-price">
              <span class="price"><?php echo formatPrice($rPrice); ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<script>
function changeImage(src, thumb) {
  document.getElementById('mainImage').src = src;
  var thumbs = document.querySelectorAll('.thumb');
  for (var i = 0; i < thumbs.length; i++) {
    thumbs[i].classList.remove('active');
  }
  thumb.classList.add('active');
}
function adjustQty(delta) {
  var input = document.getElementById('qtyInput');
  var val = parseInt(input.value) + delta;
  if (val < 1) val = 1;
  if (val > 5) val = 5;
  input.value = val;
}
function showTab(id, btn) {
  var panels = document.querySelectorAll('.tab-panel');
  for (var i = 0; i < panels.length; i++) {
    panels[i].classList.remove('active');
  }
  var buttons = document.querySelectorAll('.tab-btn');
  for (var i = 0; i < buttons.length; i++) {
    buttons[i].classList.remove('active');
  }
  document.getElementById('tab-' + id).classList.add('active');
  btn.classList.add('active');
}
function addToWishlist(productId) {
  fetch('wishlist-action.php?action=add&id=' + productId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        alert('Added to wishlist!');
        var badges = document.querySelectorAll('.nav-icon .badge');
        if (badges[1]) badges[1].textContent = data.count;
      } else if (data.login) {
        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
      }
    });
}
</script>

<style>
.product-detail-layout {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 60px; margin-bottom: 60px;
}
.product-gallery { position: sticky; top: 100px; height: fit-content; }
.main-image {
  position: relative; border-radius: 20px; overflow: hidden;
  background: var(--bg-card); border: 1px solid var(--border);
}
.main-image img { width: 100%; height: 550px; object-fit: cover; }
.thumbnail-list {
  display: flex; gap: 12px; margin-top: 16px;
}
.thumb {
  width: 80px; height: 80px; border-radius: 12px;
  overflow: hidden; cursor: pointer; border: 2px solid transparent;
  transition: border-color 0.3s;
}
.thumb.active { border-color: var(--accent); }
.thumb img { width: 100%; height: 100%; object-fit: cover; }

.product-details { padding-top: 20px; }
.product-category-tag {
  font-size: 0.78rem; font-weight: 600; color: var(--accent);
  letter-spacing: 1.5px; text-transform: uppercase;
}
.product-title { font-size: 2rem; margin: 10px 0 14px; }
.product-rating { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.product-rating .stars { color: var(--accent); font-size: 1rem; }
.product-rating .reviews { font-size: 0.85rem; color: var(--text-lt); }
.product-price-large { margin-bottom: 20px; }
.old-price-large {
  font-size: 1.2rem; color: var(--text-lt); text-decoration: line-through; margin-right: 12px;
}
.current-price { font-size: 2rem; font-weight: 700; color: var(--accent); }
.rental-option {
  display: block; font-size: 0.9rem; color: var(--primary); margin-top: 6px;
}
.product-short-desc { color: var(--text-lt); line-height: 1.8; margin-bottom: 28px; }

.option-group { margin-bottom: 22px; }
.option-group label { font-size: 0.85rem; font-weight: 600; margin-bottom: 10px; display: block; }
.size-options, .color-options, .type-options { display: flex; gap: 10px; flex-wrap: wrap; }
.size-option input, .color-option input, .type-option input { display: none; }
.size-option span {
  display: inline-block; padding: 8px 18px; border: 1.5px solid var(--border);
  border-radius: 10px; font-size: 0.85rem; cursor: pointer; transition: all 0.3s;
}
.size-option input:checked + span { background: var(--accent); border-color: var(--accent); color: #fff; }
.color-option span {
  display: block; width: 36px; height: 36px; border-radius: 50%;
  border: 2px solid var(--border); cursor: pointer; transition: all 0.3s;
}
.color-option input:checked + span { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(201,168,76,0.3); }
.type-option span {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 20px; border: 1.5px solid var(--border);
  border-radius: 10px; font-size: 0.85rem; cursor: pointer; transition: all 0.3s;
}
.type-option input:checked + span { background: var(--accent); border-color: var(--accent); color: #fff; }
.qty-selector { display: flex; align-items: center; gap: 0; }
.qty-selector button {
  width: 42px; height: 42px; border: 1.5px solid var(--border);
  background: var(--bg); font-size: 1.2rem; cursor: pointer;
}
.qty-selector button:first-child { border-radius: 10px 0 0 10px; }
.qty-selector button:last-child { border-radius: 0 10px 10px 0; }
.qty-selector input {
  width: 60px; height: 42px; border: 1.5px solid var(--border);
  border-left: none; border-right: none; text-align: center;
  font-size: 1rem; font-weight: 600;
}
.product-actions { display: flex; gap: 12px; margin: 28px 0; }
.product-meta { margin: 24px 0; padding: 20px 0; border-top: 1px solid var(--border); }
.meta-item { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-size: 0.88rem; color: var(--text-lt); }
.meta-item i { color: var(--accent); width: 20px; }
.product-share { display: flex; align-items: center; gap: 14px; }
.product-share span { font-size: 0.85rem; color: var(--text-lt); }
.product-share a {
  width: 38px; height: 38px; border-radius: 10px;
  background: var(--bg-card); border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-lt); transition: all 0.3s;
}
.product-share a:hover { background: var(--accent); border-color: var(--accent); color: #fff; }

.product-tabs { margin-top: 40px; }
.tab-buttons { display: flex; gap: 0; border-bottom: 2px solid var(--border); }
.tab-btn {
  padding: 14px 28px; background: none; border: none;
  font-family: 'Poppins', sans-serif; font-size: 0.9rem;
  font-weight: 500; color: var(--text-lt); cursor: pointer;
  position: relative; transition: color 0.3s;
}
.tab-btn.active { color: var(--accent); }
.tab-btn.active::after {
  content: ''; position: absolute; bottom: -2px; left: 0; right: 0;
  height: 2px; background: var(--accent);
}
.tab-content { padding: 32px 0; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }
.tab-inner { color: var(--text-lt); line-height: 1.9; }
.tab-inner h4 { color: var(--text); margin: 20px 0 10px; font-size: 1.1rem; }
.details-table { width: 100%; max-width: 500px; }
.details-table td { padding: 12px 0; border-bottom: 1px solid var(--border); font-size: 0.9rem; }
.details-table td:first-child { color: var(--text-lt); width: 40%; }
.details-table td:last-child { color: var(--text); font-weight: 500; }

.related-products { margin-top: 80px; }

@media (max-width: 1024px) {
  .product-detail-layout { grid-template-columns: 1fr; }
  .product-gallery { position: static; }
  .main-image img { height: 400px; }
}
</style>

<?php require_once 'footer.php'; ?>