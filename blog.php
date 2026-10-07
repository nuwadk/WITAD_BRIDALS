<?php
$pageTitle = 'Journal';
$pageDesc = 'Read the latest bridal tips, wedding trends, styling advice, and inspiration from Witad Bridal Collection in Kabwohe-Sheema, Uganda.';
$canonical = 'https://witadbridal.com/blog';
require_once 'header.php';

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = "WHERE status = 'published'";
$params = array();
$types = '';

if ($category) {
    $where .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}
if ($search) {
    $where .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $searchLike = "%$search%";
    $params[] = $searchLike;
    $params[] = $searchLike;
    $params[] = $searchLike;
    $types .= 'sss';
}

$sql = "SELECT * FROM blog_posts $where ORDER BY published_at DESC";
$result = paginate($sql, $types, $params, 6);
$posts = $result['items'];
$pageNum = $result['page'];
$totalPages = $result['pages'];
$totalItems = $result['total'];

// Get categories for filter
$categories = fetchAll("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' AND category != '' ORDER BY category");

// Get featured post (latest)
$featuredPost = fetchOne("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 1");
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label">Inspiration & Tips</span>
      <h1>The Bridal Journal</h1>
      <p class="page-sub">Stories, trends, and expert guidance to help you plan the wedding of your dreams.</p>
    </div>
  </div>
</section>

<!-- FEATURED POST -->
<?php if ($featuredPost && $pageNum == 1 && !$category && !$search): ?>
<section class="section" style="padding-bottom:0;">
  <div class="container">
    <div class="featured-post">
      <div class="featured-image">
        <img src="<?php echo $featuredPost['image']; ?>" alt="<?php echo sanitize($featuredPost['title']); ?>" />
      </div>
      <div class="featured-content">
        <span class="featured-badge"><i class="fa fa-star"></i> Featured</span>
        <span class="featured-cat"><?php echo sanitize(ucfirst($featuredPost['category'])); ?></span>
        <h2><a href="blog-post.php?slug=<?php echo $featuredPost['slug']; ?>"><?php echo sanitize($featuredPost['title']); ?></a></h2>
        <p><?php echo sanitize($featuredPost['excerpt']); ?></p>
        <div class="featured-meta">
          <span><i class="fa fa-calendar"></i> <?php echo formatDate($featuredPost['published_at']); ?></span>
          <span><i class="fa fa-eye"></i> <?php echo $featuredPost['views']; ?> views</span>
        </div>
        <a href="blog-post.php?slug=<?php echo $featuredPost['slug']; ?>" class="btn btn-primary">
          <i class="fa fa-arrow-right"></i> Read Article
        </a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- BLOG GRID -->
