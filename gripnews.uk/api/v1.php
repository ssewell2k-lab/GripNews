<?php
/**
 * Grip Protocol — API v1 Router
 * Phase 13: Infrastructure layer for gaming intelligence.
 * 
 * All endpoints return JSON. CORS enabled.
 * API key required for all access. Sign up at gripnews.uk/developers.
 */

// ── Bootstrap ────────────────────────────────────────────────
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('X-Powered-By: Grip Protocol v1');
header('Cache-Control: public, max-age=300');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed', 'allowed' => ['GET']]);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';

// ── Authentication (API key required) ────────────────────────
// Internal embed key — used by gripnews.uk embed widgets only
define('EMBED_INTERNAL_KEY', 'gn_embed_REDACTED');

function validate_api_key(string $key): array|false {
    // Check internal embed key first
    if ($key === EMBED_INTERNAL_KEY) {
        return ['tier' => 'embed', 'rate_limit' => 600, 'name' => 'Embed Widget'];
    }
    
    $keys_file = __DIR__ . '/../data/.keys.json';
    if (!file_exists($keys_file)) return false;
    $keys = json_decode(file_get_contents($keys_file), true) ?? [];
    foreach ($keys as $k) {
        if (($k['key'] ?? '') === $key && ($k['active'] ?? false)) {
            return [
                'tier' => $k['tier'] ?? 'starter',
                'rate_limit' => intval($k['rate_limit'] ?? 1000),
                'name' => $k['name'] ?? 'Unknown',
            ];
        }
    }
    return false;
}

