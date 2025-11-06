<?php include '../include1/header.php'; ?>
<?php include '../include1/sidebar.php'; ?>

<div class="container mt-4">
    <h2 class="text-center mb-4 text-primary">QUẢN LÝ THÔNG TIN BÁNH</h2>

    <!-- 🟢 Nút thêm bánh -->
    <div class="mb-3 text-end">
        <a href="themBanh.php" class="btn btn-success">+ Thêm bánh mới</a>
    </div>

    <?php
    // =========================
    // 🗑️ Xử lý xóa hoặc khóa bánh
    // =========================
    if (isset($_GET['xoa'])) {
        $maBanh = intval($_GET['xoa']);

        $sqlCheck = "SELECT COUNT(*) AS SoLanBan FROM ChiTietDonHang WHERE MaBanh = $maBanh";
        $res = $conn->query($sqlCheck);
        $row = $res->fetch_assoc();
        $daBan = $row['SoLanBan'] > 0;

        if ($daBan) {
            $conn->query("UPDATE ThongTinBanh SET TinhTrang = 0 WHERE MaBanh = $maBanh");
            echo "<script>alert('⚠️ Bánh này đã từng được bán, nên chỉ bị KHÓA chứ không thể xóa!'); window.location='QuanLyThongTinBanh.php';</script>";
        } else {
            if ($conn->query("DELETE FROM ThongTinBanh WHERE MaBanh = $maBanh")) {
                echo "<script>alert('🗑️ Đã xóa bánh thành công!'); window.location='QuanLyThongTinBanh.php';</script>";
            } else {
                echo "<div class='alert alert-danger mt-3'>❌ Lỗi khi xóa: " . $conn->error . "</div>";
            }
        }
    }

    // =========================
    // ✏️ Xử lý cập nhật
    // =========================
    if (isset($_POST['luu_sua'])) {
        $ma = intval($_POST['sua_ma']);
        $ten = trim($_POST['sua_ten']);
        $gia = floatval($_POST['sua_gia']);
        $soluong = intval($_POST['sua_soluong']);
        $tinhtrang = intval($_POST['sua_tinhtrang']);
        $loai = intval($_POST['sua_loai']);
        $anh_cu = $_POST['anh_cu'];

        // Xử lý upload ảnh mới
        $tenAnhMoi = $anh_cu;
        if (isset($_FILES['sua_hinhanh']) && $_FILES['sua_hinhanh']['error'] == 0) {
            $fileTmp = $_FILES['sua_hinhanh']['tmp_name'];
            $fileName = basename($_FILES['sua_hinhanh']['name']);
            $targetPath = "../img/" . $fileName;

            if (move_uploaded_file($fileTmp, $targetPath)) {
                $tenAnhMoi = $fileName;
            }
        }

        $sqlUpdate = "UPDATE ThongTinBanh 
                      SET TenBanh='$ten', Gia=$gia, SoLuong=$soluong, 
                          MaLoaiBanh=$loai, TinhTrang=$tinhtrang, HinhAnh='$tenAnhMoi'
                      WHERE MaBanh=$ma";

        if ($conn->query($sqlUpdate)) {
            echo "<script>alert('✅ Cập nhật bánh thành công!'); window.location='QuanLyThongTinBanh.php';</script>";
        } else {
            echo "<div class='alert alert-danger mt-3'>⚠️ Lỗi: " . $conn->error . "</div>";
        }
    }
    ?>

    <!-- 📋 Danh sách bánh -->
    <div class="card shadow-sm p-4">
        <h5 class="text-primary mb-3">Danh sách bánh</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Mã bánh</th>
                    <th>Tên bánh</th>
                    <th>Loại bánh</th>
                    <th>Giá (VNĐ)</th>
                    <th>Số lượng</th>
                    <th>Hình ảnh</th>
                    <th>Tình trạng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT tb.*, lb.TenLoaiBanh 
                        FROM ThongTinBanh tb
                        JOIN LoaiBanh lb ON tb.MaLoaiBanh = lb.MaLoaiBanh
                        ORDER BY tb.MaBanh ASC";
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $ma = htmlspecialchars($row['MaBanh']);
                        $ten = htmlspecialchars($row['TenBanh']);
                        $loai = htmlspecialchars($row['TenLoaiBanh']);
                        $maLoai = htmlspecialchars($row['MaLoaiBanh']);
                        $gia = number_format($row['Gia'], 0, ',', '.');
                        $soLuong = htmlspecialchars($row['SoLuong']);
                        $hinhAnh = htmlspecialchars($row['HinhAnh']);
                        $tinhtrang = (int)$row['TinhTrang'];

                        $hinhAnhPath = "../img/" . $hinhAnh;
                        $badge = $tinhtrang == 1
                            ? "<span class='badge bg-success text-dark px-3 py-2'>Mở</span>"
                            : "<span class='badge bg-danger text-dark px-3 py-2'>Khóa</span>";

                        echo "
                        <tr>
                            <td>$ma</td>
                            <td>$ten</td>
                            <td>$loai</td>
                            <td>$gia</td>
                            <td>$soLuong</td>
                            <td>";
                        echo $hinhAnh
                            ? "<img src='$hinhAnhPath' width='60' height='60' style='object-fit:cover;border-radius:8px;'>"
                            : "<span class='text-muted fst-italic'>Không có ảnh</span>";
                        echo "</td>
                            <td>$badge</td>
                            <td>
                                <button class='btn btn-warning btn-sm btn-edit'
                                        data-id='$ma'
                                        data-ten='$ten'
                                        data-gia='{$row['Gia']}'
                                        data-soluong='$soLuong'
                                        data-tinhtrang='$tinhtrang'
                                        data-loai='$maLoai'
                                        data-anh='$hinhAnh'>
                                    <i class='fas fa-edit'></i> Sửa
                                </button>
                                <a href='?xoa=$ma' class='btn btn-danger btn-sm'
                                   onclick='return confirm(\"⚠️ Bạn có chắc chắn muốn xóa bánh này không?\")'>Xóa</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo '<tr><td colspan="8">Chưa có bánh nào trong hệ thống.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 🔧 Modal Sửa Bánh -->
