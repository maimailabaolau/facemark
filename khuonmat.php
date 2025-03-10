<?php
include "admin/database.php";

$db = new Database();
$conn = $db->getConnection();

// Lấy danh sách sinh viên
$query = "SELECT * FROM sinhvien ORDER BY ten_sv";
$result = $conn->query($query);

// Xử lý tải lên hình ảnh khuôn mặt
if (isset($_POST['upload']) && isset($_FILES['face_image'])) {
    $ma_sv = $_POST['ma_sv'];
    
    $target_dir = "faces/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES["face_image"]["name"], PATHINFO_EXTENSION);
    $new_filename = $ma_sv . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($_FILES["face_image"]["tmp_name"], $target_file)) {
        // Cập nhật thông tin sinh viên để chỉ ra dữ liệu khuôn mặt đã tồn tại
        $update_query = "UPDATE sinhvien SET co_khuon_mat = 1 WHERE ma_sv = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $ma_sv);
        $stmt->execute();
        $stmt->close();
        
        $message = "Đã lưu khuôn mặt thành công!";
    } else {
        $message = "Lỗi khi tải lên hình ảnh.";
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
                <select name="ma_sv" required>
                    <option value="">-- Chọn sinh viên --</option>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <option value="<?php echo $row['ma_sv']; ?>"><?php echo $row['ten_sv']; ?> (<?php echo $row['ma_sv']; ?>)</option>
                    <?php endwhile; ?>
                </select>
                <input type="file" name="face_image" id="face-image" accept="image/*" required>
                <input type="hidden" name="upload" value="1">
                <button type="submit" class="btn present">Lưu khuôn mặt</button>
            </form>
            
            <h3>Danh sách khuôn mặt đã lưu</h3>
            
            <div class="student-faces">
                <?php
                // Đặt lại con trỏ để lấy lại dữ liệu
                $result->data_seek(0);
                while($row = $result->fetch_assoc()):
                    $face_file = "faces/" . $row['ma_sv'] . ".jpg";
                    $has_face = file_exists($face_file);
                ?>
                <div class="student-face">
                    <div class="student-info">
                        <strong><?php echo $row['ten_sv']; ?></strong><br>
                        MSSV: <?php echo $row['ma_sv']; ?>
                    </div>
                    <div class="face-preview">
                        <?php if ($has_face): ?>
                            <img src="<?php echo $face_file; ?>" alt="Khuôn mặt">
                        <?php else: ?>
                            <span>Chưa có khuôn mặt</span>
                        <?php endif; ?>
                    </div>
                    <div class="face-actions">
                        <?php if ($has_face): ?>
                            <button class="btn absent" onclick="deleteFace(<?php echo $row['ma_sv']; ?>)">Xóa</button>
                        <?php endif; ?>
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
                .then(function(stream) {
                    video.srcObject = stream;
                })
                .catch(function(error) {
                    console.error("Lỗi camera:", error);
                    alert("Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.");
                });
        }
        
        // Chụp ảnh khi nhấn nút
        captureBtn.addEventListener('click', function() {
            let context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 400, 300);
            
            // Chuyển đổi canvas thành file
            canvas.toBlob(function(blob) {
                let file = new File([blob], "captured_face.jpg", { type: "image/jpeg" });
                
                // Tạo đối tượng giống FileList
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