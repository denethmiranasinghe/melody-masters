<?php
require_once 'includes/functions.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

try {
    if ($category_id) {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                               FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.category_id = ?
                               ORDER BY p.name ASC");
        $stmt->execute([$category_id]);
    } else {
        $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             ORDER BY p.name ASC");
    }
    $products = $stmt->fetchAll();

    $catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();
} catch(PDOException $e) {
    $products = [];
    $categories = [];
}

require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar">
            <div class="dashboard-sidebar-header">
                <h3>Categories</h3>
            </div>
            <ul>
                <li>
                    <a href="/melody-masters/shop.php" class="<?php echo !$category_id ? 'active' : ''; ?>">
                        <i class="ph ph-squares-four"></i> All Products
                    </a>
                </li>
                <?php foreach($categories as $cat): ?>
                    <li>
                        <a href="/melody-masters/shop.php?category=<?php echo $cat['id']; ?>" class="<?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                            <i class="ph ph-folder"></i> <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <section class="dashboard-content" style="border: none; padding: 0; background: transparent;">
            <div class="dashboard-header" style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); margin-bottom: 2rem;">
                <h1 style="margin: 0; font-size: 1.5rem;">Shop Instruments</h1>
            </div>
            
            <?php if(empty($products)): ?>
                <div class="alert alert-warning" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; background: var(--surface-color); border-color: var(--border-color);">
                    <i class="ph ph-magnifying-glass" style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No Products Found</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">We couldn't find any instruments in this category right now.</p>
                    <a href="/melody-masters/shop.php" class="btn btn-primary">View All Products</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach($products as $product): ?>
                        <div class="product-card">
                            <a href="/melody-masters/product.php?id=<?php echo $product['id']; ?>" class="product-image-wrapper">
                                <img src="/melody-masters/assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='/melody-masters/assets/images/accessories.jpeg'">
                                <?php if($product['is_digital']): ?>
                                    <span class="product-badge">Digital</span>
                                <?php endif; ?>
                            </a>
                            <div class="product-details">
                                <span class="category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                                <h3><a href="/melody-masters/product.php?id=<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                
                                <div class="price-row">
                                    <span class="price"><?php echo formatPrice($product['price']); ?></span>
                                    <?php if($product['stock'] > 0 || $product['is_digital']): ?>
                                        <span class="stock-status stock-in"><i class="ph ph-check-circle"></i> In Stock</span>
                                    <?php else: ?>
                                        <span class="stock-status stock-out"><i class="ph ph-x-circle"></i> Out of Stock</span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-actions">
                                    <a href="/melody-masters/product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">View Details</a>
                                    <?php if($product['stock'] > 0 || $product['is_digital']): ?>
                                        <form action="/melody-masters/cart_action.php" method="POST" style="flex: 1; display: flex;">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="ph ph-shopping-cart-simple"></i> Add</button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled style="flex: 1;">Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
