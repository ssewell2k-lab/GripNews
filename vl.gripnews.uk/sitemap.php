<?php
header("Content-Type: application/xml; charset=utf-8");
header("Cache-Control: public, max-age=3600");

$DB_HOST = "localhost";
$DB_NAME = "gripzcxe_vault";
$DB_USER = "gripzcxe_admin";
$DB_PASS = "REDACTED_DB_PASSWORD";

function db() {
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

function to_slug($name) {
    $slug = strtolower(trim($name));
    $slug = preg_replace("/[^a-z0-9\\s-]/", "", $slug);
    $slug = preg_replace("/[\\s-]+/", "-", $slug);
    return trim($slug, "-");
}

$today = date("Y-m-d");
$base  = "https://vl.gripnews.uk";
$urls  = [];

function add_url(&$urls, $loc, $changefreq, $priority, $lastmod = null) {
    if (isset($urls[$loc])) return;
    $urls[$loc] = true;
    echo "  <url><loc>{$loc}</loc><changefreq>{$changefreq}</changefreq><priority>{$priority}</priority>";
    if ($lastmod) echo "<lastmod>{$lastmod}</lastmod>";
    echo "</url>\n";
}

$blocked = ["auth-test-game", "test-game", "test", ""];

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
add_url($urls, $base . "/",          "daily",  "1.0", $today);
add_url($urls, $base . "/games",     "daily",  "0.9", $today);
add_url($urls, $base . "/patches",   "daily",  "0.8", $today);
add_url($urls, $base . "/hardware",  "daily",  "0.8", $today);
add_url($urls, $base . "/hardware/gpu", "daily", "0.7");
add_url($urls, $base . "/hardware/cpu", "daily", "0.7");
add_url($urls, $base . "/hardware/ram", "daily", "0.7");
add_url($urls, $base . "/drivers",   "daily",  "0.8", $today);
add_url($urls, $base . "/websites",  "hourly", "0.8", $today);

// Dynamic game pages (deduplicated, test entries excluded)
try {
    $stmt = db()->query("
        SELECT DISTINCT slug FROM (
            SELECT DISTINCT LOWER(TRIM(game_name)) AS slug FROM issues
            WHERE game_name IS NOT NULL AND game_name != ''
            UNION
            SELECT DISTINCT LOWER(TRIM(game_name)) AS slug FROM patch_notes
            WHERE game_name IS NOT NULL AND game_name != ''
        ) AS g ORDER BY slug
    ");
    while ($row = $stmt->fetch()) {
        $slug = to_slug($row["slug"]);
        if ($slug && !in_array($slug, $blocked)) {
            add_url($urls, "{$base}/games/{$slug}", "weekly", "0.6");
        }
    }
} catch (Exception $e) {}

// Dynamic patch pages (games that have patch notes)
try {
    $stmt = db()->query("
        SELECT DISTINCT LOWER(TRIM(game_name)) AS slug FROM patch_notes
        WHERE game_name IS NOT NULL AND game_name != ''
        ORDER BY slug
    ");
    while ($row = $stmt->fetch()) {
        $slug = to_slug($row["slug"]);
        if ($slug && !in_array($slug, $blocked)) {
            add_url($urls, "{$base}/patches/{$slug}", "weekly", "0.5");
        }
    }
} catch (Exception $e) {}

// Driver company pages (from VPS API)
$ctx = stream_context_create([
    "http" => ["timeout" => 5, "header" => "User-Agent: GripAi-Sitemap/1.0\r\n"],
    "ssl"  => ["verify_peer" => false, "verify_peer_name" => false]
]);
$json = @file_get_contents("https://jaffaai.co.uk/Jaffa/drivers/drivers/companies", false, $ctx);
if ($json) {
    $companies = json_decode($json, true);
    if (is_array($companies)) {
        foreach ($companies as $c) {
            $name = is_array($c) ? ($c["company"] ?? "") : $c;
            $slug = to_slug($name);
            if ($slug) {
                add_url($urls, "{$base}/drivers/{$slug}", "weekly", "0.6");
            }
        }
    }
}

// Website category pages (from VPS API)
$json2 = @file_get_contents("https://jaffaai.co.uk/monitor/websites/stats", false, $ctx);
if ($json2) {
    $ws_data = json_decode($json2, true);
    if (is_array($ws_data) && isset($ws_data["byCategory"])) {
        foreach ($ws_data["byCategory"] as $cat) {
            $slug = to_slug($cat["category"] ?? "");
            if ($slug) {
                add_url($urls, "{$base}/websites/{$slug}", "hourly", "0.6");
            }
        }
    }
}

echo "</urlset>\n";
