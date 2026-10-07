<?php

require_once 'header.php';

// Get slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: blog.php');
    exit;
}

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id > 0) {
    $stmt = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch the blog post
$post = fetchOne("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'", "s", array($slug));

if (!$post) {
    header('Location: blog.php');
    exit;
}

// Update view count
query("UPDATE blog_posts SET views = views + 1 WHERE id = ?", "i", array($post['id']));

// Set page metadata
$pageTitle = $post['title'];
$pageDesc = $post['excerpt'];
$canonical = 'https://witadbridal.com/blog-post.php?slug=' . $slug;

// Get related posts
$relatedPosts = fetchAll("SELECT * FROM blog_posts WHERE status = 'published' AND id != ? AND category = ? ORDER BY published_at DESC LIMIT 3", "is", array($post['id'], $post['category']));

// Get all categories for sidebar
$categories = fetchAll("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' AND category != '' ORDER BY category");

// Get recent posts for sidebar
$recentPosts = fetchAll("SELECT title, slug, published_at FROM blog_posts WHERE status = 'published' AND id != ? ORDER BY published_at DESC LIMIT 5", "i", array($post['id']));
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-content">
      <span class="page-label"><?php echo sanitize(ucfirst($post['category'])); ?></span>
      <h1><?php echo sanitize($post['title']); ?></h1>
      <div class="post-meta-hero">
        <span><i class="fa fa-calendar"></i> <?php echo formatDate($post['published_at']); ?></span>
        <span><i class="fa fa-eye"></i> <?php echo $post['views'] + 1; ?> views</span>
        <span><i class="fa fa-user"></i> <?php echo sanitize($post['author']); ?></span>
      </div>
    </div>
  </div>
</section>

<!-- BLOG POST CONTENT -->
<section class="section">
  <div class="container">
    <div class="blog-post-layout">
      <!-- MAIN CONTENT -->
      <article class="blog-post-main">
        <div class="post-featured-image">
          <img src="<?php echo $post['image']; ?>" alt="<?php echo sanitize($post['title']); ?>" />
        </div>

        <div class="post-content">
          <?php echo nl2br($post['content']); ?>
        </div>

        <!-- SHARE -->
        <div class="post-share">
          <span>Share this article:</span>
          <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical); ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($canonical); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' ' . $canonical); ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <a href="mailto:?subject=<?php echo urlencode($post['title']); ?>&body=<?php echo urlencode($canonical); ?>" title="Email"><i class="fa fa-envelope"></i></a>
        </div>

        <!-- AUTHOR BOX -->
        <div class="author-box">
          <div class="author-avatar" style="background:linear-gradient(135deg,var(--accent),var(--primary));color:#fff;">
            <?php echo initials($post['author']); ?>
          </div>
          <div class="author-info">
            <h4><?php echo sanitize($post['author']); ?></h4>
            <p>Bridal expert and writer at Witad Bridal Collection, sharing insights and inspiration for brides across Uganda.</p>
          </div>
        </div>
      </article>

      <!-- SIDEBAR -->
      <aside class="blog-sidebar">
        <!-- Search -->
        <div class="sidebar-card">
          <h4><i class="fa fa-search"></i> Search</h4>
          <form method="GET" action="blog.php">
            <div class="search-input">
              <input type="text" name="search" placeholder="Search articles..." />
              <button type="submit"><i class="fa fa-search"></i></button>
            </div>
          </form>
        </div>

        <!-- Categories -->
        <div class="sidebar-card">
          <h4><i class="fa fa-list"></i> Categories</h4>
          <ul class="sidebar-list">
            <?php foreach ($categories as $cat): ?>
            <li>
              <a href="blog.php?category=<?php echo urlencode($cat['category']); ?>">
                <?php echo sanitize(ucfirst($cat['category'])); ?>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Recent Posts -->
        <div class="sidebar-card">
          <h4><i class="fa fa-clock"></i> Recent Posts</h4>
          <ul class="sidebar-recent">
            <?php foreach ($recentPosts as $rp): ?>
            <li>
              <a href="blog-post.php?slug=<?php echo $rp['slug']; ?>">
                <span class="recent-title"><?php echo sanitize($rp['title']); ?></span>
                <span class="recent-date"><?php echo formatDate($rp['published_at']); ?></span>
              </a>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Newsletter -->
        <div class="sidebar-card sidebar-newsletter">
          <h4><i class="fa fa-envelope"></i> Newsletter</h4>
          <p>Get bridal tips and trends delivered to your inbox.</p>
          <a href="newsletter.php" class="btn btn-primary btn-sm" style="width:100%;">
            <i class="fa fa-paper-plane"></i> Subscribe
          </a>
        </div>
      </aside>
    </div>
  </div>
