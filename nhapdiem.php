<?php
include "admin/database.php"; // Kết nối CSDL

$ma_lop = $_GET['ma_lop'] ?? null;
$ma_monhoc = $_GET['ma_monhoc'] ?? null;


if (!$ma_lop || !$ma_monhoc) {
    die("Thiếu dữ liệu lớp hoặc môn học!");
}

$db = new Database();
$conn = $db->getConnection();

// Lấy thông tin lớp, niên khóa, môn học
$query_lop = "SELECT lop.ten_lop, nien_khoa.ten_nien_khoa, monhoc.ten_monhoc
              FROM giangday 
              JOIN lop ON giangday.ma_lop = lop.ma_lop
              JOIN nien_khoa ON giangday.ma_nien_khoa = nien_khoa.ma_nien_khoa
              JOIN monhoc ON giangday.ma_monhoc = monhoc.ma_monhoc
              WHERE giangday.ma_lop = ? AND giangday.ma_monhoc = ?";
$stmt_lop = $conn->prepare($query_lop);
$stmt_lop->bind_param("ii", $ma_lop, $ma_monhoc);
$stmt_lop->execute();
$result_lop = $stmt_lop->get_result();
$lop_info = $result_lop->fetch_assoc();

if (!$lop_info) {
    die("Không tìm thấy dữ liệu giảng dạy!");
}

// Lấy danh sách sinh viên và điểm thuộc lớp đó
$query_sv = "SELECT sv.ma_sv, sv.ten_sv, d.diem_giua_ky, d.diem_cuoi_ky 
             FROM sinhvien sv
             LEFT JOIN diem d ON sv.ma_sv = d.ma_sv
             WHERE sv.ma_lop = ?";
$stmt_sv = $conn->prepare($query_sv);
$stmt_sv->bind_param("i", $ma_lop);
$stmt_sv->execute();
$result_sv = $stmt_sv->get_result();

$sinhvien_data = [];
while ($row = $result_sv->fetch_assoc()) {
    $sinhvien_data[] = $row;
}

$stmt_lop->close();
$stmt_sv->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Website Điểm Danh</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function tinhDiem(row) {
            let heSo1 = parseFloat(row.querySelector(".hs1").value) || 0;
            let cuoiKy1 = parseFloat(row.querySelector(".ck1").value) || 0;

            let diemTongKet = (heSo1 * 0.4) + (cuoiKy1 * 0.6);
            if (diemTongKet > 10) diemTongKet = 10;
            row.querySelector(".tongKet").value = diemTongKet.toFixed(2);

            let thangDiem4, diemChu, xepLoai;
            if (diemTongKet >= 9) {
                thangDiem4 = 4.0; diemChu = "A"; xepLoai = "Xuất sắc";
            } else if (diemTongKet >= 8) {
                thangDiem4 = 3.5; diemChu = "B+"; xepLoai = "Giỏi";
            } else if (diemTongKet >= 7) {
                thangDiem4 = 3.0; diemChu = "B"; xepLoai = "Khá";
            } else if (diemTongKet >= 6) {
                thangDiem4 = 2.5; diemChu = "C+"; xepLoai = "Trung bình khá";
            } else if (diemTongKet >= 5) {
                thangDiem4 = 2.0; diemChu = "C"; xepLoai = "Trung bình";
            } else if (diemTongKet >= 4) {
                thangDiem4 = 1.0; diemChu = "D"; xepLoai = "Trung bình yếu";
            } else {
                thangDiem4 = 0; diemChu = "F"; xepLoai = "Kém";
            }

            row.querySelector(".thangDiem4").value = thangDiem4;
            row.querySelector(".diemChu").innerText = diemChu;
            row.querySelector(".xepLoai").innerText = xepLoai;
        }

        // Gắn sự kiện để cập nhật điểm khi nhập dữ liệu
        document.querySelectorAll(".hs1, .ck1").forEach(input => {
            input.addEventListener("input", function () {
                tinhDiem(this.closest("tr"));
            });

        });

        // 🛠 Tự động tính điểm khi load lại trang
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll("tbody tr").forEach(row => {
                tinhDiem(row);
            });
        });


    </script>
</head>

<body>
    <div class="main">
        <div class="navbar">
            <div class="icon">
                <h2 class="logo">
                    <img src="img/logotdu.png">
                </h2>
            </div>

            <div class="menu">
                <ul>
                    <li><a href="trangchu.php" class="link active">Trang Chủ</a></li>
                    <li><a href="giangvien.php" class="link active">Giảng Viên</a></li>
                    <li><a href="lop.php" class="link active">Lớp</a></li>
                    <li><a href="about.php" class="link active">About</a></li>
                    <li><a href="index.php" class="link active">Đăng Xuất</a></li>
                </ul>
            </div>
        </div>

        <div class="container">
            <div class="khung">
                <h3 class="text-center">Bảng Nhập Điểm</h3>
                <table class="table-container table-bordered">
                    <thead>
                        <tr>
                            <th>MSSV</th>
                            <th>Họ Tên</th>
                            <th>LT Hệ số 1</th>
                            <th>Cuối kỳ 1</th>
                            <th>Điểm tổng kết</th>
                            <th>Thang điểm 4</th>
                            <th>Điểm chữ</th>
                            <th>Xếp loại</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sinhvien_data)): ?>
                            <?php foreach ($sinhvien_data as $row): ?>
                                <tr oninput="tinhDiem(this)">
                                    <td><?= $row["ma_sv"] ?></td>
                                    <td><?= $row["ten_sv"] ?></td>
                                    <td><input type="number" class="form-control hs1" value="<?= $row["diem_giua_ky"] ?>"
                                            step="0.1"></td>
                                    <td><input type="number" class="form-control ck1" value="<?= $row["diem_cuoi_ky"] ?>"
                                            step="0.1"></td>

                                    <td><input type="text" class="form-control tongKet" readonly></td>
                                    <td><input type="text" class="form-control thangDiem4" readonly></td>
                                    <td class="diemChu">-</td>
                                    <td class="xepLoai">-</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">Không có dữ liệu</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button class="btn" onclick="xacNhanDiem()">Xác nhận</button>

            </div>
        </div>
    </div>
</body>


<script>
    function xacNhanDiem() {
        let danhSachDiem = [];
        document.querySelectorAll("tbody tr").forEach(row => {
            let ma_sv = row.cells[0].innerText;
            let diem_giua_ky = row.querySelector(".hs1").value || 0;
            let diem_cuoi_ky = row.querySelector(".ck1").value || 0;
            danhSachDiem.push({ ma_sv, diem_giua_ky, diem_cuoi_ky });
        });

        let data = {
            ma_lop: <?= $ma_lop ?>,
            ma_monhoc: <?= $ma_monhoc ?>,
            danh_sach: danhSachDiem
        };

        fetch("luu_diem.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(result => alert(result.message))
            .catch(error => console.error("Lỗi:", error));
    }


    function validateInput(event) {
        let input = event.target;
        let value = parseFloat(input.value);
        if (value < 0) input.value = 0;
        if (value > 10) input.value = 10;
    }

    document.querySelectorAll(".hs1, .ck1").forEach(input => {
        input.addEventListener("change", validateInput);
    });

</script>


</html>