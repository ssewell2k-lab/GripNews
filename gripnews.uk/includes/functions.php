<?php
/**
 * GripNews.uk — Core Functions
 * Signal processing, data loading, rendering helpers.
 */

define('DATA_DIR', __DIR__ . '/../data');
define('SITE_URL', 'https://gripnews.uk');
define('SITE_NAME', 'GripNews');

// ── Category Config ───────────────────────────────────────────

function get_categories(): array {
    return [
        'patches'  => ['slug' => 'patches',  'label' => 'Patches',  'match' => ['patch', 'update'], 'desc' => 'Game updates, balance changes, hotfixes, and seasonal content.', 'icon' => '🔧'],
        'industry' => ['slug' => 'industry', 'label' => 'Industry', 'match' => ['industry'], 'desc' => 'Acquisitions, layoffs, platform changes, and market shifts.', 'icon' => '📊'],
        'esports'  => ['slug' => 'esports',  'label' => 'Esports',  'match' => ['esports'], 'desc' => 'Tournament results, roster moves, and league changes.', 'icon' => '🏆'],
        'indie'    => ['slug' => 'indie',    'label' => 'Indie',    'match' => ['indie'], 'desc' => 'Breakout indie titles, Steam trending, and surprise hits.', 'icon' => '🎮'],
        'releases' => ['slug' => 'releases', 'label' => 'Releases', 'match' => ['release'], 'desc' => 'New game launches, DLC drops, and expansion releases.', 'icon' => '🚀'],
        'rumours'  => ['slug' => 'rumours',  'label' => 'Rumours',  'match' => ['rumor', 'rumour', 'leak'], 'desc' => 'Unconfirmed leaks, insider reports, and industry rumours.', 'icon' => '🔮'],
    ];
}

// ── Data Loading ──────────────────────────────────────────────

function load_signals(string $date = ''): array {
    if (!$date) $date = date('Y-m-d');
    $file = DATA_DIR . "/{$date}.json";
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    if (!$data || !isset($data['signals'])) return [];
    usort($data['signals'], fn($a, $b) => ($b['score'] ?? 0) - ($a['score'] ?? 0));
    return $data['signals'];
}

function load_signals_by_category(string $category_slug, int $days = 30, int $limit = 50): array {
    $cats = get_categories();
    if (!isset($cats[$category_slug])) return [];
    $match = $cats[$category_slug]['match'];
    
    $results = [];
    $dates = get_available_dates($days);
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $s) {
            $cat = strtolower($s['category'] ?? '');
            if (in_array($cat, $match)) {
                $s['_date'] = $date;
                $results[] = $s;
                if (count($results) >= $limit) break 2;
            }
        }
    }
    return $results;
}

function load_day_meta(string $date = ''): array {
    if (!$date) $date = date('Y-m-d');
    $file = DATA_DIR . "/{$date}.json";
    if (!file_exists($file)) return ['date' => $date, 'generated' => '', 'total' => 0];
    $data = json_decode(file_get_contents($file), true);
    return [
        'date' => $data['date'] ?? $date,
        'generated' => $data['generated_at'] ?? '',
        'total' => count($data['signals'] ?? []),
        'top_category' => $data['top_category'] ?? '',
    ];
}

function get_signal_by_slug(string $date, string $slug): ?array {
    $signals = load_signals($date);
    foreach ($signals as $s) {
        if (slugify($s['title']) === $slug) return $s;
    }
    return null;
}

function get_available_dates(int $limit = 30): array {
    $dates = [];
    $files = glob(DATA_DIR . '/20*.json');
    if (!$files) return [];
    rsort($files);
    foreach (array_slice($files, 0, $limit) as $f) {
        $dates[] = basename($f, '.json');
    }
    return $dates;
}

// ── Helpers ───────────────────────────────────────────────────

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function format_date(string $date, string $format = 'j M Y'): string {
    return date($format, strtotime($date));
}

