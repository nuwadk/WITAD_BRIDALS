<?php
$pageTitle = 'Bridal Collection';
$pageDesc = 'Browse our curated collection of Mushanana, bridesmaids dresses, accessories, and more.';
$canonical = 'https://witadbridal.com/products.php';
require_once 'header.php';

// Handle image upload directly on this page (admin only)
$uploadMessage = '';
$uploadType = '';
if (isset($_SESSION['admin_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
    $productId = intval($_POST['product_id']);
    $uploadDir = 'uploads/products/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['product_image'];
    $product = fetchOne("SELECT id, name, image FROM products WHERE id = ?", 'i', array($productId));
    
    if ($product && $file['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (in_array($mimeType, $allowedTypes) && $file['size'] <= 5 * 1024 * 1024) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilename = 'product_' . $productId . '_' . time() . '_' . substr(md5(uniqid()), 0, 8) . '.' . strtolower($ext);
            $targetPath = $uploadDir . $newFilename;
            
            if (!empty($product['image']) && file_exists($product['image']) && strpos($product['image'], 'uploads/') === 0) {
                @unlink($product['image']);
            }
            
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                executeQuery("UPDATE products SET image = ? WHERE id = ?", 'si', array($targetPath, $productId));
                $uploadMessage = 'Image updated for "' . sanitize($product['name']) . '"!';
                $uploadType = 'success';
            } else {
                $uploadMessage = 'Failed to save image file.';
                $uploadType = 'error';
            }
        } else {
            $uploadMessage = 'Invalid file. Use JPG/PNG/GIF/WebP under 5MB.';
            $uploadType = 'error';
        }
    }
}

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000000;
$style = isset($_GET['style']) ? $_GET['style'] : '';

// Build query
$where = "WHERE p.status = 'active'";
$params = array();
$types = '';

if ($category) {
    $where .= " AND c.slug = ?";
    $params[] = $category;
    $types .= 's';
}
if ($search) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.short_desc LIKE ?)";
    $searchLike = "%$search%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= 'sss';
}
if ($minPrice > 0) {
    $where .= " AND (p.sale_price > 0 AND p.sale_price >= ? OR p.sale_price = 0 AND p.price >= ?)";
    $params[] = $minPrice;
    $params[] = $minPrice;
    $types .= 'dd';
}
if ($maxPrice < 10000000) {
    $where .= " AND (p.sale_price > 0 AND p.sale_price <= ? OR p.sale_price = 0 AND p.price <= ?)";
    $params[] = $maxPrice;
    $params[] = $maxPrice;
    $types .= 'dd';
}
if ($style) {
    $where .= " AND p.style = ?";
    $params[] = $style;
    $types .= 's';
}

// Sort
$orderBy = "ORDER BY p.created_at DESC";
switch ($sort) {
    case 'price_low': $orderBy = "ORDER BY (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) ASC"; break;
    case 'price_high': $orderBy = "ORDER BY (CASE WHEN p.sale_price > 0 THEN p.sale_price ELSE p.price END) DESC"; break;
    case 'popular': $orderBy = "ORDER BY p.views DESC"; break;
    case 'name': $orderBy = "ORDER BY p.name ASC"; break;
}

$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id $where $orderBy";
$result = paginate($sql, $types, $params, 12);
$products = $result['items'];
$pageNum = $result['page'];
$totalPages = $result['pages'];
$totalItems = $result['total'];

// Get categories for filter
$categories = fetchAll("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order");

// Get styles for filter
$styles = fetchAll("SELECT DISTINCT style FROM products WHERE status = 'active' AND style != '' ORDER BY style");

// Find category name for breadcrumb
$categoryName = 'Category';
if ($category && !empty($categories)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] == $category) {
            $categoryName = $cat['name'];
            break;
        }
    }
}

// Helper function for image path
function getProductImage($product) {
    if (!empty($product['image']) && file_exists($product['image'])) {
        return $product['image'];
    }
    // Return placeholder - create this image or it will show broken image icon
    return 'assets/images/placeholder-product.jpg';
}
?>

