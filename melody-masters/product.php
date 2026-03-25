<?php
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if(!$product) {
        header("Location: /melody-masters/shop.php");
        exit;
    }

    $revStmt = $pdo->prepare("SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
    $revStmt->execute([$product_id]);
    $reviews = $revStmt->fetchAll();

    $review_error = '';
    $review_success = '';
    
    // Check if user has purchased this product
    $has_purchased = false;
    if (isLoggedIn() && $_SESSION['role'] === 'Customer') {
        $stmt_check = $pdo->prepare("
            SELECT COUNT(*) FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.product_id = ? AND o.user_id = ? AND o.status = 'Completed'
        ");
        $stmt_check->execute([$product_id, $_SESSION['user_id']]);
        if ($stmt_check->fetchColumn() > 0) {
            $has_purchased = true;
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
        if (!isLoggedIn()) {
            $review_error = "You must be logged in to review.";
        } elseif (!$has_purchased) {
            $review_error = "You can only review products you have purchased and received.";
        } else {
            $rating = (int)$_POST['rating'];
            $comment = trim($_POST['comment']);
            if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
                $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$product_id, $_SESSION['user_id'], $rating, $comment])) {
                    $review_success = "Review submitted successfully.";
                    // Reload reviews
                    $revStmt->execute([$product_id]);
                    $reviews = $revStmt->fetchAll();
                } else {
                    $review_error = "Failed to submit review.";
                }
            } else {
                $review_error = "Please provide a valid rating and comment.";
            }
        }
    }

} catch(PDOException $e) {
    die("Error loading product.");
}

require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="single-product">
        <div class="single-product-gallery">
            <img src="/melody-masters/assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='/melody-masters/assets/images/accessories.jpeg'">
        </div>
        <div class="single-product-info">
            <span class="category"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></span>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="single-product-meta">
                <p><strong>Brand:</strong> <?php echo htmlspecialchars($product['brand']); ?></p>
                <?php if($product['is_digital']): ?>
                    <span class="badge badge-neutral"><i class="ph ph-download-simple"></i> Digital Download</span>
                <?php else: ?>
                    <span class="badge badge-neutral"><i class="ph ph-package"></i> Physical Product</span>
                <?php endif; ?>
            </div>
            
            <div class="price"><?php echo formatPrice($product['price']); ?></div>
            
            <?php if(!$product['is_digital']): ?>
            <div style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                <i class="ph ph-truck"></i> 
                <?php 
                if ($product['price'] >= 100) {
                    echo "<span style='color: var(--success-text);'>Free Delivery</span>";
                } else {
                    $estimated = 10.00 + (float)($product['shipping_cost'] ?? 0);
                    echo "Shipping from " . formatPrice($estimated) . " <span style='color: var(--success-text); font-size: 0.85rem; margin-left: 0.5rem;'>(Free over £100)</span>";
                }
                ?>
            </div>
            <?php else: ?>
            <div style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 500;">
                <i class="ph ph-cloud-arrow-down"></i> Instant Delivery
            </div>
            <?php endif; ?>
            
            <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <form action="/melody-masters/cart_action.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <?php if($product['stock'] > 0 || $product['is_digital']): ?>
                    <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?php echo $product['is_digital'] ? 1 : $product['stock']; ?>">
                    <button type="submit" class="btn btn-primary"><i class="ph ph-shopping-cart-simple"></i> Add to Cart</button>
                    <?php if(!$product['is_digital']): ?>
                        <div style="width: 100%; margin-top: 0.5rem; color: var(--text-muted); font-size: 0.85rem;">
                            <i class="ph ph-check-circle" style="color: var(--success-text);"></i> <?php echo $product['stock']; ?> items available
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled style="width: 100%;"><i class="ph ph-x-circle"></i> Out of Stock</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="reviews-section" style="margin-top: 4rem; padding-top: 2rem; border-top: 1px solid var(--border-color);" id="review">
        <h2 style="font-size: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;"><i class="ph ph-star"></i> Customer Reviews</h2>
        
        <?php if(isset($review_error) && $review_error): ?>
            <div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($review_error); ?></div>
        <?php endif; ?>
        <?php if(isset($review_success) && $review_success): ?>
            <div class="alert alert-success"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($review_success); ?></div>
        <?php endif; ?>

        <?php if(isLoggedIn()): ?>
            <?php if($has_purchased): ?>
                <div style="background: var(--bg-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;"><i class="ph ph-pencil-simple"></i> Write a Review</h3>
                    <form action="/melody-masters/product.php?id=<?php echo $product['id']; ?>#review" method="POST">
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <div class="form-group" style="margin-bottom: 0; width: 120px;">
                                <label>Rating</label>
                                <select name="rating" class="form-control" required style="padding-left: 0.5rem;">
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Good</option>
                                    <option value="3">3 - Average</option>
                                    <option value="2">2 - Poor</option>
                                    <option value="1">1 - Terrible</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Your Review</label>
                            <textarea name="comment" class="form-control" rows="3" required placeholder="What did you think about this product?"></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary"><i class="ph ph-paper-plane-right"></i> Submit Review</button>
                    </form>
                </div>
            <?php elseif($_SESSION['role'] === 'Customer'): ?>
                <div class="alert alert-warning" style="margin-bottom: 2rem;">
                    <i class="ph ph-shopping-cart"></i> You must purchase this product to write a review.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info" style="margin-bottom: 2rem;">
                <i class="ph ph-info"></i> Please <a href="/melody-masters/login.php?redirect=product.php?id=<?php echo $product['id']; ?>#review" style="font-weight: 600; text-decoration: underline;">log in</a> to write a review.
            </div>
        <?php endif; ?>
        <?php if(empty($reviews)): ?>
            <div class="alert alert-info" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 3rem 1rem; background: transparent; border: 1px dashed var(--border-color);">
                <i class="ph ph-chat-circle-text" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 0.25rem;">No Reviews Yet</h3>
                <p style="color: var(--text-muted); margin: 0;">Be the first to review this product!</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <?php foreach($reviews as $rev): ?>
                    <div style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 0.5rem; transition: transform var(--transition-speed) ease;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <h4 style="font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 32px; height: 32px; background: var(--bg-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                                    <i class="ph ph-user"></i>
                                </div>
                                <?php echo htmlspecialchars($rev['user_name']); ?>
                            </h4>
                            <div style="color: #fbbf24; font-size: 1.1rem; display: flex; gap: 0.1rem;">
                                <?php for($i = 0; $i < (int)$rev['rating']; $i++): ?><i class="ph-fill ph-star"></i><?php endfor; ?>
                                <?php for($i = (int)$rev['rating']; $i < 5; $i++): ?><i class="ph ph-star"></i><?php endfor; ?>
                            </div>
                        </div>
                        <p style="color: var(--text-main); line-height: 1.6; margin-top: 0.5rem;"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                        <small style="color: var(--text-light); display: flex; align-items: center; gap: 0.25rem; margin-top: 0.5rem;">
                            <i class="ph ph-clock"></i> <?php echo date('M d, Y', strtotime($rev['created_at'])); ?>
                        </small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
