<?php
session_start();
include "admin/database.php"; // Kết nối database

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ma_gv = $_SESSION['ma_gv'] ?? null; // Lấy mã giảng viên từ session
    $ma_lop = $_POST['ma_lop'] ?? null;
    $ma_monhoc = $_POST['ma_monhoc'] ?? null;
    $ngay_diem_danh = date("Y-m-d"); // Lấy ngày hôm nay
    $trang_thai = "Chưa điểm danh"; // Trạng thái mặc định

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
        while ($row_sv = $result_sv->fetch_assoc()) {
            $ma_sv = $row_sv['ma_sv'];

            // Chèn dữ liệu điểm danh cho mỗi sinh viên
            // Kiểm tra xem đã có bản ghi hay chưa
            $query_check = "SELECT COUNT(*) FROM diemdanh WHERE ma_sv = ? AND ma_gv = ? AND ma_monhoc = ? AND ma_lop = ? AND ngay_diem_danh = ?";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bind_param("sssss", $ma_sv, $ma_gv, $ma_monhoc, $ma_lop, $ngay_diem_danh);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count == 0) { // Chỉ chèn nếu chưa tồn tại
                $query_insert = "INSERT INTO diemdanh (ma_sv, ma_gv, ma_monhoc, ma_lop, ngay_diem_danh, trang_thai) 
                     VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($query_insert);
                $stmt_insert->bind_param("ssssss", $ma_sv, $ma_gv, $ma_monhoc, $ma_lop, $ngay_diem_danh, $trang_thai);
                $stmt_insert->execute();
                $stmt_insert->close();
            }


        }
        echo json_encode(["success" => true, "message" => "Điểm danh đã được tạo cho tất cả sinh viên trong lớp"]);
    } else {
        echo json_encode(["success" => false, "message" => "Không có sinh viên nào trong lớp này"]);
    }

    $stmt_sv->close();
}
$conn->close();
