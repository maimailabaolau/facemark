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
    <script defer src="js/face-api.min.js" onload="loadModels()"></script>
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
        const modelUrl = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights';


        // Tải mô hình face-api
        async function loadModels() {
            await Promise.all([
                faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
                faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
                faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
            ]);
            console.log("Tất cả mô hình đã được tải!");

            startBtn.disabled = false;

        }

        // Tải dữ liệu khuôn mặt sinh viên
        async function loadStudentFaces() {
            const response = await fetch(`get_student_faces.php?ma_lop=${ma_lop}`);
            const students = await response.json();

            labeledDescriptors = [];

            for (const student of students) {
                if (student.co_khuon_mat) {
                    try {
                        const img = await faceapi.fetchImage(`http://localhost:3000/get_image.php?ma_sv=${student.ma_sv}`);

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
        let lastRecognized = {}; // Lưu trạng thái sinh viên được nhận diện
        const recognitionCooldown = 2000; // Giữ tên trên màn hình trong 2 giây

        async function startFaceRecognition() {
            if (!stream) {
                alert('Camera chưa được khởi động!');
                return;
            }

            startBtn.disabled = true;
            startBtn.textContent = 'Đang nhận diện...';
            completeBtn.disabled = false;
            isRecognizing = true;

            const faceMatcher = await loadStudentFaces();

            const recognitionInterval = setInterval(async () => {
                if (!isRecognizing) {
                    clearInterval(recognitionInterval);
                    return;
                }

                const detections = await faceapi.detectAllFaces(video, new faceapi.SsdMobilenetv1Options())
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                detections.forEach(detection => {
                    const match = faceMatcher.findBestMatch(detection.descriptor);

                    if (match.label !== 'unknown' && match.distance < 0.5) {
                        const ma_sv = parseInt(match.label);

                        if (!recognizedStudents.has(ma_sv)) {
                            recognizedStudents.add(ma_sv);
                            markStudentPresent(ma_sv);
                            updateRecognizedCount();
                        }

                        // Nếu sinh viên đã nhận diện trước đó, chỉ cập nhật nếu đã qua thời gian cooldown
                        if (!lastRecognized[ma_sv] || (Date.now() - lastRecognized[ma_sv].time) > recognitionCooldown) {
                            lastRecognized[ma_sv] = { time: Date.now(), name: "Đang tải..." };

                            fetch(`get_student_name.php?ma_sv=${ma_sv}`)
                                .then(response => response.json())
                                .then(nameData => {
                                    lastRecognized[ma_sv].name = nameData.ten_sv || "Không xác định";
                                })
                                .catch(error => console.error("Lỗi lấy tên sinh viên:", error));
                        }

                        const studentName = lastRecognized[ma_sv]?.name || "Đang tải...";

                        // Vẽ hộp phát hiện
                        const box = detection.detection.box;
                        ctx.beginPath();
                        ctx.lineWidth = 3;
                        ctx.strokeStyle = 'green';
                        ctx.rect(box.x, box.y, box.width, box.height);
                        ctx.stroke();

                        // Hiển thị tên trên hộp
                        ctx.fillStyle = 'green';
                        ctx.font = '18px Arial';
                        ctx.fillText(`${studentName} (MSSV: ${ma_sv})`, box.x, box.y - 5);
                    }
                });
            }, 100);
        }

        let danhSachDiemDanh = {}; // Dùng object thay vì array

        async function markStudentPresent(ma_sv) {
            danhSachDiemDanh[ma_sv] = 1; // Đánh dấu trạng thái điểm danh
            console.log(danhSachDiemDanh); // Kiểm tra danh sách
        }



        // Đánh dấu sinh viên có mặt
        // async function markStudentPresent(ma_sv) {
        //     try {
        //         const response = await fetch('diemdanh_submit.php', {
        //             method: 'POST',
        //             headers: {
        //                 'Content-Type': 'application/x-www-form-urlencoded'
        //             },
        //             body: `ma_sv=${ma_sv}&ma_lop=${ma_lop}&ma_monhoc=${ma_monhoc}&trang_thai=1`
        //         });

        //         const data = await response.json();

        //         if (data.success) {
        //             // Lấy tên sinh viên
        //             const nameResponse = await fetch(`get_student_name.php?ma_sv=${ma_sv}`);
        //             const nameData = await nameResponse.json();

        //             // Thêm vào danh sách điểm danh trong giao diện
        //             const studentElement = document.createElement('div');
        //             studentElement.className = 'student-recognized';
        //             studentElement.innerHTML = `
        //         <strong>${nameData.ten_sv}</strong> (MSSV: ${ma_sv})
        //         <br>
        //         <small>Đã điểm danh lúc: ${new Date().toLocaleTimeString()}</small>`;
        //             attendanceList.prepend(studentElement);

        //             // Thêm vào danh sách để gửi lên server khi hoàn thành
        //             danhSachDiemDanh.push({
        //                 ma_sv: ma_sv,
        //                 trang_thai: 1
        //             });
        //         }
        //     } catch (error) {
        //         console.error('Lỗi khi điểm danh:', error);
        //     }
        // }

        // Cập nhật số lượng sinh viên đã nhận diện
        function updateRecognizedCount() {
            recognizedCountElement.textContent = recognizedStudents.size;
        }

        // Hoàn thành điểm danh
        async function completeAttendance() {
            isRecognizing = false;

            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            startBtn.disabled = false;
            startBtn.textContent = 'Bắt Đầu Nhận Diện';
            completeBtn.disabled = true;

            // Gửi danh sách sinh viên đã điểm danh lên server
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
                        window.location.href = `diemdanh.php?ma_lop=${ma_lop}&ma_monhoc=${ma_monhoc}&ngay=${ngay}`;
                    } else {
                        alert("Lỗi khi lưu điểm danh!");
                    }
                })
                .catch(error => console.error('Lỗi:', error));
        }


        // Lắng nghe sự kiện
        document.addEventListener('DOMContentLoaded', async () => {
            await loadModels(); // Đợi tải mô hình xong
            console.log("Mô hình đã sẵn sàng, có thể bắt đầu nhận diện.");
        });

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