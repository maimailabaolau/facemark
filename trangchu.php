<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Điểm Danh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>

<body>
    <div class="trangchumain">
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

        <section id="slider">
            <div class="aspect-ratio-169">
                <img src="img/1.jpg" alt="">
                <img src="img/2.jpg" alt="">
                <img src="img/3.jpg" alt="">
            </div>
        </section>
    </div>
</body>
<script>
    const header = document.querySelector("header")
    window.addEventListener("scroll", function() {
        x = window.pageXOffset
        if (x > 0) {
            header.classList.add("sticky")
        } else {
            header.classList.remove("sticky")
        }
    })

    const imgPosition = document.querySelectorAll(".aspect-ratio-169 img")
    const imgContainer = document.querySelector('.aspect-ratio-169')
    let index = 0
    let imgnumber = imgPosition.length
    // cho slide nam ngang canh nhau
    imgPosition.forEach(function(image, index) {
        image.style.left = index * 100 + "%"
    })

    function imgslide() {
        index++;
        // function slider(){
        if (index >= imgnumber) {
            index = 0
        }
        imgContainer.style.left = "-" + index * 100 + "%"
        //}
    }
    setInterval(imgslide, 5000)
</script>

</html>