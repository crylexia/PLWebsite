<?php
session_start();
include "../config/db.php";
include "../config/csrf.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin/admin_products.php");
    exit();
}

verify_csrf();

$id = (int)($_POST["id"] ?? 0);
if (!$id) {
    header("Location: ../admin/admin_products.php?error=invalid");
    exit();
}

$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    error_log("Delete product failed: " . $stmt->error);
    header("Location: ../admin/admin_products.php?error=delete_failed");
    exit();
}
$stmt->close();

header("Location: ../admin/admin_products.php?deleted=1");
exit();
