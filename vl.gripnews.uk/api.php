<?php
/**
 * GameGrip Vault API — vl.gripnews.uk
 * 
 * PUBLIC (no auth):
 *   GET  ?action=health  — Health check
 *   GET  ?action=stats   — Row counts (no sensitive data)
 *   GET  ?action=browse  — Paginated table browse (truncated snippets only)
 * 
 * RATE-LIMITED (20/min per IP):
 *   GET  ?action=search  — Full-text search (truncated results)
 * 
 * AUTH REQUIRED (X-Vault-Key):
 *   POST ?action=ingest  — Bulk insert archived rows
 *   GET  ?action=query   — Raw table queries
 */

header('Content-Type: application/json');
header('X-Powered-By: GameGrip Vault');

require_once __DIR__ . '/rate_limit.php';

// Config
$VAULT_KEY = 'vk_GrIpVaUlT2026_xQ9mN';
$DB_HOST = 'localhost';
$DB_NAME = 'gripzcxe_vault';
$DB_USER = 'gripzcxe_admin';
$DB_PASS = 'REDACTED_DB_PASSWORD';

$VALID_TABLES = [
    'crawl_raw_logs', 'cluster_trends', 'evidence', 'blog_posts',
    'cluster_history', 'patch_notes', 'issues', 'clusters',
    'cluster_recommendations', 'cluster_risk', 'pipeline_jobs',
    'confidence_log', 'issue_timeline', 'cluster_fingerprints',
    'crawl_results', 'website_checks', 'fingerprints', 'vault_meta'
];

$SEARCHABLE = [
    'crawl_raw_logs' => ['title', 'snippet'],
    'evidence' => ['content'],
    'blog_posts' => ['title', 'excerpt'],
    'patch_notes' => ['title', 'raw_content', 'analysis_notes'],
    'issues' => ['game_name', 'summary', 'raw_text'],
    'clusters' => ['label'],
    'cluster_recommendations' => ['recommendation'],
    'crawl_results' => ['title', 'snippet'],
    'fingerprints' => ['label'],
];

// Max snippet length for public endpoints
$SNIPPET_MAX = 200;

function get_db() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO(
            "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
            $DB_USER, $DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function auth_check() {
    global $VAULT_KEY;
    $key = $_SERVER['HTTP_X_VAULT_KEY'] ?? '';
    if ($key !== $VAULT_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized — API key required']);
        exit;
    }
}

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function error_response($msg, $code = 400) {
    json_response(['error' => $msg], $code);
}

function truncate_row($row, $max_len = 200) {
    $long_fields = ['raw_content', 'content', 'raw_text', 'snippet', 'excerpt',
                    'analysis_notes', 'bug_fixes', 'new_features', 'balance_changes',
                    'known_issues', 'error_message', 'recommendation'];
    foreach ($long_fields as $field) {
        if (isset($row[$field]) && is_string($row[$field]) && strlen($row[$field]) > $max_len) {
            $row[$field] = mb_substr($row[$field], 0, $max_len) . '…';
        }
    }
    return $row;
}

// === ACTIONS ===

function action_health() {
    try {
        $pdo = get_db();
        $pdo->query("SELECT 1");
        json_response(['status' => 'ok', 'service' => 'GameGrip Vault', 'version' => '2.0']);
    } catch (Exception $e) {
        json_response(['status' => 'error', 'message' => 'DB connection failed'], 500);
    }
}

function action_stats() {
    global $VALID_TABLES;
    $pdo = get_db();
    $stats = [];
    $total_rows = 0;

    foreach ($VALID_TABLES as $table) {
        if ($table === 'vault_meta') continue;
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $oldest = $pdo->query("SELECT MIN(vaulted_at) FROM `$table`")->fetchColumn();
            $newest = $pdo->query("SELECT MAX(vaulted_at) FROM `$table`")->fetchColumn();
            $stats[$table] = [
                'rows' => (int)$count,
                'oldest_vault' => $oldest,
                'newest_vault' => $newest
            ];
            $total_rows += (int)$count;
        } catch (Exception $e) {
            $stats[$table] = ['error' => $e->getMessage()];
        }
    }

    json_response(['total_rows' => $total_rows, 'tables' => $stats]);
}

