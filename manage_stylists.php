<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบของแอดมิน
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');  // หากไม่ล็อกอินจะถูกรีไดเร็กต์ไปหน้า login
    exit();
}

// เชื่อมต่อกับฐานข้อมูล
include('connection.php');

// เพิ่มช่าง
if (isset($_POST['add_stylist'])) {
    $stylist_name = $_POST['stylist_name'];
    $stylist_email = $_POST['stylist_email'];
    $stylist_phone = $_POST['stylist_phone'];

    // ตรวจสอบและอัปโหลดภาพโปรไฟล์
    $profile_picture = '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/"; // โฟลเดอร์ที่จะเก็บภาพ
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // ตรวจสอบไฟล์เป็นภาพ
        if (getimagesize($_FILES["profile_picture"]["tmp_name"])) {
            // ย้ายไฟล์ไปยังโฟลเดอร์
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = basename($_FILES["profile_picture"]["name"]);
            } else {
                $error_message = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        } else {
            $error_message = "กรุณาอัปโหลดไฟล์ภาพเท่านั้น";
        }
    }

    // ป้องกัน SQL Injection
    $stylist_name = $conn->real_escape_string($stylist_name);
    $stylist_email = $conn->real_escape_string($stylist_email);
    $stylist_phone = $conn->real_escape_string($stylist_phone);

    // คำสั่ง SQL เพื่อเพิ่มช่างใหม่
    $sql = "INSERT INTO stylists (name, email, phone, profile_picture) 
            VALUES ('$stylist_name', '$stylist_email', '$stylist_phone', '$profile_picture')";

    if ($conn->query($sql) === TRUE) {
        $success_message = "เพิ่มช่างใหม่สำเร็จ!";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการเพิ่มช่าง: " . $conn->error;
    }
}

// แก้ไขข้อมูลช่าง
if (isset($_POST['edit_stylist'])) {
    $stylist_id = $_POST['stylist_id'];
    $stylist_name = $_POST['stylist_name'];
    $stylist_email = $_POST['stylist_email'];
    $stylist_phone = $_POST['stylist_phone'];

    // ตรวจสอบและอัปโหลดภาพโปรไฟล์ใหม่
    $profile_picture = $_POST['current_profile_picture']; // ค่าภาพโปรไฟล์เดิม
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/"; // โฟลเดอร์ที่จะเก็บภาพ
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // ตรวจสอบไฟล์เป็นภาพ
        if (getimagesize($_FILES["profile_picture"]["tmp_name"])) {
            // ย้ายไฟล์ไปยังโฟลเดอร์
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = basename($_FILES["profile_picture"]["name"]);
            } else {
                $error_message = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        } else {
            $error_message = "กรุณาอัปโหลดไฟล์ภาพเท่านั้น";
        }
    }

    // ป้องกัน SQL Injection
    $stylist_name = $conn->real_escape_string($stylist_name);
    $stylist_email = $conn->real_escape_string($stylist_email);
    $stylist_phone = $conn->real_escape_string($stylist_phone);

    // คำสั่ง SQL เพื่อแก้ไขข้อมูลช่าง
    $sql = "UPDATE stylists SET name = '$stylist_name', email = '$stylist_email', phone = '$stylist_phone', profile_picture = '$profile_picture' 
            WHERE id = $stylist_id";

    if ($conn->query($sql) === TRUE) {
        $success_message = "แก้ไขข้อมูลช่างสำเร็จ!";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $conn->error;
    }
}

// ลบช่าง
if (isset($_GET['delete_stylist_id'])) {
    $stylist_id = $_GET['delete_stylist_id'];

    // คำสั่ง SQL เพื่อลบข้อมูลช่าง
    $sql = "DELETE FROM stylists WHERE id = $stylist_id";

    if ($conn->query($sql) === TRUE) {
        $success_message = "ลบช่างออกจากระบบสำเร็จ!";
    } else {
        $error_message = "เกิดข้อผิดพลาดในการลบช่าง: " . $conn->error;
    }
}

