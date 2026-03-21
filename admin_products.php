<?php
session_start();
include "db.php";

if(!isset($_SESSION["role"]) || $_SESSION["role"] != "admin"){
    header("Location: login.php");
    exit();
}

$msg = "";

/* =========================
   ADD PRODUCT
========================= */
if(isset($_POST["add_product"])){

    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $desc = mysqli_real_escape_string($conn, $_POST["description"]);
    $price = $_POST["price"];

    $imageName = $_FILES["image"]["name"];
    $tmp = $_FILES["image"]["tmp_name"];
    $folder = "uploads/" . $imageName;

    if(move_uploaded_file($tmp, $folder)){
        $sql = "INSERT INTO products (name, description, price, image)
                VALUES ('$name','$desc','$price','$imageName')";
        mysqli_query($conn,$sql);
        $msg = "Product added successfully!";
    } else {
        $msg = "Image upload failed!";
    }
}

/* =========================
   DELETE PRODUCT
========================= */
if(isset($_GET["delete"])){
    $id = $_GET["delete"];
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    header("Location: admin_products.php");
    exit();
}

/* =========================
   GET PRODUCTS
========================= */
$products = [];
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($result)){
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Products</title>
<link rel="stylesheet" href="style.css">

<style>
body{ background:#f1f5f9; }

.admin-header{
    background:#102a43;
    padding: 10px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.admin-header h1{ color:#fbbf24; }

.container{
    display:flex;
    gap:40px;
    padding:40px;
}

.form-box{
    background:white;
    padding:25px;
    border-radius:16px;
    width:350px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

input, textarea{
    width:100%;
    padding:10px;
    margin-top:10px;
    border-radius:8px;
    border:1px solid #ccc;
}

button{
    width:100%;
    margin-top:15px;
    padding:12px;
    background:#1e3a8a;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

.product-list{
    flex:1;
}

.product-card{
    background:white;
    padding:15px;
    border-radius:12px;
    margin-bottom:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
}

.action-btn{
    display:inline-block;
    padding:6px 10px;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    margin-right:5px;
}

.edit{ background:#fbbf24; color:#1e3a8a; }
.delete{ background:#dc2626; color:white; }
.success{ color:green; text-align:center; }
</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav><a href="dashboard.php">Dashboard</a></nav>
</header>

<div class="container">

    <!-- LEFT: FORM -->
    <div class="form-box">
    <h3 style="margin-bottom: 20px; color: #1e3a8a;">Add New Product</h3>

        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 5px;">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Product Name</label>
                <input type="text" name="name" placeholder="e.g. Fresh Strawberry Jam" required>
            </div>

            <div style="margin-bottom: 5px;">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Description</label>
                <textarea name="description" placeholder="Describe the item..." required></textarea>
            </div>

            <div style="margin-bottom: 5px;">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Price (₱)</label>
                <input type="number" name="price" step="0.01" placeholder="0.00" required>
            </div>

            <div style="margin-bottom: 5px;">
                <label style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;">Product Image</label>
                <input type="file" name="image" style="border:none; padding-left:0;" required>
            </div>

            <button type="submit" name="add_product" class="add-btn">Add Product</button>
        </form>

        <p class="success"><?= $msg ?></p>
    </div>

    <!-- PRODUCT GRID (same as products.php) -->
    <div class="product-grid">

    <?php foreach($products as $p): ?>

        <div class="product-card">

            <?php
                $img = (!empty($p["image"])) ? $p["image"] : "placeholder.png";
            ?>

            <img src="uploads/<?= htmlspecialchars($img) ?>" 
                alt="<?= htmlspecialchars($p["name"]) ?>">

            <h3><?= htmlspecialchars($p["name"]) ?></h3>

            <p><?= htmlspecialchars($p["description"]) ?></p>

            <strong>₱<?= number_format($p["price"],2) ?></strong>

            <!-- Admin Actions -->
            <div style="margin-top:15px;">

            <a href="edit_product.php?id=<?= $p["id"] ?>" 
            style="display:inline-block;
                    background:#fbbf24;
                    color:#1e3a8a;
                    padding:8px 14px;
                    border-radius:6px;
                    text-decoration:none;
                    font-size:13px;
                    font-weight:600;
                    margin-right:8px;">
                Edit
            </a>

            <a href="admin_products.php?delete=<?= $p["id"] ?>"
            onclick="return confirm('Delete this product?');"
            style="display:inline-block;
                    background:#dc2626;
                    color:white;
                    padding:8px 14px;
                    border-radius:6px;
                    text-decoration:none;
                    font-size:13px;
                    font-weight:600;">
                Delete
            </a>

        </div>
        </div>

    <?php endforeach; ?>

    </div>
</div>

</body>
</html>