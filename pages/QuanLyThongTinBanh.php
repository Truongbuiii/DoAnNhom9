<?php
include '../include1/header.php';
include '../include1/sidebar.php';

// ======== XỬ LÝ THÊM BÁNH MỚI ========
if (isset($_POST['them'])) {
    $ten = $conn->real_escape_string(trim($_POST['ten']));
    $gia = floatval($_POST['gia']);
    $soluong = intval($_POST['soluong']);
    $loai = intval($_POST['loai']);
    $tenAnh = '';

    // Upload ảnh
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] == 0) {
        $fileTmp = $_FILES['hinhanh']['tmp_name'];
        $fileName = basename($_FILES['hinhanh']['name']);
        $targetPath = "../img/" . $fileName;
        if (move_uploaded_file($fileTmp, $targetPath)) {
            $tenAnh = $fileName;
        }
    }

    $sqlInsert = "INSERT INTO ThongTinBanh (TenBanh, Gia, SoLuong, MaLoaiBanh, TinhTrang, HinhAnh)
                  VALUES ('$ten', $gia, $soluong, $loai, 1, '$tenAnh')";
    if ($conn->query($sqlInsert)) {
        echo "<script>window.location='QuanLyThongTinBanh.php';</script>";
        exit;
    } else {
        $errMsg = "Lỗi khi thêm bánh: " . htmlspecialchars($conn->error);
    }
}

// ======== XỬ LÝ CẬP NHẬT ========
if (isset($_POST['luu_sua'])) {
    $ma = intval($_POST['sua_ma']);
    $ten = $conn->real_escape_string(trim($_POST['sua_ten']));
    $gia = floatval($_POST['sua_gia']);
    $soluong = intval($_POST['sua_soluong']);
    $loai = intval($_POST['sua_loai']);
    $tinhtrang = intval($_POST['sua_tinhtrang']);
    $anh_cu = $_POST['anh_cu'];
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
                  SET TenBanh='$ten', Gia=$gia, SoLuong=$soluong, MaLoaiBanh=$loai, TinhTrang=$tinhtrang, HinhAnh='$tenAnhMoi'
                  WHERE MaBanh=$ma";
    if ($conn->query($sqlUpdate)) {
        echo "<script>window.location='QuanLyThongTinBanh.php';</script>";
        exit;
    } else {
        $errMsg = "Lỗi khi cập nhật bánh: " . htmlspecialchars($conn->error);
    }
}

