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
    <script src="js/face-api.min.js"></script>
    <script src="js/diemdanh.js"></script>
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
                <button type="button" class="vertical-button" id="open-recognition-btn">Bắt Đầu Nhận Diện</button>

            </div>

        </div>





        <div class="container">
            <div class="khung">
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

            <div class="khungCC" id="face-recognition-modal" style="display: none;">
                <div>
                    <h2>Điểm Danh Bằng Khuôn Mặt</h2>
                    <h3><?= $lop_info['ten_monhoc'] ?> (<?= $lop_info['ten_lop'] ?> - <?= $lop_info['ten_nien_khoa'] ?>)
                    </h3>
                    <p>Ngày: <?= $ngay ?></p>
                    <div class="camera-container">
                        <video id="video" autoplay muted></video>
                        <canvas id="canvas"></canvas>
                    </div>

                    <div class="btn-cc">
                        <button class="vertical-button" id="start-btn">Bắt Đầu</button>
                        <button class="vertical-button" class="vertical-button" id="complete-btn" disabled>Hoàn
                            Thành</button>
                        <button class="vertical-button" id="close-modal-btn">Đóng</button>
                    </div>

                    <div id="attendance-list" class="attendance-list">
                        <!-- Danh sách sinh viên nhận diện sẽ hiển thị ở đây -->
                    </div>
                </div>
            </div>

        </div>

    </div>
    <script>
        function diemDanh(ma_sv, trang_thai) {
            let trangThaiValue = (trang_thai === 'Có mặt') ? 1 : 0;

            fetch('diemdanh_submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `ma_sv=${ma_sv}&ma_lop=<?= $ma_lop ?>&ma_monhoc=<?= $ma_monhoc ?>&trang_thai=${trangThaiValue}`
            }).then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let statusElement = document.getElementById('status_' + ma_sv);
                        statusElement.textContent = trang_thai;

                        // Xóa class cũ và thêm class mới
                        statusElement.classList.remove('present', 'absent');
                        statusElement.classList.add(trangThaiValue === 1 ? 'present' : 'absent');
                    } else {
                        alert("Lỗi điểm danh: " + data.message);
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        }





        let danhSachDiemDanh = {};

        function diemDanh(ma_sv, trang_thai) {
            danhSachDiemDanh[ma_sv] = trang_thai === 'Có mặt' ? 1 : 0; // 1 = Có mặt, 0 = Vắng mặt
            document.getElementById('status_' + ma_sv).textContent = trang_thai;
            document.getElementById('status_' + ma_sv).style.color = (trang_thai === 'Có mặt') ? 'green' : 'red';
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