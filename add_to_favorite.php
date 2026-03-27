<?php
session_start();
include "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["product_id"])) {
    $user_id = $_SESSION["user_id"];
    $product_id = (int) $_POST["product_id"];

    // Check if already favorited
    $check = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id = $user_id AND product_id = $product_id");

    if (mysqli_num_rows($check) == 0) {
        mysqli_query($conn, "INSERT INTO favorites (user_id, product_id) VALUES ($user_id, $product_id)");
    }

    header("Location: products.php");
    exit();
}
?>