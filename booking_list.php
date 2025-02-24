<?php
session_start();
$conn = new mysqli("localhost", "root", "", "beauty_salon");

if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require('fpdf/fpdf.php');

$user_id = $_SESSION['user_id'];

// ทดสอบดูโครงสร้างตาราง services
$test_query = "DESCRIBE services";
$test_result = $conn->query($test_query);
if ($test_result) {
    while($row = $test_result->fetch_assoc()) {
        error_log("Column: " . $row['Field']);
    }
}

// แก้ไข query ให้แสดงข้อมูลเพิ่มเติมเพื่อตรวจสอบ
$query = "SELECT b.*, s.name as service_name, s.id as service_id, u.username,
          ROW_NUMBER() OVER (ORDER BY b.booking_date, b.booking_time) as queue_number
          FROM bookings b 
          LEFT JOIN services s ON b.service_id = s.id 
          LEFT JOIN users u ON u.id = ?
          WHERE b.customer_name = (SELECT username FROM users WHERE id = ?)
          ORDER BY b.booking_date, b.booking_time";

error_log("Query: " . $query); // เพิ่ม log เพื่อตรวจสอบ query

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบข้อมูลที่ได้
while ($row = $result->fetch_assoc()) {
    error_log("Service ID: " . $row['service_id'] . ", Service Name: " . ($row['service_name'] ?? 'NULL'));
}
$result->data_seek(0); // reset pointer กลับไปที่เริ่มต้น
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คิวที่จองแล้ว | Beauty Salon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff69b4;
            --secondary-color: #f06292;
            --background-color: #fce4ec;
            --text-color: #333;
            --card-shadow: 0 8px 16px rgba(240, 98, 146, 0.1);
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .booking-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }

        .booking-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .booking-card:hover {
            transform: translateY(-5px);
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .queue-number {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            font-weight: bold;
        }

        .customer-info {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed var(--secondary-color);
        }

        .service-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .booking-info {
            margin: 1.5rem 0;
        }

        .info-row {
            display: flex;
            align-items: center;
            margin: 0.8rem 0;
            color: var(--text-color);
        }

        .info-row i {
            margin-right: 0.8rem;
            color: var(--secondary-color);
            width: 20px;
        }

        .download-btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
            width: auto;
        }

        .btn-return{
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            margin:2px 20px;
            width: auto;
        }
        .btn-return a{
            color:white;
            font-size:20px;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 98, 146, 0.3);
        }

        .empty-message {
            text-align: center;
            padding: 3rem;
            color: var(--text-color);
            font-size: 1.2rem;
            background: white;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            margin: 2rem auto;
            max-width: 500px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .booking-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    
<div class="container">
    <h1>
        <i class="fas fa-calendar-check"></i>
        คิวที่จองแล้วของคุณ
    </h1>

    <?php if ($result->num_rows > 0): ?>
        <div class="booking-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="booking-card">
                    <!-- แสดง booking_id แทน queue_number -->
                    <div class="queue-number">
                        รหัสคิว <?php echo htmlspecialchars($row['id']); ?> <!-- เปลี่ยนจาก queue_number เป็น booking_id -->
                    </div>
                    <div class="customer-info">
                        <div class="info-row">
                            <i class="fas fa-user"></i>
                            <span>ชื่อผู้ใช้: <?php echo htmlspecialchars($row['username']); ?></span>
                        </div>
                    </div>
                    <div class="service-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <div class="booking-info">
                        <div class="info-row">
                            <i class="fas fa-tags"></i>
                            <span>บริการ: 
                                <?php 
                                if (!empty($row['service_name'])) {
                                    echo htmlspecialchars($row['service_name']);
                                } elseif (!empty($row['service_id'])) {
                                    echo 'รหัสบริการ: ' . htmlspecialchars($row['service_id']);
                                } else {
                                    echo 'ไม่ระบุบริการ';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-calendar"></i>
                            <span>วันที่: <?php echo htmlspecialchars($row['booking_date']); ?></span>
                        </div>
                        <div class="info-row">
                            <i class="fas fa-clock"></i>
                            <span>เวลา: <?php echo htmlspecialchars($row['booking_time']); ?></span>
                        </div>
                    </div>
                    <a href="download_pdf.php?id=<?php echo $row['id']; ?>" class="download-btn">
                        <i class="fas fa-download"></i> ดาวน์โหลด PDF
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-message">
            <i class="fas fa-calendar-times" style="font-size: 3rem; color: var(--secondary-color);"></i>
            <p>คุณยังไม่ได้ทำการจองคิว</p>
        </div>
    <?php endif; ?>
    <div class="btn-return"><a href="index.php">กลับ</a></div>
</div>


    <!-- เพิ่ม Font Kanit -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
</body>
</html>