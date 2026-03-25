<?php
require_once '../includes/functions.php';
requireRole('Admin');

$error = '';
$success = '';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $success = "Order status updated successfully.";
    } catch(PDOException $e) {
        $error = "Failed to update order.";
    }
}

$stmt = $pdo->query("SELECT o.*, u.name as customer_name, u.email as customer_email 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="dashboard-layout">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="dashboard-content" style="padding: 2.5rem;">
            <div class="dashboard-header">
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">All Orders</h1>
                <p style="color: var(--text-muted); margin: 0;">View all orders across the system</p>
            </div>
            
            <?php if($error): ?><div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            
            <?php if(empty($orders)): ?>
                <div class="alert alert-info">No orders found in the system.</div>
            <?php else: ?>
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $order): ?>
                                <tr>
                                    <td style="font-weight: 500;">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-weight: 500;"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                            <span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y - H:i', strtotime($order['created_at'])); ?></td>
                                    <td style="font-weight: 600;"><?php echo formatPrice($order['total']); ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <form action="" method="POST" style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-control" required style="padding: 0.25rem 0.5rem; height: 32px; font-size: 0.85rem; min-width: 120px; line-height: 1;">
                                                <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="Completed" <?php echo $order['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_order" class="btn btn-primary" style="padding: 0 0.75rem; height: 32px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.25rem;"><i class="ph ph-arrows-clockwise"></i> Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
</div>
<?php require_once '../includes/footer.php'; ?>
