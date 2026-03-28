<?php
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
<title>Product Catalog | LakbayLokal</title>
<link rel="stylesheet" href="style.css">

<style>
.product-grid{
    display:grid;
    grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
    gap:25px;
    padding:60px;
}

.product-card{
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    border-left:6px solid #f59e0b;
}

.product-card h3{
    color:#102a43;
}

/* Category badge — matches product.php style */
.product-category{
    display: inline-block;
    background: #eff6ff;
    color: #1e3a8a;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 10px;
    border-radius: 999px;
    margin-bottom: 8px;
}

.login-btn{
    margin-top:15px;
    display:block;
    background:#1e3a8a;
    color:white;
    padding:10px;
    text-align:center;
    border-radius:8px;
    text-decoration:none;
}
</style>

</head>

<body>

<header>
<div class="logo">LakbayLokal</div>
<nav>
    <a href="index.php">Home</a>
    <a href="catalog.php">Products</a>
    <a href="tourism.php">Tourism Areas</a>
    <a href="sellers.php">Sellers</a>
    <a href="login.php" class="btn">Login</a>
</nav>
</header>

<h2 style="padding:60px 60px 0;">Local Products</h2>

<div class="product-grid">

<?php foreach($products as $p): ?>
    <div class="product-card">

        <!-- Product Image -->
        <?php 
            $img = !empty($p["image"]) ? $p["image"] : "placeholder.png";
        ?>

        <img src="uploads/<?= htmlspecialchars($img) ?>" 
             alt="<?= htmlspecialchars($p["name"]) ?>" 
             style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:12px;">

        <!-- Product Category -->
        <p class="product-category"><?= htmlspecialchars($p["category"]) ?></p>

        <!-- Product Name -->
        <h3><?= htmlspecialchars($p["name"]) ?></h3>

        <!-- Product Description -->
        <p><?= htmlspecialchars($p["description"]) ?></p>

        <!-- Price -->
        <strong>₱<?= number_format($p["price"],2) ?></strong>

        <!-- Login Button -->
        <a href="login.php" class="login-btn">Login to Buy</a>

    </div>
<?php endforeach; ?>

</div>

</body>
</html>