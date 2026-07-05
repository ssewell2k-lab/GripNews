<?php
/**
 * GripNews.uk — Studios Rankings (Phase 12A enhanced)
 * Ranked studio intelligence with genres, top games, and momentum.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Studio Rankings — GripNews Intelligence';
$page_desc = 'Ranked intelligence profiles for game studios. Track momentum, genres, top games, and portfolio activity. Powered by GripAI.';
$page_canonical = SITE_URL . '/studios';
$nav_active = 'studios';

$api = 'https://gripai.uk/v2';
// Try new rankings API first, fall back to old
$data = @json_decode(@file_get_contents("$api/studios/rankings"), true);
if (!$data || empty($data['studios'])) {
  $data = @json_decode(@file_get_contents("$api/studios"), true);
}
$studios = $data['studios'] ?? [];
$date = $data['date'] ?? date('Y-m-d');

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .st-hero { text-align: center; padding: 48px 0 24px; }
  .st-hero h1 { font-size: 2.4em; margin-bottom: 8px; }
  .st-hero h1 .accent { color: var(--accent); }
  .st-hero .hero-sub { color: var(--text-secondary); font-size: 1em; margin-bottom: 12px; }
  .st-hero .hero-date { font-size: 0.85em; color: var(--text-dim); }

  .st-podium { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 36px; }
  .st-pod-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 20px 24px; text-align: center; min-width: 160px; flex: 1; max-width: 240px; transition: all 0.2s; }
  .st-pod-card:hover { border-color: var(--accent); transform: translateY(-3px); }
  .st-pod-card:first-child { border-color: rgba(255,215,0,0.3); background: linear-gradient(135deg, rgba(255,215,0,0.06), transparent); }
  .st-pod-card:nth-child(2) { border-color: rgba(192,192,192,0.2); }
  .st-pod-card:nth-child(3) { border-color: rgba(205,127,50,0.2); }
  .st-pod-rank { font-size: 0.7em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-dim); margin-bottom: 4px; }
  .st-pod-card:first-child .st-pod-rank { color: #ffd700; }
  .st-pod-name { font-size: 1.1em; font-weight: 700; margin-bottom: 6px; }
  .st-pod-name a { color: var(--text-primary); text-decoration: none; }
  .st-pod-name a:hover { color: var(--accent); }
  .st-pod-score { font-size: 1.6em; font-weight: 900; color: var(--accent); }
  .st-pod-meta { font-size: 0.75em; color: var(--text-dim); margin-top: 6px; }
  .st-pod-top { font-size: 0.78em; color: var(--text-secondary); margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.04); }
  .st-pod-top a { color: var(--accent); text-decoration: none; }

  .st-table { margin-top: 8px; }
  .st-row { display: grid; grid-template-columns: 50px 1fr 100px 120px 140px;
    align-items: center; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s; }
  .st-row:hover { background: rgba(255,255,255,0.02); }
  .st-row.header { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
  .st-rank { font-weight: 700; color: var(--text-dim); }
  .st-info { display: flex; flex-direction: column; gap: 2px; }
  .st-name a { color: var(--text-primary); text-decoration: none; font-weight: 600; font-size: 0.95em; }
  .st-name a:hover { color: var(--accent); }
  .st-genres { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; }
  .st-genre-tag { font-size: 0.65em; padding: 1px 6px; border-radius: 3px; background: rgba(255,255,255,0.05); color: var(--text-dim); }
  .st-games { font-size: 0.85em; color: var(--text-secondary); text-align: center; }
  .st-momentum { font-weight: 800; font-size: 1.1em; text-align: center; }
  .st-momentum.high { color: #e74c3c; }
  .st-momentum.mid { color: #e67e22; }
  .st-momentum.low { color: var(--text-secondary); }
  .st-top-game { font-size: 0.8em; }
  .st-top-game a { color: var(--accent); text-decoration: none; }
  .st-top-game a:hover { text-decoration: underline; }
  .st-top-game .tg-score { color: var(--text-dim); font-weight: 600; }

  @media (max-width: 768px) {
    .st-hero h1 { font-size: 1.6em; }
    .st-podium { flex-direction: column; align-items: stretch; }
    .st-pod-card { max-width: none; }
    .st-row { grid-template-columns: 40px 1fr 70px 80px; font-size: 0.9em; }
    .st-row .st-top-game { display: none; }
  }
</style>

<section class="st-hero">
  <h1>🏢 Studio <span class="accent">Rankings</span></h1>
  <p class="hero-sub">Intelligence profiles for <?= count($studios) ?> game studios, ranked by portfolio momentum.</p>
  <p class="hero-date">Updated <?= date('j M Y', strtotime($date)) ?></p>
</section>

<section class="content-wrapper">

  <!-- Top 3 Podium -->
  <?php $top3 = array_slice($studios, 0, 3); ?>
  <?php if (count($top3) >= 3): ?>
  <div class="st-podium">
    <?php foreach ($top3 as $i => $s): ?>
      <div class="st-pod-card">
        <div class="st-pod-rank"><?= $i === 0 ? '👑 #1' : '#' . ($i + 1) ?></div>
        <div class="st-pod-name"><a href="/studio/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></div>
        <div class="st-pod-score"><?= number_format($s['avg_momentum'], 1) ?></div>
        <div class="st-pod-meta">
          🎮 <?= $s['game_count'] ?> game<?= $s['game_count'] !== 1 ? 's' : '' ?>
          <?php if (($s['hot_games'] ?? 0) > 0): ?> · 🔥 <?= $s['hot_games'] ?> hot<?php endif; ?>
        </div>
        <?php if (!empty($s['top_game'])): ?>
          <div class="st-pod-top">🏆 <a href="/game/<?= e($s['top_game']['slug']) ?>"><?= e($s['top_game']['name']) ?></a></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Full Rankings Table -->
  <div class="st-table">
    <div class="st-row header">
      <div>Rank</div>
      <div>Studio</div>
      <div>Games</div>
      <div>Momentum</div>
      <div>Top Game</div>
    </div>
    <?php foreach ($studios as $s):
      $m = $s['avg_momentum'] ?? 0;
      $m_class = $m >= 20 ? 'high' : ($m >= 5 ? 'mid' : 'low');
    ?>
      <div class="st-row">
        <div class="st-rank">#<?= $s['rank'] ?? '—' ?></div>
        <div class="st-info">
          <div class="st-name"><a href="/studio/<?= e($s['slug']) ?>"><?= e($s['name']) ?></a></div>
          <?php if (!empty($s['genres'])): ?>
            <div class="st-genres">
              <?php foreach (array_slice($s['genres'], 0, 4) as $genre): ?>
                <span class="st-genre-tag"><?= e($genre) ?></span>
              <?php endforeach; ?>
              <?php if (count($s['genres']) > 4): ?>
                <span class="st-genre-tag">+<?= count($s['genres']) - 4 ?></span>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="st-games"><?= $s['game_count'] ?></div>
        <div class="st-momentum <?= $m_class ?>"><?= number_format($m, 1) ?></div>
        <div class="st-top-game">
          <?php if (!empty($s['top_game'])): ?>
            <a href="/game/<?= e($s['top_game']['slug']) ?>"><?= e($s['top_game']['name']) ?></a>
            <span class="tg-score">(<?= number_format($s['top_game']['momentum'], 1) ?>)</span>
          <?php else: ?>
            <span style="color:var(--text-dim);">—</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
