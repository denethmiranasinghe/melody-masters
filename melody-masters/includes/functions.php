<?php
session_start();
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : 'Guest';
}

function formatPrice($price) {
    return '£' . number_format($price, 2);
}

function getCartCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function getCartTotal($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if ($product) {
            $total += $product['price'] * $item['quantity'];
        }
    }
    return $total;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /melody-masters/login.php");
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (getUserRole() !== $role && getUserRole() !== 'Admin') {
        if ($role == 'Staff' && getUserRole() != 'Admin') {
            header("Location: /melody-masters/index.php");
            exit;
        }
    }
}
?>
