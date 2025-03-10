<?php
include "admin/database.php";

$ma_sv = $_GET['ma_sv'] ?? null;

if (!$ma_sv) {
    echo json_encode(['error' => 'Thiếu mã sinh viên']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Lấy tên sinh viên
$query = "SELECT ten_sv FROM sinhvien WHERE ma_sv = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ma_sv);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($student ?? ['error' => 'Không tìm thấy sinh viên']);
?>