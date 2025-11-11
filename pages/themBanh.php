<?php include '../include/header.php'; 
include '../include/sidebar.php'; ?>

<div class="container mt-4">
    <h2 class="text-center mb-4">THÊM BÁNH MỚI</h2>

    <form action="" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light shadow-sm">
        <div class="mb-3">
            <label for="tenBanh" class="form-label">Tên bánh</label>
            <input type="text" class="form-control" id="tenBanh" name="tenBanh" required>
        </div>

        <div class="mb-3">
            <label for="gia" class="form-label">Giá (VNĐ)</label>
            <input type="number" class="form-control" id="gia" name="gia" required>
        </div>

        <div class="mb-3">
            <label for="soLuong" class="form-label">Số lượng</label>
            <input type="number" class="form-control" id="soLuong" name="soLuong" required>
        </div>

        <div class="mb-3">
            <label for="maLoai" class="form-label">Loại bánh</label>
            <select class="form-select" id="maLoai" name="maLoaiBanh" required>
                <option value="">-- Chọn loại bánh --</option>
                <?php
                $loai = $conn->query("SELECT * FROM LoaiBanh");
                while ($row = $loai->fetch_assoc()) {
                    echo "<option value='{$row['MaLoaiBanh']}'>{$row['TenLoaiBanh']}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Upload hình ảnh -->
        <div class="mb-3">
            <label for="hinhAnh" class="form-label">Hình ảnh</label>
            <input type="file" class="form-control" id="hinhAnh" name="hinhAnh" accept="image/*" required>

            <!-- Xem trước ảnh -->
            <div class="mt-3 text-center">
                <img id="preview" src="" alt="Xem trước ảnh" 
                     style="max-width:150px; display:none; border-radius:8px; border:1px solid #ccc; object-fit:cover;">
            </div>
        </div>

        <div class="text-center mt-4">
            <button type="submit" name="luu" class="btn btn-success">Lưu</button>
            <a href="QuanLyThongTinBanh.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>

    <?php
    if (isset($_POST['luu'])) {
        $ten = $_POST['tenBanh'];
        $gia = $_POST['gia'];
        $soLuong = $_POST['soLuong'];
        $maLoai = $_POST['maLoaiBanh'];

        // Xử lý upload hình ảnh
        $targetDir = "../img/";
        $fileName = basename($_FILES["hinhAnh"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        $allowTypes = array('jpg', 'jpeg', 'png', 'gif', 'webp');

        if (in_array($fileType, $allowTypes)) {
            if (move_uploaded_file($_FILES["hinhAnh"]["tmp_name"], $targetFilePath)) {
                // Lưu tên file (chỉ lưu tên, không lưu đường dẫn)
                $sql = "INSERT INTO ThongTinBanh (TenBanh, Gia, SoLuong, HinhAnh, MaLoaiBanh)
                        VALUES ('$ten', '$gia', '$soLuong', '$fileName', '$maLoai')";

                if ($conn->query($sql) === TRUE) {
                    echo "<script>
                            alert('Thêm bánh mới thành công!');
                            window.location='QuanLyThongTinBanh.php';
                          </script>";
                } else {
                    echo "<script>alert('Lỗi khi thêm bánh: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Lỗi khi tải ảnh lên!');</script>";
            }
        } else {
            echo "<script>alert('Chỉ chấp nhận các định dạng ảnh: JPG, JPEG, PNG, GIF, WEBP');</script>";
        }
    }
    ?>
</div>

<!-- Xem trước ảnh trước khi tải lên -->
<script>
    document.getElementById('hinhAnh').addEventListener('change', function (event) {
        const preview = document.getElementById('preview');
        const file = event.target.files[0];

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });
</script>

<?php include '../include/footer.php'; 
?>
