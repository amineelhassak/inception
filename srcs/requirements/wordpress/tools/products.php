<?php
/**
 * Standalone Products Page
 * Direct PHP access to the products dashboard
 */

// Database connection details
$db_host = $_ENV['DB_HOST'] ?? 'mariadb:3306';
$db_name = $_ENV['DATABASE_NAME'] ?? 'db';
$db_user = $_ENV['DB_USER'] ?? 'amine';
$db_pass = $_ENV['DB_PASSWORD'] ?? 'amine1337';

// Redis connection
function get_redis_connection() {
    static $redis = null;
    
    if ($redis === null) {
        try {
            if (class_exists('Redis')) {
                $redis = new Redis();
                $redis->connect($_ENV['REDIS_HOST'] ?? 'redis', $_ENV['REDIS_PORT'] ?? 6379);
                
                if (!empty($_ENV['REDIS_PASSWORD'])) {
                    $redis->auth($_ENV['REDIS_PASSWORD']);
                }
                
                // Test connection
                $redis->ping();
                return $redis;
            }
        } catch (Exception $e) {
            error_log("Redis error: " . $e->getMessage());
            $redis = false;
        }
    }
    
    return $redis;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'clear_cache') {
        $redis = get_redis_connection();
        if ($redis && $redis !== false) {
            try {
                // Clear ALL products-related cache keys and force a fresh database fetch
                $keys = $redis->keys('products:*');
                if (!empty($keys)) {
                    $redis->del($keys);
                }
                // Also flush all cache to ensure complete clearing
                $redis->flushdb();
                
                // Now measure loading time from database (no cache)
                $start_time = microtime(true);
                $result = get_products(); // This should fetch from database now
                $end_time = microtime(true);
                $loading_time = ($end_time - $start_time) * 1000;
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cache Redis vidé avec succès!',
                    'loading_time' => number_format($loading_time, 2),
                    'from_cache' => false
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Erreur lors du nettoyage du cache']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Redis non disponible']);
        }
        exit;
    }
    
    if ($_GET['action'] === 'refresh_products') {
        $start_time = microtime(true);
        
        // Clear cache and fetch fresh data
        $redis = get_redis_connection();
        if ($redis && $redis !== false) {
            $redis->del('products:list');
        }
        
        $result = get_products();
        $products = $result['data'];
        
        $end_time = microtime(true);
        $loading_time = ($end_time - $start_time) * 1000;
        echo json_encode([
            'success' => true, 
            'count' => count($products), 
            'loading_time' => number_format($loading_time, 2),
            'from_cache' => $result['from_cache'],
            'message' => 'Produits actualisés!'
        ]);
        exit;
    }
    
    if ($_GET['action'] === 'check_status') {
        $start_time = microtime(true);
        $result = get_products();
        $end_time = microtime(true);
        $loading_time = ($end_time - $start_time) * 1000;
        
        echo json_encode([
            'success' => true,
            'loading_time' => number_format($loading_time, 2),
            'from_cache' => $result['from_cache'],
            'count' => count($result['data'])
        ]);
        exit;
    }
}

// Get products from database with Redis caching
function get_products() {
    global $db_host, $db_name, $db_user, $db_pass;
    
    $redis = get_redis_connection();
    $cache_key = 'products:list';
    $from_cache = false;
    
    // Try to get from Redis cache first
    if ($redis && $redis !== false) {
        try {
            $cached_data = $redis->get($cache_key);
            if ($cached_data) {
                $from_cache = true;
                return [
                    'data' => json_decode($cached_data, true),
                    'from_cache' => $from_cache
                ];
            }
        } catch (Exception $e) {
            error_log("Redis get error: " . $e->getMessage());
        }
    }
    
    // If not in cache, fetch from database
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Store in Redis cache for 5 minutes
        if ($redis && $redis !== false && !empty($products)) {
            try {
                $redis->setex($cache_key, 300, json_encode($products));
            } catch (Exception $e) {
                error_log("Redis set error: " . $e->getMessage());
            }
        }
        
        return [
            'data' => $products,
            'from_cache' => $from_cache
        ];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [
            'data' => array(),
            'from_cache' => $from_cache
        ];
    }
}

// Calculate loading time
$start_time = microtime(true);

$result = get_products();
$products = $result['data'];
$is_cached = $result['from_cache'];

$total_products = count($products);
$total_stock = array_sum(array_column($products, 'stock'));
$total_value = array_sum(array_map(function($p) { return $p['price'] * $p['stock']; }, $products));
$categories = array_unique(array_column($products, 'category'));

