<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sellers</title>
    <link rel="stylesheet" href="style.css">

    <style>
        :root {
            --primary: #183153;
            --primary-dark: #10253d;
            --accent: #f4b400;
            --accent-dark: #d89c00;
            --text: #1e293b;
            --muted: #64748b;
            --bg-soft: #f5f7fb;
            --card: #fcfdff;
            --border: #e2e8f0;
            --shadow: 0 16px 35px rgba(15, 23, 42, 0.08);
        }

        body {
            background: var(--bg-soft);
            margin: 0;
        }

        /* =========================
           SELLERS SECTION
        ========================== */
        .overview {
            max-width: 1400px;
            margin: 50px auto 70px;
            padding: 0 18px;
        }

        .sellers-section-card {
            background: var(--card);
            border-radius: 28px;
            padding: 48px 42px;
            box-shadow: var(--shadow);
            border: none;
        }

        .overview-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-eyebrow {
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--accent-dark);
            background: rgba(244, 180, 0, 0.12);
            padding: 8px 14px;
            border-radius: 999px;
            margin-bottom: 16px;
        }

        .overview h2 {
            font-size: 42px;
            line-height: 1.15;
            color: var(--primary);
            margin: 0 0 12px;
            font-weight: 800;
        }

        .subtitle {
            font-size: 18px;
            color: var(--muted);
            margin: 0 auto;
            max-width: 760px;
            line-height: 1.7;
            text-align: center;
        }

        /* =========================
           SELLER GRID
        ========================== */
        .seller-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 26px;
        }

        .seller-card {
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 32px 26px 28px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
            transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .seller-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent), #ffd15c);
        }

        .seller-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.10);
            border-color: #d8e2ee;
        }

        .seller-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: rgba(244, 180, 0, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: var(--accent-dark);
            box-shadow: inset 0 0 0 1px rgba(244, 180, 0, 0.12);
        }

        .seller-card h3 {
            margin: 0 0 14px;
            font-size: 28px;
            line-height: 1.4;
            color: var(--primary);
            font-weight: 800;
            text-align: center;
        }

        .seller-card p {
            margin: 0 auto 24px;
            font-size: 18px;
            line-height: 1.9;
            color: var(--muted);
            text-align: center;
            max-width: 90%;
        }

        .seller-tag {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 22px;
            border-radius: 999px;
            background: var(--accent);
            color: var(--primary);
            font-weight: 700;
            font-size: 15px;
            line-height: 1;
            box-shadow: 0 10px 22px rgba(244, 180, 0, 0.20);
            text-align: center;
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

        /* =========================
           RESPONSIVE
        ========================== */
        @media (max-width: 1100px) {
            .seller-grid {
                grid-template-columns: repeat(2, minmax(260px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .overview {
                padding: 0 12px;
                margin: 30px auto 50px;
            }

            .sellers-section-card {
                padding: 28px 22px;
                border-radius: 22px;
            }

            .overview h2 {
                font-size: 30px;
            }

            .subtitle {
                font-size: 16px;
            }

            .seller-grid {
                grid-template-columns: 1fr;
                gap: 18px;
            }

            .seller-card {
                padding: 26px 20px 24px;
            }

            .seller-card h3 {
                font-size: 22px;
            }

            .seller-card p {
                font-size: 15px;
                line-height: 1.8;
                max-width: 100%;
            }

            .seller-tag {
                font-size: 14px;
                padding: 11px 18px;
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
    <div class="logo">LakbayLokal Marketplace</div>
    <nav>
        <a href="index.php">Home</a>
        <a href="catalog.php">Products</a>
        <a href="tourism.php">Tourism Areas</a>
        <a href="sellers.php">Sellers</a>
        <a href="login.php" class="btn">Login</a>
    </nav>
</header>

<section class="overview">
    <div class="sellers-section-card">
        <div class="overview-header">
            <span class="section-eyebrow">Local Producers</span>
            <h2>Lingayen Local Producers</h2>
            <p class="subtitle">
                Authentic products inspired by Pangasinan’s culture, craftsmanship, and coastal heritage.
            </p>
        </div>

        <div class="seller-grid">

            <div class="seller-card">
                <div class="seller-icon">F</div>
                <h3>Lingayen Bangus Cooperative</h3>
                <p>
                    A group of coastal fishers and farmers producing premium-quality bangus (milkfish),
                    Pangasinan’s most famous product. Their harvests support Lingayen’s fishing communities
                    and supply fresh and processed seafood to tourists and local markets.
                </p>
                <span class="seller-tag">Seafood & Delicacies</span>
            </div>

            <div class="seller-card">
                <div class="seller-icon">N</div>
                <h3>Pangasinan Native Delicacies</h3>
                <p>
                    This group specializes in traditional Pangasinense food products such as tupig,
                    kakanin, bagoong, and sweet rice cakes that reflect the flavors of Lingayen’s festivals
                    and local celebrations.
                </p>
                <span class="seller-tag">Local Food Products</span>
            </div>

            <div class="seller-card">
                <div class="seller-icon">C</div>
                <h3>Lingayen Handicraft Makers</h3>
                <p>
                    Local artisans creating souvenirs, woven items, and cultural crafts inspired by
                    Pangasinan’s history, beaches, and the legacy of Princess Urduja.
                </p>
                <span class="seller-tag">Cultural Souvenirs</span>
            </div>

        </div>
    </div>
</section>

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