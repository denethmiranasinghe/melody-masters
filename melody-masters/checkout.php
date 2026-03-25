<?php
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header("Location: /melody-masters/login.php?redirect=checkout.php");
    exit;
}

$cart_items = [];
$total = 0;
$shipping = 0;
$is_all_digital = true;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $id => $item) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $cart_items[] = [
                'id' => $id,
                'name' => $product['name'],
                'price' => $product['price'],
                'shipping_cost' => $product['shipping_cost'] ?? 0,
                'quantity' => $item['quantity'],
                'is_digital' => $product['is_digital'],
                'image' => $product['image']
            ];
            $total += $product['price'] * $item['quantity'];
            if (!$product['is_digital']) {
                $is_all_digital = false;
            }
        }
    }
}

if(empty($cart_items)) {
    header("Location: /melody-masters/cart.php");
    exit;
}

if (!$is_all_digital && $total > 0 && $total < 100) {
    $shipping = 10.00; // Base flat shipping cost
    foreach($cart_items as $ci) {
        if(!$ci['is_digital']) {
            $shipping += ($ci['shipping_cost'] ?? 0) * $ci['quantity'];
        }
    }
}
$grand_total = $total + $shipping;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = isset($_POST['shipping_address']) ? trim($_POST['shipping_address']) : '';
    
    if (!$is_all_digital && empty($shipping_address)) {
        $error = "Shipping address is required for physical products.";
    } else {
        try {
            $pdo->beginTransaction();
            
            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, shipping_address) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'], 
                $grand_total, 
                'Pending', 
                $shipping_address
            ]);
            $order_id = $pdo->lastInsertId();
            
            // Create order items & deduct stock
            foreach($cart_items as $item) {
                // Insert item
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                
                // Deduct stock if physical
                if(!$item['is_digital']) {
                    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['id']]);
                }
            }
            
            $pdo->commit();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            $success = "Order placed successfully! Order ID: #$order_id. ";
            if($is_all_digital) {
                $success .= "Your digital products will be available for download in your Dashboard once the order is processed.";
            }
            
        } catch(PDOException $e) {
            $pdo->rollBack();
            $error = "Failed to place order. Please try again later.";
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">Checkout</h1>
    <div class="cart-layout">
        
        <div class="dashboard-content" style="padding: 2.5rem;">
            <?php if($error): ?>
                <div class="alert alert-error"><i class="ph ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success" style="flex-direction: column; text-align: center; padding: 4rem 2rem;">
                    <i class="ph-fill ph-check-circle" style="font-size: 4rem; color: var(--success-text); margin-bottom: 1rem;"></i>
                    <h2 style="margin-bottom: 1rem;">Order Successful</h2>
                    <p style="margin-bottom: 2rem; color: var(--text-main); font-size: 1.1rem;"><?php echo htmlspecialchars($success); ?></p>
                    <a href="/melody-masters/customer/dashboard.php" class="btn btn-primary" style="padding: 1rem 2rem;"><i class="ph ph-user"></i> Go to Dashboard</a>
                </div>
            <?php else: ?>
                <form action="/melody-masters/checkout.php" method="POST">
                    
                    <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><i class="ph ph-map-pin"></i> Shipping Information</h2>
                    <?php if(!$is_all_digital): ?>
                        <div class="form-group">
                            <label for="shipping_address">Full Shipping Address</label>
                            <textarea name="shipping_address" id="shipping_address" class="form-control" rows="4" required placeholder="Enter street address, city, postal code..."></textarea>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning" style="margin-bottom: 2rem;">
                            <i class="ph ph-info"></i> Your order contains only digital products. No shipping address is required.
                        </div>
                    <?php endif; ?>
                    
                    <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 3rem 0;">

                    <div style="background: var(--surface-color); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0; display: flex; align-items: center; gap: 0.5rem; color: var(--primary-color);">
                                <i class="ph-fill ph-lock-key" style="color: var(--accent-color);"></i> Secure Payment
                            </h2>
                        </div>
                        
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Simulated payment gateway environment. Please do not enter real data.</p>
                        
                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label style="font-size: 0.9rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Cardholder Name</label>
                            <input type="text" class="form-control" style="background: var(--bg-color);" value="<?php echo htmlspecialchars($_SESSION['name']); ?>" required>
                        </div>
                        
                        <div style="display: flex; flex-direction: column;">
                            <label style="font-size: 0.9rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Card Information</label>
                            <div style="position: relative;">
                                <i class="ph ph-credit-card" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-muted); font-size: 1.25rem; z-index: 2;"></i>
                                <input type="text" id="cc-number" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" style="padding-left: 3.25rem; border-bottom-left-radius: 0; border-bottom-right-radius: 0; position: relative; z-index: 1; background: var(--bg-color); height: 50px;" required>
                            </div>
                            <div style="display: flex;">
                                <input type="text" id="cc-exp" class="form-control" placeholder="MM / YY" maxlength="7" style="border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-right-radius: 0; border-top: 0; border-right: 0; width: 50%; background: var(--bg-color); height: 50px;" required>
                                <input type="text" id="cc-cvc" class="form-control" placeholder="CVC" maxlength="4" style="border-top-left-radius: 0; border-top-right-radius: 0; border-bottom-left-radius: 0; border-top: 0; width: 50%; background: var(--bg-color); height: 50px;" required>
                            </div>
                        </div>
                    </div>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const ccNumber = document.getElementById('cc-number');
                            const ccExp = document.getElementById('cc-exp');
                            const ccCvc = document.getElementById('cc-cvc');
                            
                            // Format Card Number
                            ccNumber.addEventListener('input', function (e) {
                                let val = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
                                val = val.substring(0, 16); // Limit length to 16 digits
                                
                                let formatted = '';
                                for (let i = 0; i < val.length; i++) {
                                    if (i > 0 && i % 4 === 0) {
                                        formatted += ' ';
                                    }
                                    formatted += val[i];
                                }
                                e.target.value = formatted;
                            });

                            // Format Expiry Date
                            ccExp.addEventListener('input', function (e) {
                                let val = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
                                val = val.substring(0, 4); // Limit to 4 digits (MMYY)
                                
                                if (val.length > 2) {
                                    e.target.value = val.substring(0, 2) + ' / ' + val.substring(2, 4);
                                } else {
                                    e.target.value = val;
                                }
                            });
                            
                            // Restrict CVC to digits
                            ccCvc.addEventListener('input', function (e) {
                                e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
                            });
                        });
                    </script>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 56px; font-size: 1.1rem; gap: 0.5rem;">
                        <i class="ph ph-lock-key"></i> Pay <?php echo formatPrice($grand_total); ?> Securely
                    </button>
                    
                </form>
            <?php endif; ?>
        </div>

        <div>
            <div class="cart-summary" style="margin-top: 0;">
                <h3>Order Summary</h3>
                <div style="max-height: 400px; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                    <?php foreach($cart_items as $item): ?>
                        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                            <div style="width: 60px; height: 60px; border-radius: var(--radius-sm); overflow: hidden; border: 1px solid var(--border-color);">
                                <img src="/melody-masters/assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                                <h4 style="font-size: 0.95rem; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Qty: <?php echo $item['quantity']; ?></span>
                            </div>
                            <div style="font-weight: 600; font-size: 0.95rem;">
                                <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span style="font-weight: 500; color: var(--text-main);"><?php echo formatPrice($total); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span style="font-weight: 500; color: var(--text-main);"><?php echo $shipping > 0 ? formatPrice($shipping) : 'Free'; ?></span>
                </div>
                <?php if($shipping === 0 && !$is_all_digital && $total > 0): ?>
                    <div class="alert alert-success" style="margin-top: 1rem; padding: 0.5rem; font-size: 0.85rem;">
                        <i class="ph ph-truck"></i> You qualify for Free Shipping!
                    </div>
                <?php elseif($total > 0 && $total < 100 && !$is_all_digital): ?>
                    <div class="alert alert-warning" style="margin-top: 1rem; padding: 0.5rem; font-size: 0.85rem; background: rgba(245, 158, 11, 0.1); color: var(--warning-text); border: 1px dashed #FCD34D;">
                        <i class="ph ph-sparkle"></i> Spend <strong><?php echo formatPrice(100 - $total); ?></strong> more to get Free Shipping!
                    </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Total</span>
                    <span><?php echo formatPrice($grand_total); ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
