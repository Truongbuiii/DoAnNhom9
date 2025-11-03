<?php include '../include1/header.php'; ?>
<?php include '../include1/sidebar.php'; ?>

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
    <h2 class="text-center mb-4 text-primary">CHỈNH SỬA THÔNG TIN BÁNH</h2>

    <form action="" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light shadow-sm">
        <div class="mb-3">
            <label class="form-label">Tên bánh</label>
            <input type="text" name="tenBanh" class="form-control" 
                   value="<?php echo htmlspecialchars($banh['TenBanh']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá (VNĐ)</label>
            <input type="number" name="gia" class="form-control" 
                   value="<?php echo htmlspecialchars($banh['Gia']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Số lượng</label>
            <input type="number" name="soLuong" class="form-control" 
                   value="<?php echo htmlspecialchars($banh['SoLuong']); ?>" required>
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

        <!-- Phần hình ảnh -->
        <div class="mb-3">
            <label class="form-label">Hình ảnh hiện tại</label><br>
            <?php if (!empty($banh['HinhAnh'])): ?>
                <img src="../img/<?php echo htmlspecialchars($banh['HinhAnh']); ?>" 
                     alt="Hình ảnh bánh" width="150" class="border rounded mb-2" id="previewOld">
            <?php else: ?>
                <p><i>Chưa có hình ảnh</i></p>
            <?php endif; ?>
            <br>
            <label class="form-label mt-2">Chọn ảnh mới (nếu muốn thay đổi)</label>
            <input type="file" name="hinhAnhMoi" class="form-control" accept="image/*" id="hinhAnhMoi">
            <div class="mt-2">
                <img id="previewNew" src="#" alt="Xem trước ảnh mới" 
                     style="max-width: 150px; display: none;" class="border rounded">
            </div>
        </div>

        <div class="text-center">
            <button type="submit" name="capNhat" class="btn btn-primary">Lưu thay đổi</button>
            <a href="QuanLyThongTinBanh.php" class="btn btn-secondary">Quay lại</a>
        </div>
    </form>

    <?php
    if (isset($_POST['capNhat'])) {
        $ten = $conn->real_escape_string($_POST['tenBanh']);
        $gia = floatval($_POST['gia']);
        $soLuong = intval($_POST['soLuong']);
        $maLoai = intval($_POST['maLoaiBanh']);

        // Nếu có upload hình ảnh mới
        if (!empty($_FILES['hinhAnhMoi']['name'])) {
            $fileName = basename($_FILES['hinhAnhMoi']['name']);
            $targetPath = "../img/" . $fileName;

            // Di chuyển file upload vào thư mục img
            if (move_uploaded_file($_FILES['hinhAnhMoi']['tmp_name'], $targetPath)) {
                $sql = "UPDATE ThongTinBanh 
                        SET TenBanh='$ten', Gia=$gia, SoLuong=$soLuong, 
                            HinhAnh='$fileName', MaLoaiBanh=$maLoai 
                        WHERE MaBanh=$id";
            } else {
                echo "<script>alert('Không thể tải ảnh lên.');</script>";
                exit;
            }
        } else {
            // Không thay hình, giữ nguyên
            $sql = "UPDATE ThongTinBanh 
                    SET TenBanh='$ten', Gia=$gia, SoLuong=$soLuong, 
                        MaLoaiBanh=$maLoai 
                    WHERE MaBanh=$id";
        }

        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('✅ Cập nhật thông tin bánh thành công!');
                    window.location='QuanLyThongTinBanh.php';
                  </script>";
        } else {
            echo "<script>alert('⚠️ Lỗi khi cập nhật: " . $conn->error . "');</script>";
        }
    }
    ?>
</div>

<!-- 🖼️ Script hiển thị ảnh mới ngay khi chọn -->
<script>
document.getElementById('hinhAnhMoi').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const previewNew = document.getElementById('previewNew');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewNew.src = e.target.result;
            previewNew.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        previewNew.src = "#";
        previewNew.style.display = 'none';
    }
});
</script>

<?php include '../include1/footer.php'; ?>
