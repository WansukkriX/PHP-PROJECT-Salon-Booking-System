<?php
include('connection.php'); // เชื่อมต่อกับฐานข้อมูล

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าจากฟอร์ม
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email']; // รับอีเมล

    // ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($password === $confirm_password) {
        // ตรวจสอบรูปแบบอีเมล
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "อีเมลไม่ถูกต้อง!";
        } else {
            // เข้ารหัสรหัสผ่าน
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // ป้องกัน SQL Injection
            $username = $conn->real_escape_string($username);
            $email = $conn->real_escape_string($email);

            // กำหนดค่า role เป็น 'customer'
            $role = 'customer';

            // คำสั่ง SQL เพื่อบันทึกผู้ใช้ใหม่
            $sql = "INSERT INTO users (username, password, email, role) 
                    VALUES ('$username', '$hashed_password', '$email', '$role')";

            if ($conn->query($sql) === TRUE) {
                $success_message = "สมัครสมาชิกสำเร็จ!";
            } else {
                $error_message = "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    } else {
        $error_message = "รหัสผ่านไม่ตรงกัน!";
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก - Beauty Salon</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- CSS ในไฟล์เดียว -->
    <style>
        /* CSS สำหรับหน้า Login */
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(to right, #FFB6C1, #FF69B4);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h2 {
            color: #FF69B4;
            font-size: 24px;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"], input[type="email"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #FF69B4;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #FF69B4;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #FF1493;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .success-message {
            color: green;
            margin-top: 10px;
        }

        .login-link {
            margin-top: 15px;
        }

        .login-link a {
            color: #FF69B4;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 200px;
            height: auto;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
        <img src="img/logo.png" alt="Beauty Salon Logo" class="logo">
        </div>
        <h2>สมัครสมาชิก</h2>
        
        <?php if (isset($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>
        
        <?php if (isset($success_message)) { ?>
            <p class="success-message">
                <?php echo $success_message; ?>
                <br>
                <a href="login.php" style="color: #FF69B4;">เข้าสู่ระบบ</a>
            </p>
        <?php } else { ?>
            <form method="POST" action="register.php">
                <label for="username">ชื่อผู้ใช้:</label>
                <input type="text" id="username" name="username" required>

                <label for="email">อีเมล:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">รหัสผ่าน:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">ยืนยันรหัสผ่าน:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <input type="submit" value="สมัครสมาชิก">
            </form>
            
            <div class="login-link">
                <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a></p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
