<?php
require_once '../includes/functions.php';
requireRole('Admin');

// Admin panel simple overview
$stmtRev = $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'Completed'");
$total_revenue = $stmtRev->fetchColumn() ?: 0;

$stmtOrders = $pdo->query("SELECT COUNT(*) FROM orders");
$total_orders = $stmtOrders->fetchColumn();

// New orders (Pending or Processing)
$stmtNewOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('Pending', 'Processing')");
$new_orders = $stmtNewOrders->fetchColumn();

// Out of stock physical products
$stmtOutOfStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0 AND is_digital = 0");
$out_of_stock = $stmtOutOfStock->fetchColumn();

require_once '../includes/header.php';
?>

<div class="dashboard-layout">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="dashboard-content" style="padding: 2.5rem;">
            <div class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Admin Dashboard</h1>
                    <p style="color: var(--text-muted); margin: 0;">System overview and analytics</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="users.php?action=add" class="btn btn-primary"><i class="ph ph-user-plus"></i> Add Staff / Admin</a>
                    <a href="products.php?action=add" class="btn btn-secondary"><i class="ph ph-plus-circle"></i> Add Product</a>
                </div>
            </div>

            <div class="metrics-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <a href="orders.php" class="metric-card" style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); display: flex; align-items: center; justify-content: center; color: var(--success-text);">
                        <i class="ph ph-currency-gbp" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Total Revenue</h3>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1;"><?php echo formatPrice($total_revenue); ?></p>
                    </div>
                </a>

                <a href="orders.php" class="metric-card" style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(99, 102, 241, 0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                        <i class="ph ph-receipt" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Total Orders</h3>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1;"><?php echo $total_orders; ?></p>
                    </div>
                </a>

                <a href="orders.php" class="metric-card" style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(245, 158, 11, 0.1); display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                        <i class="ph ph-clock-counter-clockwise" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">New Orders</h3>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1;"><?php echo $new_orders; ?></p>
                    </div>
                </a>

                <a href="products.php" class="metric-card" style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: center; color: var(--danger-color);">
                        <i class="ph ph-warning-circle" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h3 style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Out-of-Stock Products</h3>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--text-main); margin: 0; line-height: 1;"><?php echo $out_of_stock; ?></p>
                    </div>
                </a>
            </div>


        </div>
</div>

<?php require_once '../includes/footer.php'; ?>
