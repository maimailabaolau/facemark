document.addEventListener("DOMContentLoaded", async () => {
  const video = document.getElementById("video");
  const canvas = document.getElementById("canvas");
  const ctx = canvas.getContext("2d");
  const startBtn = document.getElementById("start-btn");
  const completeBtn = document.getElementById("complete-btn");
  const openModalBtn = document.getElementById("open-recognition-btn");
  const closeModalBtn = document.getElementById("close-modal-btn");
  const recognitionModal = document.getElementById("face-recognition-modal");
  const attendanceList = document.getElementById("attendance-list");

  let stream;
  let isRecognizing = false;
  let recognizedStudents = new Set();
  let danhSachDiemDanh = {};
  const modelUrl =
    "https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@master/weights";

  async function loadModels() {
    await Promise.all([
      faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
      faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
      faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
    ]);
    console.log("Mô hình đã tải xong!");
    startBtn.disabled = false;
  }

  async function startCamera() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = stream;
      video.addEventListener("loadedmetadata", () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
      });
    } catch (error) {
      console.error("Lỗi camera:", error);
    }
  }

  async function loadStudentFaces() {
    const response = await fetch(`get_student_faces.php?ma_lop=${ma_lop}`);
    const students = await response.json();
    let labeledDescriptors = [];

    for (const student of students) {
      if (student.co_khuon_mat) {
        try {
          const img = await faceapi.fetchImage(
            `http://localhost:3000/get_image.php?ma_sv=${student.ma_sv}`
          );
          const detection = await faceapi
            .detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor();
          if (detection) {
            labeledDescriptors.push(
              new faceapi.LabeledFaceDescriptors(student.ma_sv.toString(), [
                detection.descriptor,
              ])
            );
          }
        } catch (error) {
          console.error(`Lỗi tải khuôn mặt cho ${student.ma_sv}:`, error);
        }
      }
    }
    return new faceapi.FaceMatcher(labeledDescriptors, 0.6);
  }
  let lastRecognized = {}; // Lưu trạng thái sinh viên được nhận diện
  const recognitionCooldown = 2000; // Thời gian giữa 2 lần nhận diện

  
  async function startRecognition() {
    if (!stream) {
      await startCamera();
    }
    isRecognizing = true;
    startBtn.disabled = true;
    completeBtn.disabled = false;
    const faceMatcher = await loadStudentFaces();

    const recognitionInterval = setInterval(async () => {
      if (!isRecognizing) {
        clearInterval(recognitionInterval);
        return;
      }

      // Phát hiện khuôn mặt
      const detections = await faceapi
        .detectAllFaces(video, new faceapi.SsdMobilenetv1Options())
        .withFaceLandmarks()
        .withFaceDescriptors();

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      faceapi.draw.drawDetections(canvas, detections); // Vẽ khung quanh mặt

      detections.forEach((detection) => {
        const match = faceMatcher.findBestMatch(detection.descriptor);
        if (match.label !== "unknown" && match.distance < 0.5) {
          const ma_sv = parseInt(match.label);
          if (!recognizedStudents.has(ma_sv)) {
            recognizedStudents.add(ma_sv);
            danhSachDiemDanh[ma_sv] = 1;
            updateAttendanceList(ma_sv);
          }

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

          // Vẽ hộp chữ để hiển thị tên sinh viên
          const box = detection.detection.box;
          ctx.strokeStyle = "green";
          ctx.lineWidth = 3;
          ctx.strokeRect(box.x, box.y, box.width, box.height);

          ctx.fillStyle = "green";
          ctx.font = "16px Arial";
          ctx.fillText(`${studentName} (MSSV: ${ma_sv})`, box.x, box.y - 5);
        }
      });
    }, 100);
  }

  function updateAttendanceList(ma_sv) {
    fetch(`get_student_name.php?ma_sv=${ma_sv}`)
      .then((response) => response.json())
      .then((nameData) => {
        const studentElement = document.createElement("div");
        studentElement.className = "student-recognized";
        studentElement.innerHTML = `<strong>${nameData.ten_sv}</strong> (MSSV: ${ma_sv})`;
        attendanceList.prepend(studentElement);
      })
      .catch((error) => console.error("Lỗi lấy tên sinh viên:", error));
  }

  function completeAttendance() {
    isRecognizing = false;
    if (stream) {
      stream.getTracks().forEach((track) => track.stop());
    }
    startBtn.disabled = false;
    completeBtn.disabled = true;

    fetch("diemdanh_submit.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        ma_lop: ma_lop,
        ma_monhoc: ma_monhoc,
        ngay: ngay,
        danh_sach: danhSachDiemDanh,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Điểm danh thành công!");
          recognitionModal.style.display = "none";
          window.location.reload();
        } else {
          alert("Lỗi khi lưu điểm danh!");
        }
      })
      .catch((error) => console.error("Lỗi gửi điểm danh:", error));
  }

  openModalBtn.addEventListener("click", () => {
    recognitionModal.style.display = "block";
    loadModels();
  });

  closeModalBtn.addEventListener("click", () => {
    recognitionModal.style.display = "none";
    if (stream) {
      stream.getTracks().forEach((track) => track.stop());
    }
  });

  startBtn.addEventListener("click", startRecognition);
  completeBtn.addEventListener("click", completeAttendance);
});