function authenticate(): array {
    $key = $_SERVER['HTTP_X_API_KEY'] ?? ($_GET['api_key'] ?? '');
    
    if (!$key) {
        http_response_code(401);
        echo json_encode([
            'error' => 'API key required',
            'message' => 'All Grip Protocol API access requires an API key. Sign up at gripnews.uk/developers to get yours.',
            'docs' => 'https://gripnews.uk/developers',
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $account = validate_api_key($key);
    if (!$account) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Invalid API key',
            'message' => 'This key is not recognised or has been deactivated. Check your key at gripnews.uk/developers.',
            'docs' => 'https://gripnews.uk/developers',
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    return $account;
}

function check_rate_limit(array $account): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $hash = md5($ip . ($account['name'] ?? ''));
    $cache_dir = __DIR__ . '/../data/.cache';
    if (!is_dir($cache_dir)) @mkdir($cache_dir, 0755, true);
    $file = $cache_dir . "/rl_{$hash}.json";
    
    $now = time();
    $window = 3600;
    $limit = $account['rate_limit'] ?? 1000;
    
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : null;
    if (!$data || ($now - ($data['window_start'] ?? 0)) > $window) {
        $data = ['window_start' => $now, 'count' => 0];
    }
    
    $data['count']++;
    @file_put_contents($file, json_encode($data));
    
    $remaining = max(0, $limit - $data['count']);
    header("X-RateLimit-Limit: {$limit}");
    header("X-RateLimit-Remaining: {$remaining}");
    header("X-RateLimit-Reset: " . ($data['window_start'] + $window));
    
    if ($data['count'] > $limit) {
        http_response_code(429);
        echo json_encode([
            'error' => 'Rate limit exceeded',
            'message' => "You've hit your {$limit} requests/hour limit. Upgrade your plan at gripnews.uk/developers for higher limits.",
            'retry_after' => ($data['window_start'] + $window) - $now,
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

// Authenticate every request
$_GRIP_ACCOUNT = authenticate();
check_rate_limit($_GRIP_ACCOUNT);

// ── Routing ──────────────────────────────────────────────────
$path = $_GET['_route'] ?? '';
$path = trim($path, '/');
$parts = $path ? explode('/', $path) : [];

// Helper: JSON response
function api_response($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function api_error(string $msg, int $status = 400): void {
    api_response(['error' => $msg, 'status' => $status], $status);
}

// Helper: fetch from GripAi v2
function gripai_fetch(string $endpoint): ?array {
    $ctx = stream_context_create([
        'http' => ['timeout' => 5, 'header' => "User-Agent: GripProtocol/1.0\r\n"],
        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
    ]);
    $url = "https://gripai.uk/v2" . $endpoint;
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    return json_decode($raw, true);
}

// Helper: build game entity from signal data
function build_game_entity(string $slug, int $days = 30): ?array {
    $dates = get_available_dates($days);
    $signals = [];
    $total_impact = 0;
    $categories = [];
    $first_seen = null;
    $last_seen = null;
    
    foreach ($dates as $date) {
        $day_signals = load_signals($date);
        foreach ($day_signals as $s) {
            $tags = array_map('strtolower', $s['tags'] ?? []);
            $game_slug_field = strtolower($s['game_slug'] ?? '');
            if (in_array($slug, $tags) || $game_slug_field === $slug || slugify($s['title'] ?? '') === $slug) {
                $imp = $s['impact'] ?? [];
                $max_imp = max(intval($imp['player'] ?? 0), intval($imp['dev'] ?? 0), intval($imp['esports'] ?? 0), intval($imp['industry'] ?? 0));
                $signals[] = [
                    'title' => $s['title'],
                    'summary' => $s['summary'] ?? '',
                    'category' => $s['category'] ?? 'Update',
                    'score' => $s['score'] ?? 0,
                    'impact' => $max_imp,
                    'confidence' => $s['confidence'] ?? 'confirmed',
                    'date' => $date,
                ];
                $total_impact += $max_imp;
                $cat = strtolower($s['category'] ?? 'update');
                $categories[$cat] = ($categories[$cat] ?? 0) + 1;
                if (!$first_seen || $date < $first_seen) $first_seen = $date;
                if (!$last_seen || $date > $last_seen) $last_seen = $date;
            }
        }
    }
    
    if (empty($signals)) return null;
    
    arsort($categories);
    $mention_count = count($signals);
    $avg_impact = $mention_count > 0 ? round($total_impact / $mention_count, 1) : 0;
    
    // Momentum: compare last 3 days vs previous 3 days
    $recent = array_filter($signals, fn($s) => $s['date'] >= date('Y-m-d', strtotime('-3 days')));
    $older = array_filter($signals, fn($s) => $s['date'] < date('Y-m-d', strtotime('-3 days')) && $s['date'] >= date('Y-m-d', strtotime('-6 days')));
    $momentum = count($recent) - count($older);
    
    return [
        'slug' => $slug,
        'name' => ucwords(str_replace('-', ' ', $slug)),
        'mentions' => $mention_count,
        'avg_impact' => $avg_impact,
        'max_impact' => max(array_column($signals, 'impact')),
        'momentum' => $momentum > 0 ? "+{$momentum}" : (string)$momentum,
        'primary_category' => array_key_first($categories),
        'category_breakdown' => $categories,
        'first_seen' => $first_seen,
        'last_seen' => $last_seen,
        'days_active' => count(array_unique(array_column($signals, 'date'))),
        'signals' => $signals,
    ];
}

// ── Endpoint: Root (API info) ────────────────────────────────
if (empty($parts)) {
    api_response([
        'name' => 'Grip Protocol API',
        'version' => 'v1',
        'description' => 'Gaming intelligence infrastructure. Signals, games, studios, releases, momentum — all connected.',
        'documentation' => 'https://gripnews.uk/developers',
        'authenticated_as' => $_GRIP_ACCOUNT['name'] ?? 'Unknown',
        'tier' => $_GRIP_ACCOUNT['tier'] ?? 'starter',
        'endpoints' => [
            'GET /signals' => 'Intelligence signals (filterable)',
            'GET /signals/{date}' => 'Signals for a specific date',
            'GET /games' => 'All tracked game entities',
            'GET /games/{slug}' => 'Full game profile + signal history',
            'GET /studios' => 'Game studios with rankings',
            'GET /studios/{slug}' => 'Studio detail + portfolio',
            'GET /momentum' => 'Top movers by momentum score',
            'GET /releases/radar' => 'Upcoming release radar',
            'GET /releases/risk' => 'Release risk/delay index',
            'GET /trends' => 'Trend patterns + rising games',
            'GET /categories' => 'Signal categories',
            'GET /graph' => 'Entity relationship graph',
            'GET /overview' => 'Platform-wide intelligence summary',
            'GET /weekly' => 'Weekly intelligence report',
        ],
        'authentication' => 'API key required. Pass via X-API-Key header or ?api_key= parameter.',
        'rate_limit' => ($_GRIP_ACCOUNT['rate_limit'] ?? 1000) . ' req/hr',
        'powered_by' => 'Grip Intelligence',
    ]);
}

$resource = $parts[0] ?? '';

// ── Endpoint: /signals ───────────────────────────────────────
if ($resource === 'signals') {
    $date = $parts[1] ?? ($_GET['date'] ?? date('Y-m-d'));
    $category = strtolower($_GET['category'] ?? '');
    $min_score = intval($_GET['min_score'] ?? 0);
    $limit = min(intval($_GET['limit'] ?? 50), 100);
    $offset = intval($_GET['offset'] ?? 0);
    $tag = strtolower($_GET['tag'] ?? '');
    
    // Multi-day mode
    $days = intval($_GET['days'] ?? 1);
    $days = min(max($days, 1), 30);
    
    if ($days > 1) {
        $dates = get_available_dates($days);
    } else {
        $dates = [$date];
    }
    
    $all_signals = [];
    foreach ($dates as $d) {
        $day_signals = load_signals($d);
        foreach ($day_signals as $s) {
            $s['date'] = $d;
            
            // Filters
            if ($category && strtolower($s['category'] ?? '') !== $category) continue;
            if ($min_score && ($s['score'] ?? 0) < $min_score) continue;
            if ($tag) {
                $tags = array_map('strtolower', $s['tags'] ?? []);
                if (!in_array($tag, $tags)) continue;
            }
            
            // Clean output
            $imp = $s['impact'] ?? [];
            $all_signals[] = [
                'id' => substr(md5($d . $s['title']), 0, 12),
                'title' => $s['title'],
                'summary' => $s['summary'] ?? '',
                'why_it_matters' => $s['why_it_matters'] ?? '',
                'category' => $s['category'] ?? 'Update',
                'confidence' => $s['confidence'] ?? 'confirmed',
                'score' => $s['score'] ?? 0,
                'impact' => [
                    'player' => intval($imp['player'] ?? 0),
                    'dev' => intval($imp['dev'] ?? 0),
                    'esports' => intval($imp['esports'] ?? 0),
                    'industry' => intval($imp['industry'] ?? 0),
                ],
                'tags' => $s['tags'] ?? [],
                'date' => $d,
                'url' => SITE_URL . '/story/' . $d . '/' . slugify($s['title']),
            ];
        }
    }
    
    $total = count($all_signals);
    $paged = array_slice($all_signals, $offset, $limit);
    
    api_response([
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
        'date' => $days > 1 ? null : $date,
        'days' => $days,
        'filters' => array_filter(['category' => $category, 'min_score' => $min_score ?: null, 'tag' => $tag ?: null]),
        'signals' => $paged,
    ]);
}

// ── Endpoint: /games/{slug} ──────────────────────────────────
if ($resource === 'games') {
    $slug = $parts[1] ?? '';
    if (!$slug) {
        // List all games seen in signals
        $games = analyze_game_trends(30);
        $list = [];
        foreach ($games as $key => $g) {
            $imp_avg = $g['mentions'] > 0 ? round($g['total_impact'] / $g['mentions'], 1) : 0;
            $list[] = [
                'slug' => slugify($g['name']),
                'name' => $g['name'],
                'mentions' => $g['mentions'],
                'avg_impact' => $imp_avg,
                'max_impact' => $g['max_impact'],
                'days_active' => count($g['dates']),
                'url' => SITE_URL . '/game/' . slugify($g['name']),
                'api_url' => SITE_URL . '/api/v1/games/' . slugify($g['name']),
            ];
        }
        usort($list, fn($a, $b) => $b['mentions'] <=> $a['mentions']);
        api_response([
            'total' => count($list),
            'period_days' => 30,
            'games' => $list,
        ]);
    }
    
    $entity = build_game_entity($slug);
    if (!$entity) {
        api_error("Game '{$slug}' not found in signal data", 404);
    }
    $entity['web_url'] = SITE_URL . '/game/' . $slug;
    api_response($entity);
}

// ── Endpoint: /studios ───────────────────────────────────────
if ($resource === 'studios') {
    $slug = $parts[1] ?? '';
    
    // Fetch from GripAi v2
    $studios_data = gripai_fetch('/studios');
    $studios = $studios_data['studios'] ?? [];
    
    if (!$slug) {
        $list = [];
        foreach ($studios as $s) {
            $list[] = [
                'slug' => $s['slug'],
                'name' => $s['name'],
                'description' => $s['description'] ?? '',
                'game_count' => $s['game_count'] ?? 0,
                'avg_score' => $s['avg_score'] ?? null,
                'signal_count' => $s['signal_count'] ?? 0,
                'web_url' => SITE_URL . '/studio/' . $s['slug'],
                'api_url' => SITE_URL . '/api/v1/studios/' . $s['slug'],
            ];
        }
        api_response([
            'total' => count($list),
            'studios' => $list,
        ]);
    }
    
    // Individual studio
    $studio = null;
    foreach ($studios as $s) {
        if ($s['slug'] === $slug) { $studio = $s; break; }
    }
    if (!$studio) {
        api_error("Studio '{$slug}' not found", 404);
    }
    
    // Enrich with local signal data
    $studio['web_url'] = SITE_URL . '/studio/' . $slug;
    $studio['recent_signals'] = [];
    $dates = get_available_dates(14);
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $sig) {
            $tags = array_map('strtolower', $sig['tags'] ?? []);
            if (in_array($slug, $tags) || in_array(str_replace('-', ' ', $slug), $tags)) {
                $studio['recent_signals'][] = [
                    'title' => $sig['title'],
                    'category' => $sig['category'] ?? '',
                    'score' => $sig['score'] ?? 0,
                    'date' => $date,
                ];
            }
        }
    }
    
    api_response($studio);
}

// ── Endpoint: /releases/radar ────────────────────────────────
if ($resource === 'releases') {
    $sub = $parts[1] ?? 'radar';
    
    $dates = get_available_dates(30);
    $release_signals = [];
    
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $s) {
            $cat = strtolower($s['category'] ?? '');
            $title_lower = strtolower($s['title'] ?? '');
            $is_release = in_array($cat, ['release', 'announcement']) 
                || strpos($title_lower, 'release') !== false
                || strpos($title_lower, 'launch') !== false
                || strpos($title_lower, 'announce') !== false
                || strpos($title_lower, 'reveal') !== false
                || strpos($title_lower, 'trailer') !== false;
            
            $is_risk = strpos($title_lower, 'delay') !== false
                || strpos($title_lower, 'postpone') !== false
                || strpos($title_lower, 'push back') !== false
                || strpos($title_lower, 'cancel') !== false;
            
            if ($sub === 'risk' && $is_risk) {
                $imp = $s['impact'] ?? [];
                $release_signals[] = [
                    'title' => $s['title'],
                    'summary' => $s['summary'] ?? '',
                    'score' => $s['score'] ?? 0,
                    'impact' => max(intval($imp['player'] ?? 0), intval($imp['dev'] ?? 0), intval($imp['industry'] ?? 0)),
                    'tags' => $s['tags'] ?? [],
                    'confidence' => $s['confidence'] ?? 'confirmed',
                    'date' => $date,
                    'risk_type' => strpos($title_lower, 'cancel') !== false ? 'cancellation' : 'delay',
                ];
            } elseif ($sub === 'radar' && $is_release) {
                $imp = $s['impact'] ?? [];
                $hype = intval($imp['player'] ?? 0) * 2 + intval($imp['industry'] ?? 0) + ($s['score'] ?? 5);
                $release_signals[] = [
                    'title' => $s['title'],
                    'summary' => $s['summary'] ?? '',
                    'score' => $s['score'] ?? 0,
                    'hype_index' => $hype,
                    'tags' => $s['tags'] ?? [],
                    'confidence' => $s['confidence'] ?? 'confirmed',
                    'date' => $date,
                    'url' => SITE_URL . '/story/' . $date . '/' . slugify($s['title']),
                ];
            } elseif ($sub === 'anticipated' && $is_release) {
                $imp = $s['impact'] ?? [];
                $release_signals[] = [
                    'title' => $s['title'],
                    'summary' => $s['summary'] ?? '',
                    'score' => $s['score'] ?? 0,
                    'player_impact' => intval($imp['player'] ?? 0),
                    'tags' => $s['tags'] ?? [],
                    'date' => $date,
                ];
            }
        }
    }
    
    // Sort by relevance
    if ($sub === 'radar') {
        usort($release_signals, fn($a, $b) => ($b['hype_index'] ?? 0) <=> ($a['hype_index'] ?? 0));
    } elseif ($sub === 'risk') {
        usort($release_signals, fn($a, $b) => ($b['impact'] ?? 0) <=> ($a['impact'] ?? 0));
    } elseif ($sub === 'anticipated') {
        usort($release_signals, fn($a, $b) => ($b['player_impact'] ?? 0) <=> ($a['player_impact'] ?? 0));
    }
    
    $endpoint_names = ['radar' => 'Release Radar', 'risk' => 'Risk Index', 'anticipated' => 'Most Anticipated'];
    
    api_response([
        'endpoint' => $endpoint_names[$sub] ?? $sub,
        'period_days' => 30,
        'total' => count($release_signals),
        'releases' => array_slice($release_signals, 0, 50),
    ]);
}

// ── Endpoint: /momentum ──────────────────────────────────────
if ($resource === 'momentum') {
    $limit = min(intval($_GET['limit'] ?? 20), 50);
    $days = min(intval($_GET['days'] ?? 7), 30);
    
    $games = analyze_game_trends($days);
    $momentum = [];
    
    foreach ($games as $key => $g) {
        // Calculate momentum: weighted by recency and impact
        $recent_count = 0;
        $older_count = 0;
        $mid = intval(ceil($days / 2));
        
        foreach ($g['signals'] as $sig) {
            $sig_age = (strtotime(date('Y-m-d')) - strtotime($sig['date'])) / 86400;
            if ($sig_age <= $mid) $recent_count++;
            else $older_count++;
        }
        
        $trend = $recent_count - $older_count;
        $avg_impact = $g['mentions'] > 0 ? round($g['total_impact'] / $g['mentions'], 1) : 0;
        $momentum_score = ($g['mentions'] * 2) + ($avg_impact * 3) + ($trend * 5);
        
        $momentum[] = [
            'slug' => slugify($g['name']),
            'name' => $g['name'],
            'momentum_score' => round($momentum_score, 1),
            'mentions' => $g['mentions'],
            'avg_impact' => $avg_impact,
            'max_impact' => $g['max_impact'],
            'trend' => $trend > 0 ? "↑ rising" : ($trend < 0 ? "↓ falling" : "→ stable"),
            'trend_value' => $trend,
            'days_active' => count($g['dates']),
            'primary_categories' => array_slice(array_count_values(array_map('strtolower', $g['categories'])), 0, 3, true),
            'web_url' => SITE_URL . '/game/' . slugify($g['name']),
        ];
    }
    
    usort($momentum, fn($a, $b) => $b['momentum_score'] <=> $a['momentum_score']);
    
    api_response([
        'period_days' => $days,
        'total' => count($momentum),
        'movers' => array_slice($momentum, 0, $limit),
    ]);
}

// ── Endpoint: /trends ────────────────────────────────────────
if ($resource === 'trends') {
    $days = min(intval($_GET['days'] ?? 7), 30);
    
    $patterns = detect_signals_patterns($days);
    $dist = get_category_distribution($days);
    $rising = get_rising_games($days, 10);
    
    $rising_list = [];
    foreach ($rising as $key => $g) {
        $rising_list[] = [
            'name' => $g['name'],
            'slug' => slugify($g['name']),
            'mentions' => $g['mentions'],
            'max_impact' => $g['max_impact'],
        ];
    }
    
    api_response([
        'period_days' => $days,
        'patterns' => $patterns,
        'category_distribution' => $dist,
        'rising_games' => $rising_list,
    ]);
}

// ── Endpoint: /categories ────────────────────────────────────
if ($resource === 'categories') {
    $cats = get_categories();
    $dist = get_category_distribution(30);
    $list = [];
    foreach ($cats as $slug => $cat) {
        $list[] = [
            'slug' => $slug,
            'label' => $cat['label'],
            'description' => $cat['desc'],
            'icon' => $cat['icon'],
            'signal_count_30d' => $dist[$slug]['count'] ?? $dist[$cat['match'][0]]['count'] ?? 0,
            'web_url' => SITE_URL . '/' . $slug,
        ];
    }
    api_response(['categories' => $list]);
}

// ── Endpoint: /overview ──────────────────────────────────────
if ($resource === 'overview') {
    $dates = get_available_dates(30);
    $total_signals = 0;
    $all_tags = [];
    $all_categories = [];
    
    foreach ($dates as $date) {
        $signals = load_signals($date);
        $total_signals += count($signals);
        foreach ($signals as $s) {
            foreach ($s['tags'] ?? [] as $t) {
                $all_tags[strtolower($t)] = ($all_tags[strtolower($t)] ?? 0) + 1;
            }
            $cat = strtolower($s['category'] ?? 'update');
            $all_categories[$cat] = ($all_categories[$cat] ?? 0) + 1;
        }
    }
    
    arsort($all_tags);
    arsort($all_categories);
    
    // Merge with GripAi overview
    $gripai = gripai_fetch('/analytics/overview');
    $ov = $gripai['overview'] ?? [];
    
    api_response([
        'platform' => 'Grip Intelligence',
        'version' => 'Protocol v1',
        'stats' => [
            'total_signals_30d' => $total_signals,
            'days_of_data' => count($dates),
            'unique_entities' => count($all_tags),
            'games_tracked' => intval($ov['games_tracked'] ?? count($all_tags)),
            'avg_signals_per_day' => count($dates) > 0 ? round($total_signals / count($dates), 1) : 0,
        ],
        'top_entities' => array_slice($all_tags, 0, 20, true),
        'category_distribution' => array_slice($all_categories, 0, 10, true),
        'latest_date' => $dates[0] ?? null,
        'data_range' => [
            'from' => end($dates) ?: null,
            'to' => $dates[0] ?? null,
        ],
    ]);
}

// ── Endpoint: /graph ─────────────────────────────────────────
if ($resource === 'graph') {
    $days = min(intval($_GET['days'] ?? 14), 30);
    $games = analyze_game_trends($days);
    
    $nodes = [];
    $edges = [];
    $seen_edges = [];
    
    // Build nodes from entities
    foreach ($games as $key => $g) {
        if ($g['mentions'] < 2) continue; // Skip one-offs
        $nodes[] = [
            'id' => slugify($g['name']),
            'label' => $g['name'],
            'type' => 'entity',
            'weight' => $g['mentions'],
            'impact' => $g['max_impact'],
        ];
    }
    
    // Build edges from co-occurrence in signals
    $dates = get_available_dates($days);
    foreach ($dates as $date) {
        $signals = load_signals($date);
        foreach ($signals as $s) {
            $tags = array_map(fn($t) => slugify($t), $s['tags'] ?? []);
            $tags = array_unique($tags);
            for ($i = 0; $i < count($tags); $i++) {
                for ($j = $i + 1; $j < count($tags); $j++) {
                    $pair = [$tags[$i], $tags[$j]];
                    sort($pair);
                    $key = implode('::', $pair);
                    if (!isset($seen_edges[$key])) {
                        $seen_edges[$key] = ['source' => $pair[0], 'target' => $pair[1], 'weight' => 0];
                    }
                    $seen_edges[$key]['weight']++;
                }
            }
        }
    }
    
    // Only include edges with weight >= 2
    $edges = array_values(array_filter($seen_edges, fn($e) => $e['weight'] >= 2));
    usort($edges, fn($a, $b) => $b['weight'] <=> $a['weight']);
    
    api_response([
        'period_days' => $days,
        'nodes' => count($nodes),
        'edges' => count($edges),
        'graph' => [
            'nodes' => array_slice($nodes, 0, 100),
            'edges' => array_slice($edges, 0, 200),
        ],
    ]);
}

// ── Endpoint: /weekly ────────────────────────────────────────
if ($resource === 'weekly') {
    $report = generate_weekly_report(7);
    if (empty($report)) {
        api_error('No data available for weekly report', 404);
    }
    
    // Clean up the report for API output
    $clean = [
        'period' => [
            'start' => $report['period_start'],
            'end' => $report['period_end'],
            'days' => $report['days_covered'],
        ],
        'summary' => [
            'total_signals' => $report['total_signals'],
        ],
        'biggest_story' => $report['biggest_story'] ? [
            'title' => $report['biggest_story']['title'],
            'summary' => $report['biggest_story']['summary'] ?? '',
            'category' => $report['biggest_story']['category'] ?? '',
            'score' => $report['biggest_story']['score'] ?? 0,
            'date' => $report['biggest_story']['_date'] ?? '',
        ] : null,
        'patterns' => $report['patterns'],
        'category_distribution' => $report['category_distribution'],
        'rising_games' => array_map(fn($g) => [
            'name' => $g['name'],
            'mentions' => $g['mentions'],
            'max_impact' => $g['max_impact'],
        ], $report['rising_games']),
    ];
    
    api_response($clean);
}

// ── 404 ──────────────────────────────────────────────────────
api_error("Unknown endpoint: /api/v1/{$resource}. See /api/v1/ for available endpoints.", 404);
