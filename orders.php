<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$is_admin = ($_SESSION["role"] === "admin");

if($is_admin){
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
    .orders-container { padding: 40px; max-width: 1200px; margin: auto; }
    .orders-title { font-size: 28px; color: #102a43; margin-bottom: 20px; }
    
    .orders-table {
        width: 100%;
        background: white;
        border-radius: 12px;
        border-collapse: collapse;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .orders-table th, .orders-table td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #f0f4f8;
    }
    
    .orders-table th { background: #1e3a8a; color: white; font-weight: 600; }

    .status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    .pending { background: #fef3c7; color: #92400e; }
    .approved { background: #dcfce7; color: #166534; }

    .approve-btn {
        background: #16a34a;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 6px;

        cursor: pointer;
        transition: background 0.2s;
        font-size: 13px;
    }
    .approve-btn:hover { background: #15803d; }
    
    .verified-text { color: #16a34a; font-weight: 600; font-size: 14px; }
</style>
</head>
<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        
        <?php if(!$is_admin): ?>
            <a href="products.php">Products</a>
                <?php
                // Fetch total quantity from the database
                $uid = $_SESSION["user_id"];
                $count_res = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $uid");
                $count_row = mysqli_fetch_assoc($count_res);
                $cart_count = $count_row['total_items'] ?? 0;
                ?>
            <a href="cart.php">Cart (<?= $cart_count ?>)</a>
            <a href="reviews.php">Reviews</a>
        <?php endif; ?>
    </nav>
</header>

<div class="orders-container">
    <h2 class="orders-title">📦 Orders Management</h2>

    <table class="orders-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <?php if($is_admin): ?>
                    <th style="text-align: center;">Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $row["id"] ?></td>
                <td><?= htmlspecialchars($row["username"]) ?></td>
                <td>₱<?= number_format($row["total"], 2) ?></td>
                <td>
                    <span class="status <?= strtolower($row["status"]) ?>">
                        <?= $row["status"] ?>
                    </span>
                </td>
                <td><?= date("M d, Y", strtotime($row["created_at"])) ?></td>

                <?php if($is_admin): ?>
                <td style="text-align: center;">
                    <?php if($row["status"] == "Pending"): ?>
                        <form method="post" action="approve_order.php" style="margin:0;">
                            <input type="hidden" name="order_id" value="<?= $row["id"] ?>">
                            <button type="submit" class="approve-btn">Mark as Paid</button>
                        </form>
                    <?php else: ?>
                        <span class="verified-text">✔ Paid</span>
                    <?php endif; ?>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>