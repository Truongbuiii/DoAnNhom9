<?php
// QuanLyLoaiBanh.php
include '../include1/header.php';
include '../include1/sidebar.php';

// NOTE: giả sử $conn đã được tạo trong include '../db/connect.php' hoặc header.
// Nếu chưa, bạn cần include file kết nối DB trước khi dùng $conn.

// ======== XỬ LÝ THÊM LOẠI BÁNH ========
if (isset($_POST['them'])) {
    $tenLoai = $conn->real_escape_string(trim($_POST['tenLoai']));
    $sqlThem = "INSERT INTO LoaiBanh (TenLoaiBanh, TinhTrang) VALUES ('$tenLoai', 1)";
    if ($conn->query($sqlThem)) {
        echo "<script>window.location='QuanLyLoaiBanh.php';</script>";
        exit;
    } else {
        $errMsg = "Lỗi khi thêm: " . htmlspecialchars($conn->error);
    }
}

// ======== XỬ LÝ LƯU SỬA ========
if (isset($_POST['luu_sua'])) {
    $ma = intval($_POST['sua_ma']);
    $ten = $conn->real_escape_string(trim($_POST['sua_ten']));
    $tinhtrang = intval($_POST['sua_tinhtrang']);

    $sql = "UPDATE LoaiBanh SET TenLoaiBanh='$ten', TinhTrang=$tinhtrang WHERE MaLoaiBanh=$ma";
    if ($conn->query($sql)) {
        echo "<script>window.location='QuanLyLoaiBanh.php';</script>";
        exit;
    } else {
        $errMsg = "Lỗi khi cập nhật: " . htmlspecialchars($conn->error);
    }
}

