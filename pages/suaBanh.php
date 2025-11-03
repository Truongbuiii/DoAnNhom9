<?php include 'include/header.php'; ?>
<?php include 'include/sidebar.php'; ?>
<?php include 'include/config.php'; ?>

<?php
if (!isset($_GET['id'])) {
    header("Location: QuanLyThongTinBanh.php");
    exit;
}
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM ThongTinBanh WHERE MaBanh = $id");
$banh = $result->fetch_assoc();
?>

<div class="container mt-4">
    <h2 class="text-center mb-4">CHỈNH SỬA THÔNG TIN BÁNH</h2>

    <form action="" method="POST" class="p-4 border rounded bg-light shadow-sm">
        <div class="mb-3">
            <label class="form-label">Tên bánh</label>
            <input type="text" name="tenBanh" class="form-control" value="<?php echo $banh['TenBanh']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá (VNĐ)</label>
            <input type="number" name="gia" class="form-control" value="<?php echo $banh['Gia']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Số lượng</label>
            <input type="number" name="soLuong" class="form-control" value="<?php echo $banh['SoLuong']; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Loại bánh</label>
            <select name="maLoaiBanh" class="form-select" required>
                <?php
                $loai = $conn->query("SELECT * FROM LoaiBanh");
                while ($row = $loai->fetch_assoc()) {
                    $selected = ($row['MaLoaiBanh'] == $banh['MaLoaiBanh']) ? 'selected' : '';
                    echo "<option value='{$row['MaLoaiBanh']}' $selected>{$row['TenLoaiBanh']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Hình ảnh</label>
            <input type="text" name="hinhAnh" class="form-control" value="<?php echo $banh['HinhAnh']; ?>">
        </div>

        <div class="text-center">
            <button type="submit" name="capNhat" class="btn btn-primary">Lưu thay đổi</button>
            <a href="QuanLyThongTinBanh.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>

    <?php
    if (isset($_POST['capNhat'])) {
        $ten = $_POST['tenBanh'];
        $gia = $_POST['gia'];
        $soLuong = $_POST['soLuong'];
        $hinh = $_POST['hinhAnh'];
        $maLoai = $_POST['maLoaiBanh'];

        $sql = "UPDATE ThongTinBanh 
                SET TenBanh='$ten', Gia='$gia', SoLuong='$soLuong', HinhAnh='$hinh', MaLoaiBanh='$maLoai' 
                WHERE MaBanh=$id";

        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Cập nhật thông tin bánh thành công!');
                    window.location='QuanLyThongTinBanh.php';
                  </script>";
        } else {
            echo "<script>alert('Lỗi khi cập nhật: " . $conn->error . "');</script>";
        }
    }
    ?>
</div>

<?php include 'include/footer.php'; ?>