<section class="section">
  <div class="container">
    <!-- FILTERS & SEARCH -->
    <div class="blog-toolbar">
      <div class="blog-categories">
        <a href="blog.php" class="<?php echo !$category ? 'active' : ''; ?>">All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="blog.php?category=<?php echo urlencode($cat['category']); ?>" class="<?php echo $category == $cat['category'] ? 'active' : ''; ?>">
          <?php echo sanitize(ucfirst($cat['category'])); ?>
        </a>
        <?php endforeach; ?>
      </div>
      <form method="GET" action="" class="blog-search">
        <?php if ($category): ?><input type="hidden" name="category" value="<?php echo $category; ?>" /><?php endif; ?>
        <input type="text" name="search" placeholder="Search articles..." value="<?php echo sanitize($search); ?>" />
        <button type="submit"><i class="fa fa-search"></i></button>
      </form>
    </div>

    <?php if (!empty($posts)): ?>
    <div class="blog-grid">
      <?php foreach ($posts as $post): ?>
      <div class="blog-card">
        <div class="blog-image">
          <img src="<?php echo $post['image']; ?>" alt="<?php echo sanitize($post['title']); ?>" />
          <span class="blog-cat-badge"><?php echo sanitize(ucfirst($post['category'])); ?></span>
        </div>
        <div class="blog-card-body">
          <h3><a href="blog-post.php?slug=<?php echo $post['slug']; ?>"><?php echo sanitize($post['title']); ?></a></h3>
          <p><?php echo sanitize($post['excerpt']); ?></p>
          <div class="blog-meta">
            <span><i class="fa fa-calendar"></i> <?php echo formatDate($post['published_at']); ?></span>
            <span><i class="fa fa-eye"></i> <?php echo $post['views']; ?> views</span>
          </div>
          <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" class="read-more">Read More <i class="fa fa-arrow-right"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($pageNum > 1): ?>
      <a href="blog.php?p=<?php echo $pageNum - 1; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><i class="fa fa-chevron-left"></i></a>
      <?php endif; ?>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i == $pageNum): ?>
        <span><?php echo $i; ?></span>
        <?php else: ?>
        <a href="blog.php?p=<?php echo $i; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($pageNum < $totalPages): ?>
      <a href="blog.php?p=<?php echo $pageNum + 1; ?><?php echo $category ? '&category='.urlencode($category) : ''; ?><?php echo $search ? '&search='.urlencode($search) : ''; ?>"><i class="fa fa-chevron-right"></i></a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state" style="text-align:center;padding:80px 20px;">
      <i class="fa fa-book-open" style="font-size:4rem;color:var(--border);margin-bottom:20px;"></i>
      <h3 style="font-size:1.4rem;margin-bottom:10px;">No Articles Found</h3>
      <p style="color:var(--text-lt);">Check back soon for new bridal inspiration and tips.</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- NEWSLETTER CTA -->
<section class="section" style="padding:90px 0;">
  <div class="cta-banner">
    <span class="section-label" style="display:block;margin-bottom:8px;">Stay Inspired</span>
    <h2>Subscribe to Our Journal</h2>
    <p>Get the latest bridal trends, styling tips, and exclusive offers delivered straight to your inbox.</p>
    <form method="POST" action="newsletter.php" class="newsletter-form">
      <?php echo csrfField(); ?>
      <input type="email" name="email" placeholder="Enter your email" required />
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-paper-plane"></i> Subscribe
      </button>
    </form>
  </div>
</section>

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

/* FEATURED POST */
.featured-post {
  display: grid; grid-template-columns: 1.2fr 1fr;
  gap: 48px; align-items: center;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 24px; overflow: hidden;
}
.featured-image { height: 480px; overflow: hidden; }
.featured-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.featured-post:hover .featured-image img { transform: scale(1.03); }
.featured-content { padding: 48px 40px 48px 0; }
.featured-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: linear-gradient(135deg, var(--accent), var(--accent-dk));
  color: #fff; padding: 6px 16px; border-radius: 50px;
  font-size: 0.75rem; font-weight: 600; letter-spacing: 1px;
  margin-bottom: 16px;
}
.featured-cat {
  display: block; color: var(--accent); font-size: 0.85rem;
  font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;
  margin-bottom: 12px;
}
.featured-content h2 {
  font-size: 1.8rem; margin-bottom: 16px; line-height: 1.3;
}
.featured-content h2 a { color: var(--text); transition: color 0.3s; }
.featured-content h2 a:hover { color: var(--accent); }
.featured-content p {
  color: var(--text-lt); font-size: 0.95rem; line-height: 1.8;
  margin-bottom: 24px;
}
.featured-meta {
  display: flex; gap: 20px; margin-bottom: 28px;
  font-size: 0.85rem; color: var(--text-lt);
}
.featured-meta i { color: var(--accent); margin-right: 4px; }

/* BLOG TOOLBAR */
.blog-toolbar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 40px; flex-wrap: wrap; gap: 16px;
}
.blog-categories { display: flex; gap: 10px; flex-wrap: wrap; }
.blog-categories a {
  padding: 8px 20px; border-radius: 50px;
  border: 1.5px solid var(--border); background: var(--bg-card);
  color: var(--text-lt); font-size: 0.85rem; font-weight: 500;
  transition: all 0.3s;
}
.blog-categories a:hover, .blog-categories a.active {
  background: var(--accent); color: #fff; border-color: var(--accent);
}
.blog-search { display: flex; gap: 8px; }
.blog-search input {
  padding: 10px 16px; border: 1.5px solid var(--border);
  border-radius: 10px; background: var(--bg); font-size: 0.85rem; outline: none;
  width: 220px;
}
.blog-search input:focus { border-color: var(--accent); }
.blog-search button {
  padding: 10px 16px; background: var(--accent); color: #fff;
  border: none; border-radius: 10px; cursor: pointer;
}

