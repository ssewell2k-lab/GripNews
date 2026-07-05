<?php
/**
 * GripNews.uk — Genre Heatmap (Phase 12A)
 * Visual overview of gaming genres by activity and momentum.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Genre Heatmap — GripNews Intelligence';
$page_desc = 'See which gaming genres are heating up, growing, or cooling down. Real-time genre intelligence powered by GripAI.';
$page_canonical = SITE_URL . '/genres';
$nav_active = 'genres';

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/genres/heatmap"), true);
$genres = $data['genres'] ?? [];
$total_games = $data['total_games'] ?? 0;
$genre_count = $data['count'] ?? 0;
$date = $data['date'] ?? date('Y-m-d');

// Top 5 for leaderboard
$top5 = array_slice($genres, 0, 5);

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .gh-hero { text-align: center; padding: 48px 0 24px; }
  .gh-hero h1 { font-size: 2.4em; margin-bottom: 8px; }
  .gh-hero h1 .accent { color: var(--accent); }
  .gh-hero .hero-sub { color: var(--text-secondary); font-size: 1em; margin-bottom: 12px; }
  .gh-hero .hero-date { font-size: 0.85em; color: var(--text-dim); }

  .gh-stats { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 36px; }
  .gh-stat { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px 24px; text-align: center; min-width: 100px; }
  .gh-stat .val { font-size: 2em; font-weight: 800; color: var(--accent); }
  .gh-stat .lbl { font-size: 0.7em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-top: 4px; }

  /* Top 5 Leaderboard */
  .gh-leaders { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 36px; }
  .gh-leader { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px 20px; text-align: center; min-width: 140px; flex: 1; max-width: 200px; transition: all 0.2s; }
  .gh-leader:hover { border-color: var(--accent); transform: translateY(-2px); }
  .gh-leader:first-child { border-color: rgba(255,215,0,0.3); background: linear-gradient(135deg, rgba(255,215,0,0.05), transparent); }
  .gh-leader .gl-rank { font-size: 0.7em; color: var(--text-dim); text-transform: uppercase; letter-spacing: 1px; }
  .gh-leader:first-child .gl-rank { color: #ffd700; }
  .gh-leader .gl-name { font-weight: 700; font-size: 1em; margin: 4px 0; }
  .gh-leader .gl-name a { color: var(--text-primary); text-decoration: none; }
  .gh-leader .gl-name a:hover { color: var(--accent); }
  .gh-leader .gl-score { font-size: 1.4em; font-weight: 900; color: var(--accent); }
  .gh-leader .gl-games { font-size: 0.75em; color: var(--text-dim); margin-top: 4px; }

  /* Heatmap Grid */
  .gh-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; margin-bottom: 32px; }

  .genre-tile { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px; position: relative; overflow: hidden; transition: all 0.2s; }
  .genre-tile:hover { border-color: var(--accent); transform: translateY(-2px); }

  .gt-heat { position: absolute; top: 0; left: 0; right: 0; height: 3px; }

  .gt-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
  .gt-name { font-weight: 700; font-size: 0.95em; }
  .gt-name a { color: var(--text-primary); text-decoration: none; }
  .gt-name a:hover { color: var(--accent); }
  .gt-momentum { font-weight: 800; font-size: 1.2em; }

  .gt-bar-wrap { height: 4px; background: rgba(255,255,255,0.05); border-radius: 4px; margin: 10px 0 8px; overflow: hidden; }
  .gt-bar-fill { height: 100%; border-radius: 4px; transition: width 0.3s; }

  .gt-meta { display: flex; gap: 12px; font-size: 0.75em; color: var(--text-secondary); }
  .gt-meta span { display: flex; align-items: center; gap: 3px; }

  .gt-top { font-size: 0.75em; color: var(--text-dim); margin-top: 8px; padding-top: 8px;
    border-top: 1px solid rgba(255,255,255,0.04); }
  .gt-top a { color: var(--accent); text-decoration: none; }
  .gt-top a:hover { text-decoration: underline; }

  .gh-legend { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin: 24px 0; }
  .gl-item { display: flex; align-items: center; gap: 6px; font-size: 0.78em; color: var(--text-secondary); }
  .gl-swatch { width: 24px; height: 8px; border-radius: 4px; }

  @media (max-width: 768px) {
    .gh-hero h1 { font-size: 1.6em; }
    .gh-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
    .gh-leaders { flex-direction: column; align-items: stretch; }
    .gh-leader { max-width: none; }
  }
  @media (max-width: 480px) {
    .gh-grid { grid-template-columns: 1fr; }
  }
