<?php
session_start();
include('connection.php');

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

function uploadImage($file) {
    $target_dir = "uploads/services/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    return false;
}

if (isset($_POST['edit_service'])) {
    $id = intval($_POST['service_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    
    $image_path = '';
    if (isset($_FILES['service_img']) && $_FILES['service_img']['error'] == 0) {
        $image_path = uploadImage($_FILES['service_img']);
        $sql = "UPDATE services SET name='$name', description='$description', price=$price, service_img='$image_path' WHERE id=$id";
    } else {
        $sql = "UPDATE services SET name='$name', description='$description', price=$price WHERE id=$id";
    }
    
    if ($conn->query($sql)) {
        $success_message = "แก้ไขบริการสำเร็จ";
    } else {
        $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

if (isset($_POST['add_service'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    
    $image_path = '';
    if (isset($_FILES['service_img']) && $_FILES['service_img']['error'] == 0) {
        $image_path = uploadImage($_FILES['service_img']);
    }
    
    $sql = "INSERT INTO services (name, description, price, service_img) VALUES ('$name', '$description', $price, '$image_path')";
    if ($conn->query($sql)) {
        $success_message = "เพิ่มบริการสำเร็จ";
    } else {
        $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

if (isset($_POST['delete_service'])) {
    $id = intval($_POST['service_id']);
    $sql = "DELETE FROM services WHERE id = $id";
    if ($conn->query($sql)) {
        $success_message = "ลบบริการสำเร็จ";
    } else {
        $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

$sql = "SELECT * FROM services ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการบริการ - Beauty Salon</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
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

        h1, h2 {
            color: #ff5c8d;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .add-service-form {
            background: #fff5f7;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 92, 141, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ffd1dc;
            border-radius: 10px;
            box-sizing: border-box;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: #ff5c8d;
            box-shadow: 0 0 10px rgba(255, 92, 141, 0.2);
        }

        input[type="file"] {
            background: white;
            padding: 10px;
            border-radius: 10px;
            border: 2px solid #ffd1dc;
        }

        .submit-btn {
            background: linear-gradient(135deg, #ff8fb1 0%, #ff5c8d 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 92, 141, 0.3);
        }

        .services-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 30px;
        }

        .services-table th,
        .services-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ffd1dc;
        }

        .services-table th {
            background: linear-gradient(135deg, #ff8fb1 0%, #ff5c8d 100%);
            color: white;
            font-weight: 500;
        }

        .services-table th:first-child {
            border-top-left-radius: 15px;
        }

        .services-table th:last-child {
            border-top-right-radius: 15px;
        }

        .service-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .service-image:hover {
            transform: scale(1.1);
        }

        .edit-btn, .delete-btn {
            padding: 8px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .edit-btn {
            background: #4CAF50;
            color: white;
        }

        .delete-btn {
            background: #ff4444;
            color: white;
        }

        .edit-btn:hover, .delete-btn:hover {
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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #ff5c8d;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
            background: #fff5f7;
        }

        .back-link:hover {
            background: #ffd1dc;
            transform: translateX(-5px);
        }

        /* Messages */
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .services-table {
                display: block;
                overflow-x: auto;
            }

            .service-image {
                width: 80px;
                height: 80px;
            }

            .edit-btn, .delete-btn {
                padding: 6px 12px;
                font-size: 14px;
            }
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

    </style>
</head>
<body>
    
    <div class="container">
    <h1>✨ จัดการบริการ ✨</h1>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> กลับไปหน้าแดชบอร์ด
        </a>

        <div class="add-service-form">
            <h2>เพิ่มบริการใหม่</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">ชื่อบริการ:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">รายละเอียด:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="price">ราคา:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="service_img">รูปภาพ:</label>
                    <input type="file" id="service_img" name="service_img" accept="image/*" required>
                </div>

                <button type="submit" name="add_service" class="submit-btn">เพิ่มบริการ</button>
            </form>
        </div>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>แก้ไขบริการ</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="edit_service_id" name="service_id">
                    <div class="form-group">
                        <label for="edit_name">ชื่อบริการ:</label>
                        <input type="text" id="edit_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">รายละเอียด:</label>
                        <textarea id="edit_description" name="description" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_price">ราคา:</label>
                        <input type="number" id="edit_price" name="price" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_service_img">รูปภาพใหม่ (ไม่จำเป็นต้องเลือกหากไม่ต้องการเปลี่ยน):</label>
                        <input type="file" id="edit_service_img" name="service_img" accept="image/*">
                    </div>

                    <button type="submit" name="edit_service" class="submit-btn">บันทึกการแก้ไข</button>
                </form>
            </div>
        </div>

        <table class="services-table">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ชื่อบริการ</th>
                    <th>รายละเอียด</th>
                    <th>ราคา</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <img src="<?php echo $row['service_img']; ?>" alt="<?php echo $row['name']; ?>" class="service-image">
                    </td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td><?php echo number_format($row['price'], 2); ?> บาท</td>
                    <td>
                        <button onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>', '<?php echo addslashes($row['description']); ?>', <?php echo $row['price']; ?>)" class="edit-btn">แก้ไข</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_service" class="delete-btn" 
                                    onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบบริการนี้?')">
                                ลบ
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="dashboard.php">กลับ</a>
    </div>

    <script>
        var modal = document.getElementById("editModal");
        var span = document.getElementsByClassName("close")[0];

        function openEditModal(id, name, description, price) {
            document.getElementById("edit_service_id").value = id;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_description").value = description;
            document.getElementById("edit_price").value = price;
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>