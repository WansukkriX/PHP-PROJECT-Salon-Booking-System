<?php
session_start();
$conn = new mysqli("localhost", "root", "", "beauty_salon");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ดึงข้อมูลบริการ (services)
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);

// ดึงข้อมูลประกาศ (news) เรียงจากล่าสุด
$news_query = "SELECT * FROM news ORDER BY created_at DESC LIMIT 3"; // แสดงประกาศล่าสุด 3 รายการ
$news_result = $conn->query($news_query);

// ตรวจสอบสถานะการล็อกอิน
$is_logged_in = isset($_SESSION['user_id']); // เช็คว่าผู้ใช้ล็อกอินหรือไม่
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Salon - หน้าหลัก</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Kanit', sans-serif;
        }

        body {
            background-color: #fff;
        }

        .menu-bar {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            text-align: right;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .menu-bar a {
            text-decoration: none;
            color: #333;
            margin: 0 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .menu-bar a:hover {
            background-color: #ff69b4;
            color: white;
        }

        .hero-section {
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('img/bg.jpg') center/cover no-repeat;
            color: white;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .service-button {
            margin-top:20px;
    background-color: #ff69b4;
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    z-index: 100;
}

.service-button:hover {
    background-color: #ff1493;
    transform: scale(1.1);
}

        .news-section {
            padding: 4rem 2rem;
            background-color: #fff;
        }

        .news-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .news-item {
    background-color: #fff;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.news-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

      
        .news-item h3 {
            color: #ff69b4;
            margin-bottom: 1rem;
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
    .hero-content h1 {
        font-size: 2rem;
    }

    .hero-content p {
        font-size: 1rem;
    }

    .service-button {
        font-size: 1rem;
        padding: 0.8rem 1.5rem;
    }

    .news-section {
        padding: 2rem 1rem;
    }

    .news-item {
        padding: 1rem;
    }
}   

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-content h1, .hero-content p, .hero-content .service-button {
    animation: fadeInUp 1s ease-out;
}

.hero-content h1 {
    animation-delay: 0.3s;
}

.hero-content p {
    animation-delay: 0.6s;
}

.hero-content .service-button {
    animation-delay: 0.9s;
}
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="menu-bar">
        <a href="index.php">หน้าแรก</a>
        <a href="services.php">บริการ</a>
        <!-- <a href="booking.php">จองคิว</a> -->
        <?php if ($is_logged_in): ?>
            <!-- ถ้าผู้ใช้ล็อกอินแล้ว -->
            <a href="logout.php">ออกจากระบบ</a>
        <?php else: ?>
            <!-- ถ้าผู้ใช้ยังไม่ได้ล็อกอิน -->
            <a href="login.php">ล็อกอิน</a>
        <?php endif; ?>
    </div>

    <section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>ยินดีต้อนรับสู่ ร้านเก๋ไก๋สไลเดอร์ บิวตี้ซาลอน</h1>
        <p>ให้เราดูแลความงามของคุณ</p>
        <a href="services.php" class="service-button">ดูบริการของเรา</a>
    </div>
</section>

    <section class="news-section">
        <div class="news-container">
            <h2>ประกาศจากทางร้าน</h2>
            <div class="news">
                <?php
                if ($news_result->num_rows > 0) {
                    while($news = $news_result->fetch_assoc()) {
                        echo "<div class='news-item'>
                                <h3>" . htmlspecialchars($news['title']) . "</h3>
                                <p>" . htmlspecialchars($news['content']) . "</p>
                                <p><small>ประกาศเมื่อ: " . htmlspecialchars($news['created_at']) . "</small></p>
                              </div>";
                    }
                } else {
                    echo "<p>ไม่มีประกาศในขณะนี้</p>";
                }
                ?>
            </div>
        </div>
    </section>

    <footer>
        <p>Kakai Beautysalon | ติดต่อเรา: Kakaibeautysalon@gmail.com</p>
    </footer>
</body>
</html>