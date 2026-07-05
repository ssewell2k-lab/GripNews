<?php
/**
 * GripNews.uk — Trend Intelligence Page (Phase 10)
 * Shows a game's trend lifecycle — momentum history, stage transitions, predictions.
 */
require_once __DIR__ . '/includes/functions.php';

$slug = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (!$slug || $slug === 'trend') {
    header('Location: /trends');
    exit;
}

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/game/$slug/related"), true);

if (!$data || isset($data['error'])) {
    $page_title = 'Trend Not Found — GripNews';
    $page_desc = 'No trend data for this game.';
    $page_canonical = SITE_URL . '/trends';
    $nav_active = 'trends';
    require_once __DIR__ . '/includes/header.php';
    echo '<section class="hero"><h1>Trend Not Found</h1><p class="hero-sub">We don\'t have trend data for this game yet.</p><div style="text-align:center;margin:2rem 0"><a href="/trends" class="nav-back">← All Trends</a></div></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$game = $data['game'];
$trend = $data['trend'];
$history = $data['momentum_history'] ?? [];
$connections = $data['connections'] ?? [];
$events = $data['recent_events'] ?? [];
$signals = $data['active_signals'] ?? [];

$page_title = $game['name'] . ' Trend Analysis — GripNews';
$page_desc = "Trend lifecycle and momentum analysis for {$game['name']}. Stage: " . ($trend['current_stage'] ?? 'Unknown');
$page_canonical = SITE_URL . "/trend/$slug";
$nav_active = 'trends';

// Stage metadata
$stage_info = [
    'emerging'    => ['icon' => '🌱', 'color' => '#00e676', 'desc' => 'Early growth phase — momentum building'],
    'rising'      => ['icon' => '🚀', 'color' => '#3b82f6', 'desc' => 'Gaining traction — strong upward trajectory'],
    'peaking'     => ['icon' => '🔥', 'color' => '#ff9800', 'desc' => 'At or near peak momentum'],
    'sustained'   => ['icon' => '⚡', 'color' => '#8b5cf6', 'desc' => 'Maintaining high momentum — stable engagement'],
    'declining'   => ['icon' => '📉', 'color' => '#f44336', 'desc' => 'Momentum slowing — below recent peaks'],
    'stable'      => ['icon' => '●', 'color' => '#94a3b8', 'desc' => 'Consistent steady-state momentum'],
    'nostalgia'   => ['icon' => '🕹️', 'color' => '#a78bfa', 'desc' => 'Legacy title with recurring interest spikes'],
];

$current_stage = $trend['current_stage'] ?? 'stable';
$si = $stage_info[$current_stage] ?? $stage_info['stable'];

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .trend-hero { padding: 40px 0 24px; border-bottom: 1px solid var(--border); margin-bottom: 32px; }
  .trend-title { font-size: 2.2em; font-weight: 800; color: var(--text-primary, #e2e8f0); letter-spacing: -0.5px; }
  .trend-title span { color: var(--accent); }
  .stage-badge { display: inline-block; padding: 6px 16px; border-radius: 24px; font-size: 0.85em;
    font-weight: 700; letter-spacing: 0.5px; margin-left: 12px; vertical-align: middle; }
  .trend-sub { color: var(--text-muted); font-size: 1em; margin-top: 8px; }

  .trend-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin: 24px 0 32px; }
  .trend-stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 18px; text-align: center; }
  .trend-stat-card .stat-value { font-size: 1.8em; font-weight: 800; color: var(--accent); }
  .trend-stat-card .stat-label { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }

  .section-title { font-size: 1.05em; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px;
    color: var(--accent); margin: 32px 0 16px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }

  /* Momentum Chart */
  .momentum-chart { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 24px; margin-bottom: 24px; overflow-x: auto; }
  .chart-bars { display: flex; align-items: flex-end; gap: 3px; height: 120px; min-width: 400px; }
  .chart-bar { flex: 1; min-width: 8px; border-radius: 3px 3px 0 0; transition: opacity 0.2s; cursor: default; position: relative; }
  .chart-bar:hover { opacity: 0.8; }
  .chart-bar:hover::after { content: attr(data-label); position: absolute; bottom: 100%; left: 50%;
    transform: translateX(-50%); background: #1e293b; color: #e2e8f0; padding: 4px 8px; border-radius: 4px;
    font-size: 0.7em; white-space: nowrap; z-index: 10; }
  .chart-legend { display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.72em; color: var(--text-dim); }

  /* Stage Timeline */
  .stage-timeline { position: relative; padding-left: 30px; margin: 16px 0; }
  .stage-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: var(--border); }
  .stage-entry { position: relative; margin-bottom: 20px; }
  .stage-entry::before { content: ''; position: absolute; left: -24px; top: 4px; width: 10px; height: 10px;
    border-radius: 50%; border: 2px solid var(--accent); background: var(--bg-primary, #0f1219); }
  .stage-entry.current::before { background: var(--accent); box-shadow: 0 0 8px var(--accent); }
  .stage-name { font-weight: 700; color: var(--text-primary, #e2e8f0); font-size: 0.95em; }
  .stage-date { font-size: 0.78em; color: var(--text-muted); margin-top: 2px; font-family: var(--mono, monospace); }

  .connections-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
  .conn-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; padding: 16px; }
  .conn-card-label { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 6px; }
  .conn-card-value { font-weight: 700; color: var(--text-primary, #e2e8f0); }
  .conn-card-value a { color: var(--accent); text-decoration: none; }
  .conn-card-value a:hover { text-decoration: underline; }

  .signal-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 14px 18px; margin-bottom: 10px; }
  .signal-header { display: flex; justify-content: space-between; align-items: center; }
  .signal-type { font-size: 0.68em; text-transform: uppercase; letter-spacing: 0.5px; padding: 2px 8px;
    border-radius: 4px; font-weight: 700; background: rgba(59,130,246,0.12); color: var(--accent); }
  .signal-severity { font-size: 0.68em; text-transform: uppercase; font-weight: 700; }
  .sev-critical { color: #f44336; }
  .sev-high { color: #ff9800; }
  .sev-medium { color: #ffc107; }
  .sev-low { color: #94a3b8; }
  .signal-title { font-weight: 600; color: var(--text-primary, #e2e8f0); margin-top: 8px; font-size: 0.92em; }
  .signal-conf { font-size: 0.78em; color: var(--text-muted); margin-top: 4px; }

  .event-mini { background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px;
    padding: 14px 18px; margin-bottom: 10px; display: grid; grid-template-columns: 80px 1fr auto;
    gap: 12px; align-items: center; }
  .event-date { font-size: 0.78em; color: var(--text-muted); font-family: var(--mono, monospace); }
  .event-title { font-weight: 600; color: var(--text-primary, #e2e8f0); font-size: 0.92em; }
  .event-category { font-size: 0.72em; color: var(--accent); margin-top: 2px; }
  .event-impact { font-size: 1.2em; font-weight: 800; }
  .impact-high { color: #f44336; }
  .impact-mid { color: #ffc107; }
  .impact-low { color: var(--text-muted); }

  @media (max-width: 640px) {
    .event-mini { grid-template-columns: 1fr auto; }
    .event-date { display: none; }
  }
</style>

  <section class="trend-hero">
    <div class="trends-inner">
      <h1 class="trend-title"><?= $si['icon'] ?> <span><?= e($game['name']) ?></span>
        <span class="stage-badge" style="background:<?= $si['color'] ?>22;color:<?= $si['color'] ?>">
          <?= ucfirst($current_stage) ?>
        </span>
      </h1>
      <p class="trend-sub"><?= $si['desc'] ?> · <?= e($game['genre'] ?? 'Unknown Genre') ?></p>
    </div>
  </section>

  <!-- Stats -->
  <section class="trends-section">
    <div class="trends-inner">
      <div class="trend-stats">
        <div class="trend-stat-card">
          <div class="stat-value"><?= number_format($game['latest_momentum'], 1) ?></div>
          <div class="stat-label">Current Momentum</div>
        </div>
        <div class="trend-stat-card">
          <div class="stat-value"><?= ucfirst($game['momentum_direction']) ?></div>
          <div class="stat-label">Direction</div>
        </div>
        <?php if ($trend): ?>
        <div class="trend-stat-card">
          <div class="stat-value"><?= number_format(floatval($trend['peak_momentum'] ?? 0), 1) ?></div>
          <div class="stat-label">Peak Momentum</div>
        </div>
        <?php endif; ?>
        <div class="trend-stat-card">
          <div class="stat-value"><?= $game['event_count'] ?></div>
          <div class="stat-label">Events</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Momentum History Chart -->
  <?php if (!empty($history)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📈 Momentum History</h2>
      <div class="momentum-chart">
        <?php
          $reversed = array_reverse($history);
          $max = max(array_map(fn($h) => floatval($h['score']), $reversed));
          if ($max == 0) $max = 1;
        ?>
        <div class="chart-bars">
          <?php foreach ($reversed as $h): ?>
            <?php
              $score = floatval($h['score']);
              $pct = ($score / $max) * 100;
              $color = $score > 30 ? '#f44336' : ($score > 15 ? '#ffc107' : '#3b82f6');
              $date = date('M j', strtotime($h['calculated_at']));
            ?>
            <div class="chart-bar" style="height:<?= max($pct, 3) ?>%;background:<?= $color ?>" data-label="<?= $date ?>: <?= number_format($score, 1) ?>"></div>
          <?php endforeach; ?>
        </div>
        <div class="chart-legend">
          <span><?= date('M j', strtotime($reversed[0]['calculated_at'])) ?></span>
          <span><?= date('M j', strtotime(end($reversed)['calculated_at'])) ?></span>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Stage Lifecycle -->
  <?php if ($trend): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🔄 Lifecycle Stage</h2>
      <div class="stage-timeline">
        <?php if ($trend['previous_stage']): ?>
          <div class="stage-entry">
            <div class="stage-name"><?= ucfirst($trend['previous_stage']) ?></div>
            <div class="stage-date">Previous stage</div>
          </div>
        <?php endif; ?>
        <div class="stage-entry current">
          <div class="stage-name"><?= ucfirst($trend['current_stage']) ?></div>
          <div class="stage-date">Since <?= $trend['stage_entered_at'] ? date('M j, Y', strtotime($trend['stage_entered_at'])) : 'recently' ?></div>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Knowledge Graph Connections -->
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">🕸️ Knowledge Graph</h2>
      <div class="connections-grid">
        <?php if (!empty($connections['studios'])): ?>
          <?php foreach ($connections['studios'] as $s): ?>
            <div class="conn-card">
              <div class="conn-card-label">Developer</div>
              <div class="conn-card-value"><a href="/studio/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($connections['publisher']): ?>
          <div class="conn-card">
            <div class="conn-card-label">Publisher</div>
            <div class="conn-card-value"><a href="/publisher/<?= e($connections['publisher']['slug']) ?>"><?= e($connections['publisher']['name']) ?></a></div>
          </div>
        <?php endif; ?>
        <?php if ($connections['genre']): ?>
          <div class="conn-card">
            <div class="conn-card-label">Genre</div>
            <div class="conn-card-value"><a href="/genre/<?= e(strtolower(str_replace(' ', '-', $connections['genre']))) ?>"><?= e($connections['genre']) ?></a></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($game['platform'])): ?>
          <div class="conn-card">
            <div class="conn-card-label">Platforms</div>
            <div class="conn-card-value"><?= e(is_array($game['platform']) ? implode(', ', $game['platform']) : $game['platform']) ?></div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Related Games -->
      <?php if (!empty($connections['related_games'])): ?>
        <h3 style="margin-top:24px;font-size:0.9em;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px">Related Games</h3>
        <div class="connections-grid" style="margin-top:10px">
          <?php foreach (array_slice($connections['related_games'], 0, 6) as $rg): ?>
            <div class="conn-card">
              <div class="conn-card-value"><a href="/game/<?= e($rg['slug']) ?>"><?= e($rg['name']) ?></a></div>
              <div style="font-size:0.78em;color:var(--text-muted);margin-top:4px">
                Momentum: <?= number_format(floatval($rg['latest_momentum']), 1) ?>
                · <?= $rg['momentum_direction'] === 'rising' ? '▲' : ($rg['momentum_direction'] === 'falling' ? '▼' : '●') ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Active Signals -->
  <?php if (!empty($signals)): ?>
  <section class="trends-section">
    <div class="trends-inner">
      <h2 class="section-title">📡 Active Signals</h2>
      <?php foreach ($signals as $sig): ?>
        <div class="signal-card">
          <div class="signal-header">
            <span class="signal-type"><?= e($sig['signal_type'] ?? 'Signal') ?></span>
            <span class="signal-severity sev-<?= strtolower($sig['severity'] ?? 'low') ?>"><?= strtoupper($sig['severity'] ?? '') ?></span>
          </div>
          <div class="signal-title"><?= e($sig['title']) ?></div>
          <div class="signal-conf">Confidence: <?= number_format(floatval($sig['confidence'] ?? 0) * 100) ?>% · Detected <?= date('M j', strtotime($sig['first_detected'])) ?></div>
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
            <div class="event-category"><?= e($ev['category'] ?? '') ?></div>
          </div>
          <div class="event-impact <?= ($ev['impact_score'] ?? 0) >= 7 ? 'impact-high' : (($ev['impact_score'] ?? 0) >= 4 ? 'impact-mid' : 'impact-low') ?>">
            <?= $ev['impact_score'] ?? '—' ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <div style="text-align:center;margin:2rem 0;display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
    <a href="/game/<?= e($slug) ?>" class="nav-back">← Game Profile</a>
    <a href="/trends" class="nav-back">← All Trends</a>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
