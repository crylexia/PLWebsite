<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include "db.php";

$uid = (int) $_SESSION["user_id"];
$products = [];

/* Fetch cart count */
$count_res = mysqli_query($conn, "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = $uid");
$count_row = mysqli_fetch_assoc($count_res);
$cart_count = $count_row['total_items'] ?? 0;

/* Fetch products + favorite status
   Favorites appear first */
$sql = "SELECT p.*, 
               CASE WHEN f.id IS NOT NULL THEN 1 ELSE 0 END AS is_favorite
        FROM products p
        LEFT JOIN favorites f 
            ON p.id = f.product_id AND f.user_id = $uid
        ORDER BY is_favorite DESC, p.created_at DESC";

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
    position: relative;
    background:#fff;
    border-radius:16px;
    padding:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    border-left:6px solid #f59e0b;
}
.product-card h3{
    color:#102a43;
}
.buy-btn{
    margin-top:15px;
    background:#1e3a8a;
    color:white;
    border:none;
    padding:10px 15px;
    border-radius:8px;
    cursor:pointer;
    width:100%;
    font-weight: 600;
}

/* Category badge */
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

/* Favorite heart */
.image-wrap{
    position: relative;
}

.heart-form{
    position: absolute;
    top: 8px;
    right: 8px;
    margin: 0;
}

.heart-btn{
    width: 38px;
    height: 38px;
    border: none;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    cursor: pointer;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.heart-btn.favorited{
    color: #dc2626;
}

.heart-btn.not-favorited{
    color: #9ca3af;
}

/* Quantity Selector */
.qty-wrap{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    margin-top:15px;
}

.qty-btn{
    width:36px;
    height:36px;
    border:none;
    border-radius:10px;
    background:#e2e8f0;
    color:#102a43;
    font-size:20px;
    font-weight:bold;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}

.qty-btn:hover{
    background:#cbd5e1;
}

.qty-input{
    width:60px;
    height:36px;
    text-align:center;
    border:1px solid #cbd5e1;
    border-radius:10px;
    font-size:15px;
    font-weight:600;
    color:#102a43;
}
</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="cart.php">Cart (<?= (int)$cart_count ?>)</a>
        <a href="orders.php">Orders</a>
        <a href="reviews.php">Reviews</a>
    </nav>
</header>

<h2 style="padding:40px;">Local Products</h2>

<div class="product-grid">
<?php if(count($products) > 0): ?>
    <?php foreach($products as $p): ?>
        <div class="product-card">
            <?php $img = (!empty($p["image"])) ? $p["image"] : "placeholder.png"; ?>

            <div class="image-wrap">
                <img src="uploads/<?= htmlspecialchars($img) ?>" 
                    style="width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:12px;">

                <!-- Favorite Heart Button -->
                <form method="post" action="add_to_favorite.php" class="heart-form">
                    <input type="hidden" name="product_id" value="<?= (int)$p["id"] ?>">
                    <button 
                        type="submit" 
                        class="heart-btn <?= $p["is_favorite"] ? 'favorited' : 'not-favorited' ?>"
                        title="<?= $p["is_favorite"] ? 'Remove from favorites' : 'Add to favorites' ?>">
                        <?= $p["is_favorite"] ? '♥' : '♡' ?>
                    </button>
                </form>
            </div>

            <!-- Product Category -->
            <p class="product-category"><?= htmlspecialchars($p["category"]) ?></p>

            <h3><?= htmlspecialchars($p["name"]) ?></h3>
            <p><?= htmlspecialchars($p["description"]) ?></p>
            <strong>₱<?= number_format($p["price"],2) ?></strong>

            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= (int)$p["id"] ?>">

                <!-- Quantity Selector -->
                <div class="qty-wrap">
                    <button type="button" class="qty-btn" onclick="changeQty(<?= (int)$p['id'] ?>, -1)">−</button>
                    <input 
                        type="number" 
                        name="quantity" 
                        id="qty-<?= (int)$p['id'] ?>" 
                        class="qty-input" 
                        value="1" 
                        min="1"
                        readonly>
                    <button type="button" class="qty-btn" onclick="changeQty(<?= (int)$p['id'] ?>, 1)">+</button>
                </div>

                <button class="buy-btn" type="submit">Add to Cart</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="padding:40px;">No products available.</p>
<?php endif; ?>
</div>

<script>
function changeQty(productId, change) {
    const input = document.getElementById('qty-' + productId);
    let current = parseInt(input.value) || 1;
    current += change;

    if (current < 1) current = 1;

    input.value = current;
}
</script>

</body>
</html>