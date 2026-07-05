<?php
require_once __DIR__ . '/includes/functions.php';

$report = generate_weekly_report(7);
$page_title = 'Weekly Intelligence Report — GripNews';
$page_desc = 'Gaming intelligence: biggest stories, rising games, emerging trends, and detected patterns.';
$page_canonical = SITE_URL . '/weekly';
$nav_active = 'weekly';

require_once __DIR__ . '/includes/header.php';
?>

  <article class="report-page">
    <div class="report-header">
      <div class="report-badge">GAMING INTELLIGENCE REPORT</div>
      <h1>Weekly Report</h1>
      <?php if (!empty($report)): ?>
        <p class="report-period"><?= format_date($report['period_start'], 'j M') ?> — <?= format_date($report['period_end'], 'j M Y') ?></p>
        <p class="report-stats"><?= $report['total_signals'] ?> signals analysed across <?= $report['days_covered'] ?> day<?= $report['days_covered'] !== 1 ? 's' : '' ?></p>
      <?php endif; ?>
    </div>

    <?php if (empty($report)): ?>
      <div class="empty-state">
        <div class="icon">📊</div>
        <h2>Not enough data yet</h2>
        <p>Weekly reports generate automatically after enough signal data has been collected.</p>
      </div>
    <?php else: ?>

      <?php if ($report['biggest_story']): ?>
      <section class="report-section">
        <h2 class="report-section-title">🏆 Biggest Story</h2>
        <div class="report-highlight">
          <div class="rh-score score-<?= score_class($report['biggest_story']['_max_impact']) ?>"><?= $report['biggest_story']['_max_impact'] ?></div>
          <div class="rh-body">
            <div class="rh-title"><?= e($report['biggest_story']['title']) ?></div>
            <div class="rh-summary"><?= e($report['biggest_story']['summary'] ?? '') ?></div>
            <div class="rh-meta">
              <?php $bsCat = is_string($report['biggest_story']['category'] ?? null) ? $report['biggest_story']['category'] : 'Update'; ?>
              <span class="signal-category <?= category_class($bsCat) ?>"><?= e($bsCat) ?></span>
              <span class="signal-time"><?= format_date($report['biggest_story']['_date'], 'j M') ?></span>
            </div>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($report['patterns'])): ?>
      <section class="report-section">
        <h2 class="report-section-title">🔍 Detected Patterns</h2>
        <?php foreach ($report['patterns'] as $p): ?>
          <div class="pattern-card pattern-<?= $p['severity'] ?>">
            <div class="pattern-type"><?= strtoupper($p['type']) ?></div>
            <div class="pattern-title"><?= $p['title'] ?></div>
            <div class="pattern-detail"><?= $p['detail'] ?></div>
          </div>
        <?php endforeach; ?>
      </section>
      <?php endif; ?>

      <?php if (!empty($report['rising_games'])): ?>
      <section class="report-section">
        <h2 class="report-section-title">📈 Rising Games</h2>
        <div class="trend-table">
          <div class="trend-row trend-header">
            <span class="t-rank">#</span>
            <span class="t-name">Game / Topic</span>
            <span class="t-mentions">Mentions</span>
            <span class="t-impact">Peak</span>
          </div>
          <?php foreach ($report['rising_games'] as $i => $g): ?>
            <div class="trend-row">
              <span class="t-rank"><?= $i + 1 ?></span>
              <span class="t-name"><?= e($g['name']) ?></span>
              <span class="t-mentions"><?= $g['mentions'] ?></span>
              <span class="t-impact"><span class="score-value score-<?= score_class($g['max_impact']) ?>"><?= $g['max_impact'] ?></span></span>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if (!empty($report['category_distribution'])): ?>
      <section class="report-section">
        <h2 class="report-section-title">📊 Category Breakdown</h2>
        <div class="cat-bars">
          <?php foreach ($report['category_distribution'] as $cat => $info): ?>
            <div class="cat-bar-row">
              <span class="cat-bar-label"><?= ucfirst(e($cat)) ?></span>
              <div class="cat-bar-track">
                <div class="cat-bar-fill" style="width:<?= $info['pct'] ?>%"></div>
              </div>
              <span class="cat-bar-pct"><?= $info['pct'] ?>%</span>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <div class="report-footer-note">
        <p>Generated automatically by GripAI signal analysis.</p>
        <p><a href="/trends">View full trend data →</a></p>
      </div>

    <?php endif; ?>
  </article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
