<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$products = [];

$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Products | LakbayLokal</title>
<link rel="stylesheet" href="style.css">
<style>
.product-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
    gap:25px;
    padding:40px;
}
.product-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    border-left:6px solid #f59e0b;
}
.product-card h3{color:#102a43;}
.buy-btn{
    margin-top:15px;
    background:#1e3a8a; /* official add-to-cart color */
    color:white;
    border:none;
    padding:10px 15px;
    border-radius:8px;
    cursor:pointer;
    width:100%;
}
</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="dashboard.php"> Back to Dashboard</a>
    </nav>
</header>

<h2 style="padding:40px;">Local Products</h2>

<div class="product-grid">
<?php if(count($products) > 0): ?>
    <?php foreach($products as $p): ?>
        <div class="product-card">
    <?php
        $img = (!empty($p["image"])) ? $p["image"] : "placeholder.png";
        ?>
        <img src="uploads/<?= htmlspecialchars($img) ?>" 
            style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:12px;">

        <h3><?= htmlspecialchars($p["name"]) ?></h3>
        <p><?= htmlspecialchars($p["description"]) ?></p>
        <strong>₱<?= number_format($p["price"],2) ?></strong>

        <form method="post" action="add_to_cart.php">
            <input type="hidden" name="product_id" value="<?= $p["id"] ?>">
            <button class="buy-btn">Add to Cart</button>
        </form>
    </div>

    <?php endforeach; ?>
<?php else: ?>
    <p>No products available.</p>
<?php endif; ?>
</div>

</body>
</html>
