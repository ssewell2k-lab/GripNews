<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Top 100 Games by Momentum — GripNews Intelligence';
$page_desc = 'Daily ranking of the top 100 games by momentum score. Updated every day. The definitive measure of what\'s happening in gaming right now.';
$page_canonical = SITE_URL . '/rankings';
$nav_active = 'rankings';

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/rankings/top100"), true);
$rankings = $data['rankings'] ?? [];
$update_date = $data['date'] ?? date('Y-m-d');

// Summary stats
$rising = 0; $falling = 0; $stable = 0; $avg_score = 0;
foreach ($rankings as $g) {
    if ($g['direction'] === 'rising') $rising++;
    elseif ($g['direction'] === 'falling') $falling++;
    else $stable++;
    $avg_score += $g['momentum'];
}
$avg_score = count($rankings) ? round($avg_score / count($rankings), 1) : 0;
$top_game = $rankings[0] ?? null;

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .top100-hero { text-align: center; padding: 48px 0 32px; }
  .top100-hero h1 { font-size: 2.4em; margin-bottom: 8px; }
  .top100-hero h1 .accent { color: var(--accent); }
  .top100-hero .hero-date { font-size: 0.9em; color: var(--text-secondary); margin-bottom: 20px; }
  .top100-hero .hero-date strong { color: var(--accent); }

  .top100-stats { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 32px; }
  .ts-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px 24px; text-align: center; min-width: 120px; }
  .ts-val { font-size: 1.8em; font-weight: 800; color: var(--accent); }
  .ts-label { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-top: 4px; }
  .ts-card.rising .ts-val { color: #2ecc71; }
  .ts-card.falling .ts-val { color: #e74c3c; }

  .top100-crown { background: linear-gradient(135deg, rgba(255,215,0,0.08), rgba(255,215,0,0.02));
    border: 1px solid rgba(255,215,0,0.2); border-radius: 16px; padding: 24px 32px;
    text-align: center; margin-bottom: 32px; }
  .crown-label { font-size: 0.75em; text-transform: uppercase; letter-spacing: 2px; color: #ffd700; margin-bottom: 4px; }
  .crown-game { font-size: 1.6em; font-weight: 800; color: var(--text-primary); }
  .crown-game a { color: var(--text-primary); text-decoration: none; }
  .crown-game a:hover { color: var(--accent); }
  .crown-score { font-size: 2.2em; font-weight: 900; color: #ffd700; margin: 4px 0; }
  .crown-sub { font-size: 0.85em; color: var(--text-secondary); }

  .rankings-search { text-align: center; margin-bottom: 24px; }
  .rankings-search input { background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px;
    padding: 10px 16px; width: 100%; max-width: 400px; color: var(--text-primary); font-size: 0.95em; }
  .rankings-search input:focus { outline: none; border-color: var(--accent); }

  .rankings-legend { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; justify-content: center; }
  .legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.78em; color: var(--text-secondary); }
  .legend-dot { width: 10px; height: 10px; border-radius: 50%; }

  .r-table { width: 100%; }
  .r-row { display: grid; grid-template-columns: 50px 1fr 100px 100px 160px; align-items: center;
    padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s; }
  .r-row:hover { background: rgba(255,255,255,0.02); }
  .r-row.r-header { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
  .r-row.r-top3 { background: rgba(255,215,0,0.03); }
  .r-row.r-top3:hover { background: rgba(255,215,0,0.06); }

  .r-rank { font-weight: 800; color: var(--text-secondary); font-size: 0.95em; }
  .r-top3 .r-rank { color: #ffd700; font-size: 1.1em; }
  .r-name a { color: var(--text-primary); text-decoration: none; font-weight: 600; }
  .r-name a:hover { color: var(--accent); }
  .r-score { font-weight: 800; font-size: 1.1em; }
  .r-score.s-high { color: #2ecc71; }
  .r-score.s-mid { color: var(--accent); }
  .r-score.s-low { color: var(--text-secondary); }

  .r-dir { font-size: 0.88em; }
  .r-dir.rising { color: #2ecc71; }
  .r-dir.falling { color: #e74c3c; }
  .r-dir.stable { color: #95a5a6; }

  .comp-bars { display: flex; gap: 2px; align-items: flex-end; height: 16px; }
  .comp-bar { display: inline-block; height: 100%; border-radius: 2px; min-width: 3px; }

  .r-methodology { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 24px 28px; margin-top: 32px; }
  .r-methodology h3 { margin-bottom: 12px; font-size: 1em; }
  .r-methodology p, .r-methodology li { font-size: 0.88em; color: var(--text-secondary); line-height: 1.6; }

  .share-bar { display: flex; gap: 8px; justify-content: center; margin: 24px 0; }
  .share-btn { background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px;
    padding: 8px 16px; color: var(--text-secondary); text-decoration: none; font-size: 0.82em; transition: all 0.2s; cursor: pointer; }
  .share-btn:hover { border-color: var(--accent); color: var(--accent); }

  .embed-cta { text-align: center; margin: 32px 0; padding: 24px; background: var(--bg-card);
    border: 1px solid var(--border); border-radius: 12px; }
  .embed-cta p { color: var(--text-secondary); font-size: 0.88em; margin-bottom: 12px; }
  .embed-cta code { background: rgba(255,255,255,0.05); padding: 8px 16px; border-radius: 6px;
    font-size: 0.8em; display: block; max-width: 600px; margin: 0 auto; word-break: break-all; color: var(--accent); }

  @media (max-width: 768px) {
    .r-row { grid-template-columns: 40px 1fr 70px 70px; font-size: 0.9em; padding: 10px 8px; }
    .r-row .r-components { display: none; }
    .top100-hero h1 { font-size: 1.6em; }
    .top100-stats { gap: 8px; }
    .ts-card { padding: 12px 16px; min-width: 90px; }
    .ts-val { font-size: 1.4em; }
  }
</style>

<section class="top100-hero">
  <h1>🏆 <span class="accent">Top 100</span> Games</h1>
  <p class="hero-date">Updated <strong><?= format_date($update_date, 'j M Y') ?></strong> · Refreshed daily at 07:00 UTC</p>
</section>

<?php if (!empty($rankings)): ?>

  <!-- Summary Stats -->
  <section class="content-wrapper">
    <div class="top100-stats">
      <div class="ts-card">
        <div class="ts-val"><?= count($rankings) ?></div>
        <div class="ts-label">Games Tracked</div>
      </div>
      <div class="ts-card rising">
        <div class="ts-val"><?= $rising ?></div>
        <div class="ts-label">Rising</div>
      </div>
      <div class="ts-card falling">
        <div class="ts-val"><?= $falling ?></div>
        <div class="ts-label">Falling</div>
      </div>
      <div class="ts-card">
        <div class="ts-val"><?= $avg_score ?></div>
        <div class="ts-label">Avg Score</div>
      </div>
    </div>

    <!-- #1 Crown -->
    <?php if ($top_game): ?>
    <div class="top100-crown">
      <div class="crown-label">👑 #1 Most Active Game</div>
      <div class="crown-game"><a href="/game/<?= e($top_game['slug']) ?>"><?= e($top_game['game']) ?></a></div>
      <div class="crown-score"><?= number_format($top_game['momentum'], 1) ?></div>
      <div class="crown-sub">momentum score</div>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <div class="rankings-search">
      <input type="text" id="rankings-filter" placeholder="🔍 Search games..." oninput="filterRankings(this.value)">
    </div>

    <!-- Legend -->
    <div class="rankings-legend">
      <span class="legend-item"><span class="legend-dot" style="background:#2ecc71"></span> News</span>
      <span class="legend-item"><span class="legend-dot" style="background:#3498db"></span> Patches</span>
      <span class="legend-item"><span class="legend-dot" style="background:#e67e22"></span> Signals</span>
      <span class="legend-item"><span class="legend-dot" style="background:#9b59b6"></span> Issues</span>
    </div>

    <!-- Rankings Table -->
    <div class="r-table" id="rankings-table">
      <div class="r-row r-header">
        <span class="r-rank">#</span>
        <span class="r-name">Game</span>
        <span class="r-score">Score</span>
        <span class="r-dir">Trend</span>
        <span class="r-components">Components</span>
      </div>
      <?php foreach ($rankings as $g):
        $sc_class = $g['momentum'] > 50 ? 's-high' : ($g['momentum'] > 20 ? 's-mid' : 's-low');
        $is_top3 = $g['rank'] <= 3;
      ?>
        <div class="r-row <?= $is_top3 ? 'r-top3' : '' ?>" data-name="<?= strtolower(e($g['game'])) ?>">
          <span class="r-rank"><?= $g['rank'] ?></span>
          <span class="r-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></span>
          <span class="r-score <?= $sc_class ?>"><?= number_format($g['momentum'], 1) ?></span>
          <span class="r-dir <?= e($g['direction']) ?>">
            <?php if ($g['direction'] === 'rising'): ?>
              ▲ +<?= number_format(abs($g['delta']), 1) ?>
            <?php elseif ($g['direction'] === 'falling'): ?>
              ▼ <?= number_format($g['delta'], 1) ?>
            <?php else: ?>
              ● Stable
            <?php endif; ?>
          </span>
          <span class="r-components">
            <span class="comp-bars">
              <span class="comp-bar" style="width:<?= max($g['components']['news'] * 0.8, 3) ?>px;background:#2ecc71" title="News: <?= number_format($g['components']['news'],0) ?>"></span>
              <span class="comp-bar" style="width:<?= max($g['components']['patches'] * 0.8, 3) ?>px;background:#3498db" title="Patches: <?= number_format($g['components']['patches'],0) ?>"></span>
              <span class="comp-bar" style="width:<?= max($g['components']['signals'] * 0.8, 3) ?>px;background:#e67e22" title="Signals: <?= number_format($g['components']['signals'],0) ?>"></span>
              <span class="comp-bar" style="width:<?= max($g['components']['issues'] * 0.8, 3) ?>px;background:#9b59b6" title="Issues: <?= number_format($g['components']['issues'],0) ?>"></span>
            </span>
          </span>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Share -->
    <div class="share-bar">
      <a class="share-btn" onclick="navigator.clipboard.writeText(window.location.href);this.textContent='Copied!';setTimeout(()=>this.textContent='📋 Copy Link',1500)">📋 Copy Link</a>
      <a class="share-btn" href="https://twitter.com/intent/tweet?text=Top%20100%20Games%20by%20Momentum%20—%20<?= urlencode($top_game['game'] ?? '') ?>%20is%20%231%20right%20now&url=<?= urlencode(SITE_URL . '/rankings') ?>" target="_blank" rel="noopener">𝕏 Share</a>
      <a class="share-btn" href="https://bsky.app/intent/compose?text=Top%20100%20Games%20by%20Momentum%20—%20<?= urlencode($top_game['game'] ?? '') ?>%20is%20%231%20today%20<?= urlencode(SITE_URL . '/rankings') ?>" target="_blank" rel="noopener">🦋 Bluesky</a>
    </div>

    <!-- Embed CTA -->
    <div class="embed-cta">
      <p>Want this data on your site? Embed the Top 10 widget:</p>
      <code>&lt;script src="https://gripnews.uk/embed/top10.js"&gt;&lt;/script&gt;</code>
    </div>

    <!-- Methodology -->
    <div class="r-methodology">
      <h3>📐 How Momentum is Calculated</h3>
      <p>Each game's momentum score (0–100) combines four weighted signals from the last 7 days:</p>
      <ul>
        <li><strong>News Volume (30%)</strong> — Published articles and coverage mentioning this game</li>
        <li><strong>Patch Activity (20%)</strong> — Updates, hotfixes, and balance changes detected</li>
        <li><strong>Signal Intensity (25%)</strong> — Active intelligence signals, weighted by confidence</li>
        <li><strong>Issue Frequency (25%)</strong> — Player-reported issues and bugs tracked</li>
      </ul>
      <p>Scores normalise relative to the most active game in each category. Rankings update daily at 07:00 UTC.</p>
      <p style="margin-top:12px"><strong>Source:</strong> Data powered by the <a href="https://api.gripai.uk" style="color:var(--accent)">Grip Protocol Intelligence API</a></p>
    </div>
  </section>

<?php else: ?>
  <section class="content-wrapper">
    <div class="empty-state">
      <div class="icon">📊</div>
      <h2>Rankings loading</h2>
      <p>Rankings update daily after the momentum engine runs at 07:00 UTC. Check back soon.</p>
    </div>
  </section>
<?php endif; ?>

<script>
function filterRankings(q) {
  q = q.toLowerCase();
  document.querySelectorAll('.r-row:not(.r-header)').forEach(function(row) {
    var name = row.getAttribute('data-name') || '';
    row.style.display = name.includes(q) ? '' : 'none';
  });
}
</script>


  <!-- Embed Widget Promotion -->
  <div style="max-width:900px;margin:28px auto;padding:16px 24px;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);border-radius:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
      <div style="font-weight:700;font-size:0.95em;">📊 Embed this data on your site</div>
      <div style="font-size:0.8em;color:var(--text-muted);margin-top:2px;">Free embeddable widgets — Top 10, Momentum, Trending signals.</div>
    </div>
    <a href="/embed" style="padding:8px 20px;background:var(--accent);color:#fff;border-radius:8px;font-size:0.82em;font-weight:700;text-decoration:none;white-space:nowrap;">Get Embed Code →</a>
  </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
