<?php
/**
 * WordPress Redis Configuration
 * This file contains Redis-specific configurations for WordPress
 */

// Redis Object Cache Configuration
define('WP_REDIS_HOST', $_ENV['REDIS_HOST'] ?? 'redis');
define('WP_REDIS_PORT', $_ENV['REDIS_PORT'] ?? 6379);
define('WP_REDIS_PASSWORD', $_ENV['REDIS_PASSWORD'] ?? '');
define('WP_REDIS_DATABASE', 0);
define('WP_REDIS_TIMEOUT', 1);
define('WP_REDIS_READ_TIMEOUT', 1);

// Cache settings
define('WP_CACHE', true);
define('WP_CACHE_KEY_SALT', 'inception_');

// Redis connection settings
define('WP_REDIS_SELECTIVE_FLUSH', true);
define('WP_REDIS_MAXTTL', 86400); // 24 hours

// Debugging (set to false in production)
define('WP_REDIS_DISABLE_FAILBACK', false);
define('WP_REDIS_DISABLE_COMMENT', false);
?>
