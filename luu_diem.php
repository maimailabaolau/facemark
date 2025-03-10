<?php
include "admin/database.php"; // Kết nối CSDL

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['ma_lop'], $data['ma_monhoc'], $data['danh_sach'])) {
    echo json_encode(["success" => false, "message" => "Dữ liệu không hợp lệ"]);
    exit;
}

$ma_lop = $data['ma_lop'];
$ma_monhoc = $data['ma_monhoc'];
$danh_sach = $data['danh_sach'];

$db = new Database();
$conn = $db->getConnection();

// Lưu điểm vào bảng `diem`
foreach ($danh_sach as $sv) {
    $ma_sv = $sv['ma_sv'];
    $diem_giua_ky = $sv['diem_giua_ky'];
    $diem_cuoi_ky = $sv['diem_cuoi_ky'];
    $diem_tong = ($diem_giua_ky*0.4 + $diem_cuoi_ky*0.6);
    if($diem_tong > 10 ) $diem_tong = 10;

    $query = "INSERT INTO diem (ma_sv, ma_monhoc, diem_giua_ky, diem_cuoi_ky, diem_tong)
              VALUES (?, ?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE diem_giua_ky = VALUES(diem_giua_ky), diem_cuoi_ky = VALUES(diem_cuoi_ky), diem_tong = VALUES(diem_tong)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiddd", $ma_sv,$ma_monhoc, $diem_giua_ky, $diem_cuoi_ky ,$diem_tong);
    $stmt->execute();
}

$conn->close();
echo json_encode(["success" => true, "message" => "Lưu điểm thành công!"]);
