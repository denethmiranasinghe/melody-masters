<?php
require_once '../includes/functions.php';
requireRole('Admin');

$error = '';
$success = '';
$action = $_GET['action'] ?? 'list';

$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll();

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    // Digital products never have a shipping cost
    $shipping_cost = $is_digital ? 0.00 : (float)$_POST['shipping_cost'];
    $stock = (int)$_POST['stock'];
    $download_link = trim($_POST['download_link'] ?? '');
    
    if($is_digital && isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] == 0) {
        if (!file_exists('../assets/downloads')) { mkdir('../assets/downloads', 0777, true); }
        $ext = strtolower(pathinfo($_FILES['digital_file']['name'], PATHINFO_EXTENSION));
        $new_name = uniqid() . '_dl.' . $ext;
        if(move_uploaded_file($_FILES['digital_file']['tmp_name'], '../assets/downloads/' . $new_name)) {
            $download_link = '/melody-masters/assets/downloads/' . $new_name;
        }
    }
    
    // Default image
    $image = 'accessories.jpeg';
    
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            if(move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $new_name)) {
                $image = $new_name;
            }
        }
    }
    
    if($name && $price >= 0) {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, brand, price, shipping_cost, stock, is_digital, image, download_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$category_id, $name, $description, $brand, $price, $shipping_cost, $stock, $is_digital, $image, $download_link])) {
            $success = "Product has been successfully added.";
            $action = 'list';
        } else {
            $error = "Failed to add product.";
        }
    } else {
        $error = "Name and Price are required fields.";
    }
}

// Handle Update Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $brand = trim($_POST['brand']);
    $price = (float)$_POST['price'];
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;
    // Digital products never have a shipping cost
    $shipping_cost = $is_digital ? 0.00 : (float)$_POST['shipping_cost'];
    $stock = (int)$_POST['stock'];
    
    if($name && $price >= 0) {
        $stmtProd = $pdo->prepare("SELECT image, download_link FROM products WHERE id = ?");
        $stmtProd->execute([$id]);
        $prod_data = $stmtProd->fetch();
        $image = $prod_data['image'] ?: 'accessories.jpeg';
        
        $download_link = $prod_data['download_link'] ?? '';
        
        if ($is_digital) {
            if (!empty(trim($_POST['download_link']))) {
                $download_link = trim($_POST['download_link']);
            }
            if(isset($_FILES['digital_file']) && $_FILES['digital_file']['error'] == 0) {
                if (!file_exists('../assets/downloads')) { mkdir('../assets/downloads', 0777, true); }
                $ext = strtolower(pathinfo($_FILES['digital_file']['name'], PATHINFO_EXTENSION));
                $new_name = uniqid() . '_dl.' . $ext;
                if(move_uploaded_file($_FILES['digital_file']['tmp_name'], '../assets/downloads/' . $new_name)) {
                    $download_link = '/melody-masters/assets/downloads/' . $new_name;
                }
            }
        } else {
            $download_link = null;
        }

        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if(in_array($ext, $allowed)) {
                $new_name = uniqid() . '.' . $ext;
                if(move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/' . $new_name)) {
                    $image = $new_name;
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, description=?, brand=?, price=?, shipping_cost=?, stock=?, is_digital=?, image=?, download_link=? WHERE id=?");
        if($stmt->execute([$category_id, $name, $description, $brand, $price, $shipping_cost, $stock, $is_digital, $image, $download_link, $id])) {
            $success = "Product updated successfully.";
            $action = 'list';
        } else {
            $error = "Failed to update product.";
        }
    } else {
        $error = "Name and Price are required fields.";
    }
}

// Handle Delete Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if($stmt->execute([$id])) {
        $success = "Product has been deleted successfully.";
    } else {
        $error = "Failed to delete product. It might be linked to existing orders.";
    }
}

$edit_product = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $edit_product = $stmt->fetch();
    if (!$edit_product) {
        $error = "Product not found.";
        $action = 'list';
    }
}

