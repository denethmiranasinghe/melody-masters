<?php
require_once '../includes/functions.php';
requireRole('Customer');

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if(!$order) {
        header("Location: dashboard.php");
        exit;
    }
    
    $itemStmt = $pdo->prepare("SELECT oi.*, p.name, p.is_digital, p.download_link 
                               FROM order_items oi 
                               JOIN products p ON oi.product_id = p.id 
                               WHERE oi.order_id = ?");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll();
    
} catch(PDOException $e) {
    die("Error loading order.");
}

require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="dashboard-header" style="background: var(--surface-color); padding: 1.5rem 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;"><i class="ph ph-receipt"></i> Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h1>
            <p style="color: var(--text-muted); margin: 0;">Details for your recent purchase</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary" style="border-radius: var(--radius-full);"><i class="ph ph-arrow-left"></i> Back to Orders</a>
    </div>
    
    <div class="dashboard-layout" style="grid-template-columns: 1fr 340px;">
        <div class="dashboard-content" style="padding: 2.5rem; order: 1;">
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class="ph ph-package"></i> Order Items</h2>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                            <tr>
                                <td style="font-weight: 500;">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <?php if($item['is_digital']): ?>
                                        <span class="badge badge-neutral" style="margin-left: 0.5rem;"><i class="ph ph-download-simple"></i> Digital</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatPrice($item['price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td style="font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                <td>
                                    <?php if($item['is_digital'] && $order['status'] == 'Completed'): ?>
                                        <a href="<?php echo htmlspecialchars($item['download_link'] ?? '#'); ?>" target="_blank" class="btn btn-primary" style="padding: 0.35rem 0.75rem;"><i class="ph ph-download-simple"></i> Download</a>
                                        <a href="/melody-masters/product.php?id=<?php echo $item['product_id']; ?>" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; margin-top: 0.5rem; display: block; text-align: center;"><i class="ph ph-star"></i> Review</a>
                                    <?php elseif(!$item['is_digital'] && $order['status'] == 'Completed'): ?>
                                        <a href="/melody-masters/product.php?id=<?php echo $item['product_id']; ?>" class="btn btn-secondary" style="padding: 0.35rem 0.75rem;"><i class="ph ph-star"></i> Review</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-light);"><i class="ph ph-minus"></i></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-sidebar" style="order: 2;">
            <div class="dashboard-sidebar-header">
                <h3><i class="ph ph-info"></i> Order Summary</h3>
            </div>
            <div style="padding: 1.5rem;">
                <div style="margin-bottom: 1rem; color: var(--text-main); font-size: 0.95rem;">
                    <strong style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Order Date</strong>
                    <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?>
                </div>

                <div style="margin-bottom: 1rem; color: var(--text-main); font-size: 0.95rem;">
                    <strong style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Status</strong>
                    <?php 
                        $badge = 'badge-warning';
                        $icon = 'ph-clock';
                        if($order['status'] == 'Completed') {
                            $badge = 'badge-success';
                            $icon = 'ph-check-circle';
                        }
                        if($order['status'] == 'Cancelled') {
                            $badge = 'badge-danger';
                            $icon = 'ph-x-circle';
                        }
                    ?>
                    <span class="badge <?php echo $badge; ?>" style="font-size: 0.85rem;"><i class="ph <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($order['status']); ?></span>
                </div>

                <div style="margin-bottom: 1.5rem; color: var(--text-main); font-size: 0.95rem;">
                    <strong style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Total Amount</strong>
                    <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary-color);"><?php echo formatPrice($order['total']); ?></span>
                </div>

                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">

                <div style="color: var(--text-main); font-size: 0.95rem;">
                    <strong style="display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Shipping Address</strong>
                    <?php if($order['shipping_address']): ?>
                        <div style="background: var(--bg-color); padding: 1rem; border-radius: var(--radius-sm); border: 1px solid var(--border-color); line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </div>
                    <?php else: ?>
                        <div class="badge badge-neutral"><i class="ph ph-envelope"></i> Digital Delivery</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
