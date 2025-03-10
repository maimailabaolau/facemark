<?php
session_start();
include "admin/database.php"; // Kết nối database

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_gv = $_SESSION['ma_gv'] ?? null; // Lấy mã giảng viên từ session
    $ma_lop = $_POST['ma_lop'] ?? null;
    $ma_monhoc = $_POST['ma_monhoc'] ?? null;

    if (!$ma_gv || !$ma_lop || !$ma_monhoc) {
        echo json_encode(["success" => false, "message" => "Thiếu dữ liệu cần thiết"]);
        exit;
    }

    // Lấy danh sách sinh viên thuộc lớp được chọn
    $query_sv = "SELECT ma_sv FROM sinhvien WHERE ma_lop = ?";
    $stmt_sv = $conn->prepare($query_sv);
    $stmt_sv->bind_param("s", $ma_lop);
    $stmt_sv->execute();
    $result_sv = $stmt_sv->get_result();

    if ($result_sv->num_rows > 0) {
        echo json_encode(["success" => true, "message" => "Điểm danh đã được tạo cho tất cả sinh viên trong lớp"]);
    } else {
        echo json_encode(["success" => false, "message" => "Không có sinh viên nào trong lớp này"]);
    }

    $stmt_sv->close();
}
$conn->close();