<!-- BREADCRUMBS -->
<div class="breadcrumbs">
  <div class="container">
    <h1>Our Collection</h1>
    <div class="crumb">
      <a href="index.php">Home</a> <i class="fa fa-chevron-right"></i>
      <span>Collection</span>
      <?php if ($category): ?>
      <i class="fa fa-chevron-right"></i>
      <span><?php echo sanitize($categoryName); ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>

<section class="section" style="padding-top:60px;">
  <div class="container">
    <div class="shop-layout">
      <!-- SIDEBAR FILTERS -->
      <aside class="shop-sidebar">
        <div class="filter-card">
          <h4><i class="fa fa-search"></i> Search</h4>
          <form method="GET" action="">
            <input type="hidden" name="category" value="<?php echo $category; ?>" />
            <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
            <div class="search-input">
              <input type="text" name="search" placeholder="Search gowns..." value="<?php echo sanitize($search); ?>" />
              <button type="submit"><i class="fa fa-search"></i></button>
            </div>
          </form>
        </div>

        <div class="filter-card">
          <h4><i class="fa fa-list"></i> Categories</h4>
          <ul class="filter-list">
            <li><a href="products.php<?php echo $search ? '?search='.urlencode($search).'&sort='.$sort : '?sort='.$sort; ?>" class="<?php echo !$category ? 'active' : ''; ?>">All Categories</a></li>
            <?php foreach ($categories as $cat): ?>
            <li>
              <a href="products.php?category=<?php echo $cat['slug']; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>&sort=<?php echo $sort; ?>"
                 class="<?php echo $category == $cat['slug'] ? 'active' : ''; ?>">
                <?php echo sanitize($cat['name']); ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="filter-card">
          <h4><i class="fa fa-filter"></i> Style</h4>
          <ul class="filter-list">
            <li><a href="products.php" class="<?php echo !$style ? 'active' : ''; ?>">All Styles</a></li>
            <?php foreach ($styles as $s): ?>
            <li>
              <a href="products.php?style=<?php echo urlencode($s['style']); ?>"
                 class="<?php echo $style == $s['style'] ? 'active' : ''; ?>">
                <?php echo sanitize($s['style']); ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div class="filter-card">
          <h4><i class="fa fa-tag"></i> Price Range</h4>
          <form method="GET" action="">
            <input type="hidden" name="category" value="<?php echo $category; ?>" />
            <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
            <?php if ($search): ?><input type="hidden" name="search" value="<?php echo sanitize($search); ?>" /><?php endif; ?>
            <?php if ($style): ?><input type="hidden" name="style" value="<?php echo $style; ?>" /><?php endif; ?>
            <div class="price-range">
              <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" />
              <span>-</span>
              <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice < 10000000 ? $maxPrice : ''; ?>" />
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-check"></i></button>
            </div>
          </form>
        </div>
      </aside>

      <!-- PRODUCTS GRID -->
      <div class="shop-main">
        <div class="shop-toolbar">
          <p class="results-count">Showing <?php echo count($products); ?> of <?php echo $totalItems; ?> products</p>
          <div class="sort-dropdown">
            <select onchange="window.location.href=this.value">
              <option value="products.php?sort=newest<?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>" <?php echo $sort=='newest'?'selected':''; ?>>Newest First</option>
              <option value="products.php?sort=price_low<?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>" <?php echo $sort=='price_low'?'selected':''; ?>>Price: Low to High</option>
              <option value="products.php?sort=price_high<?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>" <?php echo $sort=='price_high'?'selected':''; ?>>Price: High to Low</option>
              <option value="products.php?sort=popular<?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>" <?php echo $sort=='popular'?'selected':''; ?>>Most Popular</option>
              <option value="products.php?sort=name<?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>" <?php echo $sort=='name'?'selected':''; ?>>Name A-Z</option>
            </select>
          </div>
        </div>

        <?php if ($uploadMessage): ?>
        <div class="upload-alert upload-alert-<?php echo $uploadType; ?>">
          <?php echo sanitize($uploadMessage); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($products)): ?>
        <div class="products-grid">
          <?php foreach ($products as $product):
            $price = $product['sale_price'] ? $product['sale_price'] : $product['price'];
            $isSale = $product['sale_price'] ? true : false;
            $imgSrc = getProductImage($product);
            $hasImage = !empty($product['image']) && file_exists($product['image']);
          ?>
          <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
            <div class="product-image">
              <?php if ($hasImage): ?>
              <img src="<?php echo $imgSrc; ?>" alt="<?php echo sanitize($product['name']); ?>" loading="lazy" />
              <?php else: ?>
              <div class="product-image-placeholder">
                <i class="fa fa-image"></i>
                <span>No Image</span>
              </div>
              <?php endif; ?>
              
              <?php if ($isSale): ?><span class="product-badge sale">Sale</span><?php endif; ?>
              <?php if ($product['rental_price']): ?><span class="product-badge rent">For Rent</span><?php endif; ?>
              
              <div class="product-overlay">
                <a href="product-detail.php?slug=<?php echo $product['slug']; ?>" class="btn btn-primary btn-sm">View Details</a>
                <button onclick="addToCartAjax(<?php echo $product['id']; ?>)" class="btn btn-outline btn-sm" style="margin-top:8px;">
                  <i class="fa fa-shopping-bag"></i> Add to Cart
                </button>
                
                <?php if (isset($_SESSION['admin_id'])): ?>
                <!-- Admin: Quick image upload -->
                <button onclick="showUploadForm(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')" class="btn btn-sm btn-upload" style="margin-top:8px;">
                  <i class="fa fa-camera"></i> Change Image
                </button>
                <?php endif; ?>
              </div>
            </div>
            <div class="product-info">
              <span class="product-category"><?php echo $product['category_name']; ?></span>
              <h3 class="product-name"><a href="product-detail.php?slug=<?php echo $product['slug']; ?>"><?php echo sanitize($product['name']); ?></a></h3>
              <div class="product-price">
                <?php if ($isSale): ?><span class="old-price"><?php echo formatPrice($product['price']); ?></span><?php endif; ?>
                <span class="price"><?php echo formatPrice($price); ?></span>
                <?php if ($product['rental_price']): ?><span class="rental-price">Rent: <?php echo formatPrice($product['rental_price']); ?></span><?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($pageNum > 1): ?>
          <a href="products.php?p=<?php echo $pageNum - 1; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort ? '&sort='.urlencode($sort) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>"><i class="fa fa-chevron-left"></i></a>
          <?php endif; ?>
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $pageNum): ?>
            <span><?php echo $i; ?></span>
            <?php else: ?>
            <a href="products.php?p=<?php echo $i; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort ? '&sort='.urlencode($sort) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
          <?php endfor; ?>
          <?php if ($pageNum < $totalPages): ?>
          <a href="products.php?p=<?php echo $pageNum + 1; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?><?php echo $sort ? '&sort='.urlencode($sort) : ''; ?><?php echo $style ? '&style='.urlencode($style) : ''; ?>"><i class="fa fa-chevron-right"></i></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state" style="text-align:center;padding:80px 20px;">
          <i class="fa fa-search" style="font-size:4rem;color:var(--border);margin-bottom:20px;"></i>
          <h3 style="font-size:1.4rem;margin-bottom:10px;">No Products Found</h3>
          <p style="color:var(--text-lt);">Try adjusting your search or filters.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Admin Upload Modal -->
