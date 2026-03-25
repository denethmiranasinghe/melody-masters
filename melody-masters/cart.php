<?php
require_once 'includes/functions.php';

// Fetch cart items
$cart_items = [];
$total = 0;
$shipping = 0;
$is_all_digital = true;

try {
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
                    'image' => $product['image'],
                    'is_digital' => $product['is_digital'],
                    'stock' => $product['stock']
                ];
                $total += $product['price'] * $item['quantity'];
                if (!$product['is_digital']) {
                    $is_all_digital = false;
                }
            } else {
                // If product no longer exists, remove from cart
                unset($_SESSION['cart'][$id]);
            }
        }
    }
} catch(PDOException $e) {
    die("Error loading cart items.");
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

require_once 'includes/header.php';
?>

<div class="container">
    <h1 class="section-title">Your Shopping Cart</h1>
    
    <div class="cart-layout" style="margin-top: 2rem;">
        <?php if(empty($cart_items)): ?>
            <div style="grid-column: 1 / -1;">
                <div class="alert alert-warning" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; background: var(--surface-color); border-color: var(--border-color); color: var(--text-main);">
                    <i class="ph ph-shopping-cart" style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h2 style="margin-bottom: 0.5rem;">Your Cart is Empty</h2>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Looks like you haven't added anything to your cart yet.</p>
                    <a href="/melody-masters/shop.php" class="btn btn-primary">Start Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-items-section">
                <form action="/melody-masters/cart_action.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cart_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="cart-item-details">
                                                <img src="/melody-masters/assets/images/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-img">
                                                <div class="cart-item-info">
                                                    <h4><a href="/melody-masters/product.php?id=<?php echo $item['id']; ?>"><?php echo htmlspecialchars($item['name']); ?></a></h4>
                                                    <?php if($item['is_digital']): ?>
                                                        <span class="badge badge-neutral"><i class="ph ph-download-simple"></i> Digital</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td>
                                            <?php if($item['is_digital']): ?>
                                                <input type="number" class="qty-input" style="width: 70px; height: 36px; padding: 0.25rem; background: var(--bg-color); color: var(--text-muted); cursor: not-allowed;" value="1" disabled>
                                                <input type="hidden" name="quantity[<?php echo $item['id']; ?>]" value="1">
                                            <?php else: ?>
                                                <input type="number" name="quantity[<?php echo $item['id']; ?>]" class="qty-input" style="width: 70px; height: 36px; padding: 0.25rem;" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                        <td style="text-align: right;">
                                            <button type="submit" formaction="/melody-masters/cart_action.php" name="action" value="remove" onclick="this.form.elements['action'].value='remove'; this.form.insertAdjacentHTML('beforeend','<input type=\'hidden\' name=\'product_id\' value=\'<?php echo $item['id']; ?>\'>');" class="btn btn-danger" style="padding: 0.5rem; border-radius: var(--radius-md);" title="Remove Item"><i class="ph ph-trash" style="font-size: 1.25rem;"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem;">
                        <a href="/melody-masters/shop.php" class="btn btn-secondary"><i class="ph ph-arrow-left"></i> Continue Shopping</a>
                        <button type="submit" class="btn btn-secondary"><i class="ph ph-arrows-clockwise"></i> Update Cart</button>
                    </div>
                </form>
            </div>

            <div class="cart-sidebar">
                <div class="cart-summary">
                    <h3>Order Summary</h3>
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
                    
                    <a href="/melody-masters/checkout.php" class="btn btn-primary" style="display: flex; width: 100%; margin-top: 2rem; height: 48px;"><i class="ph ph-lock-key"></i> Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
