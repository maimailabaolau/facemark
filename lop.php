<?php
session_start();
include "admin/database.php"; // Kết nối database

$db = new Database();
$conn = $db->getConnection();

// Lấy danh sách lớp
$ma_gv = $_SESSION['ma_gv'];

$query = "SELECT lop.ma_lop, lop.ten_lop, lop.ma_nien_khoa, nien_khoa.ten_nien_khoa 
          FROM lop 
          JOIN nien_khoa ON lop.ma_nien_khoa = nien_khoa.ma_nien_khoa
          JOIN giangday ON lop.ma_lop = giangday.ma_lop
          WHERE giangday.ma_gv = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ma_gv);
$stmt->execute();
$result = $stmt->get_result();

$lop_data = [];
while ($row = $result->fetch_assoc()) {
    $lop_data[] = $row;
}
$stmt->close();


// Lấy danh sách niên khóa
$query_nk = "SELECT * FROM nien_khoa";
$result_nk = $conn->query($query_nk);

$nien_khoa_data = [];
if ($result_nk->num_rows > 0) {
    while ($row = $result_nk->fetch_assoc()) {
        $nien_khoa_data[] = $row;
    }
}

// Lấy danh sách môn học
$query_mh = "SELECT DISTINCT monhoc.ma_monhoc, monhoc.ten_monhoc 
             FROM monhoc
             JOIN giangday ON monhoc.ma_monhoc = giangday.ma_monhoc
             WHERE giangday.ma_gv = ?";

$stmt = $conn->prepare($query_mh);
$stmt->bind_param("i", $ma_gv);
$stmt->execute();
$result_mh = $stmt->get_result();

$monhoc_data = [];
while ($row = $result_mh->fetch_assoc()) {
    $monhoc_data[] = $row;
}
$stmt->close();


$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Website Điểm Danh</title>
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
        </div>

        <div class="formchonlop">
            <h2>Chọn lớp của bạn</h2>
            <form id="lopForm">
                <!-- Chọn lớp -->
                <label for="lop">
                    <p>Các lớp hiện có:</p>
                </label>
                <select id="lop" name="lop">
                    <?php foreach ($lop_data as $lop): ?>
                        <option value="<?= $lop['ma_lop'] ?>" data-nienkhoa="<?= $lop['ma_nien_khoa'] ?>">
                            <?= $lop['ten_lop'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>

                <!-- Chọn môn học -->
                <label for="monhoc">
                    <p>Chọn môn học:</p>
                </label>
                <select id="monhoc" name="monhoc">
                    <?php foreach ($monhoc_data as $mon): ?>
                        <option value="<?= $mon['ma_monhoc'] ?>">
                            <?= $mon['ten_monhoc'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>

                <!-- Chọn niên khóa -->
                <label for="nien_khoa">
                    <p>Chọn niên khóa:</p>
                </label>
                <select id="nien_khoa" name="nien_khoa">
                    <?php foreach ($nien_khoa_data as $nk): ?>
                        <option value="<?= $nk['ma_nien_khoa'] ?>">
                            <?= $nk['ten_nien_khoa'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <br><br>
                <div class="button-containerl">
                    <button type="submit" name="action" class="btn" value="diemdanh">Điểm danh</button>
                    <button type="submit" name="action" class="btn" value="nhapdiem">Nhập điểm</button>
                </div>

            </form>
        </div>
        <script>
            document.getElementById('lopForm').addEventListener('submit', function (event) {
                event.preventDefault();

                const maLop = document.getElementById('lop').value;
                const maMonHoc = document.getElementById('monhoc').value;
                const ngayDiemDanh = new Date().toISOString().split('T')[0]; // Ngày hôm nay

                const action = event.submitter.value; // Xác định nút được bấm

                if (action === "diemdanh") {
                    // Gửi dữ liệu lên insert_diemdanh.php để thêm điểm danh
                    fetch('insert_diemdanh.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `ma_lop=${maLop}&ma_monhoc=${maMonHoc}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Chuyển đến trang điểm danh
                                window.location.href = `diemdanh.php?ma_lop=${maLop}&ma_monhoc=${maMonHoc}&ngay=${ngayDiemDanh}`;
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Lỗi:', error));
                } else if (action === "nhapdiem") {
                    fetch('check_nhapdiem.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `ma_lop=${maLop}&ma_monhoc=${maMonHoc}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Chuyển đến trang điểm danh
                                window.location.href = `nhapdiem.php?ma_lop=${maLop}&ma_monhoc=${maMonHoc}`;
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Lỗi:', error));

                }
            });

        </script>

    </div>


</body>

</html>