<?php
/**
 * GripNews Blog Sitemap — Dynamic XML sitemap for blog posts
 * Serves latest 5000 blog posts for search engine indexing
 * URL: /blog-sitemap.xml
 */
header('Content-Type: application/xml; charset=UTF-8');
header('Cache-Control: public, max-age=3600');
header('X-Robots-Tag: noindex');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php
// Fetch recent blog posts from API
$pages_to_fetch = 50; // 50 × 100 = 5000 posts
$per = 100;

for ($p = 0; $p < $pages_to_fetch; $p++) {
    $offset = $p * $per;
    $ctx = stream_context_create([
        'http' => ['timeout' => 10, 'header' => "Accept: application/json\r\n"],
        'ssl'  => ['verify_peer' => false]
    ]);
    $url = "https://gripai.uk/api/blog?limit={$per}&offset={$offset}";
    $json = @file_get_contents($url, false, $ctx);
    if (!$json) break;
    $data = json_decode($json, true);
    if (empty($data['posts'])) break;

    foreach ($data['posts'] as $post) {
        $slug = htmlspecialchars($post['slug'], ENT_XML1, 'UTF-8');
        $date = !empty($post['created_at']) ? date('Y-m-d', strtotime($post['created_at'])) : date('Y-m-d');
        echo "  <url>\n";
        echo "    <loc>https://gripnews.uk/blog/{$slug}</loc>\n";
        echo "    <lastmod>{$date}</lastmod>\n";
        echo "    <changefreq>monthly</changefreq>\n";
        echo "    <priority>0.6</priority>\n";
        echo "  </url>\n";
    }

    if (count($data['posts']) < $per) break; // Last page
}
?>
</urlset>