$stmt = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name ASC");
$products = $stmt->fetchAll();

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
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Add New Product</h1>
                        <p style="color: var(--text-muted); margin: 0;">Add a new physical or digital product to the catalog.</p>
                    </div>
                    <a href="products.php" class="btn btn-secondary"><i class="ph ph-arrow-left"></i> Back to Products</a>
                </div>
                
                <form action="products.php?action=add" method="POST" enctype="multipart/form-data" style="background: var(--surface-color); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Product Price (£)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required min="0">
                        </div>
                        <div class="form-group" id="shipping_cost_add_wrapper">
                            <label>Shipping Cost (£)</label>
                            <input type="number" step="0.01" name="shipping_cost" id="shipping_cost_add" class="form-control" required min="0" value="0.00">
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" class="form-control" required min="0" value="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_digital" id="is_digital_add" value="1" style="width: 20px; height: 20px;" onchange="toggleDigitalAdd()">
                        <label for="is_digital_add" style="margin: 0; cursor: pointer;">This is a Digital Product (No physical shipping required)</label>
                    </div>
                    
                    <div id="digital_add_section" style="display: none; background: var(--bg-color); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px dashed var(--border-color);">
                        <h4 style="margin-bottom: 1rem; color: var(--primary-color);"><i class="ph ph-file-zip"></i> Digital Product Download</h4>
                        <div class="form-group">
                            <label>Upload File Attachment</label>
                            <input type="file" name="digital_file" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>OR External Download Link</label>
                            <input type="url" name="download_link" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    
                    <button type="submit" name="add_product" class="btn btn-primary" style="height: 48px;"><i class="ph ph-plus-circle"></i> Add Product</button>
                    
                    <script>
                        function toggleDigitalAdd() {
                            const isDigital = document.getElementById('is_digital_add').checked;
                            const shippingWrapper = document.getElementById('shipping_cost_add_wrapper');
                            const shippingInput = document.getElementById('shipping_cost_add');
                            if (isDigital) {
                                shippingWrapper.style.display = 'none';
                                shippingInput.value = '0.00';
                            } else {
                                shippingWrapper.style.display = 'block';
                            }
                            document.getElementById('digital_add_section').style.display = isDigital ? 'block' : 'none';
                        }
                    </script>
                </form>

            <?php elseif ($action === 'edit' && $edit_product): ?>
                <div class="dashboard-header" style="justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Edit Product</h1>
                        <p style="color: var(--text-muted); margin: 0;">Update details for <?php echo htmlspecialchars($edit_product['name']); ?></p>
                    </div>
                    <a href="products.php" class="btn btn-secondary"><i class="ph ph-arrow-left"></i> Back to Products</a>
                </div>
                
                <form action="products.php?action=edit&id=<?php echo $edit_product['id']; ?>" method="POST" enctype="multipart/form-data" style="background: var(--surface-color); padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color);">
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label>Product Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_product['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $edit_product['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Brand</label>
                            <input type="text" name="brand" class="form-control" value="<?php echo htmlspecialchars($edit_product['brand'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Product Price (£)</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $edit_product['price']; ?>" required min="0">
                        </div>
                        <div class="form-group" id="shipping_cost_edit_wrapper" style="display: <?php echo $edit_product['is_digital'] ? 'none' : 'block'; ?>;">
                            <label>Shipping Cost (£)</label>
                            <input type="number" step="0.01" name="shipping_cost" id="shipping_cost_edit" class="form-control" value="<?php echo $edit_product['shipping_cost'] ?? '0.00'; ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label>Stock</label>
                            <input type="number" name="stock" class="form-control" value="<?php echo $edit_product['stock']; ?>" required min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Product Image (Leave blank to keep current)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <?php if(!empty($edit_product['image'])): ?>
                            <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--text-muted);">Current: <?php echo htmlspecialchars($edit_product['image']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($edit_product['description']); ?></textarea>
                    </div>
                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_digital" id="is_digital_edit" value="1" style="width: 20px; height: 20px;" <?php echo $edit_product['is_digital'] ? 'checked' : ''; ?> onchange="toggleDigitalEdit()">
                        <label for="is_digital_edit" style="margin: 0; cursor: pointer;">This is a Digital Product</label>
                    </div>
                    
                    <div id="digital_edit_section" style="display: <?php echo $edit_product['is_digital'] ? 'block' : 'none'; ?>; background: var(--bg-color); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px dashed var(--border-color);">
                        <h4 style="margin-bottom: 1rem; color: var(--primary-color);"><i class="ph ph-file-zip"></i> Digital Product Download</h4>
                        <?php if(!empty($edit_product['download_link'])): ?>
                            <div class="alert alert-info" style="margin-bottom: 1rem; padding: 0.5rem 1rem;">
                                Current Link: <a href="<?php echo htmlspecialchars($edit_product['download_link']); ?>" target="_blank" style="text-decoration: underline; font-weight: bold;">View Attached/Link</a>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label>Upload New File (overwrites current)</label>
                            <input type="file" name="digital_file" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>OR External Download Link</label>
                            <input type="url" name="download_link" class="form-control" placeholder="https://..." value="<?php echo strpos($edit_product['download_link'] ?? '', '/assets/') === false ? htmlspecialchars($edit_product['download_link'] ?? '') : ''; ?>">
                            <small style="color:var(--text-muted); display:block; margin-top:0.25rem;">Replacing this will override any previously uploaded file.</small>
                        </div>
                    </div>
                    
                    <button type="submit" name="edit_product" class="btn btn-primary" style="height: 48px;"><i class="ph ph-floppy-disk"></i> Update Product</button>
                    
                    <script>
                        function toggleDigitalEdit() {
                            const isDigital = document.getElementById('is_digital_edit').checked;
                            const shippingWrapper = document.getElementById('shipping_cost_edit_wrapper');
                            const shippingInput = document.getElementById('shipping_cost_edit');
                            if (isDigital) {
                                shippingWrapper.style.display = 'none';
                                shippingInput.value = '0.00';
                            } else {
                                shippingWrapper.style.display = 'block';
                            }
                            document.getElementById('digital_edit_section').style.display = isDigital ? 'block' : 'none';
                        }
                    </script>
                </form>

            <?php else: ?>
                <div class="dashboard-header" style="justify-content: space-between;">
                    <div>
                        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Manage Products</h1>
                        <p style="color: var(--text-muted); margin: 0;">View and manage the product catalog</p>
                    </div>
                    <a href="products.php?action=add" class="btn btn-primary"><i class="ph ph-plus-circle"></i> Add New Product</a>
                </div>
                
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Shipping</th>
                                <th>Type</th>
                                <th>Stock</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <img src="/melody-masters/assets/images/<?php echo htmlspecialchars($p['image'] ?? 'accessories.jpeg'); ?>" width="40" height="40" style="border-radius: var(--radius-sm); object-fit: cover;" onerror="this.src='/melody-masters/assets/images/accessories.jpeg'">
                                            <span style="font-weight: 500; font-size: 0.95rem;"><?php echo htmlspecialchars($p['name']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-neutral"><?php echo htmlspecialchars($p['cat_name']); ?></span></td>
                                    <td style="font-weight: 600;"><?php echo formatPrice($p['price']); ?></td>
                                    <td><?php echo $p['is_digital'] ? '<span style="color:var(--text-muted); font-size:0.85rem;">Free (Digital)</span>' : '+' . formatPrice($p['shipping_cost'] ?? 0); ?></td>
                                    <td>
                                        <?php if($p['is_digital']): ?>
                                            <span class="badge badge-primary"><i class="ph ph-download-simple"></i> Digital</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary" style="background: var(--bg-color); color: var(--text-main); border: 1px solid var(--border-color);"><i class="ph ph-package"></i> Physical</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($p['is_digital']): ?>
                                            <span style="color: var(--text-light);">∞</span>
                                        <?php elseif($p['stock'] < 5): ?>
                                            <span class="badge badge-danger"><?php echo $p['stock']; ?></span>
                                        <?php else: ?>
                                            <span style="font-weight: 500;"><?php echo $p['stock']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                            <a href="products.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-secondary" style="padding: 0.35rem 0.5rem; border-radius: var(--radius-md);" title="Edit Product"><i class="ph ph-pencil-simple" style="font-size: 1.1rem;"></i></a>
                                            <form action="products.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" name="delete_product" class="btn btn-danger" style="padding: 0.35rem 0.5rem; border-radius: var(--radius-md);" title="Delete Product"><i class="ph ph-trash" style="font-size: 1.1rem;"></i></button>
                                            </form>
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
