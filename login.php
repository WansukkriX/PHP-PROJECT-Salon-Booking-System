<?php
session_start();
include('connection.php');  // เชื่อมต่อกับฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ป้องกัน SQL Injection
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // ตรวจสอบข้อมูลในฐานข้อมูล
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // ตรวจสอบรหัสผ่าน
        if (password_verify($password, $user['password'])) {
            // เก็บข้อมูลใน session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];  // เก็บ role

            // ตรวจสอบ role และ redirect ไปยังหน้าที่เหมาะสม
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;  // ตรวจสอบว่าเป็น admin
                // เช็คการ redirect URL ใน session
                if (isset($_SESSION['redirect_url'])) {
                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: $redirect");
                } else {
                    header('Location: dashboard.php');  // ไปหน้า dashboard
                }
            } else {
                $_SESSION['customer_logged_in'] = true;  // สำหรับ customer
                // เช็คการ redirect URL ใน session
                if (isset($_SESSION['redirect_url'])) {
                    $redirect = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']);
                    header("Location: $redirect");
                } else {
                    header('Location: index.php');  // ไปหน้า index สำหรับ customer
                }
            }
            exit();
        } else {
            $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        }
    } else {
        $error_message = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
    $stmt->close();
}
?>  

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - Beauty Salon</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- CSS ในไฟล์เดียว -->
    <style>
        /* CSS สำหรับหน้า Login */
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(to right, #FFB6C1, #FF69B4); /* สีชมพูหวาน */
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
            color: #FF69B4; /* สีชมพู */
            font-size: 24px;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #FF69B4;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #FF69B4;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #FF1493; /* สีชมพูเข้ม */
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }

        .signup-link {
            margin-top: 15px;
        }

        .signup-link a {
            color: #FF69B4;
            text-decoration: none;
            font-weight: bold;
        }

        .signup-link a:hover {
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
    </style>
</head>
<body>

    <div class="login-container">
        <div class="logo-container">
            <img src="img/logo.png" alt="Beauty Salon Logo" class="logo"> <!-- ใส่โลโก้ร้าน -->
        </div>
        <h2>เข้าสู่ระบบ</h2>
        
        <form method="POST" action="login.php">
            <label for="username">ชื่อผู้ใช้:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">รหัสผ่าน:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">เข้าสู่ระบบ</button>
        </form>

        <?php if (isset($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>

        <div class="signup-link">
            <a href="register.php">สมัครสมาชิก</a>
        </div>
    </div>

</body>
</html>
