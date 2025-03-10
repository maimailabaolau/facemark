<?php
session_start();
include "admin/database.php";
$db = new Database();
$conn = $db->getConnection();

$ma_lop = $_POST['ma_lop'] ?? null;
$ma_monhoc = $_POST['ma_monhoc'] ?? null;
$ma_nien_khoa = $_POST['ma_nien_khoa'] ?? null;

if (!$ma_lop || !$ma_monhoc || !$ma_nien_khoa) {
    echo json_encode(["success" => false, "message" => "Thiếu dữ liệu đầu vào"]);
    exit;
}

// Truy vấn kiểm tra có tồn tại trong bảng giangday không
$query = "SELECT * FROM giangday WHERE ma_monhoc = ? AND ma_nien_khoa = ? AND ma_lop = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $ma_monhoc, $ma_nien_khoa, $ma_lop);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => true, "message" => "Dữ liệu hợp lệ"]);
} else {
    echo json_encode(["success" => false, "message" => "Không tìm thấy dữ liệu trong bảng giangday"]);
}

$stmt->close();
$conn->close();
