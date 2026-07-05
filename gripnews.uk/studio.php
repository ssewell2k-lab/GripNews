<?php
require_once __DIR__ . '/includes/functions.php';

// Get studio slug from URL
$slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!$slug || $slug === 'studio') {
    header('Location: /studios');
    exit;
}

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/studio/$slug"), true);

// Fetch upcoming releases for this studio
$releases_data = @json_decode(@file_get_contents("$api/releases/upcoming?sort=date&limit=100"), true);
$studio_releases = [];
if (!empty($releases_data['releases'])) {
    $studio_name = $data['name'] ?? '';
    foreach ($releases_data['releases'] as $rel) {
        if (strcasecmp($rel['studio_name'] ?? '', $studio_name) === 0) {
            $studio_releases[] = $rel;
        }
    }
}

if (!$data || isset($data['error'])) {
    $page_title = 'Studio Not Found — GripNews';
    $page_desc = 'Studio not found.';
    $page_canonical = SITE_URL . '/studios';
    $nav_active = 'studios';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="hero"><h1>Studio Not Found</h1><p class="hero-sub">We don\'t have data for this studio yet.</p></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = $data['name'] . ' — GripNews Studio Profile';
$page_desc = $data['description'] ?? "Intelligence profile for {$data['name']}.";
$page_canonical = SITE_URL . "/studio/$slug";
$nav_active = 'studios';

require_once __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <h1>🏢 <?= e($data['name']) ?></h1>
    <p class="hero-sub"><?= e($data['description'] ?? '') ?></p>
    <?php if (!empty($data['website'])): ?>
      <a href="<?= e($data['website']) ?>" target="_blank" class="studio-website-link"><?= e($data['website']) ?></a>
    <?php endif; ?>
  </section>

  <!-- Stats Overview -->
  <section class="trends-section">
    <div class="trends-inner">
      <div class="studio-overview">
        <div class="studio-stat-card">
          <div class="stat-value"><?= number_format($data['avg_momentum'], 1) ?></div>
          <div class="stat-label">Avg Momentum</div>
        </div>
        <div class="studio-stat-card">
          <div class="stat-value"><?= $data['game_count'] ?></div>
          <div class="stat-label">Games Tracked</div>
        </div>
        <div class="studio-stat-card">
          <div class="stat-value"><?= number_format($data['total_issues']) ?></div>
          <div class="stat-label">Total Issues</div>
        </div>
        <div class="studio-stat-card">
          <div class="stat-value"><?= $data['total_signals'] ?></div>
          <div class="stat-label">Active Signals</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Games Portfolio -->
  <?php if (!empty($data['games'])): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🎮 Game Portfolio</h2>
      <div class="trend-table">
        <div class="trend-row trend-header">
          <span class="t-rank">#</span>
          <span class="t-name">Game</span>
          <span class="t-mentions">Momentum</span>
          <span class="t-days">Direction</span>
          <span class="t-impact">Role</span>
        </div>
        <?php $i = 1; foreach ($data['games'] as $g): ?>
          <div class="trend-row">
            <span class="t-rank"><?= $i++ ?></span>
            <span class="t-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['name']) ?></a></span>
            <span class="t-mentions">
              <?php if ($g['momentum'] !== null): ?>
                <span class="score-value score-<?= $g['momentum'] > 50 ? 'high' : ($g['momentum'] > 20 ? 'mid' : 'low') ?>"><?= number_format($g['momentum'], 1) ?></span>
              <?php else: ?>
                <span style="color:#666">—</span>
              <?php endif; ?>
            </span>
            <span class="t-days">
              <?php if ($g['direction'] === 'rising'): ?>
                <span style="color:#2ecc71">▲ Rising</span>
              <?php elseif ($g['direction'] === 'falling'): ?>
                <span style="color:#e74c3c">▼ Falling</span>
              <?php else: ?>
                <span style="color:#95a5a6">● Stable</span>
              <?php endif; ?>
            </span>
            <span class="t-impact"><?= ucfirst($g['role']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Signals -->
  <?php if (!empty($data['signals'])): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📡 Active Signals</h2>
      <div class="patterns-list">
        <?php foreach ($data['signals'] as $sig): ?>
          <div class="pattern-card pattern-<?= $sig['severity'] === 'critical' ? 'warning' : ($sig['severity'] === 'high' ? 'warning' : 'info') ?>">
            <div class="pattern-type"><?= strtoupper($sig['type']) ?> • <?= strtoupper($sig['severity']) ?></div>
            <div class="pattern-title"><?= e($sig['title']) ?></div>
            <div class="pattern-detail">Game: <?= e($sig['game']) ?> • Confidence: <?= number_format($sig['confidence'] * 100) ?>%</div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Forecasts -->
  <?php if (!empty($data['forecasts'])): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🔮 Active Forecasts</h2>
      <div class="patterns-list">
        <?php foreach ($data['forecasts'] as $f): ?>
          <div class="pattern-card pattern-<?= $f['direction'] === 'positive' ? 'info' : ($f['direction'] === 'negative' ? 'warning' : 'neutral') ?>">
            <div class="pattern-type"><?= strtoupper($f['type']) ?> • <?= strtoupper($f['confidence']) ?></div>
            <div class="pattern-title"><?= e($f['game']) ?></div>
            <div class="pattern-detail"><?= e($f['summary']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>


  <!-- Upcoming Releases -->
  <?php if (!empty($studio_releases)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🚀 Upcoming Releases</h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;">
        <?php foreach ($studio_releases as $r):
          $hype = floatval($r['hype_score'] ?? 0);
          $risk = floatval($r['risk_score'] ?? 0);
          $hype_class = $hype >= 85 ? 'fire' : ($hype >= 70 ? 'hot' : ($hype >= 55 ? 'warm' : ''));
          $date_str = $r['release_date'] ? date('M j, Y', strtotime($r['release_date'])) : 'TBA';
          $type = ucfirst(str_replace('_', ' ', $r['release_type'] ?? 'new release'));
          $platforms = is_array($r['platforms'] ?? null) ? implode(', ', $r['platforms']) : '';
        ?>
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:18px 20px;transition:border-color 0.2s;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
            <a href="/game/<?= e($r['game_slug']) ?>" style="font-weight:700;font-size:1.05em;color:var(--text);text-decoration:none;"><?= e($r['game_name']) ?></a>
            <span style="font-size:1.4em;font-weight:900;<?= $hype_class === 'fire' ? 'color:#e74c3c' : ($hype_class === 'hot' ? 'color:#f97316' : ($hype_class === 'warm' ? 'color:#f59e0b' : 'color:var(--text-muted)')) ?>"><?= number_format($hype, 0) ?></span>
          </div>
          <?php if (!empty($r['description'])): ?>
          <div style="font-size:0.82em;color:var(--text-muted);margin-bottom:8px;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= e($r['description']) ?></div>
          <?php endif; ?>
          <div style="display:flex;gap:6px;flex-wrap:wrap;">
            <span style="font-size:0.7em;padding:3px 8px;border-radius:4px;font-weight:600;background:var(--accent-dim,rgba(59,130,246,0.12));color:var(--accent);"><?= $date_str ?></span>
            <span style="font-size:0.7em;padding:3px 8px;border-radius:4px;font-weight:600;background:rgba(255,255,255,0.05);color:var(--text-muted);text-transform:capitalize;"><?= $type ?></span>
            <?php if ($risk >= 25): ?>
            <span style="font-size:0.7em;padding:3px 8px;border-radius:4px;font-weight:600;background:rgba(239,68,68,0.12);color:#ef4444;">Risk: <?= number_format($risk, 0) ?></span>
            <?php elseif ($risk >= 15): ?>
            <span style="font-size:0.7em;padding:3px 8px;border-radius:4px;font-weight:600;background:rgba(245,158,11,0.12);color:#f59e0b;">Risk: <?= number_format($risk, 0) ?></span>
            <?php else: ?>
            <span style="font-size:0.7em;padding:3px 8px;border-radius:4px;font-weight:600;background:rgba(34,197,94,0.12);color:#22c55e;">Risk: <?= number_format($risk, 0) ?></span>
            <?php endif; ?>
            <?php if ($platforms): ?>
            <span style="font-size:0.7em;padding:3px 8px;border-radius:4px;font-weight:500;background:rgba(255,255,255,0.03);color:var(--text-dim);"><?= e($platforms) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <div style="text-align:center;margin:2rem 0">
    <a href="/studios" class="nav-back">← All Studios</a>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
