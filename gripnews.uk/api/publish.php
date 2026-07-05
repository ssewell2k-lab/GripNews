<?php
/**
 * Grip Protocol — Signal Publish Endpoint
 * POST /publish — Accepts signals from GripAI and other authorized sources.
 * 
 * Auth: X-Publish-Key header or ?publish_key= parameter
 * Body: JSON with "signals" array following schema.json format
 * 
 * Modes:
 *   - append (default): adds signals to today's file
 *   - replace: overwrites today's signals entirely
 */

error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Publish-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit;
}

// ── Auth ──
$VALID_KEYS = [
    'gn_2026_gripai_publish_key' => 'GripAI',
    'gn_grip_master_2026_zK8nM4xR9' => 'Master',
];

$key = $_SERVER['HTTP_X_PUBLISH_KEY'] ?? ($_GET['publish_key'] ?? '');
if (!$key || !isset($VALID_KEYS[$key])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or missing publish key']);
    exit;
}
$source = $VALID_KEYS[$key];

// ── Parse body ──
$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['signals']) || !is_array($body['signals'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Body must be JSON with "signals" array']);
    exit;
}

$date = $body['date'] ?? date('Y-m-d');
$mode = $body['mode'] ?? 'append'; // append or replace
$signals = $body['signals'];

// Validate each signal has required fields
$required = ['title', 'summary', 'category', 'score'];
$valid = [];
$errors = [];
foreach ($signals as $i => $s) {
    $missing = [];
    foreach ($required as $f) {
        if (empty($s[$f])) $missing[] = $f;
    }
    if ($missing) {
        $errors[] = "Signal {$i}: missing " . implode(', ', $missing);
    } else {
        // Add defaults
        $s['confidence'] = $s['confidence'] ?? 'confirmed';
        $s['impact'] = $s['impact'] ?? ['player' => 5, 'dev' => 5, 'esports' => 3, 'industry' => 5];
        $s['tags'] = $s['tags'] ?? [];
        $s['why_it_matters'] = $s['why_it_matters'] ?? '';
        $s['detail'] = $s['detail'] ?? [];
        $s['body'] = $s['body'] ?? '';
        $s['sources'] = $s['sources'] ?? [];
        $s['_published_by'] = $source;
        $s['_published_at'] = date('c');
        $valid[] = $s;
    }
}

if (empty($valid)) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid signals', 'details' => $errors]);
    exit;
}

// ── Load existing or create new ──
$data_dir = __DIR__ . '/../data';
$file = $data_dir . "/{$date}.json";

if ($mode === 'append' && file_exists($file)) {
    $existing = json_decode(file_get_contents($file), true);
    if (!$existing || !isset($existing['signals'])) {
        $existing = ['date' => $date, 'signals' => []];
    }
    
    // Deduplicate by title (case-insensitive)
    $existing_titles = array_map(fn($s) => strtolower($s['title'] ?? ''), $existing['signals']);
    $added = 0;
    foreach ($valid as $s) {
        if (!in_array(strtolower($s['title']), $existing_titles)) {
            $existing['signals'][] = $s;
            $existing_titles[] = strtolower($s['title']);
            $added++;
        }
    }
    
    $existing['total'] = count($existing['signals']);
    $existing['generated_at'] = date('c');
    
    // Recalculate top category
    $cats = [];
    foreach ($existing['signals'] as $s) {
        $cat = $s['category'] ?? 'Update';
        $cats[$cat] = ($cats[$cat] ?? 0) + 1;
    }
    arsort($cats);
    $existing['top_category'] = array_key_first($cats);
    
    $data = $existing;
    $result_msg = "Appended {$added} new signals (skipped " . (count($valid) - $added) . " duplicates)";
} else {
    // Replace or new file
    $cats = [];
    foreach ($valid as $s) {
        $cat = $s['category'] ?? 'Update';
        $cats[$cat] = ($cats[$cat] ?? 0) + 1;
    }
    arsort($cats);
    
    $data = [
        'date' => $date,
        'generated_at' => date('c'),
        'total' => count($valid),
        'top_category' => array_key_first($cats),
        'signals' => $valid,
    ];
    $result_msg = "Published " . count($valid) . " signals (replace mode)";
}

// ── Save ──
$json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
if (!file_put_contents($file, $json)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to write signal file']);
    exit;
}

// Log
$log_dir = $data_dir . '/.publish-log';
if (!is_dir($log_dir)) @mkdir($log_dir, 0755, true);
$log = [
    'timestamp' => date('c'),
    'source' => $source,
    'date' => $date,
    'mode' => $mode,
    'signals_submitted' => count($signals),
    'signals_valid' => count($valid),
    'total_after' => $data['total'],
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
];
@file_put_contents($log_dir . '/publish.log', json_encode($log) . "\n", FILE_APPEND);

echo json_encode([
    'success' => true,
    'message' => $result_msg,
    'date' => $date,
    'total_signals' => $data['total'],
    'errors' => $errors ?: null,
], JSON_PRETTY_PRINT);
