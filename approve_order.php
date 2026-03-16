<?php
session_start();
if ($_SESSION["role"] !== "admin") {
    die("Unauthorized");
}

include "db.php";

$order_id = $_POST["order_id"];

mysqli_query($conn, "UPDATE orders SET status='Approved' WHERE id='$order_id'");

$admin = $_SESSION["user_id"];
mysqli_query($conn, "INSERT INTO order_audit (order_id, admin_id, action)
                     VALUES ('$order_id','$admin','APPROVED')");

header("Location: orders.php");
exit();
?>
