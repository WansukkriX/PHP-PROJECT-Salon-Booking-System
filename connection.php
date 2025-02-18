<?php
$servername = "localhost";
$username = "root";  // ชื่อผู้ใช้ฐานข้อมูล (ค่าเริ่มต้นใน XAMPP คือ "root")
$password = "";  // รหัสผ่านฐานข้อมูล (ค่าเริ่มต้นใน XAMPP คือ "")
$dbname = "beauty_salon";  // ชื่อฐานข้อมูลที่คุณใช้งาน

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
?>
