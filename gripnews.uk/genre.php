<?php
/**
 * GripNews.uk — Genre Intelligence Page (Phase 10)
 * Displays genre-level analytics: games, momentum, trends, events.
 */
require_once __DIR__ . '/includes/functions.php';

$slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!$slug || $slug === 'genre') {
    header('Location: /');
    exit;
}

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/genre/$slug"), true);

if (!$data || isset($data['error'])) {
    $page_title = 'Genre Not Found — GripNews';
    $page_desc = 'Genre not found.';
    $page_canonical = SITE_URL;
    $nav_active = '';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="hero"><h1>Genre Not Found</h1><p class="hero-sub">We don\'t have data for this genre yet.</p><div style="text-align:center;margin:2rem 0"><a href="/" class="nav-back">← Home</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$genre = $data['genre'];
$games = $data['games'] ?? [];
$events = $data['recent_events'] ?? [];

$page_title = $genre['name'] . ' Games — Genre Intelligence | GripNews';
$page_desc = $genre['description'] ?? "Intelligence overview for {$genre['name']} games — momentum, trends, and events.";
$page_canonical = SITE_URL . "/genre/$slug";
$nav_active = '';

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .genre-hero { padding: 40px 0 24px; border-bottom: 1px solid var(--border); margin-bottom: 32px; }
  .genre-title { font-size: 2.2em; font-weight: 800; color: var(--text-primary, #e2e8f0); letter-spacing: -0.5px; }
  .genre-title span { color: var(--accent); }
  .genre-desc { color: var(--text-muted); font-size: 1.05em; margin-top: 8px; max-width: 600px; }
  .genre-trend-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 0.8em;
    font-weight: 700; letter-spacing: 0.5px; margin-left: 12px; vertical-align: middle; }
  .trend-growing { background: rgba(0,230,118,0.15); color: #00e676; }
  .trend-stable { background: rgba(59,130,246,0.15); color: #3b82f6; }
  .trend-declining { background: rgba(244,67,54,0.15); color: #f44336; }

  .genre-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin: 24px 0 32px; }
  .genre-stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 18px; text-align: center; }
  .genre-stat-card .stat-value { font-size: 1.8em; font-weight: 800; color: var(--accent); }
  .genre-stat-card .stat-label { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }

  .game-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 16px 20px; margin-bottom: 10px; transition: border-color 0.2s; display: grid;
    grid-template-columns: 40px 1fr 80px 80px 60px; gap: 12px; align-items: center; }
  .game-card:hover { border-color: var(--accent); }
  .game-rank { font-size: 1.3em; font-weight: 800; color: var(--text-dim); text-align: center; }
  .game-name { font-weight: 700; color: var(--text-primary, #e2e8f0); }
  .game-name a { color: inherit; text-decoration: none; }
  .game-name a:hover { color: var(--accent); }
  .game-meta { font-size: 0.78em; color: var(--text-muted); margin-top: 2px; }
  .game-momentum { font-size: 1.3em; font-weight: 800; text-align: center; }
  .game-events { font-size: 0.85em; color: var(--text-muted); text-align: center; }
  .game-dir { text-align: center; font-size: 0.85em; }
  .dir-rising { color: #00e676; }
  .dir-stable { color: #94a3b8; }
  .dir-falling { color: #f44336; }

  .section-title { font-size: 1.05em; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
    color: var(--accent); margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }

  .event-mini { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 14px 18px; margin-bottom: 10px; display: grid; grid-template-columns: 90px 1fr auto;
    gap: 12px; align-items: center; }
  .event-mini:hover { border-color: var(--accent); }
  .event-date { font-size: 0.78em; color: var(--text-muted); font-family: var(--mono, monospace); }
  .event-title { font-weight: 600; color: var(--text-primary, #e2e8f0); font-size: 0.92em; }
  .event-title a { color: inherit; text-decoration: none; }
  .event-title a:hover { color: var(--accent); }
  .event-game-tag { font-size: 0.7em; color: var(--accent); margin-top: 3px; }
  .event-impact { font-size: 1.2em; font-weight: 800; }
  .impact-high { color: #f44336; }
  .impact-mid { color: #ffc107; }
  .impact-low { color: var(--text-muted); }

  .no-data { text-align: center; padding: 40px 20px; color: var(--text-dim); font-size: 0.95em; }

  @media (max-width: 640px) {
    .game-card { grid-template-columns: 30px 1fr 60px; }
    .game-events, .game-dir { display: none; }
    .event-mini { grid-template-columns: 1fr auto; }
    .event-date { display: none; }
  }
</style>

  <section class="genre-hero">
    <div class="trends-inner">
      <h1 class="genre-title">🎯 <span><?= e($genre['name']) ?></span>
        <span class="genre-trend-badge trend-<?= e($genre['trend_direction']) ?>">
          <?= $genre['trend_direction'] === 'growing' ? '▲' : ($genre['trend_direction'] === 'declining' ? '▼' : '●') ?>
          <?= ucfirst(e($genre['trend_direction'])) ?>
        </span>
      </h1>
      <p class="genre-desc"><?= e($genre['description'] ?? '') ?></p>
    </div>
  </section>

  <!-- Stats Overview -->
  <section class="trends-section">
    <div class="trends-inner">
      <div class="genre-stats">
        <div class="genre-stat-card">
          <div class="stat-value"><?= $genre['game_count'] ?></div>
          <div class="stat-label">Games Tracked</div>
        </div>
        <div class="genre-stat-card">
          <div class="stat-value"><?= number_format($genre['avg_momentum'], 1) ?></div>
          <div class="stat-label">Avg Momentum</div>
        </div>
        <div class="genre-stat-card">
          <div class="stat-value"><?= ucfirst($genre['trend_direction']) ?></div>
          <div class="stat-label">Trend</div>
        </div>
        <?php if ($genre['top_game']): ?>
        <div class="genre-stat-card">
          <div class="stat-value" style="font-size:1em"><?= e(ucwords(str_replace('-', ' ', $genre['top_game']))) ?></div>
          <div class="stat-label">Top Game</div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Games in Genre -->
  <?php if (!empty($games)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🎮 Games in <?= e($genre['name']) ?></h2>
      <?php $i = 1; foreach ($games as $g): ?>
        <div class="game-card">
          <div class="game-rank"><?= $i++ ?></div>
          <div>
            <div class="game-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['name']) ?></a></div>
            <div class="game-meta"><?= $g['signal_count'] ?> signals · <?= $g['patch_count'] ?> patches</div>
          </div>
          <div class="game-momentum <?= floatval($g['latest_momentum']) > 30 ? 'impact-high' : (floatval($g['latest_momentum']) > 15 ? 'impact-mid' : 'impact-low') ?>">
            <?= number_format(floatval($g['latest_momentum']), 1) ?>
          </div>
          <div class="game-events"><?= $g['event_count'] ?> events</div>
          <div class="game-dir <?= 'dir-' . ($g['momentum_direction'] ?? 'stable') ?>">
            <?= $g['momentum_direction'] === 'rising' ? '▲' : ($g['momentum_direction'] === 'falling' ? '▼' : '●') ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Recent Events -->
  <?php if (!empty($events)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📰 Recent Events</h2>
      <?php foreach ($events as $ev): ?>
        <div class="event-mini">
          <div class="event-date"><?= date('M j', strtotime($ev['event_date'])) ?></div>
          <div>
            <div class="event-title"><?= e($ev['title']) ?></div>
            <div class="event-game-tag"><a href="/game/<?= e($ev['game_slug'] ?? '') ?>" style="color:var(--accent);text-decoration:none;"><?= e(ucwords(str_replace('-', ' ', $ev['game_slug'] ?? ''))) ?></a> · <?= e($ev['category'] ?? '') ?></div>
          </div>
          <div class="event-impact <?= ($ev['impact_score'] ?? 0) >= 7 ? 'impact-high' : (($ev['impact_score'] ?? 0) >= 4 ? 'impact-mid' : 'impact-low') ?>">
            <?= $ev['impact_score'] ?? '—' ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (empty($games) && empty($events)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <div class="no-data">No game data available for this genre yet. The knowledge graph is continuously learning.</div>
    </div>
  </section>
  <?php endif; ?>

  <div style="text-align:center;margin:2rem 0">
    <a href="/" class="nav-back">← Home</a>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
