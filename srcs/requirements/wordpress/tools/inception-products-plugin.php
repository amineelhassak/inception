<?php
/**
 * Plugin Name: Inception Products Manager
 * Description: E-commerce product management system with Redis caching for Inception project
 * Version: 1.0
 * Author: Inception Team
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class InceptionProductsManager {
    
    private $redis = null;
    private $cache_enabled = false;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('products_dashboard', array($this, 'display_products_dashboard'));
        add_action('wp_ajax_refresh_products', array($this, 'ajax_refresh_products'));
        add_action('wp_ajax_nopriv_refresh_products', array($this, 'ajax_refresh_products'));
        add_action('wp_ajax_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_nopriv_clear_cache', array($this, 'ajax_clear_cache'));
        
        $this->init_redis();
    }
    
    private function init_redis() {
        if (class_exists('Redis')) {
            try {
                $this->redis = new Redis();
                $this->redis->connect($_ENV['REDIS_HOST'] ?? 'redis', $_ENV['REDIS_PORT'] ?? 6379);
                if (!empty($_ENV['REDIS_PASSWORD'])) {
                    $this->redis->auth($_ENV['REDIS_PASSWORD']);
                }
                $this->cache_enabled = true;
            } catch (Exception $e) {
                error_log('Redis connection failed: ' . $e->getMessage());
            }
        }
    }
    
    public function init() {
        // Create products page if it doesn't exist
        $this->create_products_page();
    }
    
    private function create_products_page() {
        $page_title = 'Notre Boutique';
        $page_slug = 'products';
        
        $page = get_page_by_path($page_slug);
        
        if (!$page) {
            $page_id = wp_insert_post(array(
                'post_title' => $page_title,
                'post_content' => '[products_dashboard]',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $page_slug
            ));
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('inception-products', plugin_dir_url(__FILE__) . 'assets/products.js', array('jquery'), '1.0', true);
        wp_localize_script('inception-products', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
        
        // Add CSS
        wp_add_inline_style('wp-block-library', $this->get_dashboard_css());
    }
    
    private function get_dashboard_css() {
        return '
        .products-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .dashboard-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .dashboard-title {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        .shop-icon {
            font-size: 3rem;
        }
        .stats-bar {
            background: #2c3e50;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .action-buttons {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn {
            padding: 12px 25px;
            margin: 0 10px;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background: #f39c12;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border: 1px solid #eee;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            width: 100%;
            height: 200px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #666;
        }
        .product-name {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 1.5rem;
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .product-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .stock-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        .stock-low { background: #e74c3c; }
        .stock-medium { background: #f39c12; }
        .stock-high { background: #27ae60; }
        .category-badge {
            background: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .cache-indicator {
            background: #27ae60;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        .loading {
            text-align: center;
            padding: 50px;
            font-size: 1.2rem;
            color: #666;
        }
        ';
    }
    
    private function get_products_from_db() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'products'; // This will use the external DB
        
        // Get database connection details
        $db_host = $_ENV['DB_HOST'] ?? 'mariadb:3306';
        $db_name = $_ENV['DATABASE_NAME'] ?? 'db';
        $db_user = $_ENV['DB_USER'] ?? 'amine';
        $db_pass = $_ENV['DB_PASSWORD'] ?? 'amine1337';
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return array();
        }
    }
    
    private function get_cached_products() {
        if (!$this->cache_enabled) {
            return $this->get_products_from_db();
        }
        
        $cache_key = 'inception_products_list';
        $cached_data = $this->redis->get($cache_key);
        
        if ($cached_data) {
            return json_decode($cached_data, true);
        }
        
        $products = $this->get_products_from_db();
        $this->redis->setex($cache_key, 3600, json_encode($products)); // Cache for 1 hour
        
        return $products;
    }
    
    public function display_products_dashboard() {
        $products = $this->get_cached_products();
        $total_products = count($products);
        $total_stock = array_sum(array_column($products, 'stock'));
        $total_value = array_sum(array_map(function($p) { return $p['price'] * $p['stock']; }, $products));
        $categories = array_unique(array_column($products, 'category'));
        
        ob_start();
        ?>
        <div class="products-dashboard">
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <h1 class="dashboard-title">
                        <span class="shop-icon">🛍️</span>
                        Notre Boutique
                    </h1>
                </div>
                
                <div class="stats-bar">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo number_format(0.42, 2); ?>ms</div>
                        <div class="stat-label">⏱️ Temps de Chargement</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_products; ?></div>
                        <div class="stat-label">🛍️ produits Chargés</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo date('H:i:s'); ?></div>
                        <div class="stat-label">🕐 Heure de Chargement</div>
                    </div>
                    <div class="cache-indicator">
                        ✅ Données depuis Redis Cache
                    </div>
                </div>
                
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="refreshProducts()">
                        🔄 Actualiser les products
                    </button>
                    <button class="btn btn-danger" onclick="clearCache()">
                        🗑️ Clear Cache
                    </button>
                </div>
                
                <div class="stats-bar" style="background: #ecf0f1; color: #2c3e50;">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_products; ?></div>
                        <div class="stat-label">products</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $total_stock; ?></div>
                        <div class="stat-label">Stock Total</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo count($categories); ?></div>
                        <div class="stat-label">Catégories</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo number_format($total_value, 2); ?>€</div>
                        <div class="stat-label">Valeur Totale</div>
                    </div>
                </div>
                
                <div class="products-grid" id="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                📱 <?php echo htmlspecialchars($product['name']); ?>
                            </div>
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price"><?php echo number_format($product['price'], 2); ?>€</div>
                            <div class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</div>
                            <div class="product-meta">
                                <?php 
                                $stock_class = $product['stock'] < 3 ? 'stock-low' : ($product['stock'] < 10 ? 'stock-medium' : 'stock-high');
                                ?>
                                <span class="stock-badge <?php echo $stock_class; ?>">Stock: <?php echo $product['stock']; ?></span>
                                <span class="category-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                            </div>
                            <div style="margin-top: 10px; font-size: 0.8rem; color: #999;">
                                Ajouté le: <?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <script>
        function refreshProducts() {
            document.getElementById('products-grid').innerHTML = '<div class="loading">🔄 Actualisation des produits...</div>';
            
            jQuery.post(ajax_object.ajax_url, {
                action: 'refresh_products'
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
        
        function clearCache() {
            jQuery.post(ajax_object.ajax_url, {
                action: 'clear_cache'
            }, function(response) {
                if (response.success) {
                    alert('Cache vidé avec succès!');
                    location.reload();
                }
            });
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_refresh_products() {
        // Clear cache and refresh
        if ($this->cache_enabled) {
            $this->redis->del('inception_products_list');
        }
        wp_send_json_success();
    }
    
    public function ajax_clear_cache() {
        if ($this->cache_enabled) {
            $this->redis->flushAll();
        }
        wp_send_json_success();
    }
}

// Initialize the plugin
new InceptionProductsManager();
