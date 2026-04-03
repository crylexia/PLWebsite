<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LakbayLokal | LGU Local Products Marketplace</title>
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            margin: 0;
            padding: 0;
        }

        /* HERO */
        .hero {
            background: linear-gradient(to right, #0f172a, #1e3a8a);
            padding: 110px 60px;
            text-align: center;
            color: white;
        }

        .hero h1 {
            font-size: 48px;
            letter-spacing: -1px;
            margin-bottom: 15px;
        }

        .hero p {
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.7;
            color: #e5e7eb;
        }

        .badge {
            display: inline-block;
            background: #fbbf24;
            color: #0f172a;
            padding: 6px 14px;
            border-radius: 999px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .hero-btn {
            display: inline-block;
            margin-top: 28px;
            background: linear-gradient(135deg, #fde68a, #fbbf24);
            color: #0f172a;
            padding: 16px 36px;
            font-size: 17px;
            font-weight: 700;
            border-radius: 999px;
            text-decoration: none;
            border: 3px solid white;
            box-shadow: 0 18px 40px rgba(0,0,0,0.35);
            transition: 0.3s ease;
        }

        .hero-btn:hover {
            transform: translateY(-3px);
            background: #ffd54a;
        }

        /* TRUST BAR */
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

        /* SECTION */
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

        /* FEATURES */
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
            margin-top: 0;
        }

        /* CTA */
        .cta {
            background: linear-gradient(135deg, #0b3c5d, #1d4ed8);
            padding: 100px 60px;
            text-align: center;
            color: white;
        }

        .cta h2 {
            font-size: 36px;
            margin: 0 0 15px;
            color: #fbbf24;
        }

        .cta p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
            color: #e5e7eb;
            text-align: center;
        }

        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #fde68a, #fbbf24);
            color: #0f172a;
            padding: 18px 42px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 999px;
            text-decoration: none;
            margin-top: 30px;
            border: 3px solid white;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            transition: 0.3s ease;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            background: #ffd54a;
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

        .footer-contact {
            margin-top: 10px;
        }

        .footer-contact h4 {
            margin: 0 0 18px;
            font-size: 24px;
            font-weight: 700;
            color: #fbbf24;
        }

        .footer-contact p {
            margin: 10px 0;
            font-size: 17px;
            line-height: 1.7;
            color: #e5e7eb;
        }

        .footer-contact strong {
            color: #f8fafc;
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

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .trust-bar {
                flex-wrap: wrap;
                gap: 12px;
                justify-content: center;
                text-align: center;
            }

            .split {
                flex-direction: column;
            }

            .split img {
                width: 100%;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero,
            .section,
            .cta {
                padding-left: 25px;
                padding-right: 25px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .cta h2 {
                font-size: 30px;
            }

            .cta p {
                font-size: 17px;
            }

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
    <a href="catalog.php" class="hero-btn">Explore Products</a>
</section>

<div class="trust-bar">
    <div class="trust-item">Partnered with LGUs</div>
    <div class="trust-item">Verified Local Sellers</div>
    <div class="trust-item">Secure Transactions</div>
    <div class="trust-item">100% Filipino Products</div>
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
    <a href="catalog.php" class="cta-btn">Start Shopping</a>
</section>

<footer class="site-footer">
    <div class="footer-content">
        <h3>LakbayLokal Marketplace</h3>
        <p class="footer-tagline">Promoting local products, culture, and tourism in Lingayen, Pangasinan.</p>

        <div class="footer-contact">
            <h4>Contact Us</h4>
            <p><strong>Email:</strong> support@lakbaylokal.ph</p>
            <p><strong>Phone:</strong> +63 912 345 6789</p>
            <p><strong>Office:</strong> Municipal Tourism & Trade Office</p>
            <p><strong>Hours:</strong> Mon - Fri | 8:00 AM - 5:00 PM</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>© 2026 LakbayLokal Marketplace — Official LGU Local Products Marketplace</p>
    </div>
</footer>

</body>
</html>