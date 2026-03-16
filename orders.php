<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

/* If admin → see all orders
   If customer → see only their own orders */
if($_SESSION["role"] === "admin"){
    $sql = "SELECT o.*, u.username 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC";
} else {
    $uid = $_SESSION["user_id"];
    $sql = "SELECT o.*, u.username 
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.user_id = $uid
            ORDER BY o.created_at DESC";
}

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<title>Orders | LakbayLokal</title>
<link rel="stylesheet" href="style.css">
<style>
.orders-container{
    padding:40px;
}
.orders-title{
    font-size:28px;
    color:#102a43;
    margin-bottom:20px;
}
.orders-table{
    width:100%;
    background:white;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    border-collapse:collapse;
}
.orders-table th, .orders-table td{
    padding:15px;
    text-align:left;
}
.orders-table th{
    background:#1e3a8a;
    color:white;
}
.status{
    padding:6px 12px;
    border-radius:12px;
    font-size:14px;
    color:white;
}
.pending{ background:#f59e0b; }
.approved{ background:#16a34a; }

.approve-btn{
    background:#16a34a;
    color:white;
    border:none;
    padding:8px 12px;
    border-radius:6px;
    cursor:pointer;
}
</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="dashboard.php">← Back to Dashboard</a>
    </nav>
</header>

<div class="orders-container">
<h2 class="orders-title">📦 Orders</h2>

<table class="orders-table">
<tr>
    <th>Order ID</th>
    <th>Customer</th>
    <th>Total</th>
    <th>Status</th>
    <th>Date</th>
    <?php if($_SESSION["role"] === "admin"): ?>
        <th>Action</th>
    <?php endif; ?>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td>#<?= $row["id"] ?></td>
    <td><?= $row["username"] ?></td>
    <td>₱<?= number_format($row["total"],2) ?></td>
    <td>
        <span class="status <?= strtolower($row["status"]) ?>">
            <?= $row["status"] ?>
        </span>
    </td>
    <td><?= date("M d, Y", strtotime($row["created_at"])) ?></td>

    <?php if($_SESSION["role"] === "admin"): ?>
    <td>
        <?php if($row["status"] == "Pending"): ?>
        <form method="post" action="approve_order.php">
            <input type="hidden" name="order_id" value="<?= $row["id"] ?>">
            <button class="approve-btn">Mark as Paid</button>
        </form>
        <?php else: ?>
            ✔ Verified
        <?php endif; ?>
    </td>
    <?php endif; ?>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>
