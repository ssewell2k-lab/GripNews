<?php
/**
 * GripNews.uk — Archive
 * Browse past days' signals with date navigation
 */
require_once __DIR__ . '/includes/functions.php';

$dates = get_available_dates(90);
$page_title = 'Archive — GripNews';
$page_desc = 'Browse past gaming signals. Every day ranked by impact.';
$page_canonical = SITE_URL . '/archive';
$nav_active = 'archive';

// Optional date filter from query
$filter_date = $_GET['date'] ?? '';
if ($filter_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filter_date)) {
    $filter_date = '';
}

require_once __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <h1>Signal Archive</h1>
    <p class="hero-sub">Every day's gaming signals, ranked by impact. <?= count($dates) ?> day<?= count($dates) !== 1 ? 's' : '' ?> recorded.</p>
  </section>

  <?php if (!empty($dates)): ?>
    <div class="archive-nav">
      <div class="archive-date-list">
        <?php foreach (array_slice($dates, 0, 14) as $d): ?>
          <a href="/archive?date=<?= $d ?>" class="archive-date-btn <?= $d === $filter_date ? 'active' : '' ?>"><?= format_date($d, 'j M') ?></a>
        <?php endforeach; ?>
        <?php if (count($dates) > 14): ?>
          <span class="archive-more">+ <?= count($dates) - 14 ?> more</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <section class="signals">
    <?php if (empty($dates)): ?>
      <div class="empty-state">
        <div class="icon">📁</div>
        <h2>No archive data yet</h2>
        <p>Signals will appear here once the daily feed starts publishing.</p>
      </div>
    <?php else: ?>
      <?php 
        $show_dates = $filter_date ? [$filter_date] : $dates;
        foreach ($show_dates as $date): 
          $signals = load_signals($date);
          if (empty($signals)) continue;
      ?>
        <div class="archive-day">
          <div class="archive-date">
            <?= strtoupper(format_date($date, 'l, j F Y')) ?> — <?= count($signals) ?> signal<?= count($signals) !== 1 ? 's' : '' ?>
          </div>
          <?php foreach ($signals as $i => $signal): ?>
            <?= render_signal_card($signal, $i + 1, $date) ?>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
