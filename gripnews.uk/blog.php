<?php
/**
 * GripNews Blog — Crawl Intelligence & Gaming Intel
 * Fetches from GripAI API (gripai.uk/api/blog)
 * SEO: Schema.org BlogPosting, BreadcrumbList, rel=prev/next, clean titles
 */
require_once __DIR__ . '/includes/functions.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);
$path = rtrim($path, '/');

// Determine if listing or single post
$slug = null;
if (preg_match('#^/blog/(.+)$#', $path, $m)) {
    $slug = $m[1];
}

// ── API helper ──
function blog_api(string $endpoint, array $params = []): ?array {
    $url = 'https://gripai.uk/api/' . $endpoint;
    if ($params) $url .= '?' . http_build_query($params);
    $ctx = stream_context_create([
        'http' => ['timeout' => 8, 'header' => "Accept: application/json\r\n"],
        'ssl'  => ['verify_peer' => false]
    ]);
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) return null;
    return json_decode($json, true);
}

function time_ago_blog(string $dt): string {
    $ts = strtotime($dt);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('j M Y', $ts);
}

function post_type_badge(string $title): array {
    if (stripos($title, 'Crawl Intelligence Digest') !== false) {
        return ['INTEL DIGEST', '#8b5cf6', 'intel-digest'];
    }
    if (stripos($title, 'Daily Gaming Intel') !== false || stripos($title, 'Daily Recap') !== false) {
        return ['DAILY RECAP', '#f59e0b', 'daily-recap'];
    }
    if (stripos($title, 'Walkthrough') !== false || stripos($title, 'Strategy Guide') !== false) {
        return ['GUIDE', '#10b981', 'guide'];
    }
    if (stripos($title, 'Fix') !== false || stripos($title, 'Crash') !== false || stripos($title, 'Bug') !== false) {
        return ['FIX GUIDE', '#ef4444', 'fix-guide'];
    }
    return ['ARTICLE', '#3b82f6', 'article'];
}

/** Strip emoji and decode HTML entities for clean SEO title */
function seo_title(string $title): string {
    // Remove emoji (Unicode ranges)
    $clean = preg_replace('/[\x{1F000}-\x{1FFFF}]|[\x{2600}-\x{27BF}]|[\x{FE00}-\x{FE0F}]|[\x{1F900}-\x{1F9FF}]|[\x{200D}]|[\x{20E3}]|[\x{E0020}-\x{E007F}]/u', '', $title);
    $clean = html_entity_decode($clean, ENT_QUOTES, 'UTF-8');
    return trim(preg_replace('/\s+/', ' ', $clean));
}

/** Generate SEO-friendly description from content */
function seo_desc(string $excerpt, string $content = '', int $max = 160): string {
    $text = $excerpt ?: strip_tags($content);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/[\x{1F000}-\x{1FFFF}]|[\x{2600}-\x{27BF}]/u', '', $text);
    $text = preg_replace('/\s+/', ' ', trim($text));
    if (strlen($text) > $max) {
        $text = substr($text, 0, $max - 3) . '...';
    }
    return $text;
}

