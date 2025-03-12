<?php
include "admin/database.php";

$db = new Database();
$conn = $db->getConnection();

if (isset($_GET['ma_sv'])) {
    $ma_sv = $_GET['ma_sv'];

    $stmt = $conn->prepare("SELECT nhan_dien FROM sinhvien WHERE ma_sv = ?");
    $stmt->bind_param("s", $ma_sv);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($imageData);
        $stmt->fetch();

        if (!empty($imageData)) {
            // Đảm bảo không có lỗi output buffer
            if (ob_get_length()) {
                ob_clean();
            }
            header("Content-Type: image/jpeg");
            header("Content-Length: " . strlen($imageData));
            echo $imageData;
            exit;
        }
    }
    $stmt->close();
}

// Trả về lỗi nếu không tìm thấy ảnh
http_response_code(404);
echo "Không tìm thấy ảnh";
?>