<?php
/**
 * GripNews Blog Admin — Configuration
 * Database: gripzcxe_blog on localhost
 */

define('BLOG_DB_HOST', 'localhost');
define('BLOG_DB_NAME', 'gripzcxe_blog');
define('BLOG_DB_USER', 'gripzcxe_admin');
define('BLOG_DB_PASS', 'REDACTED_DB_PASSWORD');

// Admin password — change this after first login!
// Default: gripnews2026
define('BLOG_ADMIN_PASS_HASH', password_hash('gripnews2026', PASSWORD_DEFAULT));

// Site
define('BLOG_SITE_URL', 'https://gripnews.uk');

function blog_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . BLOG_DB_HOST . ';dbname=' . BLOG_DB_NAME . ';charset=utf8mb4',
            BLOG_DB_USER,
            BLOG_DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function blog_db_setup(): void {
    $db = blog_db();
    $db->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(500) NOT NULL,
        slug VARCHAR(500) NOT NULL,
        excerpt TEXT,
        content LONGTEXT,
        category VARCHAR(100) DEFAULT 'article',
        status ENUM('draft','published') DEFAULT 'draft',
        author VARCHAR(100) DEFAULT 'GripAi',
        published_at DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY idx_slug (slug(191)),
        KEY idx_status (status),
        KEY idx_published (published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS admin_config (
        config_key VARCHAR(100) PRIMARY KEY,
        config_value TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Set default password hash if not exists
    $stmt = $db->prepare("SELECT config_value FROM admin_config WHERE config_key = 'password_hash'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $hash = password_hash('gripnews2026', PASSWORD_DEFAULT);
        $stmt2 = $db->prepare("INSERT INTO admin_config (config_key, config_value) VALUES ('password_hash', ?)");
        $stmt2->execute([$hash]);
    }
}

function blog_admin_verify_password(string $password): bool {
    $db = blog_db();
    $stmt = $db->prepare("SELECT config_value FROM admin_config WHERE config_key = 'password_hash'");
    $stmt->execute();
    $row = $stmt->fetch();
    if (!$row) return false;
    return password_verify($password, $row['config_value']);
}

function blog_admin_change_password(string $new_password): void {
    $db = blog_db();
    $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("REPLACE INTO admin_config (config_key, config_value) VALUES ('password_hash', ?)");
    $stmt->execute([$hash]);
}

function blog_make_slug(string $title): string {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    return trim($slug, '-');
}