/** Extract game name from title for keyword tagging */
function extract_game_keywords(string $title): array {
    $keywords = ['gaming', 'game fixes', 'GripNews'];
    if (stripos($title, 'Crawl Intelligence') !== false) {
        $keywords[] = 'crawl intelligence';
        $keywords[] = 'game monitoring';
    }
    if (preg_match('/Fix\s+(.+?)\s+(Gameplay|Crash|Bug|Error|Issue)/i', $title, $m)) {
        $keywords[] = trim($m[1]);
        $keywords[] = trim($m[1]) . ' fixes';
    }
    if (stripos($title, 'Daily') !== false) {
        $keywords[] = 'daily gaming news';
        $keywords[] = 'gaming recap';
    }
    if (stripos($title, 'Walkthrough') !== false) {
        $keywords[] = 'walkthrough';
        $keywords[] = 'strategy guide';
    }
    return $keywords;
}
/** Clean legacy branding from blog post content stored in DB */
function cleanup_content(string $html): string {
    // Replace JaffaAi → GripAi
    $html = str_replace('jaffaAi', 'GripAi', $html);
    $html = str_replace('JaffaAi', 'GripAi', $html);
    $html = str_replace('jaffaai.co.uk', 'gripai.uk', $html);
    // Replace gameai.uk → gripnews.uk
    $html = str_replace('gameai.uk', 'gripnews.uk', $html);
    $html = str_replace('GameAi', 'GripAi', $html);
    // Replace gripai.uk references
    $html = str_replace('sandbox.gripai.uk', 'gripnews.uk/sandbox', $html);
    $html = str_replace('chatbox.gripai.uk', 'gripnews.uk/chatbox', $html);
    $html = str_replace('gripai.uk', 'gripai.uk', $html);
    $html = str_replace('GameGrip', 'GripAi', $html);
    return $html;
}


// ═════════════════════════════════════════════
// SINGLE POST VIEW
// ═════════════════════════════════════════════
if ($slug) {
    // Check local DB first, then API
    require_once __DIR__ . '/blog-config.php';
    try {
        $local_db = blog_db();
        $local_stmt = $local_db->prepare("SELECT * FROM posts WHERE slug = ? AND status = 'published'");
        $local_stmt->execute([$slug]);
        $local_post = $local_stmt->fetch();
        if ($local_post) {
            $post = [
                'title' => $local_post['title'],
                'slug' => $local_post['slug'],
                'excerpt' => $local_post['excerpt'],
                'content' => $local_post['content'],
                'published_at' => $local_post['published_at'],
                'created_at' => $local_post['created_at'],
                'updated_at' => $local_post['updated_at'],
            ];
        } else {
            $data = blog_api("blog/{$slug}");
            $post = $data['post'] ?? null;
        }
    } catch (Exception $e) {
        $data = blog_api("blog/{$slug}");
        $post = $data['post'] ?? null;
    }

    if (!$post) {
        http_response_code(404);
        $page_title = 'Post Not Found — GripNews';
        $page_desc  = 'The requested blog post could not be found.';
        $nav_active = 'blog';
        include __DIR__ . '/includes/header.php';
        echo '<div style="text-align:center;padding:80px 20px;"><h1 style="font-size:2rem;margin-bottom:16px;">Post not found</h1>';
        echo '<p style="color:var(--text-dim);">This article may have been removed or the URL is incorrect.</p>';
        echo '<a href="/blog" style="color:var(--accent);margin-top:20px;display:inline-block;">← Back to Blog</a></div>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }

    $badge = post_type_badge($post['title']);
    $pub_date = $post['published_at'] ?? $post['created_at'] ?? '';
    $word_count = str_word_count(strip_tags($post['content'] ?? ''));
    $read_time = max(1, ceil($word_count / 250));
    $clean_title = seo_title($post['title']);
    $clean_excerpt = seo_desc($post['excerpt'] ?? '', $post['content'] ?? '');
    $keywords = extract_game_keywords($post['title']);

    $page_title = $clean_title . ' — GripNews';
    $page_desc  = $clean_excerpt;
    $page_canonical = SITE_URL . '/blog/' . $post['slug'];
    $og_type = 'article';
    $article_date = $pub_date ? date('Y-m-d', strtotime($pub_date)) : date('Y-m-d');
    $nav_active = 'blog';
    include __DIR__ . '/includes/header.php';
    ?>

<!-- SEO: BlogPosting Schema.org -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": <?= json_encode($clean_title, JSON_UNESCAPED_UNICODE) ?>,
  "description": <?= json_encode($clean_excerpt, JSON_UNESCAPED_UNICODE) ?>,
  "url": <?= json_encode($page_canonical) ?>,
  "datePublished": "<?= $pub_date ? date('c', strtotime($pub_date)) : date('c') ?>",
  "dateModified": "<?= !empty($post['updated_at']) ? date('c', strtotime($post['updated_at'])) : ($pub_date ? date('c', strtotime($pub_date)) : date('c')) ?>",
  "wordCount": <?= $word_count ?>,
  "timeRequired": "PT<?= $read_time ?>M",
  "author": {
    "@type": "Organization",
    "name": "GripAi",
    "url": "https://gripai.uk"
  },
  "publisher": {
    "@type": "Organization",
    "name": "GripNews",
    "url": "https://gripnews.uk",
    "logo": {
      "@type": "ImageObject",
      "url": "https://gripnews.uk/assets/og-image.png"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": <?= json_encode($page_canonical) ?>
  },
  "articleSection": <?= json_encode($badge[0]) ?>,
  "keywords": <?= json_encode(implode(', ', $keywords)) ?>,
  "isPartOf": {
    "@type": "Blog",
    "@id": "https://gripnews.uk/blog",
    "name": "GripNews Blog"
  },
  "inLanguage": "en-GB"
}
</script>

<!-- SEO: BreadcrumbList Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://gripnews.uk" },
    { "@type": "ListItem", "position": 2, "name": "Blog", "item": "https://gripnews.uk/blog" },
    { "@type": "ListItem", "position": 3, "name": <?= json_encode($clean_title, JSON_UNESCAPED_UNICODE) ?>, "item": <?= json_encode($page_canonical) ?> }
  ]
}
</script>

