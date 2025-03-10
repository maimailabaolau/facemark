<?php
include "admin/database.php";

$response = ['success' => false, 'message' => ''];

if (isset($_POST['ma_sv'])) {
    $ma_sv = $_POST['ma_sv'];

    // Tìm tất cả các định dạng hình ảnh có thể có
    $extensions = ['jpg', 'jpeg', 'png'];
    $deleted = false;

    foreach ($extensions as $ext) {
        $file_path = "faces/" . $ma_sv . "." . $ext;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $deleted = true;
            }
        }
    }

    if ($deleted) {
        // Cập nhật cơ sở dữ liệu
        $db = new Database();
        $conn = $db->getConnection();

        $query = "UPDATE sinhvien SET co_khuon_mat = 0 WHERE ma_sv = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $ma_sv);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            $response['success'] = true;
            $response['message'] = 'Xóa khuôn mặt thành công.';
        } else {
            $response['message'] = 'Xóa ảnh thành công nhưng cập nhật CSDL thất bại.';
        }
    } else {
        $response['message'] = 'Không tìm thấy ảnh khuôn mặt.';
    }
} else {
    $response['message'] = 'Thiếu mã sinh viên.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>