<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include "../config/db.php";
include "../config/csrf.php";
$uid = $_SESSION["user_id"];

// Fetch cart items from the DATABASE instead of just session
$stmt = $conn->prepare(
    "SELECT c.quantity, p.* FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?"
);
$stmt->bind_param("i", $uid);
$stmt->execute();
$result = $stmt->get_result();

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart | LakbayLokal</title>
<link rel="stylesheet" href="../assets/css/style.css">
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
    padding:70px;
    text-align:center;
    border-radius:16px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
}

        /* FOOTER */
        .site-footer {
            background: #183153;
            color: #f8fafc;
            margin-top: 0;
            border-top: 4px solid #f4b400;
            text-align: center;
        }

        .footer-content {
            max-width: 850px;
            margin: 0 auto;
            padding: 40px 20px 28px;
        }

        .footer-content h3 {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            color: #f4b400;
        }

        .footer-tagline {
            margin: 14px auto 30px;
            font-size: 17px;
            line-height: 1.7;
            color: #dbe4ef;
            max-width: 680px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding: 16px 20px;
        }

        .footer-bottom p {
            margin: 0;
            font-size: 14px;
            color: #cbd5e1;
        }

@media (max-width: 768px){
.cart-wrapper, .cart-container { padding: 16px !important; }
table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
.footer-content h3 {
                font-size: 24px;
            }

            .footer-tagline {
                font-size: 15px;
                margin-bottom: 24px;
            }

            .footer-contact h4 {
                font-size: 20px;
            }

            .footer-contact p {
                font-size: 15px;
            }

            .footer-bottom p {
                font-size: 13px;
            }
}


/* --- Hamburger Button --- */
.hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    width: auto;
    margin-top: 0;
}
.hamburger span {
    display: block;
    width: 24px;
    height: 3px;
    background: #fbbf24;
    border-radius: 3px;
    transition: 0.3s ease;
}
.hamburger.open span:nth-child(1) { transform: translateY(8px) rotate(45deg); }
.hamburger.open span:nth-child(2) { opacity: 0; }
.hamburger.open span:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
@media (max-width: 768px) {
    header { flex-wrap: nowrap !important; position: relative; }
    .hamburger { display: flex; }
    #main-nav {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: rgba(16, 42, 67, 0.98);
        padding: 12px 0;
        z-index: 999;
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    #main-nav.open { display: flex; }
    #main-nav a {
        margin: 0;
        padding: 12px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.07);
        font-size: 15px;
    }
    #main-nav a:last-child { border-bottom: none; }
    #main-nav a.btn {
        margin: 8px 24px;
        text-align: center;
        border-radius: 8px;
        border-bottom: none;
    }
}

</style>
</head>

<body>

<header>
    <div class="logo">LakbayLokal Marketplace</div>
    <nav id="main-nav">
        <a href="../admin/dashboard.php">Dashboard</a>
        <a href="products.php">Products</a>
        <a href="orders.php">Orders</a>
        <a href="../reviews/reviews.php">Reviews</a>
    </nav>

    <button class="hamburger" id="hamburger-btn" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>
</header>

<div class="cart-container">

<h2 class="cart-title"> Your Shopping Cart</h2>

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
    <td><?= htmlspecialchars($p["name"], ENT_QUOTES, 'UTF-8') ?></td>
    <td>₱<?= number_format($p["price"],2) ?></td>
    <td><?= $p["qty"] ?></td>
    <td>₱<?= number_format($p["subtotal"],2) ?></td>
</tr>
<?php endforeach; ?>

</table>

<div class="cart-total">
    <h2>Total: ₱<?= number_format($total,2) ?></h2>
    <form action="checkout.php" method="post">
    <?= csrf_field() ?>
        <button class="checkout-btn">Proceed to Checkout</button>
    </form>
</div>

<?php endif; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var btn = document.getElementById("hamburger-btn");
    var nav = document.getElementById("main-nav");
    if (!btn || !nav) return;
    btn.addEventListener("click", function () {
        nav.classList.toggle("open");
        btn.classList.toggle("open");
    });
    nav.querySelectorAll("a").forEach(function(link) {
        link.addEventListener("click", function() {
            nav.classList.remove("open");
            btn.classList.remove("open");
        });
    });
});
</script>
</body>
<footer class="site-footer">
    <div class="footer-content">
        <h3>LakbayLokal Marketplace</h3>
        <p class="footer-tagline">Your online destination for authentic souvenir products from Lingayen, Pangasinan.</p>
    </div>

    <div class="footer-bottom">
        <p>© 2026 LakbayLokal Marketplace — Promoting Lingayen Souvenir Shops and Local Products</p>
    </div>
</footer>
</html>