</section>

<!-- RELATED POSTS -->
<?php if (!empty($relatedPosts)): ?>
<section class="section related-section">
  <div class="container">
    <div class="centered">
      <span class="section-label">You May Also Like</span>
      <h2 class="section-title" style="color:#fff;">Related Articles</h2>
      <div class="divider center"></div>
    </div>
    <div class="related-grid">
      <?php foreach ($relatedPosts as $rp): ?>
      <div class="related-card">
        <img src="<?php echo $rp['image']; ?>" alt="<?php echo sanitize($rp['title']); ?>" />
        <div class="related-body">
          <span class="related-cat"><?php echo sanitize(ucfirst($rp['category'])); ?></span>
          <h4><a href="blog-post.php?slug=<?php echo $rp['slug']; ?>"><?php echo sanitize($rp['title']); ?></a></h4>
          <span class="related-date"><i class="fa fa-calendar"></i> <?php echo formatDate($rp['published_at']); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section" style="padding:90px 0;">
  <div class="cta-banner">
    <span class="section-label" style="display:block;margin-bottom:8px;">Want More?</span>
    <h2>Explore All Articles</h2>
    <p>Discover more bridal inspiration, tips, and stories in our journal.</p>
    <div class="cta-btns">
      <a class="btn btn-primary btn-lg" href="blog.php">
        <i class="fa fa-book-open"></i> View All Articles
      </a>
      <a class="btn btn-outline" href="newsletter.php">
        <i class="fa fa-envelope"></i> Subscribe
      </a>
    </div>
  </div>
</section>

<style>
/* PAGE HERO */
.page-hero {
  min-height: 45vh;
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
  font-size: clamp(2rem, 4vw, 3rem); color: #fff;
  line-height: 1.2; margin-bottom: 20px;
}
.post-meta-hero {
  display: flex; justify-content: center; gap: 24px;
  flex-wrap: wrap; color: rgba(255,255,255,0.7); font-size: 0.88rem;
}
.post-meta-hero i { color: var(--accent); margin-right: 6px; }

/* BLOG POST LAYOUT */
.blog-post-layout {
  display: grid; grid-template-columns: 1fr 300px;
  gap: 48px;
}
.blog-post-main { min-width: 0; }
.post-featured-image {
  border-radius: 20px; overflow: hidden; margin-bottom: 36px;
  height: 400px;
}
.post-featured-image img { width: 100%; height: 100%; object-fit: cover; }
.post-content {
  font-size: 1rem; line-height: 1.9; color: var(--text-lt);
}
.post-content h2 { font-size: 1.5rem; color: var(--text); margin: 32px 0 16px; }
.post-content h3 { font-size: 1.25rem; color: var(--text); margin: 28px 0 12px; }
.post-content p { margin-bottom: 16px; }
.post-content ul, .post-content ol { margin: 16px 0; padding-left: 24px; }
.post-content li { margin-bottom: 8px; }
.post-content strong { color: var(--text); }
.post-content blockquote {
  border-left: 4px solid var(--accent); padding: 16px 24px;
  background: rgba(201,168,76,0.05); margin: 24px 0;
  font-style: italic; color: var(--text);
}
.post-content img { border-radius: 12px; max-width: 100%; margin: 20px 0; }

