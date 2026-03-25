<?php
require_once '../includes/functions.php';
requireRole('Customer');

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar">
            <div class="dashboard-sidebar-header">
                <h3>My Account</h3>
            </div>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="ph ph-package"></i> My Orders</a></li>
                <li><a href="profile.php"><i class="ph ph-user"></i> My Profile</a></li>
                <li><a href="/melody-masters/logout.php" style="color: var(--danger-color);"><i class="ph ph-sign-out"></i> Logout</a></li>
            </ul>
        </aside>
        
        <div class="dashboard-content" style="padding: 2.5rem;">
            <div class="dashboard-header">
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Customer Dashboard</h1>
                <p style="color: var(--text-muted);">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
            </div>
            
            <h2 style="font-size: 1.25rem; margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border-color);">Your Order History</h2>
            
            <?php if(empty($orders)): ?>
                <div class="alert alert-warning" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; background: var(--surface-color); border-color: var(--border-color);">
                    <i class="ph ph-shopping-bag" style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">No Orders Yet</h3>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">You haven't placed any orders yet.</p>
                    <a href="/melody-masters/shop.php" class="btn btn-primary">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
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
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td style="font-weight: 600;"><?php echo formatPrice($order['total']); ?></td>
                                    <td>
                                        <?php 
                                            $badge = 'badge-primary';
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
                                        <span class="badge <?php echo $badge; ?>">
                                            <i class="ph <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.85rem;"><i class="ph ph-eye"></i> View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
