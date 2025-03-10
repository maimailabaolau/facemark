<?php
include "admin/database.php";

$ma_lop = $_GET['ma_lop'] ?? null;

if (!$ma_lop) {
    echo json_encode([]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Lấy danh sách sinh viên có dữ liệu khuôn mặt
$query = "SELECT ma_sv, ten_sv, co_khuon_mat FROM sinhvien WHERE ma_lop = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ma_lop);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

header('Content-Type: application/json');
echo json_encode($students);
?>