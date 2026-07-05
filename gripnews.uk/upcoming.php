<?php
/**
 * GripNews.uk — Release Intelligence (Phase 12B)
 * Upcoming game releases calendar with hype/risk scores.
 */
require_once __DIR__ . '/includes/functions.php';

$page_title = 'Upcoming Releases — GripNews Intelligence';
$page_desc = 'Track upcoming game releases, expansions, seasons, and updates. Hype scores, risk analysis, and release calendars powered by GripAI.';
$page_canonical = SITE_URL . '/upcoming';
$nav_active = 'upcoming';

$api = 'https://gripai.uk/v2';
$stats = @json_decode(@file_get_contents("$api/releases/stats"), true);
$upcoming = @json_decode(@file_get_contents("$api/releases/upcoming?sort=date&limit=100"), true);
$calendar = @json_decode(@file_get_contents("$api/releases/calendar"), true);

$releases = $upcoming['releases'] ?? [];
$counts = $stats['counts'] ?? [];
$types = $stats['types'] ?? [];
$scores = $stats['scores'] ?? [];
$most_hyped = $stats['most_hyped'] ?? [];
$highest_risk = $stats['highest_risk'] ?? [];
$next_up = $stats['next_up'] ?? [];
$months = $calendar['months'] ?? [];
$date = $stats['date'] ?? date('Y-m-d');

require_once __DIR__ . '/includes/header.php';
?>

