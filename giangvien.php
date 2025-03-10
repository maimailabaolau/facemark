<?php
session_start(); // Khởi động session

// Kiểm tra nếu chưa đăng nhập, chuyển hướng về trang login
if (!isset($_SESSION['ma_gv'])) {
    header("Location: index.php");
    exit();
}

include "admin/database.php"; // Kết nối database
$db = new Database();
$conn = $db->getConnection();

$ma_gv = $_SESSION['ma_gv']; // Lấy mã giảng viên từ SESSION

$query = "SELECT giangvien.*, khoa.ten_khoa FROM giangvien 
          JOIN khoa ON giangvien.ma_khoa = khoa.ma_khoa 
          WHERE giangvien.ma_gv = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ma_gv);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    die("Không tìm thấy giảng viên!");
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
                    <li><a href="#" class="link active">About</a></li>
                    <li><a href="index.php" class="link active">Đăng Xuất</a></li>
                </ul>
            </div>
        </div>

        <div class="formgv">
            <h2>Thông tin giảng viên</h2><br>
            <p><b>Mã giảng viên:</b> <?php echo $row['ma_gv']; ?></p><br>
            <p><b>Họ Tên:</b> <?php echo $row['ten_gv']; ?></p><br>
            <p><b>Ngày sinh:</b> <?php echo $row['ngay_sinh']; ?></p><br>
            <p><b>Giới tính:</b> <?php echo ($row['gioi_tinh'] == 1) ? 'Nam' : 'Nữ'; ?></p><br>
            <p><b>Thuộc khoa:</b> <?php echo $row['ten_khoa']; ?></p><br>
        </div>

        <h2 class="logonguoi">
            <img src="display_image.php?ma_gv=<?php echo $row['ma_gv']; ?>" alt="Ảnh giảng viên">
        </h2>
    </div>

</body>

</html>