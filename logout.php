<?php
session_start();
session_unset();  // ล้างค่า session
session_destroy();  // ทำลาย session

// รีไดเร็กต์ไปหน้า login
header('Location: index.php');
exit();
?>