// ======== XỬ LÝ XÓA HOẶC HIỂN THỊ HỘP KHÓA ========
if (isset($_GET['xoa'])) {
    $ma = intval($_GET['xoa']);
    $ten = urldecode($_GET['ten'] ?? '');

    // Kiểm tra có sản phẩm thuộc loại này không
    $sqlCheckSP = "SELECT COUNT(*) AS TongSP FROM ThongTinBanh WHERE MaLoaiBanh = $ma";
    $resSP = $conn->query($sqlCheckSP);
    $tongSP = 0;
    if ($resSP) {
        $tongSP = (int)$resSP->fetch_assoc()['TongSP'];
    }

    // Hiển thị overlay + popup (khóa hoặc xóa)
    echo "<style>
        /* overlay + popup tạm thời (chỉ khi cần) */
        #overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1050; animation: fadeIn .25s ease; }
        .popup { position: fixed; top:50%; left:50%; transform: translate(-50%,-50%) scale(1); background: #fff; border-radius:10px; padding:28px 30px; z-index:1055; box-shadow: 0 8px 30px rgba(0,0,0,0.25); text-align:center; animation: popupShow .25s ease; }
        .popup h5 { margin-bottom:12px; }
        .btn-popup { padding:8px 18px; border-radius:6px; }
        @keyframes fadeIn { from {opacity:0} to {opacity:1} }
        @keyframes popupShow { from { transform: translate(-50%,-50%) scale(.92); opacity:0 } to { transform: translate(-50%,-50%) scale(1); opacity:1 } }
    </style>";

    echo "<div id='overlay'></div>";

    if ($tongSP > 0) {
        // Có sản phẩm -> hỏi khóa
        $tenEsc = htmlspecialchars($ten);
        echo "
        <div class='popup'>
            <h5>⚠️ Loại bánh \"{$tenEsc}\" hiện đang có sản phẩm!</h5>
            <p>Bạn có muốn <b>ẩn (khóa)</b> loại bánh này không?</p>
            <div class='d-flex justify-content-center gap-2 mt-3'>
                <a href='QuanLyLoaiBanh.php?khoa={$ma}' class='btn btn-warning btn-popup'>Khóa</a>
                <a href='QuanLyLoaiBanh.php' class='btn btn-secondary btn-popup'>Hủy</a>
            </div>
        </div>";
    } else {
        // Không có sản phẩm -> xóa luôn
        if ($conn->query("DELETE FROM LoaiBanh WHERE MaLoaiBanh = $ma")) {
            echo "
            <div id='overlay'></div>
            <div class='popup' style='background:#198754;color:#fff;'>
                ✅ Đã xóa loại bánh thành công!
            </div>
            <script>setTimeout(()=> window.location.href='QuanLyLoaiBanh.php', 1000);</script>";
        } else {
            echo "
            <div id='overlay'></div>
            <div class='popup' style='background:#dc3545;color:#fff;'>
                ❌ Lỗi khi xóa: " . htmlspecialchars($conn->error) . "
            </div>";
        }
    }
    // Dừng tiếp tục render phần dưới để tránh hiển thị giao diện gốc chồng lên popup
    exit;
}

// ======== XỬ LÝ KHÓA ========
if (isset($_GET['khoa'])) {
    $ma = intval($_GET['khoa']);
    if ($conn->query("UPDATE LoaiBanh SET TinhTrang = 0 WHERE MaLoaiBanh = $ma")) {
        echo "<style>#overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1050; }</style>";
        echo "<div id='overlay'></div>";
        echo "<div style='position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1055; background:#ffc107; padding:20px 26px; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.25);'>🔒 Đã khóa loại bánh thành công!</div>";
        echo "<script>setTimeout(()=> window.location.href='QuanLyLoaiBanh.php', 1000);</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger mt-3'>⚠️ Lỗi khi khóa loại bánh: " . htmlspecialchars($conn->error) . "</div>";
    }
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">
    <h2 class="text-center mb-4 text-primary">Quản lý loại bánh</h2>

    <!-- Hiển thị lỗi (nếu có) -->
    <?php if (!empty($errMsg)) : ?>
        <div class="alert alert-danger"><?php echo $errMsg; ?></div>
    <?php endif; ?>

    <!-- Form thêm loại bánh -->
    <div class="card mb-4 shadow-sm p-4">
        <h5 class="mb-3 text-primary">Thêm loại bánh</h5>
        <form method="POST" action="">
            <div class="row mb-3 align-items-end">
                <div class="col-md-8">
                    <label for="tenLoai" class="form-label">Tên loại bánh</label>
                    <input type="text" class="form-control" id="tenLoai" name="tenLoai" placeholder="Nhập tên loại bánh..." required>
                </div>
                <div class="col-md-4 d-flex justify-content-end">
                    <button type="submit" name="them" class="btn btn-success mt-2">Thêm</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Danh sách loại bánh -->
    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3 text-primary">Danh sách loại bánh</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Mã loại bánh</th>
                    <th>Tên loại bánh</th>
                    <th>Tình trạng</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM LoaiBanh ORDER BY MaLoaiBanh ASC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($loai = $result->fetch_assoc()) {
                        $ma = (int)$loai['MaLoaiBanh'];
                        $ten = htmlspecialchars($loai['TenLoaiBanh']);
                        $tinhtrang = (int)$loai['TinhTrang'];

                        $badge = $tinhtrang == 1
                            ? "<span class='badge bg-success text-dark px-3 py-2'>Mở</span>"
                            : "<span class='badge bg-danger text-dark px-3 py-2'>Khóa</span>";

                        // Nút sửa sẽ dùng data-* để mở modal
                        echo "
                        <tr>
                            <td>{$ma}</td>
                            <td>{$ten}</td>
                            <td>{$badge}</td>
                            <td>
                                <button class='btn btn-warning btn-sm btn-edit me-2'
                                        data-id='{$ma}'
                                        data-ten=\"" . htmlspecialchars($loai['TenLoaiBanh'], ENT_QUOTES) . "\"
                                        data-tinhtrang='{$tinhtrang}'>
                                    <i class='fas fa-edit'></i> Sửa
                                </button>
                                <a href='QuanLyLoaiBanh.php?xoa={$ma}&ten=" . urlencode($loai['TenLoaiBanh']) . "' 
                                   class='btn btn-danger btn-sm'
                                   onclick='return confirm(\"⚠️ Bạn có chắc chắn muốn xóa loại bánh " . addslashes($loai['TenLoaiBanh']) . " không?\")'>
                                   🗑️ Xóa
                                </a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo '<tr><td colspan="4">Chưa có loại bánh nào.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Sửa loại bánh -->
<div class="modal fade" id="modalSuaLoaiBanh" tabindex="-1" aria-labelledby="modalSuaLoaiBanhLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header bg-warning text-white rounded-top-4">
        <h5 class="modal-title fw-semibold" id="modalSuaLoaiBanhLabel">Sửa loại bánh</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <form method="POST" action="">
        <div class="modal-body p-4">
          <input type="hidden" id="sua_ma" name="sua_ma">

          <div class="mb-3">
            <label for="sua_ten" class="form-label fw-semibold">Tên loại bánh</label>
            <input type="text" class="form-control rounded-3 shadow-sm" id="sua_ten" name="sua_ten" required>
          </div>

          <div class="mb-3">
            <label for="sua_tinhtrang" class="form-label fw-semibold">Tình trạng</label>
            <select id="sua_tinhtrang" name="sua_tinhtrang" class="form-select rounded-3 shadow-sm">
              <option value="1">Mở</option>
              <option value="0">Khóa</option>
            </select>
          </div>
        </div>

        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" name="luu_sua" class="btn btn-success rounded-3 px-4 fw-semibold">
            Lưu thay đổi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Styles nhỏ cho form -->
<style>
.form-select, .form-control {
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

<!-- Script: mở modal sửa và gán dữ liệu -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalSuaLoaiBanh');
    const modal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            const ten = btn.dataset.ten;
            const tinhtrang = btn.dataset.tinhtrang;

            document.getElementById('sua_ma').value = id;
            document.getElementById('sua_ten').value = ten;
            document.getElementById('sua_tinhtrang').value = tinhtrang;

            modal.show();
        });
    });
});
</script>

<?php include '../include1/footer.php'; ?>