<?php if (isset($_SESSION['admin_id'])): ?>
<div id="uploadModal" class="upload-modal">
  <div class="upload-modal-content">
    <span class="upload-modal-close" onclick="closeUploadModal()">&times;</span>
    <h3><i class="fa fa-camera"></i> Update Product Image</h3>
    <p id="uploadProductName" style="color:var(--text-lt);margin-bottom:16px;"></p>
    <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
      <input type="hidden" name="product_id" id="uploadProductId" value="" />
      <input type="hidden" name="upload_image" value="1" />
      <div style="margin-bottom:16px;">
        <input type="file" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp" required 
          style="width:100%;padding:12px;border:1.5px solid var(--border);border-radius:10px;background:var(--bg);" />
        <small style="color:var(--text-lt);display:block;margin-top:6px;">JPG, PNG, GIF, WebP. Max 5MB.</small>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-upload"></i> Upload Image
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

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

<?php if (isset($_SESSION['admin_id'])): ?>
function showUploadForm(productId, productName) {
  document.getElementById('uploadProductId').value = productId;
  document.getElementById('uploadProductName').textContent = productName;
  document.getElementById('uploadModal').style.display = 'flex';
}

function closeUploadModal() {
  document.getElementById('uploadModal').style.display = 'none';
}

// Close modal on outside click
window.onclick = function(event) {
  var modal = document.getElementById('uploadModal');
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}
<?php endif; ?>
</script>

