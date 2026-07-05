<?php
// One-time vault setup - creates all vault tables
$host = 'localhost';
$db   = 'gripzcxe_vault';
$user = 'gripzcxe_admin';
$pass = 'REDACTED_DB_PASSWORD';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$tables = [
    "CREATE TABLE IF NOT EXISTS vault_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source_table VARCHAR(100) NOT NULL,
        rows_archived INT DEFAULT 0,
        last_archive_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        archive_threshold_days INT DEFAULT 7,
        total_rows INT DEFAULT 0,
        UNIQUE KEY uk_source (source_table)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS crawl_raw_logs (
        id INT NOT NULL,
        target_id INT DEFAULT NULL,
        source VARCHAR(50) NOT NULL,
        url VARCHAR(500) NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        snippet TEXT,
        author VARCHAR(100) DEFAULT NULL,
        score INT DEFAULT 0,
        relevance_score FLOAT DEFAULT 0,
        is_fix TINYINT(1) DEFAULT 0,
        source_created_at TIMESTAMP NULL,
        raw_payload JSON DEFAULT NULL,
        created_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_source (source),
        KEY idx_created (created_at),
        KEY idx_vaulted (vaulted_at),
        FULLTEXT KEY ft_search (title, snippet)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS cluster_trends (
        id BIGINT NOT NULL,
        cluster_id BIGINT NOT NULL,
        trend_direction VARCHAR(50) NOT NULL,
        confidence_velocity FLOAT DEFAULT 0,
        evidence_velocity FLOAT DEFAULT 0,
        regression_risk FLOAT DEFAULT 0,
        recurring_pattern VARCHAR(100) DEFAULT 'none',
        decay_factor FLOAT DEFAULT 0,
        operational_risk FLOAT DEFAULT 0,
        analysis_summary TEXT,
        analyzed_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cluster (cluster_id),
        KEY idx_analyzed (analyzed_at),
        KEY idx_vaulted (vaulted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS evidence (
        id INT NOT NULL,
        issue_id INT NOT NULL,
        cluster_id INT DEFAULT NULL,
        type VARCHAR(50) NOT NULL,
        source VARCHAR(255) DEFAULT NULL,
        source_trust_id INT DEFAULT NULL,
        content TEXT,
        quality DECIMAL(3,2) DEFAULT 0.50,
        trust_score FLOAT DEFAULT 0.5,
        corroboration_count INT DEFAULT 0,
        freshness_days INT DEFAULT NULL,
        computed_weight FLOAT DEFAULT 0.5,
        raw_log_id INT DEFAULT NULL,
        agent_run_id BIGINT DEFAULT NULL,
        supports_conclusion TINYINT(1) DEFAULT 1,
        contradicts_conclusion TINYINT(1) DEFAULT 0,
        metadata JSON DEFAULT NULL,
        created_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_issue (issue_id),
        KEY idx_cluster (cluster_id),
        KEY idx_type (type),
        KEY idx_created (created_at),
        FULLTEXT KEY ft_search (content)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT NOT NULL,
        issue_id INT DEFAULT NULL,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(100) NOT NULL,
        excerpt TEXT,
        content LONGTEXT,
        meta JSON DEFAULT NULL,
        seo JSON DEFAULT NULL,
        status VARCHAR(20) DEFAULT 'draft',
        published_at TIMESTAMP NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_slug (slug),
        KEY idx_status (status),
        KEY idx_created (created_at),
        FULLTEXT KEY ft_search (title, excerpt)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS cluster_history (
        id BIGINT NOT NULL,
        cluster_id INT NOT NULL,
        issue_count INT DEFAULT 0,
        confidence DECIMAL(3,2) DEFAULT 0.40,
        evidence_count INT DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        report_velocity DECIMAL(5,2) DEFAULT 0.00,
        snapshot_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cluster (cluster_id),
        KEY idx_snapshot (snapshot_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS patch_notes (
        id INT NOT NULL,
        game_name VARCHAR(100) NOT NULL,
        version VARCHAR(50) DEFAULT NULL,
        title VARCHAR(255) NOT NULL,
        source VARCHAR(50) DEFAULT 'steam_news',
        source_url VARCHAR(500) DEFAULT NULL,
        raw_content TEXT,
        release_date TIMESTAMP NULL,
        bug_fixes JSON DEFAULT NULL,
        new_features JSON DEFAULT NULL,
        balance_changes JSON DEFAULT NULL,
        known_issues JSON DEFAULT NULL,
        affected_systems JSON DEFAULT NULL,
        regression_risk FLOAT DEFAULT 0,
        analysis_notes TEXT,
        crawled_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_game (game_name),
        KEY idx_release (release_date),
        FULLTEXT KEY ft_search (title, raw_content, analysis_notes)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS issues (
        id INT NOT NULL,
        fingerprint_id INT DEFAULT NULL,
        cluster_id INT DEFAULT NULL,
        raw_text TEXT NOT NULL,
        game_name VARCHAR(100) DEFAULT NULL,
        category VARCHAR(100) DEFAULT 'unknown',
        severity VARCHAR(50) DEFAULT 'medium',
        summary TEXT,
        confidence FLOAT DEFAULT 0,
        certainty VARCHAR(20) DEFAULT 'suspected',
        ai_payload JSON DEFAULT NULL,
        pipeline_status VARCHAR(50) DEFAULT 'pending',
        source VARCHAR(50) DEFAULT 'manual',
        state VARCHAR(20) DEFAULT 'new',
        pipeline_error TEXT,
        pipeline_attempts INT DEFAULT 0,
        diagnosed_at TIMESTAMP NULL,
        crawled_at TIMESTAMP NULL,
        enriched_at TIMESTAMP NULL,
        blogged_at TIMESTAMP NULL,
        crawl_count INT DEFAULT 0,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        priority_score DECIMAL(4,3) DEFAULT 0.500,
        priority_reason VARCHAR(255) DEFAULT NULL,
        last_activity TIMESTAMP NULL,
        report_velocity INT DEFAULT 1,
        patch_resolved_by INT DEFAULT NULL,
        patch_notes TEXT,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_category (category),
        KEY idx_severity (severity),
        KEY idx_game (game_name),
        KEY idx_state (state),
        KEY idx_created (created_at),
        FULLTEXT KEY ft_search (game_name, summary, raw_text)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS clusters (
        id INT NOT NULL,
        label VARCHAR(255) NOT NULL,
        category VARCHAR(64) NOT NULL,
        status VARCHAR(20) DEFAULT 'active',
        issue_count INT DEFAULT 0,
        confidence DECIMAL(3,2) DEFAULT 0.40,
        known_fixes JSON DEFAULT NULL,
        common_causes JSON DEFAULT NULL,
        affected_games JSON DEFAULT NULL,
        affected_patches JSON DEFAULT NULL,
        created_at DATETIME NULL,
        updated_at DATETIME NULL,
        last_report_at TIMESTAMP NULL,
        report_velocity DECIMAL(5,2) DEFAULT 0.00,
        peak_issue_count INT DEFAULT 0,
        resolved_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_category (category),
        KEY idx_status (status),
        FULLTEXT KEY ft_search (label)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS cluster_recommendations (
        id BIGINT NOT NULL,
        cluster_id BIGINT NOT NULL,
        recommendation_type VARCHAR(100) NOT NULL,
        recommendation TEXT NOT NULL,
        confidence FLOAT DEFAULT 0,
        supporting_evidence INT DEFAULT 0,
        generated_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cluster (cluster_id),
        KEY idx_type (recommendation_type),
        FULLTEXT KEY ft_search (recommendation)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS cluster_risk (
        id BIGINT NOT NULL,
        cluster_id BIGINT NOT NULL,
        risk_score FLOAT DEFAULT 0,
        priority_level VARCHAR(50) DEFAULT 'low',
        blast_radius FLOAT DEFAULT 0,
        escalation_required TINYINT DEFAULT 0,
        stability_impact FLOAT DEFAULT 0,
        components JSON DEFAULT NULL,
        analyzed_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cluster (cluster_id),
        KEY idx_risk (risk_score)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS pipeline_jobs (
        id INT NOT NULL,
        issue_id INT NOT NULL,
        stage VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        duration_ms INT DEFAULT NULL,
        error_message TEXT,
        created_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_issue (issue_id),
        KEY idx_stage (stage),
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS confidence_log (
        id INT NOT NULL,
        entity_type VARCHAR(20) NOT NULL,
        entity_id INT NOT NULL,
        old_confidence DECIMAL(3,2) DEFAULT NULL,
        new_confidence DECIMAL(3,2) NOT NULL,
        reason VARCHAR(255) NOT NULL,
        evidence JSON DEFAULT NULL,
        created_at DATETIME NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_entity (entity_type, entity_id),
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS issue_timeline (
        id INT NOT NULL,
        issue_id INT NOT NULL,
        old_state VARCHAR(20) DEFAULT NULL,
        new_state VARCHAR(20) NOT NULL,
        reason VARCHAR(255) NOT NULL,
        metadata JSON DEFAULT NULL,
        created_at DATETIME NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_issue (issue_id),
        KEY idx_state (new_state),
        KEY idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS cluster_fingerprints (
        cluster_id INT NOT NULL,
        fingerprint_id INT NOT NULL,
        added_at DATETIME NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (cluster_id, fingerprint_id),
        KEY idx_fp (fingerprint_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS crawl_results (
        id INT NOT NULL,
        issue_id INT NOT NULL,
        source VARCHAR(50) NOT NULL,
        title VARCHAR(255) DEFAULT NULL,
        url VARCHAR(500) NOT NULL,
        snippet TEXT,
        score INT DEFAULT 0,
        signal_weight FLOAT DEFAULT 0.3,
        raw_payload JSON DEFAULT NULL,
        created_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_issue (issue_id),
        KEY idx_source (source),
        FULLTEXT KEY ft_search (title, snippet)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS website_checks (
        id BIGINT NOT NULL,
        website_id INT NOT NULL,
        status_code INT DEFAULT NULL,
        response_time_ms INT DEFAULT NULL,
        is_up TINYINT(1) DEFAULT 1,
        status_page_status VARCHAR(50) DEFAULT NULL,
        status_page_desc VARCHAR(255) DEFAULT NULL,
        error_message VARCHAR(255) DEFAULT NULL,
        checked_at TIMESTAMP NULL,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_website (website_id),
        KEY idx_checked (checked_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS fingerprints (
        id INT NOT NULL,
        hash VARCHAR(64) NOT NULL,
        label VARCHAR(128) NOT NULL,
        canonical_sig VARCHAR(128) DEFAULT NULL,
        game_engine VARCHAR(50) DEFAULT NULL,
        root_cause VARCHAR(100) DEFAULT NULL,
        variant VARCHAR(100) DEFAULT NULL,
        is_canonical TINYINT(1) DEFAULT 0,
        category VARCHAR(64) NOT NULL,
        subcategory VARCHAR(64) DEFAULT NULL,
        severity DECIMAL(3,2) DEFAULT 0.50,
        first_seen DATETIME NULL,
        last_seen DATETIME NULL,
        hit_count INT DEFAULT 1,
        vaulted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_hash (hash),
        KEY idx_category (category),
        FULLTEXT KEY ft_search (label)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$results = [];
foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
        // Extract table name
        preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $m);
        $results[] = ['table' => $m[1] ?? '?', 'status' => 'ok'];
    } catch (Exception $e) {
        preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/', $sql, $m);
        $results[] = ['table' => $m[1] ?? '?', 'status' => 'error', 'msg' => $e->getMessage()];
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'tables_created' => $results], JSON_PRETTY_PRINT);
?>