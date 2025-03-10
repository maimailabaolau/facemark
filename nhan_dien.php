<?php
include "admin/database.php";

$ma_lop = $_GET['ma_lop'] ?? null;
$ma_monhoc = $_GET['ma_monhoc'] ?? null;
$ngay = $_GET['ngay'] ?? date('Y-m-d');

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
$stmt_lop->close();

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <title>Điểm Danh Bằng Khuôn Mặt</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/face-api.js"></script>
    <style>
        .camera-container {
            position: relative;
            width: 640px;
            margin: 0 auto;
        }

        #video {
            width: 100%;
            border: 1px solid #ddd;
        }

        #canvas {
            position: absolute;
            top: 0;
            left: 0;
        }

        .attendance-list {
            margin-top: 20px;
            max-height: 300px;
            overflow-y: auto;
        }

        .student-recognized {
            padding: 10px;
            margin: 5px 0;
            background: #e9ffe9;
            border-left: 4px solid green;
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
            <h2>Điểm Danh Bằng Khuôn Mặt</h2>
            <h3><?= $lop_info['ten_monhoc'] ?> (<?= $lop_info['ten_lop'] ?> - <?= $lop_info['ten_nien_khoa'] ?>)</h3>
            <p>Ngày: <?= $ngay ?></p>

            <div class="camera-container">
                <video id="video" autoplay muted></video>
                <canvas id="canvas"></canvas>
            </div>

            <div class="control-buttons">
                <button id="start-btn" class="btn present">Bắt Đầu Nhận Diện</button>
                <button id="complete-btn" class="btn absent" disabled>Hoàn Thành Điểm Danh</button>
            </div>

            <div class="attendance-status">
                <p>Đã nhận diện: <span id="recognized-count">0</span> sinh viên</p>
            </div>

            <div id="attendance-list" class="attendance-list">
                <!-- Danh sách sinh viên được nhận diện sẽ xuất hiện ở đây -->
            </div>
        </div>
    </div>

    <script>
        // Biến toàn cục
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const startBtn = document.getElementById('start-btn');
        const completeBtn = document.getElementById('complete-btn');
        const attendanceList = document.getElementById('attendance-list');
        const recognizedCountElement = document.getElementById('recognized-count');

        let stream;
        let isRecognizing = false;
        let recognizedStudents = new Set();
        let faceDescriptors = [];
        let labeledDescriptors = [];

        // Tham số
        const ma_lop = <?= json_encode($ma_lop) ?>;
        const ma_monhoc = <?= json_encode($ma_monhoc) ?>;
        const ngay = <?= json_encode($ngay) ?>;

        // Tải mô hình face-api
        async function loadModels() {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri('face-models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('face-models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('face-models')
            ]);
            startBtn.disabled = false;
        }

        // Tải dữ liệu khuôn mặt sinh viên
        async function loadStudentFaces() {
            // Lấy dữ liệu sinh viên
            const response = await fetch(`get_student_faces.php?ma_lop=${ma_lop}`);
            const students = await response.json();

            // Tạo bộ mô tả có nhãn cho mỗi sinh viên
            labeledDescriptors = [];

            for (const student of students) {
                if (student.co_khuon_mat) {
                    try {
                        const img = await faceapi.fetchImage(`faces/${student.ma_sv}.jpg`);
                        const detection = await faceapi.detectSingleFace(img)
                            .withFaceLandmarks()
                            .withFaceDescriptor();

                        if (detection) {
                            labeledDescriptors.push(
                                new faceapi.LabeledFaceDescriptors(
                                    student.ma_sv.toString(),
                                    [detection.descriptor]
                                )
                            );
                        }
                    } catch (error) {
                        console.error(`Lỗi tải khuôn mặt cho sinh viên ${student.ma_sv}:`, error);
                    }
                }
            }

            return new faceapi.FaceMatcher(labeledDescriptors, 0.6);
        }

        // Khởi động camera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;

                // Đặt kích thước canvas để phù hợp với video
                video.addEventListener('loadedmetadata', () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                });
            } catch (error) {
                console.error('Lỗi truy cập camera:', error);
                alert('Không thể truy cập camera. Vui lòng kiểm tra quyền truy cập.');
            }
        }

        // Bắt đầu nhận diện khuôn mặt
        async function startFaceRecognition() {
            if (!stream) {
                alert('Camera chưa được khởi động!');
                return;
            }

            startBtn.disabled = true;
            startBtn.textContent = 'Đang nhận diện...';
            completeBtn.disabled = false;

            isRecognizing = true;

            // Tải bộ so khớp khuôn mặt
            const faceMatcher = await loadStudentFaces();

            // Vòng lặp nhận diện
            const recognitionInterval = setInterval(async () => {
                if (!isRecognizing) {
                    clearInterval(recognitionInterval);
                    return;
                }

                // Phát hiện khuôn mặt
                const detections = await faceapi.detectAllFaces(video)
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                // Xóa canvas và vẽ lại
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                // So khớp khuôn mặt phát hiện được với khuôn mặt sinh viên
                detections.forEach(detection => {
                    const match = faceMatcher.findBestMatch(detection.descriptor);

                    if (match.label !== 'unknown') {
                        const ma_sv = parseInt(match.label);

                        // Thêm vào danh sách sinh viên được nhận diện nếu chưa có
                        if (!recognizedStudents.has(ma_sv)) {
                            recognizedStudents.add(ma_sv);

                            // Đánh dấu sinh viên có mặt trong CSDL
                            markStudentPresent(ma_sv);

                            // Cập nhật giao diện
                            updateRecognizedCount();
                        }
                    }

                    // Vẽ hộp phát hiện
                    const box = detection.detection.box;
                    ctx.beginPath();
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = match.label !== 'unknown' ? 'green' : 'red';
                    ctx.rect(box.x, box.y, box.width, box.height);
                    ctx.stroke();

                    // Vẽ nhãn
                    ctx.fillStyle = match.label !== 'unknown' ? 'green' : 'red';
                    ctx.font = '18px Arial';
                    ctx.fillText(
                        match.label !== 'unknown' ? `Đã nhận diện (${match.distance.toFixed(2)})` : 'Không nhận diện',
                        box.x, box.y - 5
                    );
                });
            }, 100);
        }

        // Đánh dấu sinh viên có mặt
        async function markStudentPresent(ma_sv) {
            try {
                const response = await fetch('diemdanh_submit.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `ma_sv=${ma_sv}&ma_lop=${ma_lop}&ma_monhoc=${ma_monhoc}&trang_thai=1`
                });

                const data = await response.json();

                if (data.success) {
                    // Lấy tên sinh viên
                    const nameResponse = await fetch(`get_student_name.php?ma_sv=${ma_sv}`);
                    const nameData = await nameResponse.json();

                    // Thêm vào danh sách điểm danh trong giao diện
                    const studentElement = document.createElement('div');
                    studentElement.className = 'student-recognized';
                    studentElement.innerHTML = `
                        <strong>${nameData.ten_sv}</strong> (MSSV: ${ma_sv})
                        <br>
                        <small>Đã điểm danh lúc: ${new Date().toLocaleTimeString()}</small>
                    `;
                    attendanceList.prepend(studentElement);
                }
            } catch (error) {
                console.error('Lỗi khi điểm danh:', error);
            }
        }

        // Cập nhật số lượng sinh viên đã nhận diện
        function updateRecognizedCount() {
            recognizedCountElement.textContent = recognizedStudents.size;
        }

        // Hoàn thành điểm danh
        function completeAttendance() {
            isRecognizing = false;

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            startBtn.disabled = false;
            startBtn.textContent = 'Bắt Đầu Nhận Diện';
            completeBtn.disabled = true;

            // Chuyển đến trang điểm danh thủ công để xác minh
            window.location.href = `diemdanh.php?ma_lop=${ma_lop}&ma_monhoc=${ma_monhoc}&ngay=${ngay}`;
        }

        // Lắng nghe sự kiện
        document.addEventListener('DOMContentLoaded', loadModels);
        startBtn.addEventListener('click', async () => {
            if (!stream) {
                await startCamera();
            }
            startFaceRecognition();
        });
        completeBtn.addEventListener('click', completeAttendance);
    </script>
</body>

</html>