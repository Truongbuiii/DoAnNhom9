<?php include 'include/header.php'; ?>
<?php include 'include/sidebar.php'; ?>
<?php include 'include/config.php'; ?>

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

        <div class="mb-3">
            <label for="hinhAnh" class="form-label">Hình ảnh</label>
            <input type="text" class="form-control" id="hinhAnh" name="hinhAnh" placeholder="Nhập đường dẫn ảnh (vd: img/banhkem.jpg)">
        </div>

        <div class="text-center">
            <button type="submit" name="luu" class="btn btn-success">Lưu</button>
            <a href="QuanLyThongTinBanh.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>

    <?php
    if (isset($_POST['luu'])) {
        $ten = $_POST['tenBanh'];
        $gia = $_POST['gia'];
        $soLuong = $_POST['soLuong'];
        $hinh = $_POST['hinhAnh'];
        $maLoai = $_POST['maLoaiBanh'];

        $sql = "INSERT INTO ThongTinBanh (TenBanh, Gia, SoLuong, HinhAnh, MaLoaiBanh)
                VALUES ('$ten', '$gia', '$soLuong', '$hinh', '$maLoai')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Thêm bánh mới thành công!');
                    window.location='QuanLyThongTinBanh.php';
                  </script>";
        } else {
            echo "<script>alert('Lỗi khi thêm bánh: " . $conn->error . "');</script>";
        }
    }
    ?>
</div>

<?php include 'include/footer.php'; ?>
