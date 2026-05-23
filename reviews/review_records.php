<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit();
}

include "../config/db.php";

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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review Records | LakbayLokal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

.reviews-container {
    padding: 40px;
    flex: 1;  /* this pushes the footer down */
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
    padding:25px;
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
    <nav id="main-nav"><a href="../admin/dashboard.php">Dashboard</a></nav>

    <button class="hamburger" id="hamburger-btn" aria-label="Toggle menu">
        <span></span><span></span><span></span>
    </button>
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


<footer class="site-footer">
    <div class="footer-content">
        <h3>LakbayLokal Marketplace</h3>
        <p class="footer-tagline">Your online destination for authentic souvenir products from Lingayen, Pangasinan.</p>
    </div>

    <div class="footer-bottom">
        <p>© 2026 LakbayLokal Marketplace — Promoting Lingayen Souvenir Shops and Local Products</p>
    </div>
</footer>

</body>

</html>