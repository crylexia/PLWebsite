<?php
session_start();
include "../config/db.php";
include "../config/csrf.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["product_id"])) {
    header("Location: products.php");
    exit();
}

verify_csrf();

$user_id    = (int) $_SESSION["user_id"];
$product_id = (int) $_POST["product_id"];
$quantity   = isset($_POST["quantity"]) ? (int) $_POST["quantity"] : 1;
if ($quantity < 1) $quantity = 1;

// Get current stock
$stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stock = (int)($row["stock"] ?? 0);
if ($stock <= 0) {
    header("Location: products.php");
    exit();
}

// Check existing cart row
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$cart_row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($cart_row) {
    $new_qty = min($cart_row["quantity"] + $quantity, $stock);
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("iii", $new_qty, $user_id, $product_id);
    $stmt->execute();
    $stmt->close();
} else {
    $quantity = min($quantity, $stock);
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    $stmt->execute();
    $stmt->close();
}

header("Location: products.php");
exit();
