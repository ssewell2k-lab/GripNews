<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Trends — GripNews Intelligence';
$page_desc = 'Rising games, most patched titles, category breakdowns, and pattern detection. Updated daily.';
$page_canonical = SITE_URL . '/trends';
$nav_active = 'trends';

$rising = get_rising_games(14, 10);
$patched = get_most_patched(14, 10);
$patterns = detect_signals_patterns(7);
$dist = get_category_distribution(14);
$dates = get_available_dates(14);

require_once __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <h1>Gaming <span style="color:var(--accent)">Trends</span></h1>
    <p class="hero-sub">Pattern detection and intelligence across <?= count($dates) ?> day<?= count($dates) !== 1 ? 's' : '' ?> of signal data.</p>
  </section>

  <?php if (!empty($patterns)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🔍 Detected Patterns</h2>
      <div class="patterns-list">
        <?php foreach ($patterns as $p): ?>
          <div class="pattern-card pattern-<?= $p['severity'] ?>">
            <div class="pattern-type"><?= strtoupper($p['type']) ?></div>
            <div class="pattern-title"><?= $p['title'] ?></div>
            <div class="pattern-detail"><?= $p['detail'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📈 Rising Games</h2>
      <p class="section-sub">Games with the most signal activity, weighted by impact.</p>
      <?php if (empty($rising)): ?>
        <div class="empty-state"><p>Not enough data yet. Trends populate after a few days of signals.</p></div>
      <?php else: ?>
        <div class="trend-table">
          <div class="trend-row trend-header">
            <span class="t-rank">#</span>
            <span class="t-name">Game / Topic</span>
            <span class="t-mentions">Mentions</span>
            <span class="t-days">Days</span>
            <span class="t-impact">Peak</span>
          </div>
          <?php $rank = 1; foreach ($rising as $key => $g): ?>
            <div class="trend-row">
              <span class="t-rank"><?= $rank++ ?></span>
              <span class="t-name"><?= e($g['name']) ?></span>
              <span class="t-mentions"><?= $g['mentions'] ?></span>
              <span class="t-days"><?= count($g['dates']) ?></span>
              <span class="t-impact"><span class="score-value score-<?= score_class($g['max_impact']) ?>"><?= $g['max_impact'] ?></span></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🔧 Most Patched</h2>
      <p class="section-sub">Games receiving the most updates and patches.</p>
      <?php if (empty($patched)): ?>
        <div class="empty-state"><p>No patch data yet.</p></div>
      <?php else: ?>
        <div class="trend-table">
          <div class="trend-row trend-header">
            <span class="t-rank">#</span>
            <span class="t-name">Game</span>
            <span class="t-mentions">Patches</span>
            <span class="t-impact">Peak</span>
          </div>
          <?php $rank = 1; foreach ($patched as $key => $g): ?>
            <div class="trend-row">
              <span class="t-rank"><?= $rank++ ?></span>
              <span class="t-name"><?= e($g['name']) ?></span>
              <span class="t-mentions"><?= $g['mentions'] ?></span>
              <span class="t-impact"><span class="score-value score-<?= score_class($g['max_impact']) ?>"><?= $g['max_impact'] ?></span></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📊 Category Distribution</h2>
      <?php if (empty($dist)): ?>
        <div class="empty-state"><p>No data yet.</p></div>
      <?php else: ?>
        <div class="cat-bars">
          <?php foreach ($dist as $cat => $info): ?>
            <div class="cat-bar-row">
              <span class="cat-bar-label"><?= ucfirst(e($cat)) ?></span>
              <div class="cat-bar-track">
                <div class="cat-bar-fill" style="width:<?= $info['pct'] ?>%"></div>
              </div>
              <span class="cat-bar-pct"><?= $info['pct'] ?>% <span class="cat-bar-count">(<?= $info['count'] ?>)</span></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <div style="max-width:900px;margin:0 auto;padding:0 20px 60px;text-align:center;">
    <p style="color:var(--text-dim);font-size:0.85em">Trends update automatically as new signals are published daily.</p>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
