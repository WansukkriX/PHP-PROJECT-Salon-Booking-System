<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบของแอดมิน
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');  // หากไม่ล็อกอินจะถูกรีไดเร็กต์ไปหน้า login
    exit();
}

// เชื่อมต่อกับฐานข้อมูล
include('connection.php');

// ดึงข้อมูลผู้ใช้จากฐานข้อมูล
$username = $_SESSION['user_name'];  // ใช้ username จาก session
$query = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";  // เพิ่มการตรวจสอบ role เป็น admin
$result = $conn->query($query);

// หากไม่พบข้อมูลแสดงว่าไม่ใช่แอดมิน
if ($result->num_rows == 0) {
    header('Location: login.php');  // หากไม่ใช่แอดมินจะถูกส่งไปยังหน้า login
    exit();
}

$user = $result->fetch_assoc();

// ข้อมูลสรุป (จำนวนการจองวันนี้และยอดเงินมัดจำรวม)
$today = date('Y-m-d');

// ดึงจำนวนการจองวันนี้
$bookingCountResult = $conn->query("SELECT COUNT(*) as booking_count FROM bookings WHERE booking_date = '$today'");
$bookingCount = $bookingCountResult->fetch_assoc()['booking_count'];

// ดึงยอดเงินมัดจำรวมวันนี้
$depositSumResult = $conn->query("SELECT SUM(deposit) as total_deposit FROM bookings WHERE booking_date = '$today'");
$totalDeposit = $depositSumResult->fetch_assoc()['total_deposit'];

$conn->close();
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ด - Beauty Salon</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', Arial, sans-serif;
            background-color: #fff5f7;
            margin: 0;
            padding: 0;
            color: #4a4a4a;
        }

        .navbar {
            background: linear-gradient(135deg, #ff8fb1 0%, #ff5c8d 100%);
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(255, 92, 141, 0.2);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            margin: 0 15px;
            font-size: 18px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .container {
            width: 85%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 92, 141, 0.1);
        }

        .container h1 {
            text-align: center;
            color: #ff5c8d;
            font-size: 2.2em;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .summary {
            background: linear-gradient(135deg, #fff8f9 0%, #fff0f3 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 92, 141, 0.1);
        }

        .summary h3 {
            color: #ff5c8d;
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 20px;
        }

        .summary p {
            font-size: 1.2em;
            text-align: center;
            color: #666;
            margin: 15px 0;
        }

        .actions {
            padding: 20px;
        }

        .actions h3 {
            color: #ff5c8d;
            text-align: center;
            font-size: 1.5em;
            margin-bottom: 25px;
        }

        .actions ul {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 0;
            list-style: none;
        }

        .actions ul li {
            text-align: center;
        }

        .actions ul li a {
            display: block;
            color: #ff5c8d;
            text-decoration: none;
            font-size: 1.1em;
            padding: 15px 20px;
            background-color: #fff5f7;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .actions ul li a:hover {
            background-color: #ff5c8d;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 92, 141, 0.2);
        }

        .logout-button {
            background: linear-gradient(135deg, #ff5c8d 0%, #ff3d7f 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1.1em;
            cursor: pointer;
            display: block;
            margin: 30px auto;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-family: 'Prompt', sans-serif;
        }

        .logout-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 141, 0.3);
            background: linear-gradient(135deg, #ff3d7f 0%, #ff2d6f 100%);
        }

        /* เพิ่ม Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.8s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            .navbar a {
                display: block;
                margin: 10px 5px;
            }

            .actions ul {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard.php">🏠 แดชบอร์ด</a>
        <!-- <a href="logout.php">🚪 ออกจากระบบ</a> -->
    </div>

    <div class="container">
        <h1>✨ ยินดีต้อนรับ, <?php echo $user['username']; ?> ✨</h1>
        
        <div class="summary">
            <h3>📊 ข้อมูลสรุปวันนี้</h3>
            <p><strong>จำนวนการจอง:</strong> <?php echo $bookingCount+1; ?> ครั้ง</p>
            <p><strong>ยอดเงินมัดจำรวม:</strong> ฿<?php echo number_format($totalDeposit, 2); ?></p>
        </div>

        <div class="actions">
            <h3>🎯 เมนูการจัดการ</h3>
            <ul>
                <li><a href="view_bookings.php">📅 ดูรายชื่อคิวจอง</a></li>
                <!-- <li><a href="view_reports.php">📊 ดูรายงาน</a></li> -->
                <li><a href="manage_services.php">💇‍♀️ จัดการบริการ</a></li>
                <li><a href="manage_stylists.php">👩‍💼 จัดการช่าง</a></li>
                <li><a href="manage_users.php">👥 จัดการผู้ใช้</a></li>
            </ul>
        </div>

        <form method="POST" action="logout.php">
            <button type="submit" class="logout-button">🚪 ออกจากระบบ</button>
        </form>
    </div>
</body>
</html>