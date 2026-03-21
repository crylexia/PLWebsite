<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";
$uid = $_SESSION["user_id"];

// Fetch cart items from the DATABASE instead of just session
$sql = "SELECT c.quantity, p.* FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = $uid";

$result = mysqli_query($conn, $sql);

$products = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row["qty"] = $row["quantity"];
    $row["subtotal"] = $row["qty"] * $row["price"];
    $total += $row["subtotal"];
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Cart | LakbayLokal</title>
<link rel="stylesheet" href="style.css">
<style>
.cart-container{
    padding:40px;
}
.cart-title{
    color:#102a43;
    font-size:28px;
    margin-bottom:20px;
}
.cart-table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}
.cart-table th, .cart-table td{
    padding:15px;
    text-align:left;
}
.cart-table th{
    background:#1e3a8a;
    color:white;
}
.cart-table tr:nth-child(even){
    background:#f3f4f6;
}
.cart-total{
    margin-top:30px;
    background:#102a43;
    color:white;
    padding:25px;
    border-radius:16px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.checkout-btn{
    background:#f59e0b;
    border:none;
    padding:15px 30px;
    font-size:16px;
    border-radius:10px;
    cursor:pointer;
}
.empty-cart{
    background:white;
    padding:50px;
    text-align:center;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}
</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="reviews.php">Reviews</a>
    </nav>
</header>

<div class="cart-container">

<h2 class="cart-title">🛒 Your Shopping Cart</h2>

<?php if(empty($products)): ?>
    <div class="empty-cart">
        <h3>Your cart is empty</h3>
        <p>Browse Lingayen local products and add items to your cart.</p>
        <a href="products.php" class="btn">Shop Now</a>
    </div>
<?php else: ?>

<table class="cart-table">
<tr>
    <th>Product</th>
    <th>Price</th>
    <th>Quantity</th>
    <th>Subtotal</th>
</tr>

<?php foreach($products as $p): ?>
<tr>
    <td><?= $p["name"] ?></td>
    <td>₱<?= number_format($p["price"],2) ?></td>
    <td><?= $p["qty"] ?></td>
    <td>₱<?= number_format($p["subtotal"],2) ?></td>
</tr>
<?php endforeach; ?>

</table>

<div class="cart-total">
    <h2>Total: ₱<?= number_format($total,2) ?></h2>
    <form action="checkout.php" method="post">
        <button class="checkout-btn">Proceed to Checkout</button>
    </form>
</div>

<?php endif; ?>

</div>

</body>
</html>