<style>
.shop-layout {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 40px;
}
.shop-sidebar { position: sticky; top: 100px; height: fit-content; }
.filter-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 24px; margin-bottom: 20px;
}
.filter-card h4 {
  font-size: 0.95rem; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.filter-card h4 i { color: var(--accent); }
.search-input {
  display: flex; gap: 8px;
}
.search-input input {
  flex: 1; padding: 10px 14px; border: 1.5px solid var(--border);
  border-radius: 10px; background: var(--bg); font-size: 0.85rem; outline: none;
}
.search-input input:focus { border-color: var(--accent); }
.search-input button {
  padding: 10px 14px; background: var(--accent); color: #fff;
  border: none; border-radius: 10px; cursor: pointer;
}
.filter-list { list-style: none; }
.filter-list li { margin-bottom: 8px; }
.filter-list a {
  font-size: 0.88rem; color: var(--text-lt); display: block;
  padding: 6px 10px; border-radius: 8px; transition: all 0.3s;
}
.filter-list a:hover, .filter-list a.active {
  background: rgba(201,168,76,0.1); color: var(--accent);
}
.price-range { display: flex; align-items: center; gap: 8px; }
.price-range input {
  width: 80px; padding: 8px 10px; border: 1.5px solid var(--border);
  border-radius: 8px; font-size: 0.85rem; outline: none;
}
.price-range input:focus { border-color: var(--accent); }
.shop-toolbar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 28px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
}
.results-count { font-size: 0.9rem; color: var(--text-lt); }
.sort-dropdown select {
  padding: 8px 14px; border: 1.5px solid var(--border);
  border-radius: 10px; background: var(--bg-card); font-size: 0.85rem;
  color: var(--text); outline: none; cursor: pointer;
}

/* Product Image Placeholder */
.product-image-placeholder {
  width: 100%;
  aspect-ratio: 3/4;
  background: linear-gradient(135deg, var(--border) 0%, var(--bg-card) 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: var(--text-lt);
}
.product-image-placeholder i {
  font-size: 3rem;
  margin-bottom: 8px;
}
.product-image-placeholder span {
  font-size: 0.85rem;
}

/* Upload Alert */
.upload-alert {
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-size: 0.9rem;
}
.upload-alert-success {
  background: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}
.upload-alert-error {
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Admin Upload Button */
.btn-upload {
  background: rgba(255,255,255,0.95);
  color: var(--text);
  border: 1px solid var(--border);
}
.btn-upload:hover {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}

/* Upload Modal */
.upload-modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  align-items: center;
  justify-content: center;
}
.upload-modal-content {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 28px;
  width: 90%;
  max-width: 400px;
  position: relative;
}
.upload-modal-close {
  position: absolute;
  right: 16px;
  top: 12px;
  font-size: 1.5rem;
  cursor: pointer;
  color: var(--text-lt);
}
.upload-modal-close:hover {
  color: var(--text);
}

.pagination {
  display: flex; justify-content: center; gap: 8px; margin-top: 40px;
}
.pagination a, .pagination span {
  padding: 10px 16px; border-radius: 10px; font-size: 0.9rem;
  border: 1.5px solid var(--border); transition: all 0.3s;
}
.pagination a { color: var(--text); }
.pagination a:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
.pagination span { background: var(--accent); color: #fff; border-color: var(--accent); }

@media (max-width: 768px) {
  .shop-layout { grid-template-columns: 1fr; }
  .shop-sidebar { position: static; }
}
</style>

<?php require_once 'footer.php'; ?>