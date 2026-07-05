<?php
/**
 * GripNews.uk — Dynamic Sitemap
 * Generates comprehensive sitemap with all pages, categories, games, and stories
 */
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
require_once __DIR__ . '/includes/functions.php';
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

function sitemap_url($loc, $lastmod, $changefreq, $priority) {
    $loc = htmlspecialchars($loc, ENT_XML1, 'UTF-8');
    echo "  <url>\n    <loc>{$loc}</loc>\n    <lastmod>{$lastmod}</lastmod>\n    <changefreq>{$changefreq}</changefreq>\n    <priority>{$priority}</priority>\n  </url>\n";
}

$today = date('Y-m-d');
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
// ── Core pages ──
sitemap_url(SITE_URL, $today, 'hourly', '1.0');
sitemap_url(SITE_URL . '/archive', $today, 'daily', '0.8');
sitemap_url(SITE_URL . '/search', $today, 'weekly', '0.5');
sitemap_url(SITE_URL . '/signals', $today, 'daily', '0.8');

// ── Category pages ──
foreach (get_categories() as $slug => $cat) {
    sitemap_url(SITE_URL . '/' . $slug, $today, 'daily', '0.7');
}

// ── Intelligence pages ──
sitemap_url(SITE_URL . '/trends', $today, 'daily', '0.8');
sitemap_url(SITE_URL . '/weekly', $today, 'weekly', '0.7');
sitemap_url(SITE_URL . '/rankings', $today, 'daily', '0.8');
sitemap_url(SITE_URL . '/momentum', $today, 'daily', '0.7');
sitemap_url(SITE_URL . '/competitive', $today, 'daily', '0.8');

// ── Discovery pages ──
sitemap_url(SITE_URL . '/studios', $today, 'weekly', '0.7');
sitemap_url(SITE_URL . '/genres', $today, 'weekly', '0.6');
sitemap_url(SITE_URL . '/watchlist', $today, 'daily', '0.6');
sitemap_url(SITE_URL . '/upcoming', $today, 'daily', '0.7');
sitemap_url(SITE_URL . '/graph', $today, 'daily', '0.6');

// ── Community pages ──
sitemap_url(SITE_URL . '/sandbox', $today, 'hourly', '0.7');
sitemap_url(SITE_URL . '/chatbox', $today, 'hourly', '0.7');
sitemap_url(SITE_URL . '/blog', $today, 'hourly', '0.9');

// ── Info pages ──
sitemap_url(SITE_URL . '/about', '2026-06-12', 'monthly', '0.5');
sitemap_url(SITE_URL . '/accuracy', $today, 'weekly', '0.5');
sitemap_url(SITE_URL . '/developers', $today, 'weekly', '0.5');
sitemap_url(SITE_URL . '/privacy', '2026-06-12', 'yearly', '0.3');
sitemap_url(SITE_URL . '/terms', '2026-06-12', 'yearly', '0.3');

// ── Game intelligence pages ──
$_game_json = @file_get_contents('https://gripai.uk/v2/games/slugs');
$_game_slugs = $_game_json ? (json_decode($_game_json, true) ?: []) : [];
if (empty($_game_slugs)) {
    $_game_slugs = ['apex-legends','among-us','brawl-stars','clash-royale','diablo-ii',
        'ea-sports-fc-24','ea-sports-fc-mobile','fortnite','forza-horizon-4','forza-horizon-5',
        'gran-turismo-7','mass-effect-3','minecraft','monopoly-go','mount-and-blade-ii',
        'out-of-the-park-baseball-25','resident-evil-2','roblox','royal-match','ryse-son-of-rome',
        'simcity-buildit','star-wars-battlefront-ii','star-wars-jedi-survivor','the-sims-4',
        'titan-quest','1080-snowboarding','8-ball-pool'];
}
foreach ($_game_slugs as $gs) {
    sitemap_url(SITE_URL . '/game/' . htmlspecialchars($gs, ENT_XML1, 'UTF-8'), $today, 'daily', '0.6');
}

// ── Story pages (last 90 days) ──
$dates = get_available_dates(90);
foreach ($dates as $date) {
    $signals = load_signals($date);
    foreach ($signals as $s) {
        $slug = slugify($s['title']);
        sitemap_url(SITE_URL . "/story/{$date}/{$slug}", $date, 'never', '0.6');
    }
}
?>
</urlset>
