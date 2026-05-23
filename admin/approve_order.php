<?php
session_start();

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

include "../config/db.php";
include "../config/csrf.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../user/orders.php");
    exit();
}

verify_csrf();

$order_id = (int)($_POST["order_id"] ?? 0);
$admin_id = (int)$_SESSION["user_id"];

if (!$order_id) {
    header("Location: ../user/orders.php?error=invalid");
    exit();
}

/* Check order exists and is still Pending */
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: ../user/orders.php?error=not_found");
    exit();
}

if ($order["status"] !== "Pending") {
    header("Location: ../user/orders.php?error=already_processed");
    exit();
}

/* Update order status */
$stmt = $conn->prepare("UPDATE orders SET status = 'Approved' WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

/* Audit log */
$stmt = $conn->prepare("INSERT INTO order_audit (order_id, admin_id, action) VALUES (?, ?, 'APPROVED')");
$stmt->bind_param("ii", $order_id, $admin_id);
$stmt->execute();
$stmt->close();

header("Location: ../user/orders.php?approved=1");
exit();
?>
