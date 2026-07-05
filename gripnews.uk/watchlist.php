<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Watchlist — GripNews Intelligence';
$page_desc = 'Live momentum tracking: rising games, most active titles, and volatile situations. Updated daily.';
$page_canonical = SITE_URL . '/watchlist';
$nav_active = 'watchlist';

// Fetch from API
$api = 'https://gripai.uk/v2';
$watchlist = @json_decode(@file_get_contents("$api/watchlist"), true);
$forecasts_raw = @json_decode(@file_get_contents("$api/forecasts?limit=10"), true);

require_once __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <h1>🎯 <span style="color:var(--accent)">Watchlist</span></h1>
    <p class="hero-sub">Live momentum tracking across the gaming landscape. Games on the move right now.</p>
  </section>

  <!-- Most Active -->
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🔥 Most Active</h2>
      <p class="section-sub">Highest momentum scores across all tracked games.</p>
      <?php if (empty($watchlist['most_active'])): ?>
        <div class="empty-state"><p>Momentum data loading. Check back after the daily calculation completes.</p></div>
      <?php else: ?>
        <div class="trend-table">
          <div class="trend-row trend-header">
            <span class="t-rank">#</span>
            <span class="t-name">Game</span>
            <span class="t-mentions">Momentum</span>
            <span class="t-days">Direction</span>
            <span class="t-impact">Rank</span>
          </div>
          <?php foreach ($watchlist['most_active'] as $g): ?>
            <div class="trend-row">
              <span class="t-rank"><?= $g['rank'] ?></span>
              <span class="t-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></span>
              <span class="t-mentions"><span class="score-value score-<?= $g['momentum'] > 50 ? 'high' : ($g['momentum'] > 20 ? 'mid' : 'low') ?>"><?= number_format($g['momentum'], 1) ?></span></span>
              <span class="t-days">
                <?php if ($g['direction'] === 'rising'): ?>
                  <span style="color:#2ecc71">▲ Rising</span>
                <?php elseif ($g['direction'] === 'falling'): ?>
                  <span style="color:#e74c3c">▼ Falling</span>
                <?php else: ?>
                  <span style="color:#95a5a6">● Stable</span>
                <?php endif; ?>
              </span>
              <span class="t-impact">#<?= $g['rank'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Rising Games -->
  <?php if (!empty($watchlist['rising'])): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📈 Rising</h2>
      <p class="section-sub">Games with the biggest positive momentum shift today.</p>
      <div class="trend-table">
        <div class="trend-row trend-header">
          <span class="t-rank">#</span>
          <span class="t-name">Game</span>
          <span class="t-mentions">Momentum</span>
          <span class="t-impact">Delta</span>
        </div>
        <?php $i = 1; foreach ($watchlist['rising'] as $g): ?>
          <div class="trend-row">
            <span class="t-rank"><?= $i++ ?></span>
            <span class="t-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></span>
            <span class="t-mentions"><?= number_format($g['momentum'], 1) ?></span>
            <span class="t-impact"><span style="color:#2ecc71">+<?= number_format($g['delta'], 1) ?></span></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Most Volatile -->
  <?php if (!empty($watchlist['most_volatile'])): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">⚡ Most Volatile</h2>
      <p class="section-sub">Games with unpredictable momentum — situations evolving rapidly.</p>
      <div class="trend-table">
        <div class="trend-row trend-header">
          <span class="t-rank">#</span>
          <span class="t-name">Game</span>
          <span class="t-mentions">Momentum</span>
          <span class="t-days">Direction</span>
          <span class="t-impact">Volatility</span>
        </div>
        <?php $i = 1; foreach ($watchlist['most_volatile'] as $g): ?>
          <div class="trend-row">
            <span class="t-rank"><?= $i++ ?></span>
            <span class="t-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></span>
            <span class="t-mentions"><?= number_format($g['momentum'], 1) ?></span>
            <span class="t-days">
              <?php if ($g['direction'] === 'rising'): ?>
                <span style="color:#2ecc71">▲</span>
              <?php elseif ($g['direction'] === 'falling'): ?>
                <span style="color:#e74c3c">▼</span>
              <?php else: ?>
                <span style="color:#95a5a6">●</span>
              <?php endif; ?>
            </span>
            <span class="t-impact"><span class="score-value score-high"><?= number_format($g['volatility'], 1) ?></span></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Active Forecasts -->
  <?php if (!empty($watchlist['forecasts'])): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🔮 Active Forecasts</h2>
      <p class="section-sub">Rule-based predictions from the intelligence engine.</p>
      <div class="patterns-list">
        <?php foreach ($watchlist['forecasts'] as $f): ?>
          <div class="pattern-card pattern-<?= $f['direction'] === 'positive' ? 'info' : ($f['direction'] === 'negative' ? 'warning' : 'neutral') ?>">
            <div class="pattern-type"><?= strtoupper($f['type']) ?> • <?= strtoupper($f['confidence']) ?></div>
            <div class="pattern-title"><a href="/game/<?= e($f['slug']) ?>"><?= e($f['game']) ?></a></div>
            <div class="pattern-detail"><?= e($f['summary']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