<style>
.blog-crumbs { padding: 16px 20px 0; max-width: 900px; margin: 0 auto; font-size: 0.85rem; color: var(--text-dim); }
.blog-crumbs a { color: var(--accent); text-decoration: none; }
.blog-crumbs .sep { margin: 0 6px; opacity: 0.4; }

.blog-article { max-width: 900px; margin: 0 auto; padding: 24px 20px 60px; }
.blog-article h1 { font-size: 1.8rem; font-weight: 700; line-height: 1.3; margin-bottom: 16px; }
.blog-article .article-meta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-dim); margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.08); }
.blog-article .article-meta .badge-type { padding: 3px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.5px; }
.blog-article .article-content { line-height: 1.7; font-size: 0.95rem; }
.blog-article .article-content h2 { font-size: 1.4rem; margin: 32px 0 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); }
.blog-article .article-content h3 { font-size: 1.15rem; margin: 24px 0 12px; }
.blog-article .article-content h4 { font-size: 1rem; margin: 20px 0 10px; color: var(--accent); }
.blog-article .article-content p { margin: 0 0 14px; }
.blog-article .article-content ul, .blog-article .article-content ol { margin: 0 0 16px; padding-left: 24px; }
.blog-article .article-content li { margin-bottom: 6px; }
.blog-article .article-content a { color: var(--accent); text-decoration: none; }
.blog-article .article-content a:hover { text-decoration: underline; }
.blog-article .article-content img { max-width: 100%; border-radius: 8px; margin: 16px 0; }
.blog-article .article-content table { width: 100%; border-collapse: collapse; margin: 16px 0; }
.blog-article .article-content th, .blog-article .article-content td { padding: 8px 12px; border: 1px solid rgba(255,255,255,0.1); text-align: left; font-size: 0.85rem; }
.blog-article .article-content th { background: rgba(255,255,255,0.05); font-weight: 600; }

.blog-back { display: inline-flex; align-items: center; gap: 6px; color: var(--accent); text-decoration: none; font-size: 0.9rem; margin-bottom: 20px; }
.blog-back:hover { text-decoration: underline; }

.related-section { max-width: 900px; margin: 0 auto 60px; padding: 0 20px; }
.related-section h2 { font-size: 1.2rem; margin-bottom: 16px; }
</style>

