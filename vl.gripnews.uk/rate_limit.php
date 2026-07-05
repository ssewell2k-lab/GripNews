<?php
/**
 * Simple file-based rate limiter for cPanel (no Redis).
 * Usage: require_once 'rate_limit.php'; check_rate_limit('search', 20, 60);
 */
function check_rate_limit($action = 'default', $max_requests = 20, $window_seconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = md5($ip . ':' . $action);
    $dir = sys_get_temp_dir() . '/vault_ratelimit';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $file = "$dir/$key";
    
    $now = time();
    $hits = [];
    
    if (file_exists($file)) {
        $data = @file_get_contents($file);
        $hits = $data ? json_decode($data, true) : [];
        if (!is_array($hits)) $hits = [];
        // Prune old entries
        $hits = array_filter($hits, fn($t) => ($now - $t) < $window_seconds);
    }
    
    if (count($hits) >= $max_requests) {
        return false; // Rate limited
    }
    
    $hits[] = $now;
    @file_put_contents($file, json_encode(array_values($hits)), LOCK_EX);
    return true;
}

// Scraper detection
function is_scraper() {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $blocklist = ['scrapy', 'python-requests', 'httpclient', 'curl/', 'wget/', 
                  'java/', 'libwww', 'lwp-', 'mechanize', 'ahrefs', 'semrush',
                  'mj12bot', 'dotbot', 'rogerbot', 'seokicks', 'blexbot'];
    foreach ($blocklist as $bot) {
        if (strpos($ua, $bot) !== false) return true;
    }
    return false;
}
?>
