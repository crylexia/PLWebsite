<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourism Areas</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .tourism-image {
            width: 45%;
            flex-shrink: 0;
        }

        #instaSlider {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            line-height: 0;
            user-select: none;
        }

        #tourismMainImage {
            width: 100%;
            height: 420px;
            object-fit: cover;
            display: block;
            border-radius: 20px;
            transition: opacity 0.25s ease;
        }

        .arrow-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(0,0,0,0.50);
            color: #fff;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            z-index: 20;
            backdrop-filter: blur(4px);
            transition: background 0.2s ease, transform 0.2s ease;
            line-height: 1;
        }

        .arrow-btn:hover {
            background: rgba(0,0,0,0.78);
            transform: translateY(-50%) scale(1.1);
        }

        .left-arrow  { left: 14px; }
        .right-arrow { right: 14px; }

        /* Dots stay inside the image at the bottom */
        .slider-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 7px;
            z-index: 20;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.50);
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .dot.active {
            background: #fff;
            transform: scale(1.35);
        }

        /* Caption sits BELOW the slider box */
        .slider-caption-bar {
            margin-top: 10px;
            text-align: center;
            padding: 10px 16px;
        }

        .slider-caption-bar span {
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            letter-spacing: 0.3px;
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
    <h2>Lingayen, Pangasinan</h2>
    <p class="subtitle">A Coastal Destination Rich in History and Heritage</p>

    <div class="tourism-layout">
        <div class="tourism-text">
            <p>
                Lingayen, the capital town of Pangasinan, is a distinguished coastal destination where history, culture, and natural beauty converge.
                Situated along the shores of Lingayen Gulf, the municipality is known for its scenic beachfront, significant heritage landmarks,
                and its important role in Philippine history.
            </p>

            <ul>
                <li><b>Capitol Beachfront and Baywalk</b> – A scenic seaside promenade ideal for leisurely walks, sunset viewing, and family recreation.</li>
                <li><b>Pangasinan Provincial Capitol</b> – A prominent neoclassical structure recognized as one of the finest capitol buildings in the country.</li>
                <li><b>Urduja House</b> – The official residence of the Governor of Pangasinan, named after the legendary Princess Urduja.</li>
                <li><b>Casa Real and Pangasinan Provincial Museum</b> – A historic site that preserves and presents the rich heritage of Pangasinan.</li>
                <li><b>Sison Auditorium</b> – A longstanding cultural venue for performances, public programs, and community events.</li>
                <li><b>Veterans Memorial Park</b> – A commemorative landmark reflecting Lingayen's significance in World War II history.</li>
            </ul>

            <p>
                With its combination of historical landmarks, coastal attractions, and cultural identity, Lingayen offers visitors a meaningful
                and memorable travel experience in the heart of Pangasinan.
            </p>

            <a href="catalog.php" class="cta-btn">Explore Local Products</a>
        </div>

        <div class="tourism-image">
            <!-- Slider box -->
            <div id="instaSlider">
                <img id="tourismMainImage" src="pictures/lingbeach.jpg" alt="Lingayen Tourism Area">

                <span class="arrow-btn left-arrow"  id="prevBtn">&#10094;</span>
                <span class="arrow-btn right-arrow" id="nextBtn">&#10095;</span>

                <div class="slider-dots" id="sliderDots"></div>
            </div>

            <!-- Caption below the image -->
            <div class="slider-caption-bar">
                <span id="imageCaption">Lingayen Beachfront</span>
            </div>
        </div>
    </div>
</section>

<script>
    const tourismImages = [
        { src: "pictures/lingbeach.jpg",      caption: "Lingayen Beachfront" },
        { src: "pictures/lingcapitol.jpg",     caption: "Pangasinan Provincial Capitol" },
        { src: "pictures/urdujahouse.jpg",     caption: "Urduja House" },
        { src: "pictures/provmuseum.jpg",      caption: "Casa Real and Pangasinan Provincial Museum" },
        { src: "pictures/sisonauditorium.jpg", caption: "Sison Auditorium" }
    ];

    let current = 0;
    const img      = document.getElementById("tourismMainImage");
    const caption  = document.getElementById("imageCaption");
    const dotsWrap = document.getElementById("sliderDots");

    // Build dots
    tourismImages.forEach((_, i) => {
        const d = document.createElement("span");
        d.className = "dot" + (i === 0 ? " active" : "");
        d.addEventListener("click", () => goTo(i));
        dotsWrap.appendChild(d);
    });

    function updateDots() {
        dotsWrap.querySelectorAll(".dot").forEach((d, i) =>
            d.classList.toggle("active", i === current)
        );
    }

    function goTo(index) {
        current = (index + tourismImages.length) % tourismImages.length;
        img.style.opacity = 0;
        setTimeout(() => {
            img.src = tourismImages[current].src;
            img.alt = tourismImages[current].caption;
            caption.textContent = tourismImages[current].caption;
            img.style.opacity = 1;
            updateDots();
        }, 200);
    }

    document.getElementById("prevBtn").addEventListener("click", (e) => {
        e.stopPropagation();
        goTo(current - 1);
    });
    document.getElementById("nextBtn").addEventListener("click", (e) => {
        e.stopPropagation();
        goTo(current + 1);
    });

    // Touch swipe
    let touchStartX = 0;
    const slider = document.getElementById("instaSlider");

    slider.addEventListener("touchstart", e => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    slider.addEventListener("touchend", e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) goTo(current + (diff > 0 ? 1 : -1));
    });

    // Auto-slide every 5 seconds, resets on manual click
    let autoSlide = setInterval(() => goTo(current + 1), 5000);
    slider.addEventListener("click", () => {
        clearInterval(autoSlide);
        autoSlide = setInterval(() => goTo(current + 1), 5000);
    });
</script>

</body>
</html>