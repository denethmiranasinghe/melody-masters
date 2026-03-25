<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melody Masters Instrument Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="/melody-masters/css/style.css">
</head>
<?php
    $is_admin_dashboard = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
    $is_staff_dashboard = strpos($_SERVER['PHP_SELF'], '/staff/') !== false;
    $is_dashboard = $is_admin_dashboard || $is_staff_dashboard;
?>
<body class="<?php echo $is_dashboard ? 'dashboard-page' : ''; ?>">

<header class="main-header">
    <div class="container header-container">
        <a href="/melody-masters/index.php" class="logo">
          
            <h1>Melody Masters</h1>
        </a>
        <nav class="main-nav">
            <?php
                $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            <ul>
                <li><a href="/melody-masters/index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i class="ph ph-house"></i> Home</a></li>
                <li><a href="/melody-masters/shop.php" class="<?php echo $current_page == 'shop.php' || $current_page == 'product.php' ? 'active' : ''; ?>"><i class="ph ph-storefront"></i> Shop</a></li>
                <?php if(!$is_dashboard): ?>
                    <li><a href="/melody-masters/categories.php" class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>"><i class="ph ph-squares-four"></i> Categories</a></li>
                    <li><a href="/melody-masters/about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>"><i class="ph ph-info"></i> About</a></li>
                    <li><a href="/melody-masters/contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>"><i class="ph ph-phone"></i> Contact</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="header-right">
            <?php if(!$is_dashboard): ?>
                <a href="/melody-masters/cart.php" class="cart-btn">
                    <i class="ph ph-shopping-cart" style="font-size: 1.25rem;"></i>
                    <span class="cart-label">Cart</span>
                    <span class="cart-count"><?php echo getCartCount(); ?></span>
                </a>
            <?php endif; ?>
            
            <div class="auth-section">
                <?php if(isLoggedIn()): ?>
                    <?php 
                        $user_name = htmlspecialchars($_SESSION['name'] ?? 'User');
                        $user_role = getUserRole();
                        $avatar_initials = strtoupper(substr($user_name, 0, 1));
                    ?>
                    <div class="user-menu" id="userMenuContainer">
                        <button class="user-menu-btn" id="userMenuBtn" aria-expanded="false" aria-haspopup="true">
                            <div class="avatar"><?php echo $avatar_initials; ?></div>
                            <span class="user-name"><?php echo $user_name; ?></span>
                            <i class="ph ph-caret-down caret-icon"></i>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">
                                <strong style="display: block; font-size: 0.95rem; color: var(--text-main);"><?php echo $user_name; ?></strong>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($user_role); ?></span>
                            </div>
                            <ul class="dropdown-list">
                                <?php if($user_role === 'Admin'): ?>
                                    <li><a href="/melody-masters/admin/index.php"><i class="ph ph-shield-check"></i> Admin Dashboard</a></li>
                                <?php elseif($user_role === 'Staff'): ?>
                                    <li><a href="/melody-masters/staff/index.php"><i class="ph ph-clipboard-text"></i> Staff Dashboard</a></li>
                                <?php else: ?>
                                    <li><a href="/melody-masters/customer/dashboard.php"><i class="ph ph-user"></i> My Dashboard</a></li>
                                    <li><a href="/melody-masters/customer/profile.php"><i class="ph ph-identification-card"></i> Profile</a></li>
                                <?php endif; ?>
                            </ul>
                            <div class="dropdown-footer">
                                <a href="/melody-masters/logout.php" class="logout-btn"><i class="ph ph-sign-out"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/melody-masters/login.php" class="btn btn-secondary auth-btn">Login</a>
                    <a href="/melody-masters/register.php" class="btn btn-primary auth-btn">Sign Up</a>
                <?php endif; ?>
            </div>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="ph ph-list" style="font-size: 1.5rem;"></i>
            </button>
        </div>
    </div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        const userMenuContainer = document.getElementById('userMenuContainer');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mainNav = document.querySelector('.main-nav');
        const mobileMenuIcon = mobileMenuBtn ? mobileMenuBtn.querySelector('i') : null;

        // User dropdown
        if (userMenuBtn && userDropdown && userMenuContainer) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isExpanded = userMenuBtn.getAttribute('aria-expanded') === 'true';
                userMenuBtn.setAttribute('aria-expanded', !isExpanded);
                userDropdown.classList.toggle('show');
            });
            document.addEventListener('click', (e) => {
                if (!userMenuContainer.contains(e.target)) {
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                    userDropdown.classList.remove('show');
                }
            });
        }

        // Mobile hamburger menu
        if (mobileMenuBtn && mainNav) {
            mobileMenuBtn.addEventListener('click', () => {
                const isOpen = mainNav.classList.toggle('show-mobile');
                if (mobileMenuIcon) {
                    mobileMenuIcon.className = isOpen ? 'ph ph-x' : 'ph ph-list';
                }
            });

            // Close nav when any link inside it is clicked
            mainNav.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    mainNav.classList.remove('show-mobile');
                    if (mobileMenuIcon) mobileMenuIcon.className = 'ph ph-list';
                });
            });

            // Close nav on outside click
            document.addEventListener('click', (e) => {
                if (!mainNav.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                    mainNav.classList.remove('show-mobile');
                    if (mobileMenuIcon) mobileMenuIcon.className = 'ph ph-list';
                }
            });
        }
    });
</script>
<main class="main-content">
