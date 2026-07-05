<?php
/**
 * GripNews.uk — Competitive Intelligence
 * Esports as a sensor for game health and direction.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Competitive Intelligence — GripNews';
$page_desc = 'Esports intelligence: what competitive activity reveals about game health, momentum, and publisher commitment.';
$page_canonical = SITE_URL . '/competitive';
$nav_active = 'competitive';

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/competitive"), true);
$events = $data['events'] ?? [];
$top_games = $data['top_competitive_games'] ?? [];
$intel = $data['recent_intel'] ?? [];
$health = $data['health_scores'] ?? [];
$sources = $data['sources'] ?? [];

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .comp-hero { text-align:center; padding:40px 0 32px; }
  .comp-hero h1 { font-size:2em; font-weight:800; }
  .comp-hero h1 span { color:var(--accent); }
  .comp-hero-sub { color:var(--text-muted); margin-top:8px; font-size:0.95em; max-width:600px; margin-left:auto; margin-right:auto; }

  .comp-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:12px; margin:0 0 32px; }
  .comp-stat { background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px; text-align:center; }
  .comp-stat-val { font-size:1.6em; font-weight:800; color:var(--accent); font-family:var(--mono); }
  .comp-stat-label { font-size:0.72em; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-top:4px; }

  .comp-section { margin-bottom:32px; }
  .comp-section-title { font-size:1.1em; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:var(--accent); margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid var(--border); }

  .health-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
  .health-card { background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:16px; transition:border-color .2s; }
  .health-card:hover { border-color:var(--accent); }
  .health-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
  .health-card-name { font-weight:700; font-size:1em; }
  .health-card-name a { color:var(--text); text-decoration:none; }
  .health-card-name a:hover { color:var(--accent); }
  .health-badge { display:inline-block; padding:2px 10px; border-radius:20px; font-size:0.7em; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; }
  .health-active { background:rgba(34,197,94,0.15); color:#22c55e; }
  .health-moderate { background:rgba(245,158,11,0.15); color:#f59e0b; }
  .health-dormant { background:rgba(100,116,139,0.1); color:#64748b; }
  .health-card-stats { display:flex; gap:16px; margin-top:8px; font-size:0.82em; color:var(--text-muted); }
  .health-card-stats span { display:flex; align-items:center; gap:4px; }
  .health-momentum { font-weight:700; }
  .health-momentum.rising { color:#22c55e; }
  .health-momentum.falling { color:#ef4444; }
  .health-momentum.stable { color:#f59e0b; }

  .intel-feed { display:flex; flex-direction:column; gap:10px; }
  .intel-item { background:var(--bg-card); border:1px solid var(--border); border-radius:10px; padding:14px 16px; transition:border-color .2s; }
  .intel-item:hover { border-color:var(--accent); }
  .intel-item-header { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
  .intel-title { font-weight:600; font-size:0.92em; color:var(--text); flex:1; }
  .intel-title a { color:var(--text); text-decoration:none; }
  .intel-title a:hover { color:var(--accent); }
  .intel-source { font-size:0.72em; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; color:var(--accent); white-space:nowrap; }
  .intel-summary { font-size:0.82em; color:var(--text-muted); margin-top:6px; line-height:1.5; }
  .intel-tags { margin-top:8px; display:flex; gap:6px; flex-wrap:wrap; }
  .intel-tag { font-size:0.7em; padding:2px 8px; border-radius:12px; background:rgba(6,182,212,0.12); color:#06b6d4; font-weight:600; }

  .event-table { width:100%; }
  .event-row { display:grid; grid-template-columns:50px 1fr 100px 90px; gap:12px; padding:10px 0; border-bottom:1px solid var(--border); align-items:center; }
  .event-row:last-child { border-bottom:none; }
  .event-header { font-size:0.72em; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); font-weight:600; }
  .event-score { font-weight:800; font-family:var(--mono); text-align:center; }
  .event-title { font-weight:600; font-size:0.9em; }
  .event-title a { color:var(--text); text-decoration:none; }
  .event-title a:hover { color:var(--accent); }
  .event-game { font-size:0.78em; color:var(--text-muted); }

  .source-bar { display:flex; gap:8px; flex-wrap:wrap; }
  .source-chip { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:4px 14px; font-size:0.78em; font-weight:600; color:var(--text-muted); }
  .source-chip strong { color:var(--accent); }

  @media(max-width:600px) {
    .event-row { grid-template-columns:40px 1fr 70px; }
    .event-row .event-game { display:none; }
  }
</style>

<section class="comp-hero">
  <h1>⚔️ <span>Competitive Intelligence</span></h1>
  <p class="comp-hero-sub">What esports and competitive activity reveals about game health, momentum, and publisher commitment. Not news — intelligence.</p>
</section>

<!-- Stats -->
<div class="comp-stats">
  <div class="comp-stat">
    <div class="comp-stat-val"><?= count($top_games) ?></div>
    <div class="comp-stat-label">Competitive Titles</div>
  </div>
  <div class="comp-stat">
    <div class="comp-stat-val"><?= count($events) ?></div>
    <div class="comp-stat-label">Esports Signals</div>
  </div>
  <div class="comp-stat">
    <div class="comp-stat-val"><?= count($intel) ?></div>
    <div class="comp-stat-label">Intel Sources</div>
  </div>
  <div class="comp-stat">
    <div class="comp-stat-val"><?= count($sources) ?></div>
    <div class="comp-stat-label">Active Feeds</div>
  </div>
</div>

<!-- Competitive Health Scores -->
<?php if (!empty($health)): ?>
<section class="comp-section">
  <h2 class="comp-section-title">🏥 Competitive Health</h2>
  <div class="health-grid">
    <?php foreach ($health as $h): ?>
      <div class="health-card">
        <div class="health-card-header">
          <div class="health-card-name"><a href="/game/<?= e($h['slug']) ?>"><?= e($h['name']) ?></a></div>
          <span class="health-badge health-<?= $h['health'] ?? 'dormant' ?>"><?= $h['health'] ?? 'dormant' ?></span>
        </div>
        <div class="health-card-stats">
          <span>📊 <?= $h['signal_count'] ?? 0 ?> signals</span>
          <span>💥 <?= $h['avg_impact'] ?? '—' ?> avg impact</span>
          <span class="health-momentum <?= $h['momentum_direction'] ?? 'stable' ?>">
            <?= ($h['momentum_direction'] ?? 'stable') === 'rising' ? '📈' : (($h['momentum_direction'] ?? 'stable') === 'falling' ? '📉' : '➡️') ?>
            <?= number_format($h['latest_momentum'] ?? 0, 1) ?>
          </span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Esports Signals/Events -->
<?php if (!empty($events)): ?>
<section class="comp-section">
  <h2 class="comp-section-title">📡 Latest Esports Signals</h2>
  <div class="event-table">
    <div class="event-row event-header">
      <span>Score</span>
      <span>Signal</span>
      <span>Game</span>
      <span>Date</span>
    </div>
    <?php foreach ($events as $ev): ?>
      <div class="event-row">
        <div class="event-score score-<?= score_class($ev['impact_score'] ?? 0) ?>"><?= $ev['impact_score'] ?? '—' ?></div>
        <div class="event-title"><?= e($ev['title']) ?></div>
        <div class="event-game"><?= e($ev['game_name'] ?? '—') ?></div>
        <div class="event-game"><?= $ev['published_at'] ? format_date($ev['published_at'], 'j M') : '—' ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Intel Feed -->
<section class="comp-section">
  <h2 class="comp-section-title">🔍 Esports Intel Feed</h2>
  <?php if (empty($intel)): ?>
    <div class="empty-state"><p>Intel feed is warming up. Check back after the next crawl cycle.</p></div>
  <?php else: ?>
    <div class="intel-feed">
      <?php foreach (array_slice($intel, 0, 20) as $item): ?>
        <div class="intel-item">
          <div class="intel-item-header">
            <div class="intel-title"><a href="<?= e($item['url'] ?? '#') ?>" target="_blank" rel="noopener"><?= e($item['title']) ?></a></div>
            <div class="intel-source"><?= e($item['source']) ?></div>
          </div>
          <?php if (!empty($item['summary'])): ?>
            <div class="intel-summary"><?= e(mb_substr($item['summary'], 0, 200)) ?></div>
          <?php endif; ?>
          <?php
            $tags = $item['game_tags'] ?? [];
            if (is_string($tags)) { $tags = json_decode($tags, true) ?: []; }
            if (!empty($tags)):
          ?>
            <div class="intel-tags">
              <?php foreach ($tags as $tag): ?>
                <span class="intel-tag"><?= e($tag) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Sources -->
<?php if (!empty($sources)): ?>
<section class="comp-section">
  <h2 class="comp-section-title">📡 Active Intelligence Sources</h2>
  <div class="source-bar">
    <?php foreach ($sources as $src): ?>
      <span class="source-chip"><?= e($src['source']) ?> <strong>(<?= $src['articles'] ?>)</strong></span>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- Empty state if no data at all -->
<?php if (empty($events) && empty($intel) && empty($health)): ?>
<section class="comp-section" style="text-align:center; padding:40px 0;">
  <div class="empty-state">
    <div class="icon">⚔️</div>
    <h2>Competitive Intelligence Building</h2>
    <p>The esports sensor network is active and collecting data. Intelligence signals will appear after the next pipeline cycle.</p>
    <p style="color:var(--text-muted); font-size:0.85em; margin-top:12px;">Sources: Dot Esports, Dexerto, Esports.gg, ESTNN, HLTV.org, Esports Insider + more</p>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
