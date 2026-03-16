<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$product_id = $_POST["product_id"] ?? null;

if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Initialize cart if not existing
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Add or increase quantity
if (isset($_SESSION["cart"][$product_id])) {
    $_SESSION["cart"][$product_id]++;
} else {
    $_SESSION["cart"][$product_id] = 1;
}

header("Location: products.php?added=1");
exit();