<nav class="blog-crumbs" aria-label="Breadcrumb">
    <a href="/">Home</a> <span class="sep" aria-hidden="true">›</span> <a href="/blog">Blog</a> <span class="sep" aria-hidden="true">›</span> <span><?= e($clean_title) ?></span>
</nav>

<article class="blog-article" itemscope itemtype="https://schema.org/BlogPosting">
    <meta itemprop="datePublished" content="<?= $pub_date ? date('c', strtotime($pub_date)) : date('c') ?>">
    <meta itemprop="author" content="GripAi">
    <a href="/blog" class="blog-back" aria-label="Back to blog listing">← Back to Blog</a>
    <h1 itemprop="headline"><?= $post['title'] ?></h1>
    <div class="article-meta">
        <span class="badge-type" style="background:<?= $badge[1] ?>;color:#fff;"><?= $badge[0] ?></span>
        <span>By <strong itemprop="author">GripAi</strong></span>
        <time datetime="<?= $pub_date ? date('c', strtotime($pub_date)) : '' ?>"><?= $pub_date ? date('j F Y', strtotime($pub_date)) : '' ?></time>
        <span><?= $read_time ?> min read</span>
        <span><?= number_format($word_count) ?> words</span>
    </div>
    <div class="article-content" itemprop="articleBody">
        <?= cleanup_content($post['content'] ?? '') ?>
    </div>
</article>

<?php
    // Fetch recent posts for "More Articles" section (internal linking for SEO)
    $recent = blog_api('blog', ['limit' => 7]);
    $related = [];
    if ($recent && !empty($recent['posts'])) {
        foreach ($recent['posts'] as $rp) {
            if ($rp['slug'] !== $post['slug']) $related[] = $rp;
        }
        $related = array_slice($related, 0, 6);
    }
    if (!empty($related)):
?>
<section class="related-section" aria-label="Related articles">
    <h2>More Articles</h2>
    <div class="blog-grid">
        <?php foreach ($related as $r):
            $rb = post_type_badge($r['title']);
            $r_clean = seo_title($r['title']);
        ?>
        <div class="blog-card">
            <div class="blog-card-body">
                <span class="blog-badge" style="background:<?= $rb[1] ?>"><?= $rb[0] ?></span>
                <span class="blog-time"><?= time_ago_blog($r['created_at'] ?? '') ?></span>
                <h3><a href="/blog/<?= e($r['slug']) ?>" title="<?= e($r_clean) ?>"><?= e($r['title']) ?></a></h3>
                <p class="blog-excerpt"><?= e(substr($r['excerpt'] ?? '', 0, 120)) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// ═════════════════════════════════════════════
// BLOG LISTING
// ═════════════════════════════════════════════
$page = max(1, intval($_GET['page'] ?? 1));
$per = 12;
$offset = ($page - 1) * $per;

$data = blog_api('blog', ['limit' => $per, 'offset' => $offset]);
$posts = $data['posts'] ?? [];
$total = $data['count'] ?? 0;

// Merge local published posts (show on first page, before API posts)
if ($page === 1) {
    try {
        require_once __DIR__ . '/blog-config.php';
        $local_db = blog_db();
        $local_posts = $local_db->query("SELECT id, title, slug, excerpt, content, category, author, published_at AS created_at, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 20")->fetchAll();
        if ($local_posts) {
            $posts = array_merge($local_posts, $posts);
            $total += count($local_posts);
        }
    } catch (Exception $e) { /* silently skip if DB unavailable */ }
}
$pages = ceil($total / $per);

$page_title = ($page > 1 ? "Page {$page} — " : '') . 'Blog — Gaming Intel, Fix Guides & Crawl Digests | GripNews';
$page_desc  = 'Daily crawl intelligence digests, gaming intel recaps, fix guides, and walkthroughs. ' . number_format($total) . ' articles powered by GripAI.';
$page_canonical = SITE_URL . '/blog' . ($page > 1 ? '?page=' . $page : '');
$nav_active = 'blog';

