<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: /melody-masters/index.php");
    exit;
}

$error = '';
$success = '';

if (isset($_SESSION['registration_success'])) {
    $success = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            $redirect = $_GET['redirect'] ?? 'index.php';
            if ($user['role'] === 'Admin') {
                $redirect = 'admin/index.php';
            } elseif ($user['role'] === 'Staff') {
                $redirect = 'staff/index.php';
            } elseif ($redirect === 'checkout.php') {
                $redirect = 'checkout.php';
            } else {
                $redirect = 'index.php';
            }
            
            header("Location: /melody-masters/" . $redirect);
            exit;
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Please fill in all fields";
    }
}

require_once 'includes/header.php';
?>

<style>
    .auth-split {
        display: flex;
        min-height: calc(100vh - 80px);
        background: var(--surface-color);
    }
    .auth-image {
        flex: 1;
        display: none;
        background: url('/melody-masters/assets/images/Login.jpg') center/cover no-repeat;
        position: relative;
    }
    .auth-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(17,24,39,0.9), rgba(79,70,229,0.4));
    }
    .auth-image-content {
        position: absolute;
        bottom: 10%;
        left: 10%;
        color: white;
        z-index: 2;
        max-width: 400px;
    }
    .auth-form-container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .auth-form-inner {
        width: 100%;
        max-width: 420px;
    }
    @media(min-width: 992px) {
        .auth-image { display: block; }
    }
</style>

<div class="auth-split">
    <div class="auth-image">
        <div class="auth-image-content">
            <h2 style="font-size: 3rem; margin-bottom: 1rem; line-height: 1.1; font-weight: 800; color: white;">Welcome Back.</h2>
            <p style="font-size: 1.15rem; opacity: 0.9; line-height: 1.6;">Sign in to continue your musical journey and access your personalized dashboard.</p>
        </div>
    </div>
    <div class="auth-form-container">
        <div class="auth-form-inner">
            <div style="margin-bottom: 2.5rem;">
                <h2 style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">Sign In</h2>
                <p style="color: var(--text-muted); font-size: 1rem;">Enter your email and password to access your account.</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form action="/melody-masters/login.php<?php echo isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : ''; ?>" method="POST">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="email" style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Email Address</label>
                    <div style="position: relative;">
                        <i class="ph ph-envelope-simple" style="position: absolute; left: 1rem; top: 1.1rem; color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" style="padding-left: 3rem; height: 56px; background: var(--bg-color);" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="password" style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Password</label>
                    <div style="position: relative;">
                        <i class="ph ph-lock-key" style="position: absolute; left: 1rem; top: 1.1rem; color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" style="padding-left: 3rem; height: 56px; background: var(--bg-color);" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 56px; font-size: 1.1rem;"><i class="ph ph-sign-in"></i> Login to Account</button>
                <p style="text-align: center; margin-top: 2rem; font-size: 0.95rem; color: var(--text-muted);">
                    Don't have an account? <a href="/melody-masters/register.php" style="font-weight: 600; color: var(--accent-color);">Register here</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
