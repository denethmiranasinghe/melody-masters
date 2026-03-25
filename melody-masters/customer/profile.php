<?php
require_once '../includes/functions.php';
requireRole('Customer');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if($name && $email) {
        try {
            if($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $hash, $_SESSION['user_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $_SESSION['user_id']]);
            }
            $_SESSION['name'] = $name;
            $success = "Profile updated successfully.";
        } catch(PDOException $e) {
            $error = "Failed to update profile. Email might already be in use.";
        }
    } else {
        $error = "Name and Email are required.";
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

require_once '../includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="dashboard-layout">
        <aside class="dashboard-sidebar">
            <div class="dashboard-sidebar-header">
                <h3>My Account</h3>
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="ph ph-package"></i> My Orders</a></li>
                <li><a href="profile.php" class="active"><i class="ph ph-user"></i> My Profile</a></li>
                <li><a href="/melody-masters/logout.php" style="color: var(--danger-color); margin-top: 1rem;"><i class="ph ph-sign-out"></i> Logout</a></li>
            </ul>
        </aside>

        <div class="dashboard-content" style="padding: 2.5rem;">
            <div class="dashboard-header">
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">My Profile</h1>
                <p style="color: var(--text-muted); margin: 0;">Update your account details and password</p>
            </div>
            
            <?php if($error): ?><div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            
            <form action="profile.php" method="POST" style="max-width: 500px;">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">
                
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Change Password</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">Leave blank if you do not want to change your password.</p>
                
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary" style="margin-top: 1rem; height: 48px;"><i class="ph ph-floppy-disk"></i> Save Changes</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
