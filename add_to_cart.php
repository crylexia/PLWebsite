<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$product_id = $_POST["product_id"] ?? null;

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Check if item exists in DB cart
$check = mysqli_query($conn, "SELECT id FROM cart WHERE user_id = $user_id AND product_id = $product_id");

if (mysqli_num_rows($check) > 0) {
    // Increment quantity
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id = $user_id AND product_id = $product_id");
} else {
    // New insertion
    mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)");
}

// Redirect back with success flag
header("Location: products.php?added=1");
exit();