// Noindex paginated pages (SEO best practice — avoid duplicate thin content)
if ($page > 1) {
    $extra_meta = '<meta name="robots" content="noindex, follow">';
}
include __DIR__ . '/includes/header.php';
?>

<!-- SEO: Pagination rel prev/next -->
<?php if ($page > 1): ?>
<link rel="prev" href="<?= SITE_URL ?>/blog<?= $page > 2 ? '?page=' . ($page - 1) : '' ?>">
<?php endif; ?>
<?php if ($page < $pages): ?>
<link rel="next" href="<?= SITE_URL ?>/blog?page=<?= $page + 1 ?>">
<?php endif; ?>

<!-- SEO: CollectionPage + ItemList Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "GripNews Blog",
  "description": "<?= e($page_desc) ?>",
  "url": "https://gripnews.uk/blog",
  "isPartOf": { "@type": "WebSite", "name": "GripNews", "url": "https://gripnews.uk" },
  "publisher": { "@type": "Organization", "name": "GripNews", "url": "https://gripnews.uk" },
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": <?= $total ?>,
    "itemListElement": [
      <?php foreach ($posts as $i => $p): ?>
      {
        "@type": "ListItem",
        "position": <?= $offset + $i + 1 ?>,
        "url": "https://gripnews.uk/blog/<?= e($p['slug']) ?>",
        "name": <?= json_encode(seo_title($p['title']), JSON_UNESCAPED_UNICODE) ?>
      }<?= $i < count($posts) - 1 ? ',' : '' ?>
      <?php endforeach; ?>
    ]
  }
}
</script>

<!-- SEO: BreadcrumbList -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "name": "Home", "item": "https://gripnews.uk" },
    { "@type": "ListItem", "position": 2, "name": "Blog", "item": "https://gripnews.uk/blog" }
  ]
}
</script>

<style>
.blog-hero { max-width: 1100px; margin: 0 auto; padding: 32px 20px 0; }
.blog-hero h1 { font-size: 1.6rem; font-weight: 700; }
.blog-hero .blog-count { color: var(--text-dim); font-size: 0.9rem; }
.blog-hero p { color: var(--text-dim); font-size: 0.9rem; margin-top: 8px; max-width: 700px; line-height: 1.5; }

.blog-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; max-width: 1100px; margin: 24px auto; padding: 0 20px; }

.blog-card { background: var(--card-bg, rgba(255,255,255,0.04)); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; transition: border-color 0.2s, transform 0.2s; }
.blog-card:hover { border-color: rgba(255,255,255,0.2); transform: translateY(-2px); }

.blog-card-cover { aspect-ratio: 16/9; background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(139,92,246,0.15)); display: flex; align-items: center; justify-content: center; font-size: 2.5rem; }

.blog-card-body { padding: 16px; position: relative; }
.blog-card-body h3 { font-size: 0.95rem; font-weight: 600; line-height: 1.4; margin: 8px 0; }
.blog-card-body h3 a { color: inherit; text-decoration: none; }
.blog-card-body h3 a:hover { color: var(--accent); }
.blog-excerpt { font-size: 0.82rem; color: var(--text-dim); line-height: 1.5; margin: 0; }
.blog-meta { display: flex; gap: 12px; font-size: 0.75rem; color: var(--text-dim); margin-top: 12px; }

.blog-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.5px; color: #fff; }
.blog-time { position: absolute; top: 16px; right: 16px; font-size: 0.75rem; color: var(--text-dim); }

