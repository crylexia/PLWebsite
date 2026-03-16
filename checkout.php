<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$cart = $_SESSION["cart"] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$total = 0;

/* Get product prices */
$ids = implode(",", array_keys($cart));
$sql = "SELECT id, price FROM products WHERE id IN ($ids)";
$result = mysqli_query($conn, $sql);

$prices = [];
while ($row = mysqli_fetch_assoc($result)) {
    $prices[$row["id"]] = $row["price"];
}

/* Calculate total */
foreach ($cart as $id => $qty) {
    $total += $prices[$id] * $qty;
}

/* Insert order */
$sql = "INSERT INTO orders (user_id, total, status) 
        VALUES ('$user_id', '$total', 'Pending')";
mysqli_query($conn, $sql);

$order_id = mysqli_insert_id($conn);

/* (Optional) Save order items */
foreach ($cart as $product_id => $qty) {
    $price = $prices[$product_id];
    $subtotal = $price * $qty;

    mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, quantity, price)
                         VALUES ('$order_id','$product_id','$qty','$price')");
}

/* Clear cart */
unset($_SESSION["cart"]);

/* Redirect to orders */
header("Location: orders.php");
exit();
?>
