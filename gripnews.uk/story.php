<?php
/**
 * GripNews.uk — Story Detail Page
 * Phase 9C: Engagement + Citation Layer
 */
require_once __DIR__ . '/includes/functions.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$parts = explode('/', $path);

if (count($parts) < 3) { header('Location: /'); exit; }

$date = $parts[1];
$slug = $parts[2];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { header('Location: /'); exit; }

$signal = get_signal_by_slug($date, $slug);
if (!$signal) {
    http_response_code(404);
    $page_title = 'Signal Not Found — GripNews';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="story-page"><div class="empty-state"><div class="icon">🔍</div><h2>Signal not found</h2><p>This signal may have been removed or the URL is incorrect.</p><p style="margin-top:16px"><a href="/">← Back to today\'s signals</a></p></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$imp = $signal['impact'] ?? [];
$sc = max(intval($imp['player'] ?? 0), intval($imp['dev'] ?? 0), intval($imp['esports'] ?? 0), intval($imp['industry'] ?? 0));
$scClass = score_class($sc);
$cat = is_string($signal['category'] ?? null) ? ($signal['category'] ?? 'Update') : 'Update';
$catClass = category_class($cat);
$conf = $signal['confidence'] ?? 'confirmed';
$confClass = confidence_class($conf);

$page_title = e($signal['title']) . ' — GripNews';
$page_desc = $signal['summary'] ?? '';
$page_canonical = SITE_URL . "/story/{$date}/{$slug}";
$og_type = 'article';
$article_date = $date;
$feedback_id = "{$date}/{$slug}";

// Get GripAI insight - prefer dedicated field, fall back to why_it_matters
$insight = $signal['gripai_insight'] ?? $signal['insight'] ?? '';
$why = $signal['why_it_matters'] ?? '';

// Who is affected - compute from impact scores
$affected = [];
if (intval($imp['player'] ?? 0) >= 5) $affected[] = ['group' => 'Players', 'level' => 'high', 'score' => $imp['player']];
elseif (intval($imp['player'] ?? 0) >= 3) $affected[] = ['group' => 'Players', 'level' => 'mid', 'score' => $imp['player']];
if (intval($imp['dev'] ?? 0) >= 5) $affected[] = ['group' => 'Developers', 'level' => 'high', 'score' => $imp['dev']];
elseif (intval($imp['dev'] ?? 0) >= 3) $affected[] = ['group' => 'Developers', 'level' => 'mid', 'score' => $imp['dev']];
if (intval($imp['esports'] ?? 0) >= 5) $affected[] = ['group' => 'Esports', 'level' => 'high', 'score' => $imp['esports']];
elseif (intval($imp['esports'] ?? 0) >= 3) $affected[] = ['group' => 'Esports', 'level' => 'mid', 'score' => $imp['esports']];
if (intval($imp['industry'] ?? 0) >= 5) $affected[] = ['group' => 'Industry', 'level' => 'high', 'score' => $imp['industry']];
elseif (intval($imp['industry'] ?? 0) >= 3) $affected[] = ['group' => 'Industry', 'level' => 'mid', 'score' => $imp['industry']];

// Build keyword list from tags
$keywords = !empty($signal['tags']) ? implode(', ', array_map('e', $signal['tags'])) : 'gaming, news, intelligence';

require_once __DIR__ . '/includes/header.php';
?>

  <article class="story-page" itemscope itemtype="https://schema.org/NewsArticle">
    <!-- Enhanced JSON-LD Structured Data for Citation -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "NewsArticle",
      "headline": "<?= e($signal['title']) ?>",
      "description": "<?= e($signal['summary'] ?? '') ?>",
      "articleBody": "<?= e($signal['summary'] ?? '') ?> <?= e($why) ?>",
      "datePublished": "<?= $date ?>T00:00:00+00:00",
      "dateModified": "<?= $date ?>T00:00:00+00:00",
      "url": "<?= e($page_canonical) ?>",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?= e($page_canonical) ?>"
      },
      "author": {
        "@type": "Organization",
        "name": "GripNews",
        "url": "https://gripnews.uk"
      },
      "publisher": {
        "@type": "Organization",
        "name": "GripNews",
        "url": "https://gripnews.uk",
        "logo": {
          "@type": "ImageObject",
          "url": "https://gripnews.uk/assets/og-image.png",
          "width": 1200,
          "height": 630
        }
      },
      "image": "https://gripnews.uk/assets/og-image.png",
      "articleSection": "<?= e($cat) ?>",
      "keywords": "<?= $keywords ?>",
      "isAccessibleForFree": true,
      "inLanguage": "en-GB",
      "copyrightHolder": {
        "@type": "Organization",
        "name": "GripNews"
      },
      "citation": {
        "@type": "CreativeWork",
        "name": "<?= e($signal['title']) ?>",
        "url": "<?= e($page_canonical) ?>",
        "datePublished": "<?= $date ?>"
      }
    }
    </script>

    <!-- Additional meta for citation -->
    <meta itemprop="datePublished" content="<?= $date ?>">
    <meta itemprop="headline" content="<?= e($signal['title']) ?>">

    <a href="/" class="story-back">← Back to signals</a>
    
    <div class="story-header">
      <div class="signal-meta">
        <span class="signal-category <?= $catClass ?>"><?= e($cat) ?></span>
        <span class="signal-confidence <?= $confClass ?>">&bull; <?= e($conf) ?></span>
        <span class="signal-time"><?= format_date($date, 'j M Y') ?></span>
      </div>
      <h1 itemprop="headline"><?= e($signal['title']) ?></h1>
    </div>

    <!-- WHAT HAPPENED -->
    <section class="story-section">
      <h3 class="section-label">What Happened</h3>
      <p class="story-summary" itemprop="description"><?= e($signal['summary'] ?? '') ?></p>
    </section>

    <!-- WHY IT MATTERS -->
    <?php if ($why): ?>
    <section class="story-section">
      <h3 class="section-label">Why It Matters</h3>
      <blockquote class="insight-quote"><?= e($why) ?></blockquote>
    </section>
    <?php endif; ?>

    <!-- DETAIL -->
    <?php if (!empty($signal['detail'])): ?>
    <section class="story-content" itemprop="articleBody">
      <?php foreach ((array)$signal['detail'] as $para): ?>
        <p><?= e($para) ?></p>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- WHO IS AFFECTED -->
    <?php if (!empty($affected)): ?>
    <section class="story-section">
      <h3 class="section-label">Who Is Affected</h3>
      <div class="affected-grid">
        <?php foreach ($affected as $a): ?>
          <div class="affected-item affected-<?= $a['level'] ?>">
            <span class="affected-group"><?= $a['group'] ?></span>
            <span class="affected-score"><?= $a['score'] ?>/10</span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <!-- IMPACT & CONFIDENCE -->
    <section class="story-section">
      <div class="story-score-row">
        <div class="score-block">
          <div class="score-block-label">Impact</div>
          <div class="story-score-big score-<?= $scClass ?>"><?= $sc ?><span class="score-denom">/10</span></div>
        </div>
        <div class="score-block">
          <div class="score-block-label">Confidence</div>
          <div class="confidence-badge <?= $confClass ?>"><?= e(ucfirst($conf)) ?></div>
        </div>
        <div class="score-breakdown">
          <?php
          $impacts = ['player' => 'Player', 'dev' => 'Developer', 'esports' => 'Esports', 'industry' => 'Industry'];
          foreach ($impacts as $key => $label):
            $val = intval($signal['impact'][$key] ?? 0);
            $pct = min($val * 10, 100);
          ?>
          <div class="story-score-item">
            <span class="label"><?= $label ?></span>
            <div class="bar"><div class="bar-fill" style="width:<?= $pct ?>%;background:var(--<?= $pct >= 70 ? 'accent' : ($pct >= 40 ? 'amber' : 'text-dim') ?>)"></div></div>
            <span class="val"><?= $val ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <!-- GRIPAI INSIGHT -->
    <?php if ($insight || $why): ?>
    <section class="gripai-insight">
      <div class="insight-header">
        <span class="insight-logo">GripAI</span>
        <span class="insight-label">Insight</span>
      </div>
      <div class="insight-body">
        <?= e($insight ?: $why) ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($signal['tags'])): ?>
      <div class="signal-tags" style="margin-top:32px">
        <?php foreach ($signal['tags'] as $tag): ?>
          <span class="signal-tag"><?= e($tag) ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($signal['sources'])): ?>
      <div class="story-sources">
        <h3>Sources</h3>
        <?php foreach ($signal['sources'] as $src): ?>
          <?php if (is_array($src)): ?>
            <a href="<?= e($src['url'] ?? '#') ?>" target="_blank" rel="noopener"><?= e($src['name'] ?? $src['url'] ?? 'Source') ?></a>
          <?php else: ?>
            <a href="<?= e($src) ?>" target="_blank" rel="noopener"><?= e($src) ?></a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- ═══ WAS THIS USEFUL? ═══ -->
    <section class="feedback-widget" id="feedback-widget">
      <div class="feedback-header">
        <span class="feedback-icon">💡</span>
        <span class="feedback-title">Was this signal useful?</span>
      </div>
      <div class="feedback-buttons" id="feedback-btns">
        <button class="fb-btn fb-useful" data-emoji="useful" onclick="sendFeedback('useful')">
          <span class="fb-emoji">👍</span>
          <span class="fb-label">Useful</span>
          <span class="fb-count" id="count-useful"></span>
        </button>
        <button class="fb-btn fb-not-useful" data-emoji="not_useful" onclick="sendFeedback('not_useful')">
          <span class="fb-emoji">👎</span>
          <span class="fb-label">Not useful</span>
          <span class="fb-count" id="count-not_useful"></span>
        </button>
        <button class="fb-btn fb-fire" data-emoji="fire" onclick="sendFeedback('fire')">
          <span class="fb-emoji">🔥</span>
          <span class="fb-label">Hot</span>
          <span class="fb-count" id="count-fire"></span>
        </button>
        <button class="fb-btn fb-eyes" data-emoji="eyes" onclick="sendFeedback('eyes')">
          <span class="fb-emoji">👀</span>
          <span class="fb-label">Watching</span>
          <span class="fb-count" id="count-eyes"></span>
        </button>
      </div>
      <div class="feedback-thanks" id="feedback-thanks" style="display:none">
        <span class="thanks-icon">✓</span> Thanks for your feedback!
      </div>
    </section>

    <!-- ═══ CITE & SHARE ═══ -->
    <section class="cite-share-section">
      <h3 class="section-label">Share & Cite</h3>
      <div class="share-row">
        <button class="share-btn" onclick="shareTwitter()" title="Share on X/Twitter">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          <span>Share</span>
        </button>
        <button class="share-btn" onclick="shareLinkedIn()" title="Share on LinkedIn">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
          <span>LinkedIn</span>
        </button>
        <button class="share-btn" onclick="copyLink()" title="Copy link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
          <span id="copy-label">Copy Link</span>
        </button>
        <button class="share-btn" onclick="copyCitation()" title="Copy citation">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <span id="cite-label">Cite</span>
        </button>
      </div>
      <div class="cite-box" id="cite-box" style="display:none">
        <code id="cite-text"><?= e($signal['title']) ?>. GripNews, <?= format_date($date, 'j M Y') ?>. <?= e($page_canonical) ?></code>
        <small>Click "Cite" to copy to clipboard</small>
      </div>
    </section>

    <?= render_gripai_cta($signal) ?>
  </article>

