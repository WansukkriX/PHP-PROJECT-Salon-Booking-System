<?php
session_start();

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "beauty_salon");

if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ดึงข้อมูลการจองคิวจากตาราง bookings
$sql = "SELECT bookings.*, services.name AS service_name, stylists.name AS stylist_name 
        FROM bookings 
        JOIN services ON bookings.service_id = services.id 
        JOIN stylists ON bookings.stylist_id = stylists.id 
        ORDER BY bookings.booking_date DESC, bookings.booking_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>ดูรายการจองคิว - Beauty Salon</title>
   <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
   <style>
       body {
           font-family: 'Prompt', Arial, sans-serif;
           margin: 0;
           padding: 20px;
           background-color: #fff5f7;
           color: #4a4a4a;
       }

       .container {
           max-width: 1200px;
           margin: 0 auto;
           background: white;
           padding: 25px;
           border-radius: 20px;
           box-shadow: 0 10px 30px rgba(255, 92, 141, 0.1);
       }

       h1 {
           color: #ff5c8d;
           text-align: center;
           font-size: 2em;
           margin-bottom: 30px;
           text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
       }

       .search-box {
           margin-bottom: 30px;
           text-align: center;
       }

       .search-box input {
           padding: 12px 20px;
           width: 300px;
           border: 2px solid #ffd1dc;
           border-radius: 25px;
           font-size: 16px;
           transition: all 0.3s ease;
           font-family: 'Prompt', sans-serif;
       }

       .search-box input:focus {
           outline: none;
           border-color: #ff5c8d;
           box-shadow: 0 0 10px rgba(255, 92, 141, 0.2);
       }

       .search-box button {
           padding: 12px 25px;
           background: linear-gradient(135deg, #ff8fb1 0%, #ff5c8d 100%);
           color: white;
           border: none;
           border-radius: 25px;
           cursor: pointer;
           font-size: 16px;
           margin-left: 10px;
           transition: all 0.3s ease;
           font-family: 'Prompt', sans-serif;
       }

       .search-box button:hover {
           transform: translateY(-2px);
           box-shadow: 0 5px 15px rgba(255, 92, 141, 0.3);
       }

       .table-container {
           overflow-x: auto;
           border-radius: 15px;
           box-shadow: 0 5px 15px rgba(0,0,0,0.05);
       }

       table {
           width: 100%;
           border-collapse: separate;
           border-spacing: 0;
           background: white;
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

       tr:last-child td:first-child {
           border-bottom-left-radius: 15px;
       }

       tr:last-child td:last-child {
           border-bottom-right-radius: 15px;
       }

       tr:hover {
           background-color: #fff5f7;
           transition: background-color 0.3s ease;
       }

       .proof-image {
           max-width: 100px;
           max-height: 100px;
           border-radius: 10px;
           cursor: pointer;
           transition: transform 0.3s ease;
           box-shadow: 0 3px 10px rgba(0,0,0,0.1);
       }

       .proof-image:hover {
           transform: scale(1.1);
       }

       /* Add Animation */
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
               padding: 15px;
           }

           .search-box input {
               width: calc(100% - 40px);
               margin-bottom: 10px;
           }

           .search-box button {
               width: 100%;
               margin-left: 0;
           }

           th, td {
               padding: 10px;
               font-size: 14px;
           }

           .proof-image {
               max-width: 60px;
               max-height: 60px;
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
   <a href="dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i> กลับไปหน้าแดชบอร์ด
        </a>
       <h1>📅 รายการจองคิว</h1>

       <div class="search-box">
           <form method="GET" action="">
               <input type="text" name="search" placeholder="🔍 ค้นหาชื่อลูกค้า..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
               <button type="submit">ค้นหา</button>
           </form>
       </div>

       <div class="table-container">
           <table>
               <thead>
                   <tr>
                       <th>ลำดับ</th>
                       <th>ชื่อลูกค้า</th>
                       <th>บริการ</th>
                       <th>ช่างทำผม</th>
                       <th>วันที่จอง</th>
                       <th>เวลาจอง</th>
                       <th>เงินมัดจำ</th>
                       <th>หลักฐานการโอน</th>
                   </tr>
               </thead>
               <tbody>
                   <?php
                   if ($result->num_rows > 0) {
                       $count = 1;
                       while($row = $result->fetch_assoc()) {
                           echo "<tr>
                                   <td>" . $count . "</td>
                                   <td>" . $row['customer_name'] . "</td>
                                   <td>" . $row['service_name'] . "</td>
                                   <td>" . $row['stylist_name'] . "</td>
                                   <td>" . $row['booking_date'] . "</td>
                                   <td>" . $row['booking_time'] . "</td>
                                   <td>฿" . number_format($row['deposit'], 2) . "</td>
                                   <td>";
                           
                           if (!empty($row['transfer_proof'])) {
                               $image_path = "uploads/" . $row['transfer_proof'];
                               echo "<img src='$image_path' alt='หลักฐานการโอน' class='proof-image'>";
                           } else {
                               echo "ไม่มีหลักฐาน";
                           }

                           echo "</td>
                                 </tr>";
                           $count++;
                       }
                   } else {
                       echo "<tr><td colspan='8' style='text-align: center;'>ไม่พบข้อมูลการจองคิว</td></tr>";
                   }
                   ?>
               </tbody>
           </table>
       </div>
   </div>
</body>
</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>