function category_class(string $cat): string {
    $map = [
        'patch' => 'cat-patch',
        'update' => 'cat-patch',
        'release' => 'cat-release',
        'announcement' => 'cat-announcement',
        'esports' => 'cat-esports',
        'industry' => 'cat-industry',
        'indie' => 'cat-indie',
        'rumor' => 'cat-rumor',
        'rumour' => 'cat-rumor',
        'leak' => 'cat-rumor',
    ];
    return $map[strtolower($cat)] ?? 'cat-announcement';
}

function confidence_class(string $conf): string {
    $map = [
        'confirmed' => 'conf-confirmed',
        'rumor' => 'conf-rumor',
        'rumour' => 'conf-rumor',
        'leak' => 'conf-leak',
    ];
    return $map[strtolower($conf)] ?? '';
}

function score_class(int $score): string {
    if ($score >= 7) return 'high';
    if ($score >= 4) return 'mid';
    return 'low';
}

function e($str): string {
    if (is_array($str)) {
        $flat = [];
        array_walk_recursive($str, function($v) use (&$flat) { $flat[] = (string)$v; });
        $str = implode(', ', $flat);
    }
    return htmlspecialchars((string)($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// ── Rendering ─────────────────────────────────────────────────

function render_signal_card(array $s, int $rank, string $date, bool $show_date = false): string {
    $slug = slugify($s['title']);
    $cat = $s['category'] ?? 'Update';
    if (is_array($cat)) $cat = $cat[0] ?? 'Update';
    $catClass = category_class($cat);
    $conf = $s['confidence'] ?? 'confirmed';
    $confClass = confidence_class($conf);
    $imp = $s['impact'] ?? [];
    $sc = max(intval($imp['player'] ?? 0), intval($imp['dev'] ?? 0), intval($imp['esports'] ?? 0), intval($imp['industry'] ?? 0));
    $scClass = score_class($sc);
    $fillH = min(($sc / 10) * 100, 100);
    $title = e($s['title']);
    $summary = e($s['summary'] ?? '');
    $why = e($s['why_it_matters'] ?? '');
    $catE = e($cat);
    $confE = e($conf);
    
    $why_html = '';
    if ($why) {
        $why_html = '<div class="signal-why">→ ' . $why . '</div>';
    }
    
    $date_html = $show_date ? '<span class="signal-time">' . format_date($date, 'j M') . '</span>' : '';
    
    $tags_html = '';
    if (!empty($s['tags'])) {
        $tags_html = '<div class="signal-tags">';
        foreach (array_slice($s['tags'], 0, 4) as $tag) {
            $tags_html .= '<span class="signal-tag">' . e($tag) . '</span>';
        }
        $tags_html .= '</div>';
    }
    
    return <<<HTML
    <a href="/story/{$date}/{$slug}" class="signal-card">
      <div class="signal-rank">{$rank}</div>
      <div class="signal-body">
        <div class="signal-meta">
          <span class="signal-category {$catClass}">{$catE}</span>
          <span class="signal-confidence {$confClass}">&bull; {$confE}</span>
          {$date_html}
        </div>
        <div class="signal-title">{$title}</div>
        <div class="signal-summary">{$summary}</div>
        {$why_html}
        {$tags_html}
      </div>
      <div class="signal-score">
        <div class="score-value score-{$scClass}">{$sc}</div>
        <div class="score-label">impact</div>
        <div class="score-bar"><div class="score-fill score-fill-{$scClass}" style="height:{$fillH}%"></div></div>
      </div>
    </a>
HTML;
}

function render_gripai_cta(array $signal): string {
    $game = e($signal['tags'][0] ?? $signal['title'] ?? 'this game');
    $gameSlug = slugify($game);
    $titleJson = e(json_encode($signal['title'] ?? ''));
    return <<<HTML
    <div class="gripai-cta">
      <div class="gripai-cta-inner">
        <div class="gripai-badge">Powered by <a href="https://gripai.uk" target="_blank" rel="noopener"><strong>GripAI</strong></a></div>
        <div class="gripai-actions">
          <a href="/game/{$gameSlug}" class="gripai-btn">Analyse this game</a>
          <button class="gripai-btn" onclick="gripaiDepth('explain', this)">Explain this patch</button>
          <button class="gripai-btn gripai-btn-primary" onclick="gripaiDepth('predict', this)">Predict impact</button>
        </div>
        <div id="gripai-depth-panel" class="gripai-depth-panel"></div>
      </div>
    </div>
    <script>
    (function(){
      var API = "https://gripai.uk/v2";
      var title = {$titleJson};

      window.gripaiDepth = function(mode, btn) {
        var panel = document.getElementById("gripai-depth-panel");
        if (panel.dataset.mode === mode && panel.style.display === "block") {
          panel.style.display = "none";
          return;
        }
        panel.style.display = "block";
        panel.dataset.mode = mode;
        panel.innerHTML = '<div style="text-align:center;padding:20px;color:#aaa;">🧠 Analysing…</div>';

        // Search for this event by title
        fetch(API + "/news/search?q=" + encodeURIComponent(title.substring(0, 60)))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            var ev = (data.events || data.results || [])[0];
            if (!ev) {
              panel.innerHTML = '<div style="padding:16px;color:#ffc107;">No matching intelligence found for this signal.</div>';
              return;
            }

            var html = '';
            if (mode === 'explain') {
              html += '<h4 style="color:#26c6da;font-size:0.75em;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:10px;">🧠 GripAI Deep Analysis</h4>';
              if (ev.detail && ev.detail.length) {
                ev.detail.forEach(function(d) { html += '<p style="margin-bottom:8px;color:#ccc;font-size:0.9em;line-height:1.6;">' + esc(d) + '</p>'; });
              }
              if (ev.why_it_matters) {
                html += '<div style="margin-top:12px;padding:10px 14px;background:rgba(139,92,246,0.1);border-left:3px solid #8b5cf6;border-radius:0 8px 8px 0;font-size:0.9em;color:#e2e8f0;"><strong>→ Why it matters:</strong> ' + esc(ev.why_it_matters) + '</div>';
              }
              if (ev.gripai_insight) {
                html += '<div style="margin-top:12px;padding:10px 14px;background:rgba(6,182,212,0.08);border-left:3px solid #06b6d4;border-radius:0 8px 8px 0;font-size:0.9em;color:#e2e8f0;"><strong>🧠 GripAI:</strong> ' + esc(ev.gripai_insight) + '</div>';
              }
            } else {
              html += '<h4 style="color:#8b5cf6;font-size:0.75em;text-transform:uppercase;letter-spacing:1.5px;margin-bottom:10px;">📊 Impact Prediction</h4>';
              var imp = ev.impact || {};
              var labels = {player:"Player", dev:"Developer", esports:"Esports", industry:"Industry"};
              html += '<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">';
              for (var k in labels) {
                var v = imp[k] || 0;
                var col = v >= 7 ? '#ef4444' : (v >= 4 ? '#f59e0b' : '#64748b');
                html += '<span style="padding:3px 10px;border-radius:6px;font-size:0.78em;font-family:monospace;background:' + col + '20;color:' + col + ';border:1px solid ' + col + '40;">' + labels[k] + ': ' + v + '/10</span>';
              }
              html += '</div>';
              var strength = ev.signal_strength || "developing";
              var sCol = strength === "strong" ? "#00e676" : (strength === "developing" ? "#ffc107" : "#94a3b8");
              html += '<div style="margin-bottom:8px;"><span style="padding:2px 10px;border-radius:12px;font-size:0.65em;font-weight:700;letter-spacing:0.8px;text-transform:uppercase;background:' + sCol + '20;color:' + sCol + ';border:1px solid ' + sCol + '40;">' + strength + ' signal</span>';
              html += ' <span style="font-size:0.8em;color:#94a3b8;margin-left:8px;">Confidence: ' + (ev.signal_confidence || 'N/A') + ' · Evidence: ' + (ev.evidence_count || 'N/A') + '</span></div>';
              if (ev.gripai_insight) {
                html += '<div style="margin-top:12px;padding:10px 14px;background:rgba(139,92,246,0.1);border-left:3px solid #8b5cf6;border-radius:0 8px 8px 0;font-size:0.9em;color:#e2e8f0;"><strong>🔮 Prediction:</strong> ' + esc(ev.gripai_insight) + '</div>';
              }
            }
            panel.innerHTML = html;
          })
          .catch(function() {
            panel.innerHTML = '<div style="padding:16px;color:#ef4444;">Failed to fetch analysis. Try again.</div>';
          });
      };

      function esc(s) {
        var d = document.createElement("div");
        d.textContent = s || "";
        return d.innerHTML;
      }
    })();
    </script>
HTML;
}


// ── Trend & Intelligence Analysis ─────────────────────────

function analyze_game_trends(int $days = 14): array {
    $dates = get_available_dates($days);
    $games = [];
    
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $s) {
            $tags = $s['tags'] ?? [];
            $imp = $s['impact'] ?? [];
            $max_impact = max(intval($imp['player'] ?? 0), intval($imp['dev'] ?? 0), intval($imp['esports'] ?? 0), intval($imp['industry'] ?? 0));
            
            foreach ($tags as $tag) {
                $key = strtolower(trim($tag));
                if (strlen($key) < 2) continue;
                if (!isset($games[$key])) {
                    $games[$key] = ['name' => $tag, 'mentions' => 0, 'total_impact' => 0, 'max_impact' => 0, 'categories' => [], 'dates' => [], 'signals' => []];
                }
                $games[$key]['mentions']++;
                $games[$key]['total_impact'] += $max_impact;
                $games[$key]['max_impact'] = max($games[$key]['max_impact'], $max_impact);
                $games[$key]['categories'][] = $s['category'] ?? 'Update';
                if (!in_array($date, $games[$key]['dates'])) {
                    $games[$key]['dates'][] = $date;
                }
                $games[$key]['signals'][] = ['title' => $s['title'], 'date' => $date, 'impact' => $max_impact];
            }
        }
    }
    
    return $games;
}

