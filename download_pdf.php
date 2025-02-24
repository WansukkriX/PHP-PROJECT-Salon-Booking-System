<?php
require('fpdf/fpdf.php');
$conn = new mysqli("localhost", "root", "", "beauty_salon");

if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];

    // ดึงข้อมูลการจองที่ต้องการพร้อมกับข้อมูลชื่อผู้ใช้
    $query = "SELECT b.*, s.name as service_name, u.username
              FROM bookings b
              LEFT JOIN services s ON b.service_id = s.id
              LEFT JOIN users u ON u.id = b.customer_name
              WHERE b.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();

        // สร้างไฟล์ PDF สำหรับการจอง
        $pdf = new FPDF('P', 'mm', array(105, 148));  // ใช้ขนาด A6 (105mm x 148mm)
        $pdf->AddPage();
        $pdf->AddFont('sarabun', '', 'THSarabunNew.php'); // ใช้ฟอนต์ที่แปลงแล้ว
        $pdf->SetFont('sarabun', '', 14);  // ตั้งฟอนต์และขนาดฟอนต์

        // ตั้งสีพื้นหลังให้สวยงาม
        $pdf->SetFillColor(255, 223, 186);  // สีพื้นหลังอ่อน
        $pdf->Rect(0, 0, 105, 148, 'F');  // พื้นหลังสีครีม

        // กำหนดขนาดของกรอบ
        $pdf->SetDrawColor(0, 0, 0);  // สีกรอบ
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(10, 10, 85, 128); // กรอบที่ล้อมรอบข้อมูล

        // เพิ่มรูปภาพโลโก้ตรงกลางด้านบน
        $imageWidth = 20;  // กำหนดความกว้างของรูปภาพ
        $imageHeight = 20; // กำหนดความสูงของรูปภาพ
        $imageX = (105 - $imageWidth) / 2;  // คำนวณตำแหน่ง X เพื่อให้อยู่กลาง
        $imageY = 15;  // ตั้งตำแหน่ง Y ให้เหมาะสม
        $pdf->Image('img/l1.png', $imageX, $imageY, $imageWidth, $imageHeight);  // เพิ่มรูปภาพ

        // ตั้งค่าตำแหน่งเริ่มต้นของเนื้อหา
        $pdf->SetXY(15, 40);  // เริ่มต้นที่ตำแหน่ง X=15, Y=40

        // เพิ่มข้อมูลการจอง
        $pdf->SetFont('sarabun', '', 14);

        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', "หมายเลขคิว: "), 0, 0);
        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', $booking_id), 0, 1);

        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', "บริการ: "), 0, 0);
        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', htmlspecialchars($booking['service_name'])), 0, 1);

        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', "ชื่อผู้ใช้: "), 0, 0);
        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', htmlspecialchars($booking['username'])), 0, 1);

        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', "วันที่: "), 0, 0);
        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', htmlspecialchars($booking['booking_date'])), 0, 1);

        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', "เวลา: "), 0, 0);
        $pdf->Cell(40, 10, iconv('utf-8', 'cp874', htmlspecialchars($booking['booking_time'])), 0, 1);

        // ส่งออกไฟล์ PDF
        $pdf->Output('D', 'booking_' . $booking_id . '.pdf');  // ส่งออก PDF พร้อมชื่อไฟล์ที่ไม่ซ้ำ
        exit();
    } else {
        echo "ไม่พบข้อมูลการจองนี้";
    }

    $stmt->close();
}

$conn->close();
?>