<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

/* If admin → see all reviews
   If customer → see only their own reviews */
if($_SESSION["role"] === "admin"){
    $sql = "SELECT r.*, p.name, u.username
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            ORDER BY r.created_at DESC";
} else {
    $uid = $_SESSION["user_id"];
    $sql = "SELECT r.*, p.name, u.username
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            WHERE r.user_id = $uid
            ORDER BY r.created_at DESC";
}

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Review Records | LakbayLokal</title>
<link rel="stylesheet" href="style.css">
<style>
.reviews-container{
    padding:40px;
}

.reviews-title{
    font-size:28px;
    color:#102a43;
    margin-bottom:20px;
}

.reviews-table{
    width:100%;
    background:white;
    border-radius:16px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    border-collapse:collapse;
}

.reviews-table th,
.reviews-table td{
    padding:15px;
    text-align:left;
}

.reviews-table th{
    background:#1e3a8a;
    color:white;
}

.rating-badge{
    background:#f59e0b;
    color:white;
    padding:6px 12px;
    border-radius:12px;
    font-size:14px;
}

.comment{
    max-width:300px;
    color:#374151;
}

.back-link{
    display:inline-block;
    margin-bottom:15px;
    text-decoration:none;
    color:#1e3a8a;
    font-weight:600;
}
</style>
</head>

<body>

<header>
<div class="logo">LakbayLokal Marketplace</div>
<nav>
    <a href="dashboard.php">Dashboard</a>
</nav>
</header>

<div class="reviews-container">

<h2 class="reviews-title">⭐ Review Records</h2>

<table class="reviews-table">
<tr>
    <th>Review ID</th>
    <th>Product</th>
    <th>User</th>
    <th>Rating</th>
    <th>Comment</th>
    <th>Date</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
    <td>#<?= $row["id"] ?></td>
    <td><?= $row["name"] ?></td>
    <td><?= $row["username"] ?></td>
    <td>
        <span class="rating-badge">
            <?= $row["rating"] ?>/5
        </span>
    </td>
    <td class="comment"><?= $row["comment"] ?></td>
    <td><?= date("M d, Y", strtotime($row["created_at"])) ?></td>
</tr>
<?php endwhile; ?>

</table>

</div>

</body>
</html>