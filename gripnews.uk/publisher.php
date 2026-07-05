<?php
/**
 * GripNews.uk — Publisher Intelligence Page (Phase 10)
 * Displays publisher-level analytics: studios, games, momentum, events.
 */
require_once __DIR__ . '/includes/functions.php';

$slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!$slug || $slug === 'publisher') {
    header('Location: /');
    exit;
}

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/publisher/$slug"), true);

if (!$data || isset($data['error'])) {
    $page_title = 'Publisher Not Found — GripNews';
    $page_desc = 'Publisher not found.';
    $page_canonical = SITE_URL;
    $nav_active = '';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="hero"><h1>Publisher Not Found</h1><p class="hero-sub">We don\'t have data for this publisher yet.</p><div style="text-align:center;margin:2rem 0"><a href="/" class="nav-back">← Home</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pub = $data['publisher'];
$games = $data['games'] ?? [];
$studios = $data['studios'] ?? [];
$stats = $data['stats'] ?? [];

$page_title = $pub['name'] . ' — Publisher Intelligence | GripNews';
$page_desc = $pub['description'] ?? "Intelligence profile for {$pub['name']} — studios, games, momentum, and industry signals.";
$page_canonical = SITE_URL . "/publisher/$slug";
$nav_active = '';

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .pub-hero { padding: 40px 0 24px; border-bottom: 1px solid var(--border); margin-bottom: 32px; }
  .pub-title { font-size: 2.2em; font-weight: 800; color: var(--text-primary, #e2e8f0); letter-spacing: -0.5px; }
  .pub-title span { color: var(--accent); }
  .pub-meta { color: var(--text-muted); font-size: 0.95em; margin-top: 6px; display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
  .pub-meta-item { display: flex; align-items: center; gap: 4px; }

  .pub-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin: 24px 0 32px; }
  .pub-stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 18px; text-align: center; }
  .pub-stat-card .stat-value { font-size: 1.8em; font-weight: 800; color: var(--accent); }
  .pub-stat-card .stat-label { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }

  .section-title { font-size: 1.05em; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
    color: var(--accent); margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }

  .studio-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 18px 22px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; transition: border-color 0.2s; }
  .studio-card:hover { border-color: var(--accent); }
  .studio-name { font-weight: 700; color: var(--text-primary, #e2e8f0); }
  .studio-name a { color: inherit; text-decoration: none; }
  .studio-name a:hover { color: var(--accent); }
  .studio-meta { font-size: 0.8em; color: var(--text-muted); margin-top: 3px; }
  .studio-momentum { font-size: 1.3em; font-weight: 800; color: var(--accent); }

  .game-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 16px 20px; margin-bottom: 10px; transition: border-color 0.2s; display: grid;
    grid-template-columns: 40px 1fr 80px 80px; gap: 12px; align-items: center; }
  .game-card:hover { border-color: var(--accent); }
  .game-rank { font-size: 1.3em; font-weight: 800; color: var(--text-dim); text-align: center; }
  .game-name { font-weight: 700; color: var(--text-primary, #e2e8f0); }
  .game-name a { color: inherit; text-decoration: none; }
  .game-name a:hover { color: var(--accent); }
  .game-genre-tag { font-size: 0.72em; color: var(--accent); margin-top: 2px; }
  .game-momentum { font-size: 1.3em; font-weight: 800; text-align: center; }
  .game-dir { text-align: center; font-size: 0.85em; }
  .dir-rising { color: #00e676; }
  .dir-stable { color: #94a3b8; }
  .dir-falling { color: #f44336; }
  .impact-high { color: #f44336; }
  .impact-mid { color: #ffc107; }
  .impact-low { color: var(--text-muted); }

  .genre-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
  .genre-tag { background: rgba(59,130,246,0.12); color: var(--accent); padding: 4px 12px; border-radius: 20px;
    font-size: 0.78em; font-weight: 600; text-decoration: none; transition: background 0.2s; }
  .genre-tag:hover { background: rgba(59,130,246,0.25); }

  @media (max-width: 640px) {
    .game-card { grid-template-columns: 30px 1fr 60px; }
    .game-dir { display: none; }
    .studio-card { flex-direction: column; gap: 8px; align-items: flex-start; }
  }
</style>

  <section class="pub-hero">
    <div class="trends-inner">
      <h1 class="pub-title">🏛️ <span><?= e($pub['name']) ?></span></h1>
      <div class="pub-meta">
        <?php if ($pub['hq_country']): ?>
          <span class="pub-meta-item">📍 <?= e($pub['hq_country']) ?></span>
        <?php endif; ?>
        <?php if ($pub['website']): ?>
          <span class="pub-meta-item">🔗 <a href="<?= e($pub['website']) ?>" target="_blank" style="color:var(--accent)"><?= e(preg_replace('#^https?://#', '', $pub['website'])) ?></a></span>
        <?php endif; ?>
        <span class="pub-meta-item">🎮 <?= $pub['game_count'] ?> games tracked</span>
      </div>
      <?php if (!empty($pub['description'])): ?>
        <p style="color:var(--text-muted);margin-top:10px;max-width:600px"><?= e($pub['description']) ?></p>
      <?php endif; ?>
    </div>
  </section>

  <!-- Stats Overview -->
  <section class="trends-section">
    <div class="trends-inner">
      <div class="pub-stats">
        <div class="pub-stat-card">
          <div class="stat-value"><?= count($games) ?></div>
          <div class="stat-label">Games in Graph</div>
        </div>
        <div class="pub-stat-card">
          <div class="stat-value"><?= count($studios) ?></div>
          <div class="stat-label">Studios</div>
        </div>
        <div class="pub-stat-card">
          <div class="stat-value"><?= number_format($stats['avg_momentum'] ?? 0, 1) ?></div>
          <div class="stat-label">Avg Momentum</div>
        </div>
        <div class="pub-stat-card">
          <div class="stat-value"><?= $stats['total_patches'] ?? 0 ?></div>
          <div class="stat-label">Total Patches</div>
        </div>
      </div>

      <?php if (!empty($stats['genres'])): ?>
        <div class="genre-tags">
          <?php foreach ($stats['genres'] as $g): ?>
            <a href="/genre/<?= e(strtolower(str_replace(' ', '-', $g))) ?>" class="genre-tag"><?= e($g) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Studios -->
  <?php if (!empty($studios)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🏢 Studios</h2>
      <?php foreach ($studios as $s): ?>
        <div class="studio-card">
          <div>
            <div class="studio-name"><a href="/studio/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></div>
            <div class="studio-meta"><?= $s['game_count'] ?? 0 ?> games tracked</div>
          </div>
          <div class="studio-momentum"><?= number_format(floatval($s['avg_momentum'] ?? 0), 1) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Game Portfolio -->
  <?php if (!empty($games)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🎮 Game Portfolio</h2>
      <?php $i = 1; foreach ($games as $g): ?>
        <div class="game-card">
          <div class="game-rank"><?= $i++ ?></div>
          <div>
            <div class="game-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['name']) ?></a></div>
            <div class="game-genre-tag"><?= e($g['genre'] ?? 'Unknown Genre') ?> · <?= $g['event_count'] ?? 0 ?> events · <?= $g['patch_count'] ?? 0 ?> patches</div>
          </div>
          <div class="game-momentum <?= floatval($g['latest_momentum'] ?? 0) > 30 ? 'impact-high' : (floatval($g['latest_momentum'] ?? 0) > 15 ? 'impact-mid' : 'impact-low') ?>">
            <?= number_format(floatval($g['latest_momentum'] ?? 0), 1) ?>
          </div>
          <div class="game-dir <?= 'dir-' . ($g['momentum_direction'] ?? 'stable') ?>">
            <?= ($g['momentum_direction'] ?? 'stable') === 'rising' ? '▲ Rising' : (($g['momentum_direction'] ?? 'stable') === 'falling' ? '▼ Falling' : '● Stable') ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <div style="text-align:center;margin:2rem 0">
    <a href="/" class="nav-back">← Home</a>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