</style>

<section class="gh-hero">
  <h1>🎯 Genre <span class="accent">Heatmap</span></h1>
  <p class="hero-sub">Which gaming genres are heating up, growing, or cooling down.</p>
  <p class="hero-date">Tracking <strong><?= $total_games ?></strong> games across <strong><?= $genre_count ?></strong> genres</p>
</section>

<section class="content-wrapper">

  <!-- Top 5 Genre Leaderboard -->
  <?php if (!empty($top5)): ?>
  <div class="gh-leaders">
    <?php foreach ($top5 as $i => $g): ?>
      <div class="gh-leader">
        <div class="gl-rank"><?= $i === 0 ? '👑 #1' : '#' . ($i + 1) ?></div>
        <div class="gl-name"><a href="/genre/<?= e($g['slug']) ?>"><?= e($g['name']) ?></a></div>
        <div class="gl-score"><?= number_format($g['avg_momentum'], 1) ?></div>
        <div class="gl-games"><?= $g['game_count'] ?> game<?= $g['game_count'] !== 1 ? 's' : '' ?> · <?= $g['hot_games'] ?> hot</div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Legend -->
  <div class="gh-legend">
    <span class="gl-item"><span class="gl-swatch" style="background:linear-gradient(90deg,#e74c3c,#c0392b);"></span> Hot (20+)</span>
    <span class="gl-item"><span class="gl-swatch" style="background:linear-gradient(90deg,#e67e22,#d35400);"></span> Active (10–20)</span>
    <span class="gl-item"><span class="gl-swatch" style="background:linear-gradient(90deg,#f1c40f,#f39c12);"></span> Warming (3–10)</span>
    <span class="gl-item"><span class="gl-swatch" style="background:linear-gradient(90deg,#3498db,#2980b9);"></span> Cool (0–3)</span>
  </div>

  <!-- Heatmap Grid -->
  <div class="gh-grid">
    <?php foreach ($genres as $g):
      $m = $g['avg_momentum'];
      if ($m >= 20) { $heat_color = '#e74c3c'; $heat_grad = 'linear-gradient(90deg,#e74c3c,#c0392b)'; }
      elseif ($m >= 10) { $heat_color = '#e67e22'; $heat_grad = 'linear-gradient(90deg,#e67e22,#d35400)'; }
      elseif ($m >= 3) { $heat_color = '#f1c40f'; $heat_grad = 'linear-gradient(90deg,#f1c40f,#f39c12)'; }
      else { $heat_color = '#3498db'; $heat_grad = 'linear-gradient(90deg,#3498db,#2980b9)'; }
      $bar_width = min(100, max(5, ($m / 35) * 100));
    ?>
      <div class="genre-tile">
        <div class="gt-heat" style="background:<?= $heat_grad ?>;"></div>
        <div class="gt-header">
          <div class="gt-name"><a href="/genre/<?= e($g['slug']) ?>"><?= e($g['name']) ?></a></div>
          <div class="gt-momentum" style="color:<?= $heat_color ?>;"><?= number_format($m, 1) ?></div>
        </div>
        <div class="gt-bar-wrap">
          <div class="gt-bar-fill" style="width:<?= $bar_width ?>%;background:<?= $heat_grad ?>;"></div>
        </div>
        <div class="gt-meta">
          <span>🎮 <?= $g['game_count'] ?> games</span>
          <span>🔥 <?= $g['hot_games'] ?> hot</span>
          <span>⚡ <?= $g['warm_games'] ?> active</span>
        </div>
        <?php if ($g['top_game']): ?>
          <div class="gt-top">
            🏆 <a href="/game/<?= e($g['top_game']['slug']) ?>"><?= e($g['top_game']['name']) ?></a>
            <span style="color:<?= $heat_color ?>;font-weight:700;"><?= number_format($g['top_game']['momentum'], 1) ?></span>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Methodology -->
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-top:16px;">
    <h3 style="margin-bottom:12px;font-size:1em;">How Genre Heat Works</h3>
    <p style="font-size:0.88em;color:var(--text-secondary);line-height:1.6;">
      Each genre's heat level is determined by the average momentum score of all games in that genre.
      Games are assigned genres by GripAI during entity extraction. The <strong>top game</strong> shown is the
      highest-momentum game in each genre. Hot games (🔥) have momentum 20+, active games (⚡) are in the 1–20 range.
    </p>
  </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
