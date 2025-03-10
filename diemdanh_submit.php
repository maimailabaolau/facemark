<?php
include "admin/database.php"; // Kết nối CSDL


// Nhận dữ liệu JSON từ request
$data = json_decode(file_get_contents("php://input"), true);
if ($data === null) {
    echo json_encode(["success" => false, "message" => "Lỗi JSON: " . json_last_error_msg()]);
    exit;
}


if (!$data || !isset($data['ma_lop'], $data['ma_monhoc'], $data['ngay'], $data['danh_sach'])) {
    echo json_encode(["success" => false, "message" => "Thiếu dữ liệu hoặc sai định dạng"]);
    exit;
}


$ma_lop = $data['ma_lop'];
$ma_monhoc = $data['ma_monhoc'];
$ngay_diem_danh = $data['ngay'];
$danh_sach = $data['danh_sach'];

$db = new Database();
$conn = $db->getConnection();

// 🔹 **Lấy ma_gv từ bảng giangday**
$query_gv = "SELECT ma_gv FROM giangday WHERE ma_lop = ? AND ma_monhoc = ?";
$stmt_gv = $conn->prepare($query_gv);
$stmt_gv->bind_param("ii", $ma_lop, $ma_monhoc);
$stmt_gv->execute();
$result_gv = $stmt_gv->get_result();
$row_gv = $result_gv->fetch_assoc();
$ma_gv = $row_gv['ma_gv'] ?? null;
$stmt_gv->close();


if (!$ma_gv) {
    echo json_encode(["success" => false, "message" => "Không tìm thấy giảng viên cho lớp và môn học này!"]);
    exit();
}


// 🔹 **Lưu dữ liệu vào bảng diemdanh**
foreach ($danh_sach as $ma_sv => $trang_thai) {
    // Kiểm tra bản ghi đã tồn tại và lấy thời gian tạo
    $query_check = "SELECT created_at FROM diemdanh 
                    WHERE ma_sv = ? AND ma_gv = ? AND ma_monhoc = ? AND ma_lop = ? AND ngay_diem_danh = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("iiiis", $ma_sv, $ma_gv, $ma_monhoc, $ma_lop, $ngay_diem_danh);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($created_at);
        $stmt_check->fetch();
        $stmt_check->close();

        // Kiểm tra nếu quá 30 phút
        if (strtotime($created_at) + 1800 < time()) {
            echo json_encode(["success" => false, "message" => "Không thể chỉnh sửa điểm danh sau 30 phút!"]);
            exit();
        }

        // Cập nhật trạng thái điểm danh
        $query = "UPDATE diemdanh SET trang_thai = ? 
                  WHERE ma_sv = ? AND ma_gv = ? AND ma_monhoc = ? AND ma_lop = ? AND ngay_diem_danh = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siiiis", $trang_thai, $ma_sv, $ma_gv, $ma_monhoc, $ma_lop, $ngay_diem_danh);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt_check->close();
    }
}


$conn->close();
echo json_encode(["success" => true]);
?>