// ดึงข้อมูลช่างทั้งหมด
$sql = "SELECT * FROM stylists";
$stylist_result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการช่าง - Beauty Salon</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffd1dc 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 92, 141, 0.1);
        }

        h1, h2, h3 {
            color: #ff5c8d;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .form-container {
            background: #fff5f7;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 92, 141, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ffd1dc;
            border-radius: 10px;
            box-sizing: border-box;
            font-family: 'Prompt', sans-serif;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus {
            outline: none;
            border-color: #ff5c8d;
            box-shadow: 0 0 10px rgba(255, 92, 141, 0.2);
        }

        button, .submit-btn {
            background: linear-gradient(135deg, #ff8fb1 0%, #ff5c8d 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        button:hover, .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 141, 0.3);
        }

        .table-container {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ffd1dc;
        }

        th {
            background: linear-gradient(135deg, #ff8fb1 0%, #ff5c8d 100%);
            color: white;
            font-weight: 500;
        }

        th:first-child {
            border-top-left-radius: 15px;
        }

        th:last-child {
            border-top-right-radius: 15px;
        }

        .action-buttons button,
        .action-buttons a {
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            margin: 5px;
            width: auto;
        }

        .action-buttons button {
            background: #4CAF50;
            color: white;
        }

        .action-buttons a {
            background: #ff4444;
            color: white;
            text-decoration: none;
            display: inline-block;
        }

        .action-buttons button:hover,
        .action-buttons a:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            color: #ff5c8d;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .close:hover {
            color: #ff3d7f;
            transform: rotate(90deg);
        }

        .success-message, .error-message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .success-message {
            background: #4CAF50;
            color: white;
        }

        .error-message {
            background: #ff4444;
            color: white;
        }

        .back-link {
            display: inline-block;
            margin-bottom:10px;
            color: #ff5c8d;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
            background:rgb(194, 247, 240);
        }

        .back-link:hover {
            background: #ffd1dc;
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            table {
                display: block;
                overflow-x: auto;
            }

            .action-buttons button,
            .action-buttons a {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✨ จัดการช่าง ✨</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> กลับไปหน้าแดชบอร์ด
        </a>
        <div class="form-container">
            
            <h2>เพิ่มช่างใหม่</h2>
            <form method="POST" action="manage_stylists.php" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" name="stylist_name" placeholder="ชื่อช่าง" required>
                    <input type="email" name="stylist_email" placeholder="อีเมล" required>
                    <input type="tel" name="stylist_phone" placeholder="เบอร์โทรศัพท์" required>
                    <input type="file" name="profile_picture" accept="image/*">
                </div>
                <button type="submit" name="add_stylist" class="submit-btn">
                    <i class="fas fa-plus"></i> เพิ่มช่าง
                </button>
            </form>
        </div>

        <div class="table-container">
            <h2>รายชื่อช่าง</h2>
            <table>
                <thead>
                    <tr>
                        <th>ชื่อช่าง</th>
                        <th>อีเมล</th>
                        <th>เบอร์โทรศัพท์</th>
                        <th>การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stylist = $stylist_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $stylist['name']; ?></td>
                            <td><?php echo $stylist['email']; ?></td>
                            <td><?php echo $stylist['phone']; ?></td>
                            <td class="action-buttons">
                                <button onclick="openEditModal(<?php echo $stylist['id']; ?>, '<?php echo addslashes($stylist['name']); ?>', '<?php echo addslashes($stylist['email']); ?>', '<?php echo addslashes($stylist['phone']); ?>', '<?php echo addslashes($stylist['profile_picture']); ?>')">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </button>
                                <a href="manage_stylists.php?delete_stylist_id=<?php echo $stylist['id']; ?>" onclick="return confirm('คุณต้องการลบช่างนี้หรือไม่?');">
                                    <i class="fas fa-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeEditModal()">&times;</span>
                <h2>แก้ไขข้อมูลช่าง</h2>
                <form method="POST" action="manage_stylists.php" enctype="multipart/form-data">
                    <input type="hidden" id="edit_stylist_id" name="stylist_id">
                    <input type="hidden" id="edit_current_profile_picture" name="current_profile_picture">
                    <div class="form-group">
                        <input type="text" id="edit_stylist_name" name="stylist_name" placeholder="ชื่อช่าง" required>
                        <input type="email" id="edit_stylist_email" name="stylist_email" placeholder="อีเมล" required>
                        <input type="tel" id="edit_stylist_phone" name="stylist_phone" placeholder="เบอร์โทรศัพท์" required>
                        <input type="file" id="edit_profile_picture" name="profile_picture" accept="image/*">
                    </div>
                    <button type="submit" name="edit_stylist" class="submit-btn">
                        <i class="fas fa-save"></i> บันทึกการแก้ไข
                    </button>
                </form>
            </div>
        </div>

       
    </div>

    <script>
        function openEditModal(id, name, email, phone, profile_picture) {
            document.getElementById('edit_stylist_id').value = id;
            document.getElementById('edit_stylist_name').value = name;
            document.getElementById('edit_stylist_email').value = email;
            document.getElementById('edit_stylist_phone').value = phone;
            document.getElementById('edit_current_profile_picture').value = profile_picture;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    </script>
</body>
</html>