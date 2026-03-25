<?php
$current_page = basename($_SERVER['PHP_SELF']);

$stmt_nav_unread = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
$nav_unread_messages = $stmt_nav_unread->fetchColumn();

$stmt_nav_pending = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'");
$nav_pending_orders = $stmt_nav_pending->fetchColumn();
?>
<aside class="dashboard-sidebar" id="dashSidebar">
    <div class="dashboard-sidebar-header">
        <h3>Admin Panel</h3>
        <button class="sidebar-toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
            <i class="ph ph-sidebar-simple"></i>
        </button>
    </div>
    <ul>
        <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
            <i class="ph ph-chart-line-up"></i>
            <span class="sidebar-text">Overview</span>
        </a></li>
        <li><a href="users.php" class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <i class="ph ph-users"></i>
            <span class="sidebar-text">Manage Users</span>
        </a></li>
        <li><a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
            <i class="ph ph-speaker-hifi"></i>
            <span class="sidebar-text">Manage Products</span>
        </a></li>
        <li><a href="categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
            <i class="ph ph-squares-four"></i>
            <span class="sidebar-text">Categories</span>
        </a></li>
        <li>
            <a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <i class="ph ph-receipt"></i>
                <span class="sidebar-text">Orders <?php if($nav_pending_orders > 0) echo "<span style='background:#ef4444;color:white;border-radius:999px;padding:0.1rem 0.55rem;font-size:0.7rem;font-weight:700;margin-left:0.25rem;'>$nav_pending_orders</span>"; ?></span>
            </a>
        </li>
        <li>
            <a href="messages.php" class="<?php echo $current_page == 'messages.php' ? 'active' : ''; ?>">
                <i class="ph ph-envelope"></i>
                <span class="sidebar-text">Messages <?php if($nav_unread_messages > 0) echo "<span style='background:#ef4444;color:white;border-radius:999px;padding:0.1rem 0.55rem;font-size:0.7rem;font-weight:700;margin-left:0.25rem;'>$nav_unread_messages</span>"; ?></span>
            </a>
        </li>
        <li class="sidebar-divider" style="height:1px;background:var(--border-color);margin:0.5rem 0.5rem;list-style:none;"></li>
        <li><a href="/melody-masters/logout.php" class="sidebar-logout">
            <i class="ph ph-sign-out"></i>
            <span class="sidebar-text">Logout</span>
        </a></li>
    </ul>
</aside>

<script>
(function() {
    const sidebar = document.getElementById('dashSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const layout = document.querySelector('.dashboard-layout');
    const STORAGE_KEY = 'adminSidebarCollapsed';

    function applyState(collapsed) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            layout && layout.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            layout && layout.classList.remove('sidebar-collapsed');
        }
    }

    applyState(localStorage.getItem(STORAGE_KEY) === 'true');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isNowCollapsed = !sidebar.classList.contains('collapsed');
            applyState(isNowCollapsed);
            localStorage.setItem(STORAGE_KEY, isNowCollapsed);
        });
    }
})();
</script>