// ======== XỬ LÝ XÓA HOẶC HỎI KHÓA ========
if (isset($_GET['xoa'])) {
    $maBanh = intval($_GET['xoa']);
    $tenBanh = urldecode($_GET['ten'] ?? '');

    $sqlCheck = "SELECT COUNT(*) AS SoLanBan FROM ChiTietDonHang WHERE MaBanh = $maBanh";
    $res = $conn->query($sqlCheck);
    $daBan = false;
    if ($res) $daBan = $res->fetch_assoc()['SoLanBan'] > 0;

    echo "<style>
        #overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1050; animation: fadeIn .25s ease; }
        .popup { position: fixed; top:50%; left:50%; transform: translate(-50%,-50%) scale(1); background: #fff; border-radius:10px; padding:28px 30px; z-index:1055; box-shadow: 0 8px 30px rgba(0,0,0,0.25); text-align:center; animation: popupShow .25s ease; }
        .popup h5 { margin-bottom:12px; }
        .btn-popup { padding:8px 18px; border-radius:6px; }
        @keyframes fadeIn { from {opacity:0} to {opacity:1} }
        @keyframes popupShow { from { transform: translate(-50%,-50%) scale(.92); opacity:0 } to { transform: translate(-50%,-50%) scale(1); opacity:1 } }
    </style>";
    echo "<div id='overlay'></div>";

    if ($daBan) {
        $tenEsc = htmlspecialchars($tenBanh);
        echo "
        <div class='popup'>
            <h5>⚠️ Bánh \"{$tenEsc}\" đã từng được bán!</h5>
            <p>Bạn có muốn <b>ẩn (khóa)</b> bánh này không?</p>
            <div class='d-flex justify-content-center gap-2 mt-3'>
                <a href='QuanLyThongTinBanh.php?khoa={$maBanh}' class='btn btn-warning btn-popup'>Khóa</a>
                <a href='QuanLyThongTinBanh.php' class='btn btn-secondary btn-popup'>Hủy</a>
            </div>
        </div>";
    } else {
        if ($conn->query("DELETE FROM ThongTinBanh WHERE MaBanh = $maBanh")) {
            echo "<div class='popup' style='background:#198754;color:#fff;'>✅ Đã xóa bánh thành công!</div>";
            echo "<script>setTimeout(()=> window.location.href='QuanLyThongTinBanh.php', 1000);</script>";
        } else {
            echo "<div class='popup' style='background:#dc3545;color:#fff;'>❌ Lỗi khi xóa: ".htmlspecialchars($conn->error)."</div>";
        }
    }
    exit;
}

// ======== XỬ LÝ KHÓA ========
if (isset($_GET['khoa'])) {
    $maBanh = intval($_GET['khoa']);
    if ($conn->query("UPDATE ThongTinBanh SET TinhTrang = 0 WHERE MaBanh = $maBanh")) {
        echo "<div id='overlay'></div>";
        echo "<div class='popup' style='background:#ffc107;color:#000;'>🔒 Đã khóa bánh thành công!</div>";
        echo "<script>setTimeout(()=> window.location.href='QuanLyThongTinBanh.php', 1000);</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger mt-3'>⚠️ Lỗi khi khóa bánh: " . htmlspecialchars($conn->error) . "</div>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4 text-primary">QUẢN LÝ THÔNG TIN BÁNH</h2>

    <!-- Nút thêm bánh -->
    <div class="mb-3 text-end">
        <a href="themBanh.php" class="btn btn-success">+ Thêm bánh mới</a>
    </div>

    <?php if (!empty($errMsg)) echo "<div class='alert alert-danger'>$errMsg</div>"; ?>

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
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $ma = $row['MaBanh'];
                    $ten = htmlspecialchars($row['TenBanh']);
                    $loai = htmlspecialchars($row['TenLoaiBanh']);
                    $maLoai = $row['MaLoaiBanh'];
                    $gia = number_format($row['Gia'],0,',','.');
                    $soluong = $row['SoLuong'];
                    $hinhAnh = $row['HinhAnh'];
                    $tinhtrang = (int)$row['TinhTrang'];

                    $badge = $tinhtrang ? "<span class='badge bg-success text-dark px-3 py-2'>Mở</span>"
                                        : "<span class='badge bg-danger text-dark px-3 py-2'>Khóa</span>";
                    $hinhAnhPath = "../img/".$hinhAnh;

                    echo "<tr>
                        <td>$ma</td>
                        <td>$ten</td>
                        <td>$loai</td>
                        <td>$gia</td>
                        <td>$soluong</td>
                        <td>";
                    echo $hinhAnh ? "<img src='$hinhAnhPath' width='60' height='60' style='object-fit:cover;border-radius:8px;'>"
                                  : "<span class='text-muted fst-italic'>Không có ảnh</span>";
                    echo "</td>
                        <td>$badge</td>
                        <td>
                            <button class='btn btn-warning btn-sm btn-edit'
                                    data-id='$ma'
                                    data-ten='$ten'
                                    data-gia='{$row['Gia']}'
                                    data-soluong='$soluong'
                                    data-tinhtrang='$tinhtrang'
                                    data-loai='$maLoai'
                                    data-anh='$hinhAnh'>
                                <i class='fas fa-edit'></i> Sửa
                            </button>
                            <a href='?xoa=$ma&ten=".urlencode($ten)."' class='btn btn-danger btn-sm'>Xóa</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Chưa có bánh nào trong hệ thống.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Sửa Bánh -->
<div class="modal fade" id="modalSuaBanh" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-warning text-white rounded-top-4">
        <h5 class="modal-title fw-semibold">Sửa thông tin bánh</h5>
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
