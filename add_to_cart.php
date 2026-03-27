<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $user_id = (int) $_SESSION["user_id"];
    $product_id = (int) $_POST["product_id"];
    $quantity = isset($_POST["quantity"]) ? (int) $_POST["quantity"] : 1;

    if ($quantity < 1) {
        $quantity = 1;
    }

    // Check if product already in cart
    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id");

    if (mysqli_num_rows($check) > 0) {
        // If already in cart, increase quantity
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id");
    } else {
        // If not yet in cart, insert new row
        mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
    }

    header("Location: products.php");
    exit();
}
?>