// Calculate actual loading time
$end_time = microtime(true);
$loading_time = ($end_time - $start_time) * 1000; // Convert to milliseconds

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notre Boutique - Inception</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .products-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
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
            flex-wrap: wrap;
            gap: 15px;
        }
        .stat-item {
            text-align: center;
            flex: 1;
            min-width: 100px;
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
            flex-wrap: wrap;
            gap: 10px;
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
            transition: all 0.3s ease;
        }
        .cache-indicator.from-cache {
            background: #27ae60; /* Green for cached */
        }
        .cache-indicator.from-database {
            background: #e74c3c; /* Red for database/cleared cache */
        }
        .loading {
            text-align: center;
            padding: 50px;
            font-size: 1.2rem;
            color: #666;
        }
        @media (max-width: 768px) {
            .stats-bar {
                flex-direction: column;
                text-align: center;
            }
            .dashboard-title {
                font-size: 2rem;
            }
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
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
                    <div class="stat-value"><?php echo number_format($loading_time, 2); ?>ms</div>
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
                <div class="cache-indicator <?php echo $is_cached ? 'from-cache' : 'from-database'; ?>">
                    <?php if ($is_cached): ?>
                        ✅ Données depuis Redis Cache
                    <?php else: ?>
                        📀 Données depuis Base de Données
                    <?php endif; ?>
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
                <?php if (empty($products)): ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px;">
                        <h3>🔄 Chargement des produits...</h3>
                        <p>Si les produits ne s'affichent pas, vérifiez la connexion à la base de données.</p>
                        <a href="/test-db.php" class="btn btn-primary">🔧 Tester la base de données</a>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Function to show notifications
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                z-index: 1000;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                font-weight: bold;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Function to refresh products
        function refreshProducts() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Actualisation...';
            
            const startTime = performance.now();
            
            fetch('?action=refresh_products')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const clientTime = performance.now() - startTime;
                        showNotification(data.message + ' (' + data.count + ' produits, ' + data.loading_time + 'ms serveur, ' + clientTime.toFixed(2) + 'ms client)');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('Erreur lors de l\'actualisation', 'error');
                    }
                })
                .catch(error => {
                    showNotification('Erreur de connexion', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '🔄 Actualiser les products';
                });
        }
        
        // Function to clear cache
        function clearCache() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '⏳ Nettoyage...';
            
            const startTime = performance.now();
            
            fetch('?action=clear_cache')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const clientTime = performance.now() - startTime;
                        
                        // Update loading time display immediately
                        const loadingTimeElement = document.querySelector('.stat-value');
                        if (loadingTimeElement) {
                            loadingTimeElement.textContent = data.loading_time + 'ms';
                        }
                        
                        // Update cache indicator to show database source
                        const cacheIndicator = document.querySelector('.cache-indicator');
                        if (cacheIndicator) {
                            cacheIndicator.innerHTML = '📀 Données depuis Base de Données';
                            cacheIndicator.style.background = '#e74c3c'; // Red to show cache cleared
                        }
                        
                        showNotification(data.message + ' (Temps: ' + data.loading_time + 'ms serveur, ' + clientTime.toFixed(2) + 'ms client)');
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Erreur de connexion', 'error');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '🗑️ Clear Cache';
                });
        }
        
        // Function to update status after cache operations
        function updateStatus() {
            fetch('?action=check_status')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update loading time display
                        const loadingTimeElement = document.querySelector('.stat-value');
                        if (loadingTimeElement) {
                            loadingTimeElement.textContent = data.loading_time + 'ms';
                        }
                        
                        // Update cache indicator
                        const cacheIndicator = document.querySelector('.cache-indicator');
                        if (cacheIndicator) {
                            if (data.from_cache) {
                                cacheIndicator.innerHTML = '✅ Données depuis Redis Cache';
                                cacheIndicator.style.background = '#27ae60';
                            } else {
                                cacheIndicator.innerHTML = '📀 Données depuis Base de Données';
                                cacheIndicator.style.background = '#3498db';
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la mise à jour du statut:', error);
                });
        }
        
        // Auto-refresh every 30 seconds
        setInterval(function() {
            console.log('Auto-refreshing data...');
        }, 30000);
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Products dashboard loaded successfully!');
            console.log('📊 Total products:', <?php echo $total_products; ?>);
        });
    </script>
</body>
</html>
