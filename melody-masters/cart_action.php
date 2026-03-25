<?php
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if ($action === 'add') {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        if ($product_id > 0) {
            // Check stock
            $stmt = $pdo->prepare("SELECT stock, is_digital FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product) {
                if ($product['is_digital']) {
                    $quantity = 1; // Digital items only 1 max
                    $_SESSION['cart'][$product_id] = [
                        'quantity' => 1
                    ];
                } else {
                    $current_qty = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id]['quantity'] : 0;
                    if (($current_qty + $quantity) <= $product['stock']) {
                        $_SESSION['cart'][$product_id] = [
                            'quantity' => $current_qty + $quantity
                        ];
                    } else {
                        $_SESSION['cart'][$product_id] = [
                            'quantity' => $product['stock']
                        ];
                    }
                }
            }
        }
    } elseif ($action === 'update') {
        foreach ($_POST['quantity'] as $pid => $qty) {
            $pid = (int)$pid;
            $qty = (int)$qty;
            if ($qty > 0) {
                $stmt = $pdo->prepare("SELECT stock, is_digital FROM products WHERE id = ?");
                $stmt->execute([$pid]);
                $product = $stmt->fetch();
                if($product) {
                    if($product['is_digital']) {
                        $_SESSION['cart'][$pid]['quantity'] = 1;
                    } else {
                        $_SESSION['cart'][$pid]['quantity'] = min($qty, $product['stock']);
                    }
                }
            } else {
                unset($_SESSION['cart'][$pid]);
            }
        }
    } elseif ($action === 'remove') {
        $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

header("Location: /melody-masters/cart.php");
exit;
