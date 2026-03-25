<?php
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: /melody-masters/index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    if($name && $email && $password && $confirm) {
        if($password !== $confirm) {
            $error = "Passwords do not match.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if($stmt->fetch()) {
                $error = "Email is already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                if($stmt->execute([$name, $email, $hash])) {
                    $_SESSION['registration_success'] = "Account successfully created! Please log in.";
                    header("Location: /melody-masters/login.php");
                    exit;
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    } else {
        $error = "All fields are required.";
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
        background: url('/melody-masters/assets/images/Register.jpg') center/cover no-repeat;
        position: relative;
    }
    .auth-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(17,24,39,0.85), rgba(79,70,229,0.3));
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
            <h2 style="font-size: 3rem; margin-bottom: 1rem; line-height: 1.1; font-weight: 800; color: white;">Join the Masters.</h2>
            <p style="font-size: 1.15rem; opacity: 0.9; line-height: 1.6;">Create an account to track orders, access digital sheet music, and review your purchases.</p>
        </div>
    </div>
    <div class="auth-form-container">
        <div class="auth-form-inner">
            <div style="margin-bottom: 2.5rem;">
                <h2 style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">Create Account</h2>
                <p style="color: var(--text-muted); font-size: 1rem;">Fill in the details below to join Melody Masters.</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="/melody-masters/register.php" method="POST">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="name" style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Full Name</label>
                    <div style="position: relative;">
                        <i class="ph ph-user" style="position: absolute; left: 1rem; top: 1.1rem; color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="text" name="name" id="name" class="form-control" placeholder="John Doe" style="padding-left: 3rem; height: 56px; background: var(--bg-color);" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label for="email" style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Email Address</label>
                    <div style="position: relative;">
                        <i class="ph ph-envelope-simple" style="position: absolute; left: 1rem; top: 1.1rem; color: var(--text-muted); font-size: 1.25rem;"></i>
                        <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" style="padding-left: 3rem; height: 56px; background: var(--bg-color);" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="password" style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Password</label>
                        <div style="position: relative;">
                            <i class="ph ph-lock-key" style="position: absolute; left: 1rem; top: 1.1rem; color: var(--text-muted); font-size: 1.25rem;"></i>
                            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" style="padding-left: 3rem; height: 56px; background: var(--bg-color);" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="confirm" style="font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Confirm</label>
                        <div style="position: relative;">
                            <i class="ph ph-lock-key-fill" style="position: absolute; left: 1rem; top: 1.1rem; color: var(--text-muted); font-size: 1.25rem;"></i>
                            <input type="password" name="confirm" id="confirm" class="form-control" placeholder="••••••••" style="padding-left: 3rem; height: 56px; background: var(--bg-color);" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; height: 56px; font-size: 1.1rem;"><i class="ph ph-user-plus"></i> Create Account</button>
                <p style="text-align: center; margin-top: 2rem; font-size: 0.95rem; color: var(--text-muted);">
                    Already have an account? <a href="/melody-masters/login.php" style="font-weight: 600; color: var(--accent-color);">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