/* BLOG GRID */
.blog-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 28px;
}
.blog-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 18px; overflow: hidden;
  transition: transform 0.3s, box-shadow 0.3s;
}
.blog-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px var(--shadow); }
.blog-image { position: relative; height: 220px; overflow: hidden; }
.blog-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.blog-card:hover .blog-image img { transform: scale(1.05); }
.blog-cat-badge {
  position: absolute; top: 12px; left: 12px;
  background: var(--accent); color: #fff;
  padding: 4px 12px; border-radius: 50px;
  font-size: 0.72rem; font-weight: 600; letter-spacing: 0.5px;
}
.blog-card-body { padding: 24px; }
.blog-card-body h3 { font-size: 1.1rem; margin-bottom: 10px; line-height: 1.4; }
.blog-card-body h3 a { color: var(--text); transition: color 0.3s; }
.blog-card-body h3 a:hover { color: var(--accent); }
.blog-card-body p { font-size: 0.87rem; color: var(--text-lt); line-height: 1.7; margin-bottom: 16px; }
.blog-meta {
  font-size: 0.8rem; color: var(--text-lt); margin-bottom: 16px;
  display: flex; gap: 16px;
}
.blog-meta i { color: var(--accent); margin-right: 4px; }
.read-more {
  font-size: 0.88rem; font-weight: 600; color: var(--accent);
  display: inline-flex; align-items: center; gap: 6px;
  transition: gap 0.3s;
}
.read-more:hover { gap: 10px; }

/* PAGINATION */
.pagination {
  display: flex; justify-content: center; gap: 8px; margin-top: 48px;
}
.pagination a, .pagination span {
  padding: 10px 16px; border-radius: 10px; font-size: 0.9rem;
  border: 1.5px solid var(--border); transition: all 0.3s;
}
.pagination a { color: var(--text); }
.pagination a:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
.pagination span { background: var(--accent); color: #fff; border-color: var(--accent); }

/* NEWSLETTER CTA */
.cta-banner {
  background: linear-gradient(135deg, rgba(244,167,185,0.15), rgba(201,168,76,0.1)), var(--bg-card);
  border-radius: 28px; padding: 72px 60px;
  text-align: center; margin: 0 24px;
  border: 1px solid var(--border);
}
.cta-banner h2 { font-size: clamp(2rem, 4vw, 2.8rem); margin-bottom: 16px; }
.cta-banner p { color: var(--text-lt); font-size: 1rem; max-width: 520px; margin: 0 auto 36px; }
.newsletter-form {
  display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;
  max-width: 480px; margin: 0 auto;
}
.newsletter-form input {
  flex: 1; padding: 14px 20px; border: 1.5px solid var(--border);
  border-radius: 50px; background: var(--bg); font-size: 0.9rem; outline: none;
  min-width: 260px;
}
.newsletter-form input:focus { border-color: var(--accent); }
.newsletter-form button {
  padding: 14px 28px; background: var(--accent); color: #fff;
  border: none; border-radius: 50px; font-weight: 600;
  cursor: pointer; transition: all 0.3s;
}
.newsletter-form button:hover { background: var(--accent-dk); }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .featured-post { grid-template-columns: 1fr; }
  .featured-image { height: 300px; }
  .featured-content { padding: 32px; }
}
@media (max-width: 768px) {
  .blog-toolbar { flex-direction: column; align-items: stretch; }
  .blog-search input { width: 100%; }
  .cta-banner { padding: 52px 28px; margin: 0 12px; }
  .page-hero-content { padding: 100px 20px 40px; }
  .newsletter-form { flex-direction: column; }
  .newsletter-form input { min-width: auto; }
}
</style>

<?php require_once 'footer.php'; ?>