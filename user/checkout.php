<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit();
}

include "../config/db.php";
include "../config/csrf.php";

verify_csrf();

$user_id = (int)$_SESSION["user_id"];

/* ── FETCH CART ITEMS ────────────────────────── */
$stmt = $conn->prepare(
    "SELECT c.product_id, c.quantity, p.price, p.name
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result     = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

/* ── CHECK STOCK ─────────────────────────────── */
foreach ($cart_items as $item) {
    $pid  = (int)$item["product_id"];
    $qty  = (int)$item["quantity"];

    $s    = $conn->prepare("SELECT stock, name FROM products WHERE id = ?");
    $s->bind_param("i", $pid);
    $s->execute();
    $row  = $s->get_result()->fetch_assoc();
    $s->close();

    if (!$row || $qty > (int)$row["stock"]) {
        $name = htmlspecialchars($row["name"] ?? "a product");
        $_SESSION["cart_error"] = "Not enough stock for \"{$name}\". Please update your cart.";
        header("Location: cart.php");
        exit();
    }
}

/* ── COMPUTE TOTAL ───────────────────────────── */
$total = 0;
foreach ($cart_items as $item) {
    $total += $item["quantity"] * $item["price"];
}

/* ── CREATE ORDER ────────────────────────────── */
$stmt = $conn->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'Pending')");
$stmt->bind_param("id", $user_id, $total);
$stmt->execute();
$order_id = $conn->insert_id;
$stmt->close();

/* ── INSERT ITEMS + DEDUCT STOCK ─────────────── */
foreach ($cart_items as $item) {
    $product_id = (int)$item["product_id"];
    $qty        = (int)$item["quantity"];
    $price      = (float)$item["price"];

    $stmt = $conn->prepare(
        "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("iiid", $order_id, $product_id, $qty, $price);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("ii", $qty, $product_id);
    $stmt->execute();
    $stmt->close();
}

/* ── CLEAR CART ──────────────────────────────── */
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

$_SESSION["order_success"] = "Your order has been placed successfully!";
header("Location: orders.php");
exit();
?>