function action_browse() {
    global $VALID_TABLES, $SNIPPET_MAX;
    $table = $_GET['table'] ?? '';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $per_page = 25;
    $offset = ($page - 1) * $per_page;

    if (!$table || !in_array($table, $VALID_TABLES) || $table === 'vault_meta') {
        error_response('Invalid table');
    }

    $pdo = get_db();
    $total = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $total_pages = max(1, ceil($total / $per_page));

    $stmt = $pdo->prepare("SELECT * FROM `$table` ORDER BY vaulted_at DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    // Truncate all long fields
    $rows = array_map(fn($r) => truncate_row($r, $SNIPPET_MAX), $rows);

    json_response([
        'table' => $table,
        'page' => $page,
        'per_page' => $per_page,
        'total_rows' => $total,
        'total_pages' => $total_pages,
        'rows' => $rows
    ]);
}

function action_search() {
    global $SEARCHABLE, $SNIPPET_MAX;

    // Rate limit: 20 searches per minute per IP
    if (!check_rate_limit('search', 20, 60)) {
        error_response('Rate limited — max 20 searches per minute', 429);
    }
    if (is_scraper()) {
        error_response('Automated access blocked', 403);
    }

    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) error_response('Query must be at least 2 characters');

    $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
    $filter_tables = !empty($_GET['tables']) ? explode(',', $_GET['tables']) : [];

    $pdo = get_db();
    $results = [];
    $total_matches = 0;

    foreach ($SEARCHABLE as $table => $cols) {
        if ($filter_tables && !in_array($table, $filter_tables)) continue;

        try {
            $ft_cols = implode(',', array_map(fn($c) => "`$c`", $cols));
            $sql = "SELECT *, MATCH($ft_cols) AGAINST(:q IN BOOLEAN MODE) AS relevance
                    FROM `$table`
                    WHERE MATCH($ft_cols) AGAINST(:q2 IN BOOLEAN MODE)
                    ORDER BY relevance DESC
                    LIMIT :lim";

            $stmt = $pdo->prepare($sql);
            $search_term = '*' . str_replace(' ', '* *', $q) . '*';
            $stmt->bindValue(':q', $search_term);
            $stmt->bindValue(':q2', $search_term);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            if ($rows) {
                // Truncate for public display
                $rows = array_map(fn($r) => truncate_row($r, $SNIPPET_MAX), $rows);

                $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE MATCH($ft_cols) AGAINST(:q IN BOOLEAN MODE)");
                $count_stmt->execute([':q' => $search_term]);
                $match_count = (int)$count_stmt->fetchColumn();

                $total_matches += $match_count;
                $results[$table] = ['total_matches' => $match_count, 'rows' => $rows];
            }
        } catch (Exception $e) {
            // Skip tables with search errors
        }
    }

    json_response(['query' => $q, 'total_matches' => $total_matches, 'results' => $results]);
}

function action_query() {
    // LOCKED — requires API key
    auth_check();

    global $VALID_TABLES;
    $table = $_GET['table'] ?? '';
    if (!in_array($table, $VALID_TABLES)) error_response("Invalid table");

    $pdo = get_db();
    $limit = min(200, max(1, (int)($_GET['limit'] ?? 50)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $order = ($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

    $stmt = $pdo->prepare("SELECT * FROM `$table` ORDER BY vaulted_at $order LIMIT :lim OFFSET :off");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();

    json_response([
        'table' => $table,
        'rows' => $stmt->fetchAll(),
        'limit' => $limit,
        'offset' => $offset
    ]);
}

function action_ingest() {
    global $VALID_TABLES;
    auth_check();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) error_response('Invalid JSON body');

    $table = $input['table'] ?? '';
    $rows = $input['rows'] ?? [];

    if (!in_array($table, $VALID_TABLES)) error_response("Invalid table: $table");
    if (empty($rows) || !is_array($rows)) error_response('No rows provided');

    $pdo = get_db();
    $pdo->beginTransaction();

    try {
        $inserted = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $cols = array_keys($row);
            $placeholders = array_map(fn($c) => ":$c", $cols);
            $col_list = implode(', ', array_map(fn($c) => "`$c`", $cols));
            $ph_list = implode(', ', $placeholders);

            $sql = "INSERT IGNORE INTO `$table` ($col_list) VALUES ($ph_list)";
            $stmt = $pdo->prepare($sql);

            foreach ($row as $key => $val) {
                if (is_array($val) || is_object($val)) {
                    $stmt->bindValue(":$key", json_encode($val));
                } elseif (is_null($val)) {
                    $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(":$key", $val);
                }
            }

            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $inserted++;
            } else {
                $skipped++;
            }
        }

        // Update vault_meta
        try {
            $meta_sql = "INSERT INTO vault_meta (source_table, last_archive_at, rows_archived)
                        VALUES (:table, NOW(), :count)
                        ON DUPLICATE KEY UPDATE last_archive_at = NOW(), rows_archived = rows_archived + :count2";
            $meta = $pdo->prepare($meta_sql);
            $meta->execute([':table' => $table, ':count' => $inserted, ':count2' => $inserted]);
        } catch (Exception $e) {}

        $pdo->commit();
        json_response(['success' => true, 'table' => $table, 'inserted' => $inserted, 'skipped_duplicates' => $skipped]);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_response('Ingest failed: ' . $e->getMessage(), 500);
    }
}

// === ROUTER ===
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'health': action_health(); break;
    case 'stats': action_stats(); break;
    case 'browse': action_browse(); break;
    case 'search': action_search(); break;
    case 'query': action_query(); break;
    case 'ingest': action_ingest(); break;
    default:
        error_response('Unknown action. Available: health, stats, browse, search', 404);
}
?>
