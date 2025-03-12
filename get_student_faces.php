<?php
header('Content-Type: application/json');
include "admin/database.php";

// Kiểm tra tham số đầu vào
$ma_lop = $_GET['ma_lop'] ?? null;
if (!$ma_lop) {
    echo json_encode(["error" => "Thiếu mã lớp"]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Truy vấn danh sách sinh viên trong lớp
$query = "SELECT ma_sv, ten_sv, co_khuon_mat FROM sinhvien WHERE ma_lop = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ma_lop);
$stmt->execute();
$result = $stmt->get_result();

$students = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Trả về JSON danh sách sinh viên
echo json_encode($students);
