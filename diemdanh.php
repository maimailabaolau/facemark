<?php
include "admin/database.php"; // Kết nối CSDL


$ma_lop = $_GET['ma_lop'] ?? null;
$ma_monhoc = $_GET['ma_monhoc'] ?? null; // Đúng với URL
$ngay = $_GET['ngay'] ?? null;

if (!$ma_lop || !$ma_monhoc || !$ngay) {
    die("Thiếu dữ liệu lớp, môn học hoặc ngày!");
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
$stmt_lop->close();


// Lấy danh sách sinh viên thuộc lớp đó
$query_sv = "SELECT * FROM sinhvien WHERE ma_lop = ?";
$stmt_sv = $conn->prepare($query_sv);
$stmt_sv->bind_param("i", $ma_lop);
$stmt_sv->execute();
$result_sv = $stmt_sv->get_result();

$sinhvien_data = [];
while ($row = $result_sv->fetch_assoc()) {
    $sinhvien_data[] = $row;
}
$stmt_sv->close();

// Lấy trạng thái điểm danh từ CSDL
$query_diemdanh = "SELECT ma_sv, trang_thai FROM diemdanh WHERE ma_lop = ? AND ma_monhoc = ? AND ngay_diem_danh = ?";
$stmt_diemdanh = $conn->prepare($query_diemdanh);
$stmt_diemdanh->bind_param("iis", $ma_lop, $ma_monhoc, $ngay);
$stmt_diemdanh->execute();
$result_diemdanh = $stmt_diemdanh->get_result();

$trang_thai_diemdanh = [];
while ($row = $result_diemdanh->fetch_assoc()) {
    $trang_thai_diemdanh[$row['ma_sv']] = $row['trang_thai'];
}
$stmt_diemdanh->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Điểm Danh</title>
    <link rel="stylesheet" href="style.css">
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
            <div class="button-container">
                <a href="khuonmat.php" class="vertical-button">Khuôn mặt</a>
                <a href="javascript:void(0);" class="vertical-button" onclick="nhanDien()">Nhận Diện</a>
                <button type="button" class="vertical-button" onclick="xacNhanDiemDanh()">Xác nhận</button>

            </div>

        </div>
        <div class="khung ">
            <h2>Điểm danh - <?= $lop_info['ten_monhoc'] ?> (<?= $lop_info['ten_lop'] ?> -
                <?= $lop_info['ten_nien_khoa'] ?>)
            </h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Tên sinh viên</th>
                            <th>Mã số sinh viên</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sinhvien_data as $index => $sv): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $sv['ten_sv'] ?></td>
                                <td><?= $sv['ma_sv'] ?></td>
                                <td>
                                    <span class="status <?= isset($trang_thai_diemdanh[$sv['ma_sv']])
                                        ? ($trang_thai_diemdanh[$sv['ma_sv']] == 1 ? 'present' : 'absent')
                                        : '' ?>" id="status_<?= $sv['ma_sv'] ?>">
                                        <?= isset($trang_thai_diemdanh[$sv['ma_sv']])
                                            ? ($trang_thai_diemdanh[$sv['ma_sv']] == 1 ? "Có mặt" : "Vắng mặt")
                                            : "Chưa điểm danh" ?>
                                    </span>
                                </td>


                                <td style="display: flex; gap: 10px;">
                                    <button class="btn present" onclick="diemDanh(<?= $sv['ma_sv'] ?>, 'Có mặt')">Có
                                        mặt</button>
                                    <button class="btn absent" onclick="diemDanh(<?= $sv['ma_sv'] ?>, 'Vắng mặt')">Vắng
                                        mặt</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($sinhvien_data)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Không có sinh viên nào trong lớp này.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        let danhSachDiemDanh = {};

        function diemDanh(ma_sv, trang_thai) {
            let trangThaiValue = (trang_thai === 'Có mặt') ? 1 : 0;

            // Cập nhật giao diện ngay lập tức
            let statusElement = document.getElementById('status_' + ma_sv);
            statusElement.textContent = trang_thai;
            statusElement.style.color = (trang_thai === 'Có mặt') ? 'green' : 'red';

            // Lưu trạng thái vào object danhSachDiemDanh
            danhSachDiemDanh[ma_sv] = trangThaiValue;

            // Gửi dữ liệu lên server để lưu vào CSDL
            fetch('diemdanh_submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `ma_sv=${ma_sv}&ma_lop=<?= $ma_lop ?>&ma_monhoc=<?= $ma_monhoc ?>&trang_thai=${trangThaiValue}`
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        alert("Lỗi điểm danh: " + data.message);
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        }



        const ma_lop = <?= json_encode($ma_lop) ?>;
        const ma_monhoc = <?= json_encode($ma_monhoc) ?>;
        const ngay = <?= json_encode($ngay) ?>;

        function xacNhanDiemDanh() {
            fetch('diemdanh_submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ma_lop: ma_lop,
                    ma_monhoc: ma_monhoc,
                    ngay: ngay,
                    danh_sach: danhSachDiemDanh
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Điểm danh thành công!");

                        window.location.reload(); // Reload lại trang sau khi lưu
                    } else {
                        alert("Lỗi khi lưu điểm danh!");
                    }
                })
                .catch(error => console.error('Lỗi:', error));

        }


        function nhanDien() {
            const ma_lop = <?= json_encode($ma_lop) ?>;
            const ma_monhoc = <?= json_encode($ma_monhoc) ?>;
            const ngay = <?= json_encode($ngay) ?>;

            // Điều hướng sang trang nhan_dien.php
            window.location.href = `nhan_dien.php?ma_lop=${ma_lop}&ma_monhoc=${ma_monhoc}&ngay=${ngay}`;
        }


    </script>
</body>

</html>