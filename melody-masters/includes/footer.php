</main>
<?php 
$is_admin_dashboard = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
$is_staff_dashboard = strpos($_SERVER['PHP_SELF'], '/staff/') !== false;
$is_auth_page = in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php']);
$is_dashboard = isset($is_dashboard) ? $is_dashboard : ($is_admin_dashboard || $is_staff_dashboard);
if (!$is_dashboard && !$is_auth_page): 
?>
<footer class="main-footer">
    <div class="container footer-container">
        <div class="footer-brand">
            <a href="/melody-masters/index.php" class="logo-text">
               
                Melody Masters
            </a>
            <p>Your one-stop shop for professional and beginner musical instruments. Discover your sound with us today.</p>
        </div>
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="/melody-masters/shop.php"><i class="ph ph-shopping-bag"></i> Shop All</a></li>
                <li><a href="/melody-masters/about.php"><i class="ph ph-info"></i> About Us</a></li>
                <li><a href="/melody-masters/contact.php"><i class="ph ph-envelope-simple"></i> Contact</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Customer Support</h3>
            <ul>
                <li><a href="/melody-masters/contact.php"><i class="ph ph-question"></i> FAQ</a></li>
                <li><a href="/melody-masters/about.php"><i class="ph ph-truck"></i> Shipping Policy</a></li>
                <li><a href="/melody-masters/contact.php"><i class="ph ph-arrow-u-down-left"></i> Returns</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Melody Masters. All rights reserved.</p>
    </div>
</footer>
<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log("Melody Masters loaded.");
        
        // Sidebar Toggle Logic
        const sidebarToggle = document.getElementById('sidebarToggle');
        const dashboardLayout = document.querySelector('.dashboard-layout');
        
        if (sidebarToggle && dashboardLayout) {
            // Check localStorage for saved state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                dashboardLayout.classList.add('collapsed');
            }
            
            sidebarToggle.addEventListener('click', function() {
                dashboardLayout.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', dashboardLayout.classList.contains('collapsed'));
            });
        }
    });
</script>
</body>
</html>