<style>
  /* ── Release Intelligence Styles ── */
  .rel-hero { text-align: center; padding: 48px 0 32px; }
  .rel-hero h1 { font-size: 2.4em; margin-bottom: 8px; }
  .rel-hero h1 .accent { color: var(--accent); }
  .rel-hero .hero-sub { color: var(--text-muted); font-size: 1em; margin-bottom: 16px; max-width: 600px; margin-left: auto; margin-right: auto; }
  .rel-hero .hero-date { font-size: 0.85em; color: var(--text-dim); }
  .rel-hero .hero-date strong { color: var(--accent); }

  /* Summary Stats */
  .rel-summary { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 36px; }
  .rel-stat { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 16px 24px; text-align: center; min-width: 100px; }
  .rel-stat .val { font-size: 2em; font-weight: 800; color: var(--accent); }
  .rel-stat .lbl { font-size: 0.7em; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-top: 4px; }
  .rel-stat.hype .val { color: #e74c3c; }
  .rel-stat.risk .val { color: #f59e0b; }
  .rel-stat.types .val { color: #22c55e; }
  .rel-stat.seasons .val { color: #a855f7; }

  /* Filter Tabs */
  .rel-filters { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 32px; }
  .rel-filter { padding: 8px 18px; border-radius: 20px; border: 1px solid var(--border); background: var(--bg-card);
    color: var(--text-muted); cursor: pointer; font-size: 0.85em; font-weight: 600; transition: all 0.2s; }
  .rel-filter:hover { border-color: var(--accent); color: var(--text); }
  .rel-filter.active { background: var(--accent); color: #fff; border-color: var(--accent); }

  /* Next Up Banner */
  .next-up { background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(168,85,247,0.06));
    border: 1px solid rgba(59,130,246,0.2); border-radius: 16px; padding: 24px 28px;
    margin-bottom: 36px; }
  .next-up h2 { font-size: 1.1em; color: var(--accent); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
  .next-up-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
  .next-up-item { background: rgba(255,255,255,0.03); border-radius: 10px; padding: 12px 14px; }
  .next-up-item .nui-name { font-weight: 700; font-size: 0.95em; margin-bottom: 4px; }
  .next-up-item .nui-name a { color: var(--text); text-decoration: none; }
  .next-up-item .nui-name a:hover { color: var(--accent); }
  .next-up-item .nui-meta { font-size: 0.75em; color: var(--text-dim); }
  .next-up-item .nui-hype { font-size: 0.75em; font-weight: 700; }
  .nui-hype.fire { color: #e74c3c; }
  .nui-hype.hot { color: #f97316; }
  .nui-hype.warm { color: #f59e0b; }

  /* Most Hyped / Highest Risk sidebars */
  .rel-spotlights { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 36px; }
  @media (max-width: 700px) { .rel-spotlights { grid-template-columns: 1fr; } }
  .spotlight-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px; padding: 20px 22px; }
  .spotlight-card h3 { font-size: 1em; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
  .spotlight-card h3 .ico { font-size: 1.2em; }
  .spotlight-list { list-style: none; }
  .spotlight-list li { display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.04); }
  .spotlight-list li:last-child { border-bottom: none; }
  .sl-name { font-weight: 600; font-size: 0.9em; }
  .sl-name a { color: var(--text); text-decoration: none; }
  .sl-name a:hover { color: var(--accent); }
  .sl-score { font-weight: 800; font-size: 1.1em; }
  .sl-score.hype { color: #e74c3c; }
  .sl-score.risk { color: #f59e0b; }
  .sl-type { font-size: 0.7em; color: var(--text-dim); margin-left: 6px; }

  /* Calendar Section */
  .rel-section { margin-bottom: 40px; }
  .rel-section h2 { font-size: 1.3em; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
  .rel-section h2 .badge { font-size: 0.55em; background: rgba(255,255,255,0.05); padding: 4px 10px;
    border-radius: 20px; color: var(--text-muted); font-weight: 500; }

  .month-block { margin-bottom: 28px; }
  .month-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px;
    padding-bottom: 8px; border-bottom: 1px solid rgba(59,130,246,0.12); }
  .month-name { font-size: 1.2em; font-weight: 800; color: var(--accent); }
  .month-count { font-size: 0.78em; background: var(--accent-dim); color: var(--accent);
    padding: 3px 10px; border-radius: 12px; font-weight: 600; }

  /* Release Cards */
  .rel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px; }
  .rel-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
    padding: 18px 20px; transition: all 0.2s; position: relative; overflow: hidden; }
  .rel-card:hover { border-color: var(--accent); transform: translateY(-2px); }
  .rel-card.new_release { border-left: 3px solid var(--accent); }
  .rel-card.expansion { border-left: 3px solid var(--purple); }
  .rel-card.season { border-left: 3px solid var(--green); }
  .rel-card.major_update { border-left: 3px solid var(--orange); }
  .rel-card.dlc { border-left: 3px solid var(--cyan); }
  .rel-card.remaster { border-left: 3px solid var(--pink); }

  .rc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px; }
  .rc-name { font-size: 1.05em; font-weight: 700; flex: 1; }
  .rc-name a { color: var(--text); text-decoration: none; }
  .rc-name a:hover { color: var(--accent); }
  .rc-hype { font-size: 1.4em; font-weight: 900; min-width: 40px; text-align: right; }
  .rc-hype.fire { color: #e74c3c; }
  .rc-hype.hot { color: #f97316; }
  .rc-hype.warm { color: #f59e0b; }
  .rc-hype.cool { color: var(--text-muted); }

  .rc-studio { font-size: 0.8em; color: var(--accent); margin-bottom: 6px; }
  .rc-studio a { color: var(--accent); }
  .rc-desc { font-size: 0.82em; color: var(--text-muted); margin-bottom: 10px; line-height: 1.5;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

  .rc-meta { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
  .rc-chip { font-size: 0.7em; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
  .rc-chip.date { background: var(--accent-dim); color: var(--accent); }
  .rc-chip.type { background: rgba(255,255,255,0.05); color: var(--text-muted); text-transform: capitalize; }
  .rc-chip.platform { background: rgba(255,255,255,0.03); color: var(--text-dim); font-weight: 500; }
  .rc-chip.risk-low { background: var(--green-dim); color: var(--green); }
  .rc-chip.risk-med { background: var(--amber-dim); color: var(--amber); }
  .rc-chip.risk-high { background: var(--red-dim); color: var(--red); }

  .rc-bar { display: flex; gap: 2px; margin-top: 10px; }
  .rc-bar-seg { height: 3px; border-radius: 2px; flex: 1; }
  .rc-bar-seg.hype { background: linear-gradient(90deg, #e74c3c, #f97316); }
  .rc-bar-seg.risk { background: linear-gradient(90deg, #22c55e, #f59e0b); }

  /* Methodology */
  .rel-method { background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
    padding: 24px 28px; margin-bottom: 36px; }
  .rel-method h3 { font-size: 1em; margin-bottom: 12px; color: var(--accent); }
  .rel-method p { font-size: 0.85em; color: var(--text-muted); line-height: 1.7; margin-bottom: 8px; }
  .rel-method .def-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px; }
  @media (max-width: 600px) { .rel-method .def-grid { grid-template-columns: 1fr; } }
  .rel-method .def { padding: 10px 14px; background: rgba(255,255,255,0.02); border-radius: 8px; }
  .rel-method .def dt { font-weight: 700; font-size: 0.85em; color: var(--text); margin-bottom: 4px; }
  .rel-method .def dd { font-size: 0.78em; color: var(--text-dim); }

  @media (max-width: 600px) {
    .rel-hero h1 { font-size: 1.6em; }
    .rel-grid { grid-template-columns: 1fr; }
    .next-up-list { grid-template-columns: 1fr 1fr; }
  }
</style>

<div class="container" style="max-width:1100px;margin:0 auto;padding:0 16px;">

  <!-- Hero -->
  <div class="rel-hero">
    <h1>🚀 <span class="accent">Upcoming</span> Releases</h1>
    <p class="hero-sub">Release intelligence across the gaming ecosystem — new games, expansions, seasons, and major updates tracked by GripAI.</p>
    <div class="hero-date">Updated <strong><?= e($date) ?></strong> · <?= intval($counts['total'] ?? 0) ?> releases tracked</div>
  </div>

  <!-- Summary Stats -->
  <div class="rel-summary">
    <div class="rel-stat">
      <div class="val"><?= intval($counts['upcoming'] ?? 0) ?></div>
      <div class="lbl">Upcoming</div>
    </div>
    <div class="rel-stat">
      <div class="val"><?= intval($counts['released'] ?? 0) ?></div>
      <div class="lbl">Released</div>
    </div>
    <div class="rel-stat hype">
      <div class="val"><?= number_format($scores['avg_hype'] ?? 0, 1) ?></div>
      <div class="lbl">Avg Hype</div>
    </div>
    <div class="rel-stat risk">
      <div class="val"><?= number_format($scores['avg_risk'] ?? 0, 1) ?></div>
      <div class="lbl">Avg Risk</div>
    </div>
    <div class="rel-stat types">
      <div class="val"><?= intval($types['new_releases'] ?? 0) ?></div>
      <div class="lbl">New Games</div>
    </div>
    <div class="rel-stat seasons">
      <div class="val"><?= intval($types['seasons'] ?? 0) + intval($types['major_updates'] ?? 0) ?></div>
      <div class="lbl">Updates & Seasons</div>
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="rel-filters">
    <span class="rel-filter active" data-filter="all">All</span>
    <span class="rel-filter" data-filter="new_release">🎮 New Games</span>
    <span class="rel-filter" data-filter="expansion">⚔️ Expansions</span>
    <span class="rel-filter" data-filter="season">🔄 Seasons</span>
    <span class="rel-filter" data-filter="major_update">📦 Updates</span>
  </div>

  <!-- Next Up Banner -->
  <?php if (!empty($next_up)): ?>
  <div class="next-up">
    <h2>⏰ Coming Next</h2>
    <div class="next-up-list">
      <?php foreach ($next_up as $nu): ?>
      <div class="next-up-item">
        <div class="nui-name"><a href="/game/<?= e($nu['game_slug']) ?>"><?= e($nu['game_name']) ?></a></div>
        <div class="nui-meta"><?= $nu['release_date'] ? date('M j', strtotime($nu['release_date'])) : 'TBA' ?> · <?= ucfirst(str_replace('_', ' ', $nu['release_type'])) ?></div>
        <div class="nui-hype <?= $nu['hype_score'] >= 85 ? 'fire' : ($nu['hype_score'] >= 70 ? 'hot' : 'warm') ?>">
          🔥 <?= number_format($nu['hype_score'], 0) ?> hype
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Most Hyped / Highest Risk -->
  <div class="rel-spotlights">
    <div class="spotlight-card">
      <h3><span class="ico">🔥</span> Most Hyped</h3>
      <ul class="spotlight-list">
        <?php foreach ($most_hyped as $mh): ?>
        <li>
          <div>
            <span class="sl-name"><a href="/game/<?= e($mh['game_slug']) ?>"><?= e($mh['game_name']) ?></a></span>
            <span class="sl-type"><?= ucfirst(str_replace('_', ' ', $mh['release_type'])) ?></span>
          </div>
          <span class="sl-score hype"><?= number_format($mh['hype_score'], 0) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="spotlight-card">
      <h3><span class="ico">⚠️</span> Highest Risk</h3>
      <ul class="spotlight-list">
        <?php foreach ($highest_risk as $hr): ?>
        <li>
          <div>
            <span class="sl-name"><a href="/game/<?= e($hr['game_slug']) ?>"><?= e($hr['game_name']) ?></a></span>
            <span class="sl-type"><?= ucfirst(str_replace('_', ' ', $hr['release_type'])) ?></span>
          </div>
          <span class="sl-score risk"><?= number_format($hr['risk_score'], 0) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Release Calendar by Month -->
  <div class="rel-section">
    <h2>📅 Release Calendar <span class="badge"><?= count($releases) ?> upcoming</span></h2>

    <?php
    // Group releases by month
    $by_month = [];
    foreach ($releases as $r) {
      $m = $r['release_date'] ? date('Y-m', strtotime($r['release_date'])) : 'TBA';
      $by_month[$m][] = $r;
    }
    ksort($by_month);

    foreach ($by_month as $month_key => $month_releases):
      $month_label = $month_key === 'TBA' ? 'TBA' : date('F Y', strtotime($month_key . '-01'));
      usort($month_releases, function($a, $b) {
        return ($b['hype_score'] ?? 0) - ($a['hype_score'] ?? 0);
      });
    ?>
    <div class="month-block" data-month="<?= e($month_key) ?>">
      <div class="month-header">
        <span class="month-name"><?= $month_label ?></span>
        <span class="month-count"><?= count($month_releases) ?> release<?= count($month_releases) !== 1 ? 's' : '' ?></span>
      </div>
      <div class="rel-grid">
        <?php foreach ($month_releases as $r):
          $hype = floatval($r['hype_score'] ?? 0);
          $risk = floatval($r['risk_score'] ?? 0);
          $hype_class = $hype >= 85 ? 'fire' : ($hype >= 70 ? 'hot' : ($hype >= 55 ? 'warm' : 'cool'));
          $risk_class = $risk >= 25 ? 'risk-high' : ($risk >= 15 ? 'risk-med' : 'risk-low');
          $type = $r['release_type'] ?? 'new_release';
          $platforms = is_array($r['platforms']) ? $r['platforms'] : [];
          $date_str = $r['release_date'] ? date('M j', strtotime($r['release_date'])) : 'TBA';
          $precision = $r['release_precision'] ?? 'exact';
          if ($precision === 'month') $date_str = date('M Y', strtotime($r['release_date']));
          elseif ($precision === 'quarter') $date_str = 'Q' . ceil(date('n', strtotime($r['release_date'])) / 3) . ' ' . date('Y', strtotime($r['release_date']));
          elseif ($precision === 'year') $date_str = date('Y', strtotime($r['release_date']));
          elseif ($precision === 'tba') $date_str = 'TBA';
        ?>
        <div class="rel-card <?= e($type) ?>" data-type="<?= e($type) ?>">
          <div class="rc-header">
            <div class="rc-name"><a href="/game/<?= e($r['game_slug']) ?>"><?= e($r['game_name']) ?></a></div>
            <div class="rc-hype <?= $hype_class ?>"><?= number_format($hype, 0) ?></div>
          </div>
          <?php if (!empty($r['studio_name'])): ?>
          <div class="rc-studio"><a href="/studio/<?= e(strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $r['studio_name'] ?? '')))) ?>" style="color:var(--accent);text-decoration:none;"><?= e($r['studio_name']) ?></a><?php if (!empty($r['publisher_name']) && $r['publisher_name'] !== $r['studio_name']): ?> · <a href="/publisher/<?= e(strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $r['publisher_name'])))) ?>" style="color:var(--text-muted);text-decoration:none;"><?= e($r['publisher_name']) ?></a><?php endif; ?></div>
          <?php endif; ?>
          <?php if (!empty($r['description'])): ?>
          <div class="rc-desc"><?= e($r['description']) ?></div>
          <?php endif; ?>
          <div class="rc-meta">
            <span class="rc-chip date"><?= $date_str ?></span>
            <span class="rc-chip type"><?= str_replace('_', ' ', $type) ?></span>
            <span class="rc-chip <?= $risk_class ?>">Risk: <?= number_format($risk, 0) ?></span>
            <?php foreach (array_slice($platforms, 0, 3) as $p): ?>
            <span class="rc-chip platform"><?= e($p) ?></span>
            <?php endforeach; ?>
            <?php if (count($platforms) > 3): ?>
            <span class="rc-chip platform">+<?= count($platforms) - 3 ?></span>
            <?php endif; ?>
          </div>
          <div class="rc-bar">
            <div class="rc-bar-seg hype" style="width:<?= $hype ?>%;"></div>
            <div class="rc-bar-seg risk" style="width:<?= $risk ?>%;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Methodology -->
  <div class="rel-method">
    <h3>📊 How Release Intelligence Works</h3>
    <p>GripAI tracks game release announcements, delays, and updates across the gaming ecosystem. Each release is scored on two axes:</p>
    <div class="def-grid">
      <div class="def">
        <dt>🔥 Hype Score (0–100)</dt>
        <dd>Measures anticipation based on studio track record, franchise history, social buzz, pre-release coverage volume, and community sentiment. Higher = more anticipated.</dd>
      </div>
      <div class="def">
        <dt>⚠️ Risk Score (0–100)</dt>
        <dd>Assesses delivery risk based on development time, studio capacity, scope ambition, delay history, and early signal quality. Higher = more risk.</dd>
      </div>
      <div class="def">
        <dt>📅 Release Precision</dt>
        <dd>Shows how confirmed the date is — "exact" means a specific date is announced, "month" or "quarter" means an approximate window, "year" means sometime that year.</dd>
      </div>
      <div class="def">
        <dt>📦 Release Types</dt>
        <dd>New Release (full game), Expansion (paid DLC/standalone), Season (live-service season), Major Update (free content), DLC (smaller add-on), Remaster/Port.</dd>
      </div>
    </div>
  </div>

</div>

<script>
// Filter functionality
document.querySelectorAll('.rel-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.rel-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('.rel-card').forEach(card => {
      if (filter === 'all' || card.dataset.type === filter) {
        card.style.display = '';
      } else {
        card.style.display = 'none';
      }
    });
    // Update month block visibility
    document.querySelectorAll('.month-block').forEach(block => {
      const visible = block.querySelectorAll('.rel-card:not([style*="display: none"])');
      block.style.display = visible.length > 0 ? '' : 'none';
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
