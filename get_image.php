<?php
include "admin/database.php";

$db = new Database();
$conn = $db->getConnection();

if (isset($_GET['ma_sv'])) {
    $ma_sv = $_GET['ma_sv'];

    // Kiểm tra nếu `ma_sv` là chuỗi thì thay `i` bằng `s`
    $stmt = $conn->prepare("SELECT nhan_dien FROM sinhvien WHERE ma_sv = ?");
    $stmt->bind_param("i", $ma_sv); // Chỉnh lại "s" nếu ma_sv là chuỗi
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($imageData);
        $stmt->fetch();

        // Kiểm tra nếu dữ liệu ảnh không rỗng
        if (!empty($imageData)) {
            header("Content-Type: image/jpeg");
            echo $imageData;
            exit;
        }
    }

    // Nếu không có ảnh, trả về ảnh mặc định
    header("Content-Type: image/png");
    readfile("default.png");
}
?>
