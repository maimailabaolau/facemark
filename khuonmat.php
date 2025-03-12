<?php
include "admin/database.php";

$db = new Database();
$conn = $db->getConnection();

// Lấy danh sách sinh viên
$query = "SELECT * FROM sinhvien ORDER BY ten_sv";
$result = $conn->query($query);

// Xử lý tải lên hình ảnh khuôn mặt
if (isset($_POST['upload']) && isset($_FILES['face_image'])) {
    $ma_sv = intval($_POST['ma_sv']); // Chuyển về int (phòng lỗi)
    $imageData = file_get_contents($_FILES["face_image"]["tmp_name"]);

    if (!$imageData) {
        $message = "Lỗi: Không thể đọc file ảnh!";
    } else {
        $update_query = "UPDATE sinhvien SET nhan_dien = ? WHERE ma_sv = ?";
        $stmt = $conn->prepare($update_query);

        if ($stmt) {
            // Bind chỉ số của dữ liệu blob
            $stmt->bind_param("bi", $null, $ma_sv); // "b" cho BLOB, "i" cho int
            $stmt->send_long_data(0, $imageData); // Gửi dữ liệu ảnh

            if ($stmt->execute()) {
                $message = "Đã lưu khuôn mặt thành công!";
            } else {
                $message = "Lỗi khi lưu ảnh vào database: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "Lỗi truy vấn SQL: " . $conn->error;
        }
    }
}



?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Quản Lý Khuôn Mặt</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .camera-container {
            width: 400px;
            margin: 20px 0;
        }

        .student-face {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .student-info {
            flex: 2;
        }

        .face-preview {
            flex: 1;
            text-align: center;
        }

        .face-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
        }

        .face-actions {
            flex: 1;
            text-align: right;
        }
    </style>
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

        <div class="khung">
            <h2>Quản Lý Khuôn Mặt Sinh Viên</h2>

            <?php if (isset($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="camera-container">
                <h3>Thêm khuôn mặt mới</h3>
                <div id="camera-wrapper">
                    <video id="camera" width="400" height="300" autoplay></video>
                    <canvas id="canvas" width="400" height="300" style="display:none;"></canvas>
                </div>
                <button id="capture-btn" class="btn present">Chụp ảnh</button>
            </div>

            <form id="upload-form" method="POST" enctype="multipart/form-data" style="display:none;">
                <select name="ma_sv" title="Chọn sinh viên" required>
                    <option value="">-- Chọn sinh viên --</option>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <option value="<?php echo $row['ma_sv']; ?>"><?php echo $row['ten_sv']; ?>
                            (<?php echo $row['ma_sv']; ?>)</option>
                    <?php endwhile; ?>
                </select>
                <input type="file" name="face_image" id="face-image" accept="image/*" required>
                <input type="hidden" name="upload" value="1">
                <button type="submit" class="btn present">Lưu khuôn mặt</button>
            </form>

            <h3>Danh sách khuôn mặt đã lưu</h3>

            <div class="student-faces">
                <?php
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()): ?>
                    <div class="student-face">
                        <div class="student-info">
                            <strong><?php echo $row['ten_sv']; ?></strong><br>
                            MSSV: <?php echo $row['ma_sv']; ?>
                        </div>
                        <div class="face-preview">

                            <img src="get_image.php?ma_sv=<?php echo $row['ma_sv']; ?>" alt="Khuôn mặt">

                        </div>
                        <div class="face-actions">
                            <button class="btn absent" onclick="deleteFace(<?php echo $row['ma_sv']; ?>)">Xóa</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </div>
    </div>

    <script>
        // Xử lý camera
        let video = document.getElementById('camera');
        let canvas = document.getElementById('canvas');
        let captureBtn = document.getElementById('capture-btn');
        let uploadForm = document.getElementById('upload-form');
        let faceImageInput = document.getElementById('face-image');

        // Khởi tạo camera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    video.srcObject = stream;
                })
                .catch(function (error) {
                    console.error("Lỗi camera:", error);
                    alert("Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.");
                });
        }

        // Chụp ảnh khi nhấn nút
        captureBtn.addEventListener('click', function () {
            let context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 400, 300);

            canvas.toBlob(function (blob) {
                if (!blob) {
                    console.error("Lỗi: Không tạo được blob từ canvas!");
                    return;
                }

                let file = new File([blob], "captured_face.jpg", { type: "image/jpeg" });

                console.log("Ảnh đã chụp:", file); // Kiểm tra xem file có tồn tại không

                let dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                faceImageInput.files = dataTransfer.files;

                uploadForm.style.display = 'block';
            }, 'image/jpeg');
        });


        // Hàm xóa khuôn mặt
        function deleteFace(ma_sv) {
            if (confirm("Bạn có chắc muốn xóa khuôn mặt này?")) {
                fetch('delete_face.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'ma_sv=' + ma_sv
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Đã xóa khuôn mặt!");
                            location.reload();
                        } else {
                            alert("Lỗi: " + data.message);
                        }
                    });
            }
        }
    </script>
</body>

</html>