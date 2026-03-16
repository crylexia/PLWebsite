<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION["role"] ?? "buyer";
?>

<!DOCTYPE html>
<html>
<head>
<title>Dashboard | LakbayLokal</title>
<link rel="stylesheet" href="style.css">
<style>
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#f3f4f6;
}

.dashboard {
    padding: 60px;
}

/* Welcome Section */
.welcome {
    background: linear-gradient(to right, #102a43, #1e3a8a);
    color: white;
    padding: 50px;
    border-radius: 16px;
}

.welcome h1 {
    color: #fbbf24;
    margin:0;
}

/* Cards */
.cards {
    display: flex;
    gap: 30px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.card {
    background: white;
    flex: 1;
    min-width: 250px;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-left: 8px solid #f59e0b;
    text-decoration:none;
    color:inherit;
    transition:0.3s;
}

.card:hover{
    transform:translateY(-5px);
}

.card h3 {
    color: #102a43;
    margin-top:0;
}

/* Header */
header{
    background:#102a43;
    padding:15px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    color:white;
    font-weight:600;
    font-size:18px;
}

nav a{
    color:white;
    text-decoration:none;
    margin-left:20px;
    font-weight:500;
}

nav a:hover{
    color:#fbbf24;
}
</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="dashboard">

    <div class="welcome">
        <h1>Welcome to LakbayLokal</h1>
        <p>You are successfully logged in to the LGU Local Products Marketplace.</p>
    </div>

    <div class="cards">

    <?php if($role === "admin"): ?>

        <a href="admin_products.php" class="card" style="border-left:8px solid #dc2626;">
            <h3>LGU Product Control</h3>
            <p>Add, edit, and remove local products.</p>
        </a>

        <a href="orders.php" class="card" style="border-left:8px solid #2563eb;">
            <h3>Order Management</h3>
            <p>Approve and manage customer orders.</p>
        </a>

        <a href="review_records.php" class="card" style="border-left:8px solid #16a34a;">
            <h3>Review Records</h3>
            <p>View all submitted product reviews.</p>
        </a>

    <?php else: ?>

        <a href="products.php" class="card">
            <h3>Browse Products</h3>
            <p>Explore authentic LGU-approved products.</p>
        </a>
        
        <a href="cart.php" class="card" style="border-left:8px solid #f59e0b;">
            <h3>My Cart</h3>
            <p>View items you added and proceed to checkout.</p>
        </a>

        <a href="orders.php" class="card">
            <h3>My Orders</h3>
            <p>Track your purchases and payments.</p>
        </a>

        <a href="reviews.php" class="card">
            <h3>My Reviews</h3>
            <p>View and manage your submitted reviews.</p>
        </a>

    <?php endif; ?>

    </div>

</div>

</body>
</html>