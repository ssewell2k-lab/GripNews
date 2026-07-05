<?php
/**
 * GripNews.uk — Momentum Movers (Phase 12A)
 * Fastest-rising, hottest, and most active games by momentum.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Momentum Movers — GripNews Intelligence';
$page_desc = 'Track which games are heating up, cooling down, and dominating the gaming ecosystem. Live momentum intelligence powered by GripAI.';
$page_canonical = SITE_URL . '/momentum';
$nav_active = 'momentum';

$api = 'https://gripai.uk/v2';
$data = @json_decode(@file_get_contents("$api/momentum/movers"), true);
$summary = $data['summary'] ?? [];
$hot = $data['tiers']['hot'] ?? [];
$active = $data['tiers']['active'] ?? [];
$warming = $data['tiers']['warming'] ?? [];
$rising = $data['movers']['rising'] ?? [];
$falling = $data['movers']['falling'] ?? [];
$total = $data['total_tracked'] ?? 0;
$date = $data['date'] ?? date('Y-m-d');

require_once __DIR__ . '/includes/header.php';
?>

<style>
  .mom-hero { text-align: center; padding: 48px 0 32px; }
  .mom-hero h1 { font-size: 2.4em; margin-bottom: 8px; }
  .mom-hero h1 .accent { color: var(--accent); }
  .mom-hero .hero-sub { color: var(--text-secondary); font-size: 1em; margin-bottom: 16px; }
  .mom-hero .hero-date { font-size: 0.85em; color: var(--text-dim); }
  .mom-hero .hero-date strong { color: var(--accent); }

  .mom-summary { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 36px; }
  .mom-stat { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px 24px; text-align: center; min-width: 100px; }
  .mom-stat .val { font-size: 2em; font-weight: 800; color: var(--accent); }
  .mom-stat .lbl { font-size: 0.7em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-secondary); margin-top: 4px; }
  .mom-stat.hot .val { color: #e74c3c; }
  .mom-stat.active .val { color: #e67e22; }
  .mom-stat.warm .val { color: #f1c40f; }
  .mom-stat.rising .val { color: #2ecc71; }
  .mom-stat.falling .val { color: #e74c3c; }

  .mom-section { margin-bottom: 40px; }
  .mom-section h2 { font-size: 1.3em; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
  .mom-section h2 .badge { font-size: 0.55em; background: rgba(255,255,255,0.05); padding: 4px 10px;
    border-radius: 20px; color: var(--text-secondary); font-weight: 500; }

  .tier-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px; }
  .tier-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px 20px; transition: all 0.2s; position: relative; overflow: hidden; }
  .tier-card:hover { border-color: var(--accent); transform: translateY(-2px); }
  .tier-card.hot { border-left: 3px solid #e74c3c; }
  .tier-card.active { border-left: 3px solid #e67e22; }
  .tier-card.warm { border-left: 3px solid #f1c40f; }

  .tc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
  .tc-rank { font-size: 0.75em; color: var(--text-dim); font-weight: 600; }
  .tc-name { font-size: 1.05em; font-weight: 700; }
  .tc-name a { color: var(--text-primary); text-decoration: none; }
  .tc-name a:hover { color: var(--accent); }
  .tc-score { font-size: 1.6em; font-weight: 900; }
  .tc-score.high { color: #e74c3c; }
  .tc-score.mid { color: #e67e22; }
  .tc-score.low { color: #f1c40f; }

  .tc-genre { font-size: 0.78em; color: var(--accent); margin-bottom: 8px; }

  .tc-components { display: flex; gap: 6px; flex-wrap: wrap; }
  .tc-comp { font-size: 0.7em; padding: 3px 8px; border-radius: 4px; background: rgba(255,255,255,0.04); color: var(--text-secondary); }
  .tc-comp .cv { font-weight: 700; color: var(--text-primary); }

  .tc-bar { position: absolute; bottom: 0; left: 0; height: 3px; border-radius: 0 0 0 12px; }
  .tc-bar.news { background: #2ecc71; }
  .tc-bar.patches { background: #3498db; left: 25%; }
  .tc-bar.signals { background: #e67e22; left: 50%; }
  .tc-bar.issues { background: #9b59b6; left: 75%; }

  .mom-table { width: 100%; }
  .mom-row { display: grid; grid-template-columns: 50px 1fr 90px 90px 120px; align-items: center;
    padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.15s; }
  .mom-row:hover { background: rgba(255,255,255,0.02); }
  .mom-row.header { font-size: 0.72em; text-transform: uppercase; letter-spacing: 1px;
    color: var(--text-secondary); font-weight: 600; border-bottom: 1px solid var(--border); }
  .mr-rank { font-weight: 700; color: var(--text-dim); }
  .mr-name a { color: var(--text-primary); text-decoration: none; font-weight: 600; }
  .mr-name a:hover { color: var(--accent); }
  .mr-genre { font-size: 0.78em; color: var(--text-dim); }
  .mr-score { font-weight: 800; }
  .mr-score.high { color: #2ecc71; }
  .mr-score.mid { color: var(--accent); }
  .mr-score.low { color: var(--text-secondary); }

  .tab-bar { display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
  .tab-btn { background: none; border: none; color: var(--text-secondary); font-size: 0.9em; padding: 8px 16px;
    cursor: pointer; border-radius: 8px 8px 0 0; transition: all 0.2s; font-weight: 600; }
  .tab-btn:hover { color: var(--text-primary); background: rgba(255,255,255,0.03); }
  .tab-btn.active { color: var(--accent); border-bottom: 2px solid var(--accent); }
  .tab-panel { display: none; }
  .tab-panel.active { display: block; }

  .empty-tier { text-align: center; padding: 32px; color: var(--text-dim); font-size: 0.9em; }

  @media (max-width: 768px) {
    .mom-hero h1 { font-size: 1.6em; }
    .tier-grid { grid-template-columns: 1fr; }
    .mom-row { grid-template-columns: 40px 1fr 70px 70px; font-size: 0.9em; }
    .mom-row .mr-comps { display: none; }
    .mom-summary { gap: 8px; }
    .mom-stat { padding: 10px 14px; min-width: 70px; }
    .mom-stat .val { font-size: 1.4em; }
  }
</style>

<section class="mom-hero">
  <h1>📈 <span class="accent">Momentum</span> Movers</h1>
  <p class="hero-sub">Which games are heating up, cooling down, and dominating the ecosystem right now.</p>
  <p class="hero-date">Updated <strong><?= date('j M Y', strtotime($date)) ?></strong> · <?= $total ?> games tracked</p>
</section>

<section class="content-wrapper">

  <!-- Summary Stats -->
  <div class="mom-summary">
    <div class="mom-stat hot">
      <div class="val"><?= $summary['hot'] ?? 0 ?></div>
      <div class="lbl">🔥 Hot</div>
    </div>
    <div class="mom-stat active">
      <div class="val"><?= $summary['active'] ?? 0 ?></div>
      <div class="lbl">⚡ Active</div>
    </div>
    <div class="mom-stat warm">
      <div class="val"><?= $summary['warming'] ?? 0 ?></div>
      <div class="lbl">🌡️ Warming</div>
    </div>
    <?php if (($summary['rising'] ?? 0) > 0): ?>
    <div class="mom-stat rising">
      <div class="val"><?= $summary['rising'] ?></div>
      <div class="lbl">📈 Rising</div>
    </div>
    <?php endif; ?>
    <?php if (($summary['falling'] ?? 0) > 0): ?>
    <div class="mom-stat falling">
      <div class="val"><?= $summary['falling'] ?></div>
      <div class="lbl">📉 Falling</div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Tab Navigation -->
  <div class="tab-bar">
    <button class="tab-btn active" onclick="showTab('hot')">🔥 Hot Games</button>
    <button class="tab-btn" onclick="showTab('active')">⚡ Active</button>
    <button class="tab-btn" onclick="showTab('warming')">🌡️ Warming Up</button>
    <?php if (!empty($rising)): ?>
    <button class="tab-btn" onclick="showTab('rising')">📈 Rising</button>
    <?php endif; ?>
  </div>

  <!-- HOT TIER (30+) -->
  <div class="tab-panel active" id="tab-hot">
    <div class="mom-section">
      <h2>🔥 Hot Games <span class="badge">Momentum 30+</span></h2>
      <?php if (empty($hot)): ?>
        <div class="empty-tier">No games currently in the hot tier.</div>
      <?php else: ?>
        <div class="tier-grid">
          <?php foreach ($hot as $g): ?>
            <div class="tier-card hot">
              <div class="tc-header">
                <div>
                  <div class="tc-rank">#<?= $g['rank'] ?></div>
                  <div class="tc-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></div>
                </div>
                <div class="tc-score high"><?= number_format($g['momentum'], 1) ?></div>
              </div>
              <?php if ($g['genre']): ?>
                <div class="tc-genre"><a href="/genre/<?= e(strtolower(str_replace(' ', '-', $g['genre']))) ?>" style="color:var(--accent);text-decoration:none;"><?= e($g['genre']) ?></a></div>
              <?php endif; ?>
              <div class="tc-components">
                <span class="tc-comp">📰 <span class="cv"><?= number_format($g['components']['news'], 0) ?></span></span>
                <span class="tc-comp">🔧 <span class="cv"><?= number_format($g['components']['patches'], 0) ?></span></span>
                <span class="tc-comp">📡 <span class="cv"><?= number_format($g['components']['signals'], 0) ?></span></span>
                <span class="tc-comp">🐛 <span class="cv"><?= number_format($g['components']['issues'], 0) ?></span></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ACTIVE TIER (10-30) -->
  <div class="tab-panel" id="tab-active">
    <div class="mom-section">
      <h2>⚡ Active Games <span class="badge">Momentum 10–30</span></h2>
      <?php if (empty($active)): ?>
        <div class="empty-tier">No games in the active tier right now.</div>
      <?php else: ?>
        <div class="tier-grid">
          <?php foreach ($active as $g): ?>
            <div class="tier-card active">
              <div class="tc-header">
                <div>
                  <div class="tc-rank">#<?= $g['rank'] ?></div>
                  <div class="tc-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></div>
                </div>
                <div class="tc-score mid"><?= number_format($g['momentum'], 1) ?></div>
              </div>
              <?php if ($g['genre']): ?>
                <div class="tc-genre"><a href="/genre/<?= e(strtolower(str_replace(' ', '-', $g['genre']))) ?>" style="color:var(--accent);text-decoration:none;"><?= e($g['genre']) ?></a></div>
              <?php endif; ?>
              <div class="tc-components">
                <span class="tc-comp">📰 <span class="cv"><?= number_format($g['components']['news'], 0) ?></span></span>
                <span class="tc-comp">🔧 <span class="cv"><?= number_format($g['components']['patches'], 0) ?></span></span>
                <span class="tc-comp">📡 <span class="cv"><?= number_format($g['components']['signals'], 0) ?></span></span>
                <span class="tc-comp">🐛 <span class="cv"><?= number_format($g['components']['issues'], 0) ?></span></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- WARMING TIER (0-10) -->
  <div class="tab-panel" id="tab-warming">
    <div class="mom-section">
      <h2>🌡️ Warming Up <span class="badge">Momentum 1–10</span></h2>
      <?php if (empty($warming)): ?>
        <div class="empty-tier">No games warming up right now.</div>
      <?php else: ?>
        <div class="mom-table">
          <div class="mom-row header">
            <div>Rank</div>
            <div>Game</div>
            <div>Score</div>
            <div>Genre</div>
            <div class="mr-comps">Breakdown</div>
          </div>
          <?php foreach ($warming as $g): ?>
            <div class="mom-row">
              <div class="mr-rank">#<?= $g['rank'] ?></div>
              <div class="mr-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></div>
              <div class="mr-score <?= $g['momentum'] > 5 ? 'mid' : 'low' ?>"><?= number_format($g['momentum'], 1) ?></div>
              <div class="mr-genre"><?php if ($g['genre']): ?><a href="/genre/<?= e(strtolower(str_replace(' ', '-', $g['genre']))) ?>" style="color:var(--text-dim);text-decoration:none;"><?= e($g['genre']) ?></a><?php else: ?>—<?php endif; ?></div>
              <div class="mr-comps" style="font-size:0.75em;color:var(--text-dim);">
                📰<?= number_format($g['components']['news'], 0) ?>
                🔧<?= number_format($g['components']['patches'], 0) ?>
                📡<?= number_format($g['components']['signals'], 0) ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RISING (if any) -->
  <?php if (!empty($rising)): ?>
  <div class="tab-panel" id="tab-rising">
    <div class="mom-section">
      <h2>📈 Rising Fast <span class="badge">Biggest momentum gains</span></h2>
      <div class="tier-grid">
        <?php foreach ($rising as $g): ?>
          <div class="tier-card" style="border-left: 3px solid #2ecc71;">
            <div class="tc-header">
              <div>
                <div class="tc-rank">#<?= $g['rank'] ?></div>
                <div class="tc-name"><a href="/game/<?= e($g['slug']) ?>"><?= e($g['game']) ?></a></div>
              </div>
              <div>
                <div class="tc-score" style="color:#2ecc71;"><?= number_format($g['momentum'], 1) ?></div>
                <div style="font-size:0.75em;color:#2ecc71;">+<?= number_format($g['delta'], 1) ?></div>
              </div>
            </div>
            <?php if ($g['genre']): ?>
              <div class="tc-genre"><a href="/genre/<?= e(strtolower(str_replace(' ', '-', $g['genre']))) ?>" style="color:var(--accent);text-decoration:none;"><?= e($g['genre']) ?></a></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Methodology -->
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:24px 28px;margin-top:32px;">
    <h3 style="margin-bottom:12px;font-size:1em;">How Momentum Works</h3>
    <p style="font-size:0.88em;color:var(--text-secondary);line-height:1.6;">
      Momentum scores combine four real-time signals: <strong>News Volume</strong> (media coverage and article mentions),
      <strong>Patch Activity</strong> (updates and content drops), <strong>Signal Intensity</strong> (detected patterns
      and anomalies), and <strong>Issue Frequency</strong> (bug reports and player complaints).
      Scores update daily. A score above 30 indicates a game in the hot zone — significant activity across multiple signals.
    </p>
  </div>

</section>

<script>
function showTab(id) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-' + id).classList.add('active');
  event.target.classList.add('active');
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
