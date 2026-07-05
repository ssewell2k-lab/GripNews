<?php
/**
 * GripNews.uk — Category Page
 * /patches, /industry, /esports, /indie, /releases, /rumours
 */
require_once __DIR__ . '/includes/functions.php';

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$category_slug = $path;

$cats = get_categories();
if (!isset($cats[$category_slug])) {
    header('Location: /');
    exit;
}

$cat_info = $cats[$category_slug];
$signals = load_signals_by_category($category_slug, 30, 50);

$page_title = $cat_info['label'] . ' — GripNews';
$page_desc = $cat_info['desc'];
$page_canonical = SITE_URL . '/' . $category_slug;
$nav_active = $category_slug;

require_once __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <div class="hero-date" style="font-size:1.5em;margin-bottom:4px"><?= $cat_info['icon'] ?></div>
    <h1><?= $cat_info['label'] ?></h1>
    <p class="hero-sub"><?= $cat_info['desc'] ?></p>
    <p style="margin-top:8px;font-size:0.85em;color:var(--text-dim)"><?= count($signals) ?> signal<?= count($signals) !== 1 ? 's' : '' ?> from the last 30 days</p>
  </section>

  <section class="signals">
    <?php if (empty($signals)): ?>
      <div class="empty-state">
        <div class="icon"><?= $cat_info['icon'] ?></div>
        <h2>No <?= strtolower($cat_info['label']) ?> signals yet</h2>
        <p>Check back soon — this category will populate as signals are published.</p>
      </div>
    <?php else: ?>
      <?php foreach ($signals as $i => $signal): ?>
        <?php $date = $signal['_date'] ?? date('Y-m-d'); ?>
        <?= render_signal_card($signal, $i + 1, $date, true) ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <div style="max-width:900px;margin:0 auto;padding:0 20px 60px;text-align:center;">
    <a href="/" style="color:var(--text-muted);font-size:0.9em">← Back to today's signals</a>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
