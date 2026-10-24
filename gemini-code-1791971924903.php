<?php
session_start();
require_once "config/database.php";

// ดึงเมนูอาหารจากฐานข้อมูล
$sql = "SELECT food_id, food_name, price, description
        FROM foods
        ORDER BY food_id ASC";

$result = $conn->query($sql);

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// นับจำนวนรายการในตะกร้าทั้งหมด
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// ฟังก์ชันหาไฟล์รูปภาพอาหาร (เบื้องหลังใช้ pic1.jpg, pic2.jpg เหมือนเดิม)
function get_food_image_path($food_id) {
    $extensions = ['jpg', 'png', 'webp', 'jpeg'];
    $folders = ['assets/images/', 'images/', ''];
    
    foreach ($folders as $folder) {
        foreach ($extensions as $ext) {
            $path = "{$folder}pic{$food_id}.{$ext}";
            if (file_exists($path)) {
                return $path;
            }
        }
    }
    return "assets/images/pic{$food_id}.jpg";
}

$hero_img = get_food_image_path(1);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orderfood | สั่งอาหารออนไลน์ อร่อยสดใหม่ ส่งตรงถึงบ้านคุณ</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-50: #fff7ed;
            --primary-100: #ffedd5;
            --primary-500: #f97316;
            --primary-600: #ea580c;
            --primary-700: #c2410c;
            --gradient-orange: linear-gradient(135deg, #ff6b00 0%, #ff3d00 100%);
            --gradient-hero: linear-gradient(180deg, #fffbf7 0%, #fff4ec 100%);
            --accent-green: #10b981;
            --dark-900: #0f172a;
            --dark-800: #1e293b;
            --gray-600: #475569;
            --gray-400: #94a3b8;
            --gray-100: #f1f5f9;
            --border-light: #f3e8e0;
            --shadow-subtle: 0 10px 30px -10px rgba(249, 115, 22, 0.08);
            --shadow-card: 0 20px 40px -15px rgba(15, 23, 42, 0.07);
            --shadow-floating: 0 25px 50px -12px rgba(234, 88, 12, 0.25);
            --radius-pill: 9999px;
            --radius-2xl: 28px;
            --radius-xl: 20px;
            --radius-lg: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #faf9f6;
            color: var(--dark-900);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* --- Header Glassmorphic Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(243, 232, 224, 0.8);
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar .logo {
            font-family: 'Plus Jakarta Sans', 'Prompt', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: var(--dark-900);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .navbar .logo-icon {
            width: 44px;
            height: 44px;
            background: var(--gradient-orange);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 22px;
            box-shadow: 0 8px 20px rgba(255, 107, 0, 0.3);
        }

        .navbar nav {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar nav a {
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            padding: 10px 22px;
            border-radius: var(--radius-pill);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar nav a:hover {
            color: var(--primary-600);
            background: var(--primary-50);
        }

        .navbar nav a.active {
            color: #ffffff;
            background: var(--dark-900);
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.15);
        }

        .cart-count-badge {
            background: var(--gradient-orange);
            color: #ffffff;
            font-size: 12px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: 4px;
        }

        .navbar .user-pill {
            font-size: 14px;
            color: var(--primary-700);
            background: var(--primary-50);
            padding: 8px 20px;
            border-radius: var(--radius-pill);
            font-weight: 700;
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        /* --- Ultra-Modern Hero Section --- */
        .hero {
            position: relative;
            background: var(--gradient-hero);
            padding: 80px 6% 110px;
            overflow: hidden;
        }

        .hero-blob-1 {
            position: absolute;
            top: -150px;
            right: -100px;
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(255, 107, 0, 0.18) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(40px);
        }

        .hero-blob-2 {
            position: absolute;
            bottom: -100px;
            left: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 183, 77, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            filter: blur(40px);
        }

        .hero-wrapper {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .hero-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            color: var(--primary-600);
            padding: 8px 20px;
            border-radius: var(--radius-pill);
            font-size: 13.5px;
            font-weight: 700;
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.12);
            border: 1px solid rgba(249, 115, 22, 0.2);
            margin-bottom: 24px;
        }

        .hero-title {
            font-size: 58px;
            font-weight: 800;
            line-height: 1.15;
            color: var(--dark-900);
            margin-bottom: 22px;
            letter-spacing: -1.5px;
        }

        .hero-title .gradient-text {
            background: var(--gradient-orange);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 19px;
            color: var(--gray-600);
            margin-bottom: 36px;
            max-width: 560px;
            line-height: 1.7;
            font-weight: 400;
        }

        /* Hero Search Bar */
        .hero-search-box {
            width: 100%;
            max-width: 540px;
            background: #ffffff;
            padding: 8px 8px 8px 24px;
            border-radius: var(--radius-pill);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            border: 1.5px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 36px;
            transition: all 0.3s ease;
        }

        .hero-search-box:focus-within {
            border-color: var(--primary-500);
            box-shadow: 0 20px 40px rgba(249, 115, 22, 0.18);
        }

        .hero-search-input {
            border: none;
            outline: none;
            width: 100%;
            font-family: inherit;
            font-size: 16px;
            color: var(--dark-900);
            background: transparent;
        }

        .hero-search-btn {
            background: var(--gradient-orange);
            color: #ffffff;
            border: none;
            padding: 14px 28px;
            border-radius: var(--radius-pill);
            font-family: inherit;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(255, 61, 0, 0.3);
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .hero-search-btn:hover {
            transform: scale(1.03);
            box-shadow: 0 12px 25px rgba(255, 61, 0, 0.4);
        }

        /* Trust Perks Bar */
        .hero-perks {
            display: flex;
            align-items: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .perk-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14.5px;
            font-weight: 600;
            color: var(--dark-800);
        }

        .perk-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.04);
            border: 1px solid var(--border-light);
        }

        /* Visual Column (3D Glass Floating Effect) */
        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .hero-main-card {
            position: relative;
            width: 100%;
            max-width: 460px;
            height: 460px;
            border-radius: 44px;
            background: #ffffff;
            padding: 18px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.9);
            transform: perspective(1000px) rotateY(-8deg) rotateX(4deg);
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .hero-main-card:hover {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg) scale(1.02);
            box-shadow: 0 35px 70px -15px rgba(249, 115, 22, 0.22);
        }

        .hero-img-box {
            width: 100%;
            height: 100%;
            border-radius: 32px;
            overflow: hidden;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            position: relative;
        }

        .hero-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-img-fallback {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 130px;
            background: linear-gradient(135deg, #fed7aa 0%, #f97316 100%);
        }

        /* Glass Badges floating over image */
        .glass-float-badge {
            position: absolute;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 14px 22px;
            border-radius: 22px;
            box-shadow: var(--shadow-floating);
            border: 1px solid rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 10;
            animation: floatSlow 5s ease-in-out infinite alternate;
        }

        .glass-float-badge.badge-top {
            top: -20px;
            right: -25px;
        }

        .glass-float-badge.badge-bottom {
            bottom: -25px;
            left: -30px;
            animation-delay: -2.5s;
        }

        @keyframes floatSlow {
            0% { transform: translateY(0px); }
            100% { transform: translateY(-12px); }
        }

        .badge-avatar {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: var(--primary-50);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .badge-text-top {
            font-size: 15px;
            font-weight: 800;
            color: var(--dark-900);
        }

        .badge-text-sub {
            font-size: 12.5px;
            color: var(--gray-600);
        }

        /* --- Interactive Food Filter Tabs --- */
        .filter-section {
            padding: 40px 6% 0;
            max-width: 1280px;
            margin: 0 auto;
        }

        .filter-tabs {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 12px;
            scroll-behavior: smooth;
        }

        .filter-tabs::-webkit-scrollbar {
            display: none;
        }

        .tab-btn {
            background: #ffffff;
            color: var(--gray-600);
            border: 1.5px solid var(--border-light);
            padding: 12px 26px;
            border-radius: var(--radius-pill);
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .tab-btn:hover {
            border-color: var(--primary-500);
            color: var(--primary-600);
            transform: translateY(-2px);
        }

        .tab-btn.active {
            background: var(--dark-900);
            color: #ffffff;
            border-color: var(--dark-900);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
        }

        /* --- Main Menu Grid --- */
        .container {
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            padding: 50px 6% 90px;
            flex: 1;
        }

        .section-header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 20px;
        }

        .section-header .title-group h2 {
            font-size: 34px;
            font-weight: 800;
            color: var(--dark-900);
            letter-spacing: -0.5px;
        }

        .section-header .title-group p {
            color: var(--gray-600);
            font-size: 16px;
            margin-top: 4px;
        }

        .menu-count-badge {
            background: var(--primary-50);
            color: var(--primary-600);
            font-size: 14px;
            font-weight: 700;
            padding: 8px 18px;
            border-radius: var(--radius-pill);
            border: 1px solid rgba(249, 115, 22, 0.2);
        }

        /* Cards Layout */
        .food-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 32px;
        }

        .food-card {
            background: #ffffff;
            border-radius: var(--radius-2xl);
            border: 1px solid var(--border-light);
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .food-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(249, 115, 22, 0.12);
            border-color: rgba(249, 115, 22, 0.3);
        }

        .food-img-container {
            position: relative;
            width: 100%;
            height: 230px;
            overflow: hidden;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        }

        .food-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .food-card:hover .food-img-container img {
            transform: scale(1.08);
        }

        .price-chip {
            position: absolute;
            bottom: 16px;
            right: 16px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            color: #ffffff;
            font-size: 18px;
            font-weight: 800;
            padding: 6px 18px;
            border-radius: var(--radius-pill);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.15);
        }

        .rating-chip {
            position: absolute;
            top: 16px;
            left: 16px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(8px);
            color: var(--dark-900);
            font-size: 13px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: var(--radius-pill);
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .food-details {
            padding: 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .food-details h3 {
            font-size: 21px;
            font-weight: 700;
            color: var(--dark-900);
            margin-bottom: 8px;
        }

        .food-details p {
            font-size: 14.5px;
            color: var(--gray-600);
            margin-bottom: 24px;
            line-height: 1.6;
            flex: 1;
        }

        .btn-add-cart {
            width: 100%;
            background: var(--gradient-orange);
            color: #ffffff;
            border: none;
            padding: 14px 24px;
            border-radius: var(--radius-lg);
            font-family: inherit;
            font-size: 15.5px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(255, 61, 0, 0.25);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(255, 61, 0, 0.38);
        }

        /* --- Footer --- */
        .footer {
            background: #ffffff;
            border-top: 1px solid var(--border-light);
            text-align: center;
            padding: 36px 6%;
            color: var(--gray-600);
            font-size: 14px;
            margin-top: auto;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-wrapper {
                grid-template-columns: 1fr;
                gap: 50px;
                text-align: center;
            }
            .hero-content {
                align-items: center;
            }
            .hero-title {
                font-size: 44px;
            }
            .hero-search-box {
                max-width: 100%;
            }
            .hero-perks {
                justify-content: center;
            }
            .hero-main-card {
                max-width: 380px;
                height: 380px;
                transform: none;
            }
        }

        @media (max-width: 640px) {
            .navbar {
                padding: 14px 20px;
                flex-direction: column;
                gap: 12px;
            }
            .hero {
                padding: 40px 20px 70px;
            }
            .hero-title {
                font-size: 34px;
            }
            .hero-search-box {
                flex-direction: column;
                padding: 8px;
                border-radius: 24px;
                gap: 10px;
            }
            .hero-search-input {
                padding: 8px 12px;
                text-align: center;
            }
            .hero-search-btn {
                width: 100%;
            }
            .glass-float-badge.badge-top {
                right: -10px;
            }
            .glass-float-badge.badge-bottom {
                left: -10px;
            }
            .container {
                padding: 35px 20px 60px;
            }
            .food-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Header Navbar -->
<header class="navbar">
    <a class="logo" href="index.php">
        <div class="logo-icon">🍽️</div>
        <span>Orderfood</span>
    </a>

    <nav>
        <a href="index.php" class="active">🏠 หน้าแรก</a>
        <a href="cart.php">
            🛒 ตะกร้าสินค้า
            <?php if ($cart_count > 0): ?>
                <span class="cart-count-badge"><?= $cart_count ?></span>
            <?php endif; ?>
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my_orders.php">📋 ประวัติสั่งซื้อ</a>
            <span class="user-pill">
                👋 <?= e($_SESSION['full_name'] ?? 'สมาชิก') ?>
            </span>
            <a href="auth/logout.php">ออกจากระบบ</a>
        <?php else: ?>
            <a href="auth/login.php">เข้าสู่ระบบ</a>
            <a href="auth/register.php">สมัครสมาชิก</a>
        <?php endif; ?>
    </nav>
</header>

<!-- Ultra-Modern Hero Banner -->
<section class="hero">
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>

    <div class="hero-wrapper">
        <!-- Text Column -->
        <div class="hero-content">
            <div class="hero-tag">
                <span>🔥</span>
                <span>บริการสั่งอาหารอันดับ 1 ในใจคุณ</span>
            </div>

            <h1 class="hero-title">
                อร่อยจัดเต็ม <br>
                <span class="gradient-text">ส่งตรงถึงหน้าบ้านคุณ</span>
            </h1>

            <p class="hero-subtitle">
                ค้นหาและเลือกสั่งเมนูอาหารไทยยอดฮิต รสชาติเด็ด วัตถุดิบสดใหม่ ปรุงสุกร้อนๆ พร้อมเสิร์ฟความอร่อยถึงมือคุณในทุกวัน
            </p>

            <!-- Search Bar in Hero -->
            <div class="hero-search-box">
                <span style="font-size:20px; color:var(--gray-400); margin-right:8px;">🔍</span>
                <input 
                    type="text" 
                    id="searchFoodInput" 
                    class="hero-search-input" 
                    placeholder="ค้นหาเมนูโปรดของคุณ... (เช่น ผัดไทย, ข้าวมันไก่)"
                    onkeyup="filterFoodByName()"
                >
                <button class="hero-search-btn" onclick="document.getElementById('menu').scrollIntoView({behavior: 'smooth'})">
                    ค้นหาเมนู
                </button>
            </div>

            <div class="hero-perks">
                <div class="perk-item">
                    <div class="perk-icon">⚡</div>
                    <span>จัดส่งไวใน 30 นาที</span>
                </div>
                <div class="perk-item">
                    <div class="perk-icon">🌶️</div>
                    <span>ระบุโน้ตความเผ็ดได้</span>
                </div>
                <div class="perk-item">
                    <div class="perk-icon">💯</div>
                    <span>รสชาติกลมกล่อม</span>
                </div>
            </div>
        </div>

        <!-- 3D Glass Visual Column -->
        <div class="hero-visual">
            
            <div class="glass-float-badge badge-top">
                <div class="badge-avatar">🔥</div>
                <div>
                    <div class="badge-text-top">เมนูขายดีประจำวัน</div>
                    <div class="badge-text-sub">สั่งง่าย ได้ไว ไร้คิว</div>
                </div>
            </div>

            <div class="hero-main-card">
                <div class="hero-img-box">
                    <img 
                        src="<?= e($hero_img) ?>" 
                        alt="เมนูแนะนำ"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                    >
                    <div class="hero-img-fallback" style="display:none;">
                        🍛
                    </div>
                </div>
            </div>

            <div class="glass-float-badge badge-bottom">
                <div class="badge-avatar">⭐</div>
                <div>
                    <div class="badge-text-top">4.9 / 5.0 คะแนน</div>
                    <div class="badge-text-sub">จากลูกค้ารีวิวมากกว่า 1,000+ คน</div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Filter Category Tabs -->
<div class="filter-section">
    <div class="filter-tabs">
        <button class="tab-btn active" onclick="filterCategory('all', this)">
            <span>🍽️ ทั้งหมด</span>
        </button>
        <button class="tab-btn" onclick="filterCategory('rice', this)">
            <span>🍛 ข้าวผัด / จานเดียว</span>
        </button>
        <button class="tab-btn" onclick="filterCategory('noodle', this)">
            <span>🍜 ก๋วยเตี๋ยว / เส้น</span>
        </button>
        <button class="tab-btn" onclick="filterCategory('drink', this)">
            <span>🥤 เครื่องดื่ม / ของหวาน</span>
        </button>
    </div>
</div>

<!-- Main Menu Grid Section -->
<main class="container" id="menu">
    <div class="section-header">
        <div class="title-group">
            <h2>รายการอาหารทั้งหมด</h2>
            <p>คัดสรรความอร่อย พร้อมให้คุณเลือกสั่งได้ตามใจชอบ</p>
        </div>
        <div class="menu-count-badge">
            ✨ พร้อมเสิร์ฟสดใหม่ทุกวัน
        </div>
    </div>

    <div class="food-grid" id="foodGrid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($food = $result->fetch_assoc()): ?>
                <?php 
                    $food_id = (int)$food['food_id'];
                    $img_path = get_food_image_path($food_id);
                ?>
                <article class="food-card" data-name="<?= e(mb_strtolower($food['food_name'])) ?>">
                    <div class="food-img-container">
                        <div class="rating-chip">
                            <span>⭐ 4.9</span>
                        </div>

                        <img 
                            src="<?= e($img_path) ?>" 
                            alt="<?= e($food['food_name']) ?>"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        
                        <div class="food-img-fallback" style="display:none;">
                            🍛
                        </div>

                        <div class="price-chip">
                            ฿<?= number_format((float)$food['price'], 2) ?>
                        </div>
                    </div>

                    <div class="food-details">
                        <h3><?= e($food['food_name']) ?></h3>
                        <p><?= e($food['description'] ?? 'รสชาติอร่อยกลมกล่อม คัดสรรวัตถุดิบคุณภาพดี') ?></p>

                        <form action="cart.php" method="POST" style="margin-top:auto;">
                            <input type="hidden" name="food_id" value="<?= $food_id ?>">
                            <input type="hidden" name="action" value="add">
                            <button class="btn-add-cart" type="submit">
                                🛒 + ใส่ตะกร้าสั่งเลย
                            </button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; grid-column: 1/-1; padding: 60px; color: var(--gray-600); font-size:18px;">
                ยังไม่มีรายการอาหารในระบบ
            </p>
        <?php endif; ?>
    </div>
</main>

<footer class="footer">
    <p>© <?= date('Y') ?> Orderfood | สั่งอาหารออนไลน์ มั่นใจ สะดวก อร่อยสดใหม่ทุกวัน</p>
</footer>

<script>
// ระบบค้นหาเมนูอาหารแบบค้นหาทันที (Instant Live Search)
function filterFoodByName() {
    const input = document.getElementById('searchFoodInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.food-card');

    cards.forEach(card => {
        const foodName = card.getAttribute('data-name') || '';
        if (foodName.includes(input)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// ระบบเปลี่ยนแท็บหมวดหมู่
function filterCategory(cat, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    // รีเซ็ตช่องค้นหา
    document.getElementById('searchFoodInput').value = '';
    const cards = document.querySelectorAll('.food-card');
    cards.forEach(card => card.style.display = 'flex');
}
</script>

</body>
</html>