/* SHARE */
.post-share {
  display: flex; align-items: center; gap: 12px;
  margin: 40px 0; padding: 24px 0;
  border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
}
.post-share span { font-size: 0.9rem; color: var(--text-lt); margin-right: 8px; }
.post-share a {
  width: 40px; height: 40px; border-radius: 50%;
  background: var(--bg); border: 1.5px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  color: var(--text-lt); font-size: 0.95rem; transition: all 0.3s;
}
.post-share a:hover { background: var(--accent); color: #fff; border-color: var(--accent); }

/* AUTHOR BOX */
.author-box {
  display: flex; align-items: center; gap: 20px;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 28px; margin-top: 32px;
}
.author-avatar {
  width: 64px; height: 64px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 1.3rem; flex-shrink: 0;
}
.author-info h4 { font-size: 1.1rem; margin-bottom: 6px; }
.author-info p { font-size: 0.85rem; color: var(--text-lt); line-height: 1.6; margin: 0; }

/* SIDEBAR */
.blog-sidebar { position: sticky; top: 100px; height: fit-content; }
.sidebar-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 16px; padding: 24px; margin-bottom: 20px;
}
.sidebar-card h4 {
  font-size: 0.95rem; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.sidebar-card h4 i { color: var(--accent); }
.search-input { display: flex; gap: 8px; }
.search-input input {
  flex: 1; padding: 10px 14px; border: 1.5px solid var(--border);
  border-radius: 10px; background: var(--bg); font-size: 0.85rem; outline: none;
}
.search-input input:focus { border-color: var(--accent); }
.search-input button {
  padding: 10px 14px; background: var(--accent); color: #fff;
  border: none; border-radius: 10px; cursor: pointer;
}
.sidebar-list { list-style: none; }
.sidebar-list li { margin-bottom: 8px; }
.sidebar-list a {
  font-size: 0.88rem; color: var(--text-lt); display: block;
  padding: 6px 10px; border-radius: 8px; transition: all 0.3s;
}
.sidebar-list a:hover { background: rgba(201,168,76,0.1); color: var(--accent); }
.sidebar-recent { list-style: none; }
.sidebar-recent li { margin-bottom: 14px; }
.sidebar-recent a { display: block; }
.recent-title { font-size: 0.88rem; color: var(--text); display: block; margin-bottom: 4px; transition: color 0.3s; }
.sidebar-recent a:hover .recent-title { color: var(--accent); }
.recent-date { font-size: 0.78rem; color: var(--text-lt); }
.sidebar-newsletter p { font-size: 0.85rem; color: var(--text-lt); margin-bottom: 16px; }

/* RELATED POSTS */
.related-section {
  background: linear-gradient(160deg, #2a1a25 0%, #1e1018 100%);
  position: relative; overflow: hidden;
}
.related-grid {
  display: grid; grid-template-columns: repeat(3, 1fr);
  gap: 28px; margin-top: 52px;
}
.related-card {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(201,168,76,0.15);
  border-radius: 18px; overflow: hidden;
  transition: transform 0.3s;
}
.related-card:hover { transform: translateY(-6px); }
.related-card img { width: 100%; height: 180px; object-fit: cover; }
.related-body { padding: 20px; }
.related-cat {
  font-size: 0.72rem; font-weight: 600; color: var(--accent);
  letter-spacing: 1.5px; text-transform: uppercase; display: block; margin-bottom: 8px;
}
.related-body h4 { font-size: 1rem; margin-bottom: 10px; }
.related-body h4 a { color: #fff; transition: color 0.3s; }
.related-body h4 a:hover { color: var(--accent); }
.related-date { font-size: 0.78rem; color: rgba(255,255,255,0.45); }
.related-date i { margin-right: 4px; }

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
  .blog-post-layout { grid-template-columns: 1fr; }
  .blog-sidebar { position: static; }
  .related-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .related-grid { grid-template-columns: 1fr; }
  .post-featured-image { height: 250px; }
  .author-box { flex-direction: column; text-align: center; }
  .post-meta-hero { gap: 12px; }
  .cta-banner { padding: 52px 28px; margin: 0 12px; }
  .page-hero-content { padding: 100px 20px 40px; }
}
</style>

<?php require_once 'footer.php'; ?>