<?php
session_start(); // Khởi tạo phiên làm việc

// Kết nối đến cơ sở dữ liệu
include dirname(__FILE__) . '/admin/database.php';
$db = new Database();

// Kiểm tra nếu dữ liệu đã được gửi qua POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Sử dụng prepared statement để bảo vệ SQL Injection
        $conn = $db->getConnection();
        $query = "SELECT * FROM login WHERE username = '" . $conn->real_escape_string($username) . "'";
        $result = $db->select($query);
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Kiểm tra mật khẩu (so sánh trực tiếp)
            if ($password === $user['password']) {
                $_SESSION['username'] = $username;
                $_SESSION['ma_gv'] = $user['ma_gv'];
                header("Location: trangchu.php");
                exit();
            } else {
                echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng');</script>";
            }
        } else {
            echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng');</script>";
        }
    } else {
        echo "<script>alert('Vui lòng nhập tên đăng nhập và mật khẩu');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Website Điểm Danh</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="main">
        <div class="icon">
            <h2 class="logo">
                <img src="img/logotdu.png">
            </h2>
        </div>

        <div class="form">
            <h2>Đăng Nhập</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Tên Đăng Nhập" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Mật Khẩu" required>
                </div>
                <button type="submit" class="btn">Đăng nhập</button>
            </form>
        </div>
    </div>
</body>

</html>