<div class="modal fade" id="modalSuaBanh" tabindex="-1" aria-labelledby="modalSuaBanhLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-warning text-white rounded-top-4">
        <h5 class="modal-title fw-semibold" id="modalSuaBanhLabel">Sửa thông tin bánh</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form method="POST" action="" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <input type="hidden" id="sua_ma" name="sua_ma">
          <input type="hidden" id="anh_cu" name="anh_cu">

          <div class="mb-3">
            <label class="form-label fw-semibold">Tên bánh</label>
            <input type="text" class="form-control" id="sua_ten" name="sua_ten" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Loại bánh</label>
            <select id="sua_loai" name="sua_loai" class="form-select" required>
              <?php
              $resLoai = $conn->query("SELECT * FROM LoaiBanh");
              while ($rowLoai = $resLoai->fetch_assoc()) {
                  echo "<option value='{$rowLoai['MaLoaiBanh']}'>{$rowLoai['TenLoaiBanh']}</option>";
              }
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Giá (VNĐ)</label>
            <input type="number" class="form-control" id="sua_gia" name="sua_gia" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Số lượng</label>
            <input type="number" class="form-control" id="sua_soluong" name="sua_soluong" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Tình trạng</label>
            <select id="sua_tinhtrang" name="sua_tinhtrang" class="form-select">
              <option value="1">Mở</option>
              <option value="0">Khóa</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Hình ảnh mới (nếu muốn thay)</label>
            <input type="file" class="form-control" id="sua_hinhanh" name="sua_hinhanh" accept="image/*">
          </div>
        </div>

        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" name="luu_sua" class="btn btn-success">Lưu thay đổi</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('modalSuaBanh'));
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('sua_ma').value = btn.dataset.id;
            document.getElementById('sua_ten').value = btn.dataset.ten;
            document.getElementById('sua_gia').value = btn.dataset.gia;
            document.getElementById('sua_soluong').value = btn.dataset.soluong;
            document.getElementById('sua_tinhtrang').value = btn.dataset.tinhtrang;
            document.getElementById('sua_loai').value = btn.dataset.loai;
            document.getElementById('anh_cu').value = btn.dataset.anh;
            modal.show();
        });
    });
});
</script>

<style>
.form-control, .form-select {
  font-size: 15px;
  padding: 10px 14px;
  border: 1px solid #ccc;
  transition: all 0.2s ease-in-out;
}
.form-control:focus, .form-select:focus {
  border-color: #f0ad4e;
  box-shadow: 0 0 5px rgba(240, 173, 78, 0.4);
}
</style>

<?php include '../include1/footer.php'; ?>