function get_rising_games(int $days = 14, int $limit = 10): array {
    $games = analyze_game_trends($days);
    uasort($games, function($a, $b) {
        $scoreA = $a['mentions'] * ($a['total_impact'] / max($a['mentions'], 1));
        $scoreB = $b['mentions'] * ($b['total_impact'] / max($b['mentions'], 1));
        return $scoreB <=> $scoreA;
    });
    return array_slice($games, 0, $limit, true);
}

function get_most_patched(int $days = 14, int $limit = 10): array {
    $games = analyze_game_trends($days);
    $patched = array_filter($games, function($g) {
        $cats = array_map('strtolower', is_array($g['categories']) ? $g['categories'] : []);
        return in_array('patch', $cats) || in_array('update', $cats);
    });
    uasort($patched, function($a, $b) { return $b['mentions'] <=> $a['mentions']; });
    return array_slice($patched, 0, $limit, true);
}

function get_category_distribution(int $days = 14): array {
    $dates = get_available_dates($days);
    $dist = [];
    $total = 0;
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $s) {
            $cat = is_string($s['category'] ?? null) ? strtolower($s['category']) : 'update';
            $dist[$cat] = ($dist[$cat] ?? 0) + 1;
            $total++;
        }
    }
    foreach ($dist as $cat => $count) {
        $dist[$cat] = ['count' => $count, 'pct' => $total > 0 ? round(($count / $total) * 100) : 0];
    }
    arsort($dist);
    return $dist;
}

