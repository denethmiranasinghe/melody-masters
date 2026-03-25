<?php
require_once '../includes/functions.php';
requireRole('Admin');

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if ($name && $email && $password && $role) {
        // Admins can only create Staff or Admin accounts — customers must self-register
        if ($role === 'Customer') {
            $error = "Customers must register themselves via the Sign Up page. You can only create Staff or Admin accounts here.";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email is already registered.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hash, $role])) {
                    $success = "User added successfully.";
                    $action = 'list';
                } else {
                    $error = "Failed to add user.";
                }
            }
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = (int)$_POST['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if ($name && $email && $role) {
        // Prevent changing any user to Customer role via admin panel
        if ($role === 'Customer') {
            $error = "You cannot assign the Customer role from here. Customers must self-register.";
        } else {
            try {
                if ($password) {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $role, $hash, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $role, $id]);
                }
                $success = "User updated successfully.";
                $action = 'list';
            } catch(PDOException $e) {
                $error = "Failed to update user. Email might be in use.";
            }
        }
    } else {
        $error = "Name, email, and role are required.";
    }
}

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['user_id'];
    if ($id === $_SESSION['user_id']) {
        $error = "You cannot delete yourself.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            $success = "User has been deleted successfully.";
        } else {
            $error = "Failed to delete user.";
        }
    }
}

$edit_user = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_user = $stmt->fetch();
    if (!$edit_user) {
        $error = "User not found.";
        $action = 'list';
    }
}

// Fetch all users for list
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="dashboard-layout">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="dashboard-content" style="padding: 2.5rem;">
            
            <?php if($error): ?><div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <?php if ($action === 'add'): ?>
                <div class="dashboard-header" style="justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Add Staff / Admin Account</h1>
                        <p style="color: var(--text-muted); margin: 0;">Create a new Staff or Admin account. Customers must sign up themselves.</p>
                    </div>
                    <a href="users.php" class="btn btn-secondary"><i class="ph ph-arrow-left"></i> Back to Users</a>
                </div>
                
                <form action="users.php?action=add" method="POST" style="max-width: 600px; background: var(--surface-color); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                            <small style="color: var(--text-muted); display: block; margin-top: 0.4rem;"><i class="ph ph-info"></i> Customers can only sign up themselves via the public registration page.</small>
                        </div>
                    <button type="submit" name="add_user" class="btn btn-primary" style="height: 48px; margin-top: 1rem;"><i class="ph ph-user-plus"></i> Add User</button>
                </form>

            <?php elseif ($action === 'edit' && $edit_user): ?>
                <div class="dashboard-header" style="justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Edit User</h1>
                        <p style="color: var(--text-muted); margin: 0;">Update details for <?php echo htmlspecialchars($edit_user['name']); ?></p>
                    </div>
                    <a href="users.php" class="btn btn-secondary"><i class="ph ph-arrow-left"></i> Back to Users</a>
                </div>
                
                <form action="users.php?action=edit&id=<?php echo $edit_user['id']; ?>" method="POST" style="max-width: 600px; background: var(--surface-color); padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <?php if($edit_user['role'] === 'Customer'): ?>
                                    <option value="Customer" selected disabled>Customer (read-only — assigned at signup)</option>
                                    <option value="Staff">Promote to Staff</option>
                                    <option value="Admin">Promote to Admin</option>
                                <?php else: ?>
                                    <option value="Staff" <?php echo $edit_user['role'] == 'Staff' ? 'selected' : ''; ?>>Staff</option>
                                    <option value="Admin" <?php echo $edit_user['role'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                <?php endif; ?>
                            </select>
                            <?php if($edit_user['role'] === 'Customer'): ?>
                            <small style="color: var(--warning-text); display: block; margin-top: 0.4rem;"><i class="ph ph-warning"></i> This is a customer account. You can promote them but cannot revert to Customer.</small>
                            <?php endif; ?>
                        </div>
                    <button type="submit" name="edit_user" class="btn btn-primary" style="height: 48px; margin-top: 1rem;"><i class="ph ph-floppy-disk"></i> Update User</button>
                </form>

            <?php else: ?>
                <div class="dashboard-header" style="justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Manage Users</h1>
                        <p style="color: var(--text-muted); margin: 0;">View all users — customers self-register via the Sign Up page</p>
                    </div>
                    <a href="users.php?action=add" class="btn btn-primary"><i class="ph ph-user-plus"></i> Add Staff / Admin</a>
                </div>
                
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                                <tr>
                                    <td style="font-weight: 500;">#<?php echo $user['id']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 32px; height: 32px; background: var(--bg-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary-color);">
                                                <i class="ph ph-user"></i>
                                            </div>
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php 
                                            $badge = 'badge-neutral';
                                            if($user['role'] == 'Admin') $badge = 'badge-primary';
                                            if($user['role'] == 'Staff') $badge = 'badge-success';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($user['role']); ?></span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                            <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="padding: 0.35rem 0.5rem; border-radius: var(--radius-md);" title="Edit User"><i class="ph ph-pencil-simple" style="font-size: 1.1rem;"></i></a>
                                            <?php if($user['id'] !== $_SESSION['user_id']): ?>
                                                <form action="users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger" style="padding: 0.35rem 0.5rem; border-radius: var(--radius-md);" title="Delete User"><i class="ph ph-trash" style="font-size: 1.1rem;"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
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
