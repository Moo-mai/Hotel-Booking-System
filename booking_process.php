<?php
// booking_process.php - ไฟล์ประมวลผลการจอง

// เริ่ม session
session_start();

// ตั้งค่า timezone
date_default_timezone_set('Asia/Bangkok');

// ฟังก์ชันสำหรับทำความสะอาดข้อมูล
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // รับข้อมูลจากฟอร์ม
    $room_type = clean_input($_POST['room_type']);
    $guests = clean_input($_POST['guests']);
    $checkin = clean_input($_POST['checkin']);
    $checkout = clean_input($_POST['checkout']);
    $fullname = clean_input($_POST['fullname']);
    $phone = clean_input($_POST['phone']);
    $email = clean_input($_POST['email']);
    $special_request = clean_input($_POST['special_request']);
    $nights = clean_input($_POST['nights']);
    $total_price = clean_input($_POST['total_price']);
    
    // ตรวจสอบข้อมูลที่จำเป็น
    $errors = array();
    
    if (empty($room_type)) {
        $errors[] = "กรุณาเลือกประเภทห้องพัก";
    }
    
    if (empty($guests) || $guests < 1) {
        $errors[] = "กรุณาระบุจำนวนผู้เข้าพักที่ถูกต้อง";
    }
    
    if (empty($checkin) || empty($checkout)) {
        $errors[] = "กรุณาระบุวันที่เช็คอินและเช็คเอาท์";
    }
    
    // ตรวจสอบวันที่
    $checkin_date = strtotime($checkin);
    $checkout_date = strtotime($checkout);
    $today = strtotime(date('Y-m-d'));
    
    if ($checkin_date < $today) {
        $errors[] = "วันที่เช็คอินต้องไม่น้อยกว่าวันนี้";
    }
    
    if ($checkout_date <= $checkin_date) {
        $errors[] = "วันที่เช็คเอาท์ต้องมากกว่าวันที่เช็คอิน";
    }
    
    if (empty($fullname)) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุล";
    }
    
    if (empty($phone)) {
        $errors[] = "กรุณากรอกเบอร์โทรศัพท์";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    }
    
    // ถ้ามี error แสดงข้อความ error
    if (!empty($errors)) {
        echo "<!DOCTYPE html>
        <html lang='th'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>เกิดข้อผิดพลาด - Grand Paradise Hotel</title>
            <link rel='stylesheet' href='style.css'>
            <style>
                .error-container {
                    max-width: 600px;
                    margin: 100px auto;
                    padding: 30px;
                    background: white;
                    border-radius: 15px;
                    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                }
                .error-title {
                    color: #e74c3c;
                    font-size: 2rem;
                    margin-bottom: 20px;
                }
                .error-list {
                    background: #fee;
                    padding: 20px;
                    border-radius: 8px;
                    border-left: 4px solid #e74c3c;
                }
                .error-list li {
                    margin: 10px 0;
                    color: #c0392b;
                }
                .back-btn {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 30px;
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                    border-radius: 25px;
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1 class='error-title'>❌ เกิดข้อผิดพลาด</h1>
                <div class='error-list'>
                    <ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>
                </div>
                <a href='index.html' class='back-btn'>← กลับไปแก้ไข</a>
            </div>
        </body>
        </html>";
        exit;
    }
    
    // สร้างเลขที่การจอง
    $booking_id = 'BK' . date('Ymd') . rand(1000, 9999);
    
    // บันทึกข้อมูลลงไฟล์ (ในการใช้งานจริงควรบันทึกลงฐานข้อมูล)
    $booking_data = array(
        'booking_id' => $booking_id,
        'room_type' => $room_type,
        'guests' => $guests,
        'checkin' => $checkin,
        'checkout' => $checkout,
        'fullname' => $fullname,
        'phone' => $phone,
        'email' => $email,
        'special_request' => $special_request,
        'nights' => $nights,
        'total_price' => $total_price,
        'booking_date' => date('Y-m-d H:i:s')
    );
    
    // สร้างโฟลเดอร์ bookings ถ้ายังไม่มี
    if (!file_exists('bookings')) {
        mkdir('bookings', 0777, true);
    }
    
    // บันทึกข้อมูลลงไฟล์
    $filename = 'bookings/' . $booking_id . '.json';
    file_put_contents($filename, json_encode($booking_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // แสดงหน้ายืนยันการจอง
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>การจองสำเร็จ - Grand Paradise Hotel</title>
        <link rel="stylesheet" href="style.css">
        <style>
            .success-container {
                max-width: 800px;
                margin: 50px auto;
                padding: 40px;
                background: white;
                border-radius: 15px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            }
            .success-icon {
                text-align: center;
                font-size: 5rem;
                margin-bottom: 20px;
            }
            .success-title {
                text-align: center;
                color: #27ae60;
                font-size: 2.5rem;
                margin-bottom: 30px;
            }
            .booking-details {
                background: #f9f9f9;
                padding: 30px;
                border-radius: 10px;
                margin: 30px 0;
            }
            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 12px 0;
                border-bottom: 1px solid #e0e0e0;
            }
            .detail-row:last-child {
                border-bottom: none;
            }
            .detail-label {
                font-weight: 600;
                color: #555;
            }
            .detail-value {
                color: #333;
            }
            .total-price {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 10px;
                text-align: center;
                font-size: 1.5rem;
                font-weight: bold;
                margin: 20px 0;
            }
            .action-buttons {
                display: flex;
                gap: 20px;
                justify-content: center;
                margin-top: 30px;
            }
            .btn {
                padding: 15px 40px;
                border-radius: 25px;
                text-decoration: none;
                font-weight: bold;
                transition: transform 0.3s;
            }
            .btn-primary {
                background: #667eea;
                color: white;
            }
            .btn-secondary {
                background: white;
                color: #667eea;
                border: 2px solid #667eea;
            }
            .btn:hover {
                transform: translateY(-2px);
            }
            .important-note {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin-top: 20px;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="success-container">
            <div class="success-icon">✅</div>
            <h1 class="success-title">การจองสำเร็จ!</h1>
            
            <p style="text-align: center; font-size: 1.1rem; color: #666; margin-bottom: 30px;">
                ขอบคุณที่เลือกใช้บริการ Grand Paradise Hotel<br>
                เราได้รับการจองของคุณเรียบร้อยแล้ว
            </p>
            
            <div class="booking-details">
                <h2 style="margin-bottom: 20px; color: #333;">📋 รายละเอียดการจอง</h2>
                
                <div class="detail-row">
                    <span class="detail-label">เลขที่การจอง:</span>
                    <span class="detail-value" style="font-weight: bold; color: #667eea;"><?php echo $booking_id; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">ประเภทห้องพัก:</span>
                    <span class="detail-value"><?php echo $room_type; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">จำนวนผู้เข้าพัก:</span>
                    <span class="detail-value"><?php echo $guests; ?> ท่าน</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">วันที่เช็คอิน:</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($checkin)); ?> (14:00 น.)</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">วันที่เช็คเอาท์:</span>
                    <span class="detail-value"><?php echo date('d/m/Y', strtotime($checkout)); ?> (12:00 น.)</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">จำนวนคืน:</span>
                    <span class="detail-value"><?php echo $nights; ?> คืน</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">ชื่อผู้จอง:</span>
                    <span class="detail-value"><?php echo $fullname; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">เบอร์โทรศัพท์:</span>
                    <span class="detail-value"><?php echo $phone; ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">อีเมล:</span>
                    <span class="detail-value"><?php echo $email; ?></span>
                </div>
                
                <?php if (!empty($special_request)) { ?>
                <div class="detail-row">
                    <span class="detail-label">คำขอพิเศษ:</span>
                    <span class="detail-value"><?php echo $special_request; ?></span>
                </div>
                <?php } ?>
            </div>
            
            <div class="total-price">
                💰 ราคารวมทั้งหมด: ฿<?php echo number_format($total_price); ?>
            </div>
            
            <div class="important-note">
                <strong>⚠️ หมายเหตุสำคัญ:</strong>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>กรุณาเก็บเลขที่การจองไว้สำหรับการเช็คอิน</li>
                    <li>เราได้ส่งอีเมลยืนยันไปที่ <?php echo $email; ?> แล้ว</li>
                    <li>หากต้องการยกเลิกหรือเปลี่ยนแปลงการจอง กรุณาติดต่อล่วงหน้าอย่างน้อย 24 ชั่วโมง</li>
                    <li>การชำระเงินสามารถทำได้ที่โรงแรมเมื่อเช็คอิน</li>
                </ul>
            </div>
            
            <div class="action-buttons">
                <a href="index.html" class="btn btn-secondary">← กลับหน้าแรก</a>
                <a href="javascript:window.print()" class="btn btn-primary">🖨️ พิมพ์ใบจอง</a>
            </div>
            
            <p style="text-align: center; margin-top: 30px; color: #666;">
                หากมีคำถามเพิ่มเติม กรุณาติดต่อ: 📞 02-123-4567 | ✉️ info@grandparadise.com
            </p>
        </div>
    </body>
    </html>
    <?php
    
} else {
    // ถ้าเข้ามาโดยตรงโดยไม่ผ่านฟอร์ม ให้กลับไปหน้าแรก
    header("Location: index.html");
    exit;
}
?>