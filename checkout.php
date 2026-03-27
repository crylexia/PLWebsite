<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit();
}

include "db.php";

$user_id = (int) $_SESSION["user_id"];

/* Fetch cart items from database */
$sql = "SELECT c.product_id, c.quantity, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = $user_id";

$result = mysqli_query($conn, $sql);

$cart_items = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $product_id = (int) $row["product_id"];
    $qty = (int) $row["quantity"];
    $price = (float) $row["price"];
    $subtotal = $qty * $price;

    $cart_items[] = [
        "product_id" => $product_id,
        "quantity" => $qty,
        "price" => $price
    ];

    $total += $subtotal;
}

if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

/* Insert order */
$order_sql = "INSERT INTO orders (user_id, total, status) VALUES ($user_id, $total, 'Pending')";
if (!mysqli_query($conn, $order_sql)) {
    die("Error creating order: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

/* Insert order items */
foreach ($cart_items as $item) {
    $product_id = $item["product_id"];
    $qty = $item["quantity"];
    $price = $item["price"];

    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                 VALUES ($order_id, $product_id, $qty, $price)";
    if (!mysqli_query($conn, $item_sql)) {
        die("Error saving order item: " . mysqli_error($conn));
    }
}

/* Clear cart */
mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

/* Redirect */
header("Location: orders.php");
exit();
?>