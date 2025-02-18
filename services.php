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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
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

        header {
           
            background-image: url('img/sv.jpg');
            background-size: cover;
            background-position: center;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            margin-top: 55px;
            position: relative;
        }

        .header-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        header h1 {
            position: relative;
            z-index: 1;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .services-container {
            width: 80%;
            max-width: 1200px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        .service-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .service-content {
            padding: 20px;
        }

        .service-content h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.4em;
        }

        .service-content p {
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .price {
            color: #444;
            font-size: 1.2em;
            font-weight: bold;
        }

        .book-button {
            display: inline-block;
            background-color: #ff69b4;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .book-button:hover {
            background-color:rgb(212, 73, 141)
        }

        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .services-container {
                width: 90%;
                grid-template-columns: 1fr;
            }

            header {
                height: 200px;
            }

            header h1 {
                font-size: 2em;
            }
        }
    </style>
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

    <header>
        <div class="header-overlay"></div>
        <h1>บริการของเรา</h1>
    </header>

    <div class="services-container">
        <?php
        if ($services_result->num_rows > 0) {
            while($service = $services_result->fetch_assoc()) {
                echo "<a href='booking.php?service_id=" . htmlspecialchars($service['id']) . "' class='service-card'>
                        <img src='" . htmlspecialchars($service['service_img']) . "' alt='" . htmlspecialchars($service['name']) . "' class='service-image'>
                        <div class='service-content'>
                            <h3>" . htmlspecialchars($service['name']) . "</h3>
                            <p>" . htmlspecialchars($service['description']) . "</p>
                            <p class='price'>" . number_format($service['price']) . " ฿</p>
                            <div class='book-button'>จองบริการ</div>
                        </div>
                      </a>";
            }
        } else {
            echo "<p>ไม่มีบริการในขณะนี้</p>";
        }
        ?>
    </div>

    <footer>
    <p>Kakai Beautysalon | ติดต่อเรา: Kakaibeautysalon@gmail.com</p>
    </footer>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>