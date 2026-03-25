<?php
require_once '../includes/functions.php';
requireRole('Admin');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if($name) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        if($stmt->execute([$name])) {
            $success = "Category added successfully.";
        } else {
            $error = "Failed to add category.";
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    $id = (int)$_POST['category_id'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    if($stmt->execute([$id])) {
        $success = "Category deleted successfully.";
    } else {
        $error = "Failed to delete category. It might be linked to products.";
    }
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

require_once '../includes/header.php';
?>
<div class="dashboard-layout">
        <?php include '../includes/admin_sidebar.php'; ?>

        <div class="dashboard-content" style="padding: 2.5rem;">
            <div class="dashboard-header" style="justify-content: space-between;">
                <div>
                    <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Categories</h1>
                    <p style="color: var(--text-muted); margin: 0;">Manage product categories</p>
                </div>
            </div>
            
            <?php if($error): ?><div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><i class="ph ph-check-circle"></i> <?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            
            <div style="margin-bottom: 2rem; background: var(--bg-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                <form action="" method="POST" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="form-group" style="margin-bottom: 0; flex: 1;">
                        <label>New Category Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Keyboards">
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary" style="height: 48px;"><i class="ph ph-plus"></i> Add Category</button>
                </form>
            </div>
            
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                            <tr>
                                <td style="font-weight: 500;">#<?php echo $cat['id']; ?></td>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td style="text-align: right;">
                                    <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-danger" style="padding: 0.35rem 0.75rem; border-radius: var(--radius-md);"><i class="ph ph-trash"></i> Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
</div>
<?php require_once '../includes/footer.php'; ?>