function detect_signals_patterns(int $days = 7): array {
    $games = analyze_game_trends($days);
    $patterns = [];
    
    $multi_day = array_filter($games, fn($g) => count($g['dates']) >= 2);
    if (!empty($multi_day)) {
        uasort($multi_day, fn($a, $b) => count($b['dates']) <=> count($a['dates']));
        $top = array_slice($multi_day, 0, 3, true);
        foreach ($top as $key => $g) {
            $patterns[] = [
                'type' => 'recurring',
                'title' => e($g['name']) . ' appeared in signals on ' . count($g['dates']) . ' different days',
                'detail' => 'Total mentions: ' . $g['mentions'] . ', Average impact: ' . round($g['total_impact'] / max($g['mentions'], 1), 1),
                'severity' => count($g['dates']) >= 3 ? 'high' : 'mid',
            ];
        }
    }
    
    $dist = get_category_distribution($days);
    foreach ($dist as $cat => $info) {
        if ($info['pct'] >= 40) {
            $patterns[] = [
                'type' => 'category_spike',
                'title' => ucfirst($cat) . ' signals dominate at ' . $info['pct'] . '% of all coverage',
                'detail' => $info['count'] . ' signals in this category over ' . $days . ' days',
                'severity' => 'high',
            ];
        }
    }
    
    $high_impact = array_filter($games, fn($g) => $g['max_impact'] >= 8);
    if (count($high_impact) >= 3) {
        $patterns[] = [
            'type' => 'high_impact',
            'title' => count($high_impact) . ' games have critical-level impact scores (8+)',
            'detail' => 'Games: ' . implode(', ', array_map(fn($g) => $g['name'], array_slice($high_impact, 0, 5))),
            'severity' => 'high',
        ];
    }
    
    return $patterns;
}

