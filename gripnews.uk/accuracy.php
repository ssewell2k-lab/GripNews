<?php
require_once __DIR__ . '/includes/functions.php';

$page_title = "Accuracy Scorecards — GripNews";
$page_desc  = "Full transparency on GripNews prediction accuracy. Trust weights, forecast outcomes, and methodology.";
$nav_active = 'accuracy';

require_once __DIR__ . '/includes/header.php';

$api = "https://gripai.uk/v2";

// Fetch data
$trust = json_decode(@file_get_contents("$api/trust/scores"), true);
$accuracy = json_decode(@file_get_contents("$api/forecast/accuracy"), true);
$outcomes = json_decode(@file_get_contents("$api/outcomes/recent"), true);
$intel = json_decode(@file_get_contents("$api/intelligence/summary"), true);
?>

<style>
.acc-hero { text-align: center; padding: 48px 20px 32px; }
.acc-hero h1 { font-size: 2.2em; font-weight: 900; margin-bottom: 8px; }
.acc-hero h1 span { color: #6c63ff; }
.acc-hero p { color: #8b949e; max-width: 600px; margin: 0 auto 24px; }

.acc-stats { display: flex; justify-content: center; gap: 32px; flex-wrap: wrap; margin-bottom: 40px; }
.acc-stat { background: #161b22; border: 1px solid #30363d; border-radius: 12px; padding: 20px 28px; text-align: center; min-width: 140px; }
.acc-stat .num { font-size: 2em; font-weight: 900; }
.acc-stat .num.green { color: #2ecc71; }
.acc-stat .num.purple { color: #6c63ff; }
.acc-stat .num.yellow { color: #f1c40f; }
.acc-stat .label { font-size: .72em; color: #8b949e; text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

.acc-section { margin-bottom: 40px; }
.acc-section h2 { font-size: 1.3em; font-weight: 800; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }

.trust-table { width: 100%; border-collapse: collapse; background: #161b22; border-radius: 12px; overflow: hidden; border: 1px solid #30363d; }
.trust-table th { text-align: left; padding: 12px 16px; font-size: .72em; text-transform: uppercase; letter-spacing: 1px; color: #8b949e; border-bottom: 1px solid #30363d; background: #0d1117; }
.trust-table td { padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: .9em; }
.trust-table tr:last-child td { border-bottom: none; }
.trust-table tr:hover { background: rgba(108,99,255,0.04); }

.trust-bar { display: flex; align-items: center; gap: 8px; }
.trust-bar-fill { height: 8px; border-radius: 4px; min-width: 4px; transition: width 0.3s; }
.trust-bar-fill.high { background: #2ecc71; }
.trust-bar-fill.mid { background: #6c63ff; }
.trust-bar-fill.low { background: #f1c40f; }
.trust-bar-fill.noise { background: #e74c3c; }

.outcome-card { background: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 16px; margin-bottom: 12px; display: flex; align-items: center; gap: 16px; }
.outcome-icon { font-size: 1.6em; }
.outcome-info { flex: 1; }
.outcome-info h3 { font-size: .95em; margin-bottom: 2px; }
.outcome-info .meta { color: #8b949e; font-size: .78em; }
.outcome-result { font-weight: 700; padding: 4px 10px; border-radius: 6px; font-size: .8em; }
.outcome-result.correct { background: rgba(46,204,113,0.15); color: #2ecc71; }
.outcome-result.incorrect { background: rgba(231,76,60,0.15); color: #e74c3c; }
.outcome-result.pending { background: rgba(108,99,255,0.15); color: #6c63ff; }
.outcome-result.expired { background: rgba(139,148,158,0.15); color: #8b949e; }

.method-card { background: #161b22; border: 1px solid #30363d; border-radius: 12px; padding: 24px; margin-bottom: 16px; }
.method-card h3 { font-size: 1em; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
.method-card p { color: #8b949e; font-size: .88em; line-height: 1.6; }

@media (max-width: 768px) {
  .acc-stats { gap: 12px; }
  .acc-stat { min-width: 100px; padding: 14px 16px; }
  .acc-stat .num { font-size: 1.5em; }
  .outcome-card { flex-direction: column; align-items: flex-start; gap: 8px; }
}
</style>

<div class="acc-hero">
  <h1>🎯 Accuracy <span>Scorecards</span></h1>
  <p>Full transparency on how well GripNews predictions perform. Every signal, forecast, and trust weight is tracked and scored.</p>
</div>

<?php
// Calculate summary stats
$total_forecasts = 0;
$correct = 0;
$pending = 0;
$trust_weights = $trust['weights'] ?? [];
$avg_trust = 0;

if (!empty($accuracy['forecasts'])) {
    foreach ($accuracy['forecasts'] as $f) {
        $total_forecasts++;
        if (($f['outcome'] ?? '') === 'correct') $correct++;
        if (($f['outcome'] ?? '') === 'pending') $pending++;
    }
}

if (!empty($trust_weights)) {
    $sum = 0;
    foreach ($trust_weights as $tw) {
        $sum += floatval($tw['weight'] ?? $tw['trust_weight'] ?? 0);
    }
    $avg_trust = $sum / count($trust_weights);
}

$acc_pct = $total_forecasts > 0 ? round(($correct / max(1, $total_forecasts - $pending)) * 100) : 0;
?>

<div class="acc-stats">
  <div class="acc-stat">
    <div class="num green"><?= $acc_pct ?>%</div>
    <div class="label">Forecast Accuracy</div>
  </div>
  <div class="acc-stat">
    <div class="num purple"><?= $total_forecasts ?></div>
    <div class="label">Total Forecasts</div>
  </div>
  <div class="acc-stat">
    <div class="num yellow"><?= count($trust_weights) ?></div>
    <div class="label">Trust Sources</div>
  </div>
  <div class="acc-stat">
    <div class="num purple"><?= round($avg_trust * 100) ?>%</div>
    <div class="label">Avg Trust Score</div>
  </div>
</div>

<!-- Trust Weights -->
<div class="acc-section">
  <h2>🔒 Source Trust Weights</h2>
  <p style="color:#8b949e;font-size:.88em;margin-bottom:16px">Each source type earns trust over time based on how often its signals lead to accurate outcomes. These weights directly influence confidence scoring.</p>

  <table class="trust-table">
    <tr><th>Source Type</th><th>Trust Weight</th><th>Visual</th><th>Reliability</th></tr>
    <?php
    // Sort by weight descending
    usort($trust_weights, function($a, $b) {
        return (floatval($b['weight'] ?? $b['trust_weight'] ?? 0)) <=> (floatval($a['weight'] ?? $a['trust_weight'] ?? 0));
    });

    foreach ($trust_weights as $tw):
        $weight = floatval($tw['weight'] ?? $tw['trust_weight'] ?? 0);
        $source = $tw['source_type'] ?? $tw['source'] ?? 'unknown';
        $pct = round($weight * 100);
        $cls = $weight >= 0.8 ? 'high' : ($weight >= 0.5 ? 'mid' : ($weight >= 0.3 ? 'low' : 'noise'));
        $label = $weight >= 0.8 ? '🎯 Highly Reliable' : ($weight >= 0.5 ? '✅ Reliable' : ($weight >= 0.3 ? '📡 Emerging' : '⚠️ Unverified'));
    ?>
    <tr>
      <td style="font-weight:600"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $source))) ?></td>
      <td style="font-family:monospace;font-weight:700"><?= number_format($weight, 2) ?></td>
      <td>
        <div class="trust-bar">
          <div class="trust-bar-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div>
          <span style="font-size:.78em;color:#8b949e"><?= $pct ?>%</span>
        </div>
      </td>
      <td style="font-size:.82em"><?= $label ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<!-- Forecast Outcomes -->
<div class="acc-section">
  <h2>📋 Forecast Outcomes</h2>
  <p style="color:#8b949e;font-size:.88em;margin-bottom:16px">Every prediction GripNews makes is tracked. When the prediction window closes, we check the outcome and update our accuracy score.</p>

  <?php
  $forecast_list = $accuracy['forecasts'] ?? [];
  if (empty($forecast_list)):
  ?>
    <div style="background:#161b22;border:1px solid #30363d;border-radius:10px;padding:24px;text-align:center;color:#8b949e">
      <div style="font-size:2em;margin-bottom:8px">📊</div>
      <p>Forecasts are being tracked. Results will appear here as prediction windows close.</p>
      <p style="font-size:.82em;margin-top:8px">The system generates forecasts based on momentum patterns and signal intelligence.</p>
    </div>
  <?php else: ?>
    <?php foreach ($forecast_list as $f):
      $outcome = $f['outcome'] ?? 'pending';
      $icon = $outcome === 'correct' ? '✅' : ($outcome === 'incorrect' ? '❌' : ($outcome === 'expired' ? '⏰' : '🔮'));
    ?>
    <div class="outcome-card">
      <span class="outcome-icon"><?= $icon ?></span>
      <div class="outcome-info">
        <h3><?= htmlspecialchars($f['game_name'] ?? $f['game_slug'] ?? 'Unknown') ?> — <?= htmlspecialchars($f['summary'] ?? $f['forecast_type'] ?? '') ?></h3>
        <div class="meta">
          <?= htmlspecialchars($f['direction'] ?? '') ?> forecast · <?= htmlspecialchars($f['confidence'] ?? '') ?> confidence
          <?php if (!empty($f['created_at'])): ?> · Created <?= date('M j, Y', strtotime($f['created_at'])) ?><?php endif; ?>
        </div>
      </div>
      <span class="outcome-result <?= $outcome ?>"><?= ucfirst($outcome) ?></span>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Recent Outcomes -->
<?php if (!empty($outcomes['outcomes'])): ?>
<div class="acc-section">
  <h2>🔍 Recent Outcome Checks</h2>
  <?php foreach (array_slice($outcomes['outcomes'], 0, 10) as $o): ?>
  <div class="outcome-card">
    <span class="outcome-icon"><?= ($o['result'] ?? '') === 'confirmed' ? '✅' : '📋' ?></span>
    <div class="outcome-info">
      <h3><?= htmlspecialchars($o['title'] ?? $o['event_id'] ?? '') ?></h3>
      <div class="meta"><?= htmlspecialchars($o['notes'] ?? '') ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Methodology -->
<div class="acc-section">
  <h2>📐 How We Score Accuracy</h2>

  <div class="method-card">
    <h3>📡 Signal Trust Weights</h3>
    <p>Each data source (Steam news, patch notes, social media, leaks, etc.) starts with a baseline trust weight. Over time, the system adjusts these weights based on how often signals from each source lead to confirmed outcomes. High-trust sources influence momentum scores and predictions more heavily.</p>
  </div>

  <div class="method-card">
    <h3>🔮 Forecast Tracking</h3>
    <p>When the system generates a forecast (e.g., "Fortnite momentum will rise in the next 7 days"), it records the current momentum score and prediction details. When the window closes, the outcome checker compares the predicted direction against what actually happened. The result (correct/incorrect/expired) feeds back into the accuracy score.</p>
  </div>

  <div class="method-card">
    <h3>🔄 Self-Improving Loop</h3>
    <p>Every day at 07:30 UTC, the outcome checker runs: it snapshots momentum history, checks pending forecasts, scores signal accuracy, updates trust weights, and advances trend lifecycles. This creates a feedback loop where the system gets smarter over time — unreliable sources lose influence, and accurate patterns get amplified.</p>
  </div>

  <div class="method-card">
    <h3>📊 Transparency</h3>
    <p>Everything on this page is pulled live from the Intelligence API. No cherry-picking, no hiding bad predictions. When we get it wrong, you'll see it here. That's the point — transparency builds trust.</p>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