<script>
const API = 'https://gripai.uk/v2';
const ITEM_TYPE = 'signal';
const ITEM_ID = '<?= e($feedback_id) ?>';

// Load existing counts on page load
(async () => {
  try {
    const r = await fetch(`${API}/reactions/${ITEM_TYPE}/${encodeURIComponent(ITEM_ID)}`);
    const d = await r.json();
    if (d.counts) {
      Object.entries(d.counts).forEach(([emoji, cnt]) => {
        const el = document.getElementById(`count-${emoji}`);
        if (el && cnt > 0) el.textContent = cnt;
      });
    }
  } catch(e) { /* silent */ }
})();

async function sendFeedback(emoji) {
  try {
    const r = await fetch(`${API}/reactions`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ item_type: ITEM_TYPE, item_id: ITEM_ID, emoji })
    });
    const d = await r.json();
    
    // Update all counts
    ['useful','not_useful','fire','eyes'].forEach(e => {
      const el = document.getElementById(`count-${e}`);
      if (el) el.textContent = d.counts?.[e] || '';
    });

    // Highlight selected
    document.querySelectorAll('.fb-btn').forEach(b => b.classList.remove('fb-active'));
    if (d.action === 'added') {
      document.querySelector(`[data-emoji="${emoji}"]`)?.classList.add('fb-active');
    }
    
    // Show thanks
    document.getElementById('feedback-thanks').style.display = 'flex';
    setTimeout(() => { document.getElementById('feedback-thanks').style.display = 'none'; }, 3000);
  } catch(e) { /* silent */ }
}

function shareTwitter() {
  const t = encodeURIComponent(document.querySelector('h1')?.textContent || '');
  const u = encodeURIComponent(window.location.href);
  window.open(`https://twitter.com/intent/tweet?text=${t}&url=${u}&via=GripNews`, '_blank', 'width=550,height=420');
}
function shareLinkedIn() {
  const u = encodeURIComponent(window.location.href);
  window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${u}`, '_blank', 'width=550,height=420');
}
function copyLink() {
  navigator.clipboard.writeText(window.location.href).then(() => {
    const el = document.getElementById('copy-label');
    el.textContent = 'Copied!';
    setTimeout(() => el.textContent = 'Copy Link', 2000);
  });
}
function copyCitation() {
  const box = document.getElementById('cite-box');
  const txt = document.getElementById('cite-text').textContent;
  box.style.display = box.style.display === 'none' ? 'block' : 'none';
  navigator.clipboard.writeText(txt).then(() => {
    const el = document.getElementById('cite-label');
    el.textContent = 'Copied!';
    setTimeout(() => el.textContent = 'Cite', 2000);
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
