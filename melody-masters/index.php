<?php
require_once 'includes/functions.php';

// Fetch recent products
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY p.id DESC LIMIT 8");
    $recent_products = $stmt->fetchAll();
} catch(PDOException $e) {
    $recent_products = [];
}

require_once 'includes/header.php';
?>

<div class="container">
    <section class="hero">
        <div class="hero-content">
            <h2>Discover Your Sound</h2>
            <p>Premium instruments for musicians of all levels.</p>
            <a href="/melody-masters/shop.php" class="btn btn-primary">Shop Now</a>
        </div>
    </section>

    <section class="featured-products">
        <h2 class="section-title">New Arrivals</h2>
        
        <?php if(empty($recent_products)): ?>
            <div class="alert alert-warning">
                <i class="ph ph-warning-circle" style="font-size: 1.2rem;"></i>
                No products available at the moment.
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($recent_products as $product): ?>
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

<?php require_once 'includes/footer.php'; ?>
