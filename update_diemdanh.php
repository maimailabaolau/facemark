<?php
include "admin/database.php"; // Kết nối database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $ma_sv = $_POST["ma_sv"] ?? null;
    $ma_monhoc = $_POST["ma_monhoc"] ?? null;
    $ngay = $_POST["ngay"] ?? null;
    $trang_thai = $_POST["trang_thai"] ?? null;

    if (!$ma_sv || !$ma_monhoc || !$ngay || !$trang_thai) {
        die("Thiếu dữ liệu!");
    }

    $db = new Database();
    $conn = $db->getConnection();

    // Kiểm tra xem sinh viên đã điểm danh ngày hôm đó chưa
    $query_check = "SELECT * FROM giangday WHERE ma_sv = ? AND ma_monhoc = ? AND ngay_diemdanh = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("iis", $ma_sv, $ma_monhoc, $ngay);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Cập nhật trạng thái điểm danh
        $query_update = "UPDATE giangday SET trang_thai = ? WHERE ma_sv = ? AND ma_monhoc = ? AND ngay_diemdanh = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("siis", $trang_thai, $ma_sv, $ma_monhoc, $ngay);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Thêm mới vào bảng giangday
        $query_insert = "INSERT INTO giangday (ma_sv, ma_monhoc, ngay_diemdanh, trang_thai) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $stmt_insert->bind_param("iiss", $ma_sv, $ma_monhoc, $ngay, $trang_thai);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    $stmt_check->close();
    $conn->close();
}
