<!DOCTYPE html>
<html>
<head>
<title>LakbayLokal | LGU Local Products Marketplace</title>
<link rel="stylesheet" href="style.css">
<style>
.hero {
    background: linear-gradient(to right, #0f172a, #1e3a8a);
    padding: 110px 60px;
}

.hero h1 {
    font-size: 48px;
    letter-spacing: -1px;
}

.hero p {
    max-width: 650px;
}

.trust-bar {
    background: #ffffff;
    padding: 25px 60px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.trust-item {
    font-weight: 600;
    color: #0f172a;
}

.section {
    padding: 100px 60px;
}

.split {
    display: flex;
    gap: 60px;
    align-items: center;
}

.split img {
    width: 50%;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.badge {
    display: inline-block;
    background: #fbbf24;
    padding: 6px 14px;
    border-radius: 999px;
    font-weight: 600;
    margin-bottom: 15px;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    margin-top: 50px;
}

.feature-card {
    background: white;
    padding: 40px;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.feature-card h3 {
    color: #0f172a;
}

.cta {
    background: linear-gradient(to right, #16a34a, #22c55e);
    padding: 90px 60px;
    text-align: center;
    color: white;
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
    <a href="register.php" class="btn">Register</a>
    <a href="login.php" class="btn">Login</a>
</nav>
</header>

<section class="hero">
    <span class="badge">LGU Verified Marketplace</span>
    <h1>Discover Filipino Culture Through Local Products</h1>
    <p>Shop authentic, LGU-approved products from provinces and cities across the Philippines while supporting community enterprises.</p>
    <a href="catalog.php"><button>Explore Products</button></a>
</section>

<div class="trust-bar">
    <div class="trust-item">🏛 Partnered with LGUs</div>
    <div class="trust-item">🛍 Verified Local Sellers</div>
    <div class="trust-item">📦 Secure Transactions</div>
    <div class="trust-item">🇵🇭 100% Filipino Products</div>
</div>

<section class="section">
    <div class="split">
        <div>
            <h2>Built for Tourism and Community Growth</h2>
            <p>LakbayLokal is more than a store — it is a digital tourism platform that connects every product to a place, story, and culture.</p>
        </div>
        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e" alt="Philippines">
    </div>
</section>

<section class="section">
    <h2>Why LGUs and MSMEs Trust LakbayLokal</h2>

    <div class="feature-grid">
        <div class="feature-card">
            <h3>LGU-Regulated Marketplace</h3>
            <p>Tourism offices approve sellers and products to ensure authenticity and quality.</p>
        </div>
        <div class="feature-card">
            <h3>Tourism-Driven Commerce</h3>
            <p>Every product promotes destinations, festivals, and local heritage.</p>
        </div>
        <div class="feature-card">
            <h3>Nationwide Market Access</h3>
            <p>Local MSMEs reach customers across the Philippines and beyond.</p>
        </div>
    </div>
</section>

<section class="cta">
    <h2>Support Local. Travel Through Culture.</h2>
    <p>Join thousands of customers who choose authentic Filipino products.</p>
    <a href="products.php" class="cta-btn">Start Shopping</a>
</section>

<footer>
    <p>© 2026 LakbayLokal — Official LGU Local Products Marketplace</p>
</footer>

</body>
</html>