.blog-pagination { display: flex; justify-content: center; align-items: center; gap: 8px; padding: 32px 20px 60px; flex-wrap: wrap; }
.blog-pagination a, .blog-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 0 10px; border-radius: 8px; font-size: 0.85rem; text-decoration: none; }
.blog-pagination a { background: rgba(255,255,255,0.05); color: var(--text-dim); border: 1px solid rgba(255,255,255,0.08); }
.blog-pagination a:hover { background: rgba(255,255,255,0.1); color: #fff; }
.blog-pagination .current { background: var(--accent); color: #fff; font-weight: 600; }
.blog-pagination .dots { color: var(--text-dim); border: none; background: none; }
</style>

<div class="blog-hero">
    <nav aria-label="Breadcrumb" style="font-size:0.85rem;color:var(--text-dim);margin-bottom:12px;">
        <a href="/" style="color:var(--accent);text-decoration:none;">Home</a> <span style="margin:0 6px;opacity:0.4;">›</span> Blog
    </nav>
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <h1>Blog</h1>
        <span class="blog-count"><?= number_format($total) ?> articles</span>
    </div>
    <p>Daily crawl intelligence digests, gaming intel recaps, fix guides, and walkthroughs — powered by <a href="https://gripai.uk" style="color:var(--accent);text-decoration:none;">GripAI</a>.</p>
</div>

<?php if (empty($posts)): ?>
    <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:3rem;margin-bottom:16px;">📝</div>
        <h2>No posts yet</h2>
        <p style="color:var(--text-dim);">Check back soon for articles, guides, and news.</p>
    </div>
<?php else: ?>

<div class="blog-grid">
    <?php foreach ($posts as $b):
        $badge = post_type_badge($b['title']);
        $emoji = '📰';
        if (stripos($b['title'], 'Crawl') !== false) $emoji = '📡';
        elseif (stripos($b['title'], 'Recap') !== false) $emoji = '🎮';
        elseif (stripos($b['title'], 'Fix') !== false || stripos($b['title'], 'Crash') !== false) $emoji = '🔧';
        elseif (stripos($b['title'], 'Walkthrough') !== false || stripos($b['title'], 'Guide') !== false) $emoji = '📖';
        $b_clean = seo_title($b['title']);
    ?>
    <article class="blog-card">
        <div class="blog-card-cover" aria-hidden="true"><?= $emoji ?></div>
        <div class="blog-card-body">
            <span class="blog-badge" style="background:<?= $badge[1] ?>"><?= $badge[0] ?></span>
            <span class="blog-time"><time datetime="<?= $b['created_at'] ?? '' ?>"><?= time_ago_blog($b['created_at'] ?? '') ?></time></span>
            <h3><a href="/blog/<?= e($b['slug']) ?>" title="<?= e($b_clean) ?>"><?= e($b['title']) ?></a></h3>
            <p class="blog-excerpt"><?= e(substr(cleanup_content($b['excerpt'] ?? ''), 0, 150)) ?></p>
            <div class="blog-meta">
                <span>By GripAi</span>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<?php if ($pages > 1): ?>
<nav class="blog-pagination" aria-label="Blog pagination">
    <?php if ($page > 1): ?>
        <a href="/blog<?= $page > 2 ? '?page=' . ($page - 1) : '' ?>" rel="prev" aria-label="Previous page">← Prev</a>
    <?php endif; ?>

    <?php
    $range = 2;
    for ($i = 1; $i <= $pages; $i++):
        if ($i == 1 || $i == $pages || abs($i - $page) <= $range):
    ?>
        <?php if ($i == $page): ?>
            <span class="current" aria-current="page"><?= $i ?></span>
        <?php else: ?>
            <a href="/blog<?= $i > 1 ? '?page=' . $i : '' ?>" aria-label="Page <?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php
        elseif ($i == 2 && $page > $range + 2):
            echo '<span class="dots" aria-hidden="true">…</span>';
        elseif ($i == $pages - 1 && $page < $pages - $range - 1):
            echo '<span class="dots" aria-hidden="true">…</span>';
        endif;
    endfor;
    ?>

    <?php if ($page < $pages): ?>
        <a href="/blog?page=<?= $page + 1 ?>" rel="next" aria-label="Next page">Next →</a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