function generate_weekly_report(int $days = 7): array {
    $dates = get_available_dates($days);
    $all_signals = [];
    
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $s) {
            $s['_date'] = $date;
            $imp = $s['impact'] ?? [];
            $s['_max_impact'] = max(intval($imp['player'] ?? 0), intval($imp['dev'] ?? 0), intval($imp['esports'] ?? 0), intval($imp['industry'] ?? 0));
            $all_signals[] = $s;
        }
    }
    
    if (empty($all_signals)) return [];
    
    usort($all_signals, fn($a, $b) => $b['_max_impact'] <=> $a['_max_impact']);
    
    $games = analyze_game_trends($days);
    $rising = get_rising_games($days, 5);
    $patterns = detect_signals_patterns($days);
    $dist = get_category_distribution($days);
    
    return [
        'period_start' => end($dates),
        'period_end' => $dates[0],
        'total_signals' => count($all_signals),
        'days_covered' => count($dates),
        'biggest_story' => $all_signals[0] ?? null,
        'most_patched' => array_values(get_most_patched($days, 3)),
        'rising_games' => array_values(array_slice($rising, 0, 5)),
        'patterns' => $patterns,
        'category_distribution' => $dist,
        'top_tags' => array_slice($games, 0, 10, true),
    ];
}


// ─── Phase 8: Trust & Intelligence Labels ───
function trust_label(float $confidence, string $tier = 'developing'): array {
    // Returns ['label' => string, 'class' => string, 'icon' => string]
    if ($confidence >= 0.85 && in_array($tier, ['strong'])) {
        return ['label' => 'Historically Accurate', 'class' => 'trust-high', 'icon' => '🎯'];
    } elseif ($confidence >= 0.70 || $tier === 'strong') {
        return ['label' => 'High Confidence', 'class' => 'trust-high', 'icon' => '✅'];
    } elseif ($confidence >= 0.50 || $tier === 'developing') {
        return ['label' => 'Emerging Pattern', 'class' => 'trust-medium', 'icon' => '📡'];
    } else {
        return ['label' => 'Unverified', 'class' => 'trust-low', 'icon' => '⚠️'];
    }
}

function trust_badge_html(float $confidence, string $tier = 'developing'): string {
    $t = trust_label($confidence, $tier);
    return '<span class="trust-badge ' . e($t['class']) . '" title="Intelligence confidence: ' . round($confidence * 100) . '%">' . $t['icon'] . ' ' . e($t['label']) . '</span>';
}
