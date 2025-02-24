<?php
session_start();

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "beauty_salon");

if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    $_SESSION['error_message'] = "กรุณาล็อกอินก่อนทำการจองคิว";
    header('Location: login.php');
    exit();
}

// ดึงข้อมูลผู้ใช้จาก session
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// ตรวจสอบ service_id
if (!isset($_GET['service_id'])) {
    die("ไม่พบบริการที่เลือก");
}

$service_id = $_GET['service_id'];
$service_query = "SELECT * FROM services WHERE id = ?";
$stmt = $conn->prepare($service_query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$service_result = $stmt->get_result();

if ($service_result->num_rows > 0) {
    $service = $service_result->fetch_assoc();
} else {
    die("ไม่พบบริการที่เลือก");
}
$stmt->close();

// ดึงข้อมูลช่างทำผม
$stylists_query = "SELECT * FROM stylists";
$stylists_result = $conn->query($stylists_query);

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stylist_id = $_POST['stylist_id'] ?? null;
    $booking_date = $_POST['booking_date'] ?? null;
    $booking_time = $_POST['booking_time'] ?? null;
    $deposit = $_POST['deposit'] ?? null;
    $customer_name = $user['username']; // ดึงจากฐานข้อมูลโดยตรง

    // ตรวจสอบค่าที่จำเป็น
    if (empty($stylist_id) || empty($booking_date) || empty($booking_time) || empty($deposit)) {
        echo "<p style='color: red;'>กรุณากรอกข้อมูลให้ครบถ้วน</p>";
    } else {
        // ตรวจสอบไฟล์อัปโหลด
        if (isset($_FILES['transfer_proof']) && $_FILES['transfer_proof']['error'] == 0) {
            $file = $_FILES['transfer_proof'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (in_array($file['type'], $allowed_types)) {
                // สร้างโฟลเดอร์ถ้ายังไม่มี
                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = uniqid() . '_' . basename($file['name']);
                $file_path = $upload_dir . $file_name;

                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    // เพิ่มข้อมูลการจอง (ใช้ Prepared Statement)
                    $sql = "INSERT INTO bookings (customer_name, service_id, stylist_id, booking_date, booking_time, deposit, transfer_proof) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("siissds", $customer_name, $service_id, $stylist_id, $booking_date, $booking_time, $deposit, $file_name);

                    if ($stmt->execute()) {
                        // เพิ่มข้อมูลการจองสำเร็จ
                        $_SESSION['booking_success'] = "จองคิวสำเร็จ!";
                        header("Location: booking_list.php"); // เปลี่ยนเป็นหน้าแสดงคิว
                        exit(); // หยุดการทำงานของโค้ดที่เหลือหลังจาก redirect
                    } else {
                        echo "<p style='color: red;'>เกิดข้อผิดพลาด: " . $conn->error . "</p>";
                    }
                    $stmt->close();
                } else {
                    echo "<p style='color: red;'>ไม่สามารถอัปโหลดไฟล์ได้</p>";
                }
            } else {
                echo "<p style='color: red;'>ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ JPEG, PNG, GIF)</p>";
            }
        } else {
            echo "<p style='color: red;'>กรุณาอัปโหลดหลักฐานการโอนเงิน</p>";
        }
    }
}


$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองคิว - Beauty Salon</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Kanit', sans-serif;
        }

        body {
            background-color:rgb(197, 173, 185);
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

        .booking-container {
            max-width: 800px;
            margin: 80px auto 40px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .service-details {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .service-details p {
            color: #666;
            margin: 10px 0;
            line-height: 1.6;
        }

        .price {
            font-size: 1.2em;
            color: #444;
            font-weight: bold;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #444;
            font-weight: 500;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1em;
            transition: border-color 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #666;
        }

        .qr-section {
            grid-column: 1 / -1;
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 8px;
        }

        .qr-section img {
            max-width: 300px;
            height: auto;
            margin: 10px 0;
        }

        .file-upload {
            background-color: #f8f8f8;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .submit-button {
            grid-column: 1 / -1;
            background-color: #444;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #555;
        }

        .success {
            color: #2ecc71;
            padding: 10px;
            background-color: #eafaf1;
            border-radius: 4px;
            margin-top: 20px;
        }

        .error {
            color: #e74c3c;
            padding: 10px;
            background-color: #fdeaea;
            border-radius: 4px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .booking-container {
                margin: 60px 20px;
                padding: 20px;
            }

            form {
                grid-template-columns: 1fr;
            }

            .qr-section img {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="menu-bar">
        <a href="index.php">หน้าแรก</a>
        <a href="services.php">บริการ</a>
        <a href="booking.php">จองคิว</a>
        <?php if ($is_logged_in): ?>
            <a href="logout.php">ออกจากระบบ</a>
        <?php else: ?>
            <a href="login.php">ล็อกอิน</a>
        <?php endif; ?>
    </div>

    <div class="booking-container">
        <div class="service-details">
            <h1>จองคิวสำหรับบริการ: <?php echo htmlspecialchars($service['name']); ?></h1>
            <p>รายละเอียดบริการ: <?php echo htmlspecialchars($service['description']); ?></p>
            <p class="price">ราคา: <?php echo number_format($service['price']); ?> ฿</p>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="service_id" value="<?php echo htmlspecialchars($service['id']); ?>">
            
            <div class="form-group">
                <label for="customer_name">ชื่อลูกค้า:</label>
                <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($user['username']); ?>" readonly> 
            </div>

            <div class="form-group">
                <label for="stylist_id">เลือกช่างทำผม:</label>
                <select id="stylist_id" name="stylist_id" required>
                    <option value="">-- เลือกช่างทำผม --</option>
                    <?php
                    if ($stylists_result->num_rows > 0) {
                        while($stylist = $stylists_result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($stylist['id']) . "'>" . 
                                 htmlspecialchars($stylist['name']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>ไม่มีช่างทำผมในระบบ</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="booking_date">วันที่จอง:</label>
                <input type="date" id="booking_date" name="booking_date" required>
            </div>

            <div class="form-group">
                <label for="booking_time">เวลาจอง:</label>
                <input type="time" id="booking_time" name="booking_time" required>
            </div>

            <div class="form-group">
                <label for="deposit">เงินมัดจำ (บาท):</label>
                <input type="number" id="deposit" name="deposit" required>
            </div>

            <div class="qr-section form-group full-width">
                <h3>ช่องทางการชำระเงิน</h3>
                <img src="img/qr.jpg" alt="QR Code สำหรับชำระเงิน">
                <p>กรุณาสแกน QR Code เพื่อชำระเงินมัดจำ</p>
            </div>

            <div class="form-group full-width file-upload">
                <label for="transfer_proof">แนบหลักฐานการโอนเงิน:</label>
                <input type="file" id="transfer_proof" name="transfer_proof" accept="image/*" required>
            </div>

            <button type="submit" class="submit-button">ยืนยันการจองคิว</button>
        </form>
    </div>
</body>
</html>
 <!-- แสดง Pop-up เมื่อจองสำเร็จ -->
 <?php if (isset($_SESSION['booking_success'])): ?>
        <script>
            alert('<?php echo $_SESSION['booking_success']; ?>');
        </script>
        <?php unset($_SESSION['booking_success']); ?>
    <?php endif; ?>
<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>