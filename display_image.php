<?php
include "admin/database.php"; // Kết nối DB

$db = new Database();
$conn = $db->getConnection();

if (isset($_GET['ma_gv'])) {
    $ma_gv = intval($_GET['ma_gv']);

    // Truy vấn lấy ảnh
    $stmt = $conn->prepare("SELECT nhan_dien FROM giangvien WHERE ma_gv = ?");
    $stmt->bind_param("i", $ma_gv);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($imageData);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    // Kiểm tra xem có dữ liệu ảnh hay không
    if (!empty($imageData)) {
        header("Content-Type: image/jpeg"); // Xác định loại ảnh
        echo $imageData; // Xuất ảnh ra trình duyệt
    } else {
        // Nếu không có ảnh, hiển thị ảnh mặc định
        header("Content-Type: image/png");
        readfile("img/default-avatar.png");
    }
}
