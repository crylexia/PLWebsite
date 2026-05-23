<?php
session_start();
include "../config/db.php";
include "../config/csrf.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin_products.php");
    exit();
}

verify_csrf();

$product_id = (int)($_POST["product_id"] ?? 0);
$add_stock  = (int)($_POST["add_stock"]  ?? 0);

if ($product_id <= 0 || $add_stock <= 0) {
    header("Location: admin_products.php?error=invalid_stock");
    exit();
}

$stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
$stmt->bind_param("ii", $add_stock, $product_id);
if (!$stmt->execute()) {
    error_log("Restock failed: " . $stmt->error);
    header("Location: admin_products.php?error=restock_failed");
    exit();
}
$stmt->close();

header("Location: admin_products.php?restocked=1");
exit();
