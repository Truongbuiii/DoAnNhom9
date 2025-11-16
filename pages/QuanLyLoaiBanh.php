<?php
// QuanLyLoaiBanh.php
include '../include/header.php'; 
include '../include/sidebar.php'; 

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
// ✅ THÊM KHỐI MỚI NÀY ĐỂ HỎI LẦN 1
// ======== HỎI XÁC NHẬN XÓA (LẦN 1) ========
if (isset($_GET['kiemtraxoa'])) {
    $ma = intval($_GET['kiemtraxoa']);
    $ten = urldecode($_GET['ten'] ?? '');
    $tenEsc = htmlspecialchars($ten);

    echo "<div id='overlay'></div>";
    echo "
    <div class='popup'>
        <h5>Bạn có chắc chắn muốn xóa \"{$tenEsc}\"?</h5>        
        <div class='d-flex justify-content-center mt-3'>
            <a href='QuanLyLoaiBanh.php?xacnhanxoa={$ma}&ten=" . urlencode($ten) . "' class='btn btn-danger btn-popup'>Xóa</a>
            
            <a href='QuanLyLoaiBanh.php' class='btn btn-secondary btn-popup ms-3'>Hủy</a>
        </div>
    </div>";
}
// ======== XỬ LÝ XÓA HOẶC HIỂN THỊ HỘP KHÓA ========
if (isset($_GET['xacnhanxoa'])) {
    $ma = intval($_GET['xacnhanxoa']);
    $ten = urldecode($_GET['ten'] ?? '');
    // Kiểm tra có sản phẩm thuộc loại này không
    $sqlCheckSP = "SELECT COUNT(*) AS TongSP FROM ThongTinBanh WHERE MaLoaiBanh = $ma";
    $resSP = $conn->query($sqlCheckSP);
    $tongSP = 0;
    if ($resSP) {
        $tongSP = (int)$resSP->fetch_assoc()['TongSP'];
    }
    
    echo "<div id='overlay'></div>";
   if ($tongSP > 0) {
        $tenEsc = htmlspecialchars($ten);
        echo "
        <div class='popup'>
            <h5>Loại bánh \"{$tenEsc}\" hiện đang có sản phẩm!</h5>
            <p>Bạn có muốn <b>ẩn (khóa)</b> loại bánh này không?</p>
            
            <div class='d-flex justify-content-center mt-3'>
                <a href='QuanLyLoaiBanh.php?khoa={$ma}' class='btn btn-warning btn-popup'>Khóa</a>
                
                <a href='QuanLyLoaiBanh.php' class='btn btn-secondary btn-popup ms-2'>Hủy</a>
            </div>
        </div>";
    } else {
        if ($conn->query("DELETE FROM LoaiBanh WHERE MaLoaiBanh = $ma")) {
            echo "
            <div id='overlay'></div>
            <div class='popup' style='background:#198754;color:#fff;'>Đã xóa loại bánh thành công! </div>
            <script>setTimeout(()=> window.location.href='QuanLyLoaiBanh.php', 1000);</script>";
        } else {
            echo "
            <div id='overlay'></div>
            <div class='popup' style='background:#dc3545;color:#fff;'>Lỗi khi xóa: " . htmlspecialchars($conn->error) . " </div>";
        }
    }
}

// ======== XỬ LÝ KHÓA ========
if (isset($_GET['khoa'])) {
    $ma = intval($_GET['khoa']);
    if ($conn->query("UPDATE LoaiBanh SET TinhTrang = 0 WHERE MaLoaiBanh = $ma")) {
        echo "<style>#overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index:1050; }</style>";
        echo "<div id='overlay'></div>";
        echo "<div style='position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:1055; background:#ffc107; padding:20px 26px; border-radius:10px; box-shadow:0 8px 30px rgba(0,0,0,0.25);'>Đã khóa loại bánh thành công!</div>";
        echo "<script>setTimeout(()=> window.location.href='QuanLyLoaiBanh.php', 1000);</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger mt-3'>Lỗi khi khóa loại bánh: " . htmlspecialchars($conn->error) . "</div>";
    }
}
?>


<style>
/* 1. HIỆU ỨNG HOVER CHO NÚT (SHADOW RISE) */
.shadow-rise-btn {
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.shadow-rise-btn:hover {
    transform: translateY(-2px); /* Nhấc nút lên 2px */
    box-shadow: 0 4px 8px rgba(0,0,0,0.15); /* Thêm bóng mờ */
}

/* 2. HIỆU ỨNG HOVER CHO BẢNG */
.table-bordered tbody tr:hover {
    background-color: #f8f9fa; /* Màu xám siêu nhạt */
    cursor: default; 
    transition: background-color 0.2s ease-in-out;
}

/* 3. SỬA MÀU CHỮ TRÊN BADGE */
.badge.bg-success,
.badge.bg-danger {
    color: #fff !important; /* Luôn dùng chữ trắng trên nền xanh/đỏ */
}
/* Tăng kích thước badge một chút */
.badge.px-3.py-2 {
    font-size: 0.85rem;
    font-weight: 600;
}

/* 4. GIÃN CÁCH ICON TRONG NÚT */
.btn .fas {
    margin-right: 5px;
}

/* 5. CSS CHO FORM (ĐÃ GỘP) */

/* 5a. Form tìm kiếm (bên ngoài) */
form.d-flex.flex-wrap.gap-3 {
    justify-content: flex-start;
    gap: 1rem 1.5rem;
}

/* 5b. Hiệu ứng focus cho form bên ngoài (tìm kiếm) */
/* Dùng :not(.shadow-sm) để tránh xung đột với form trong modal */
.form-control:not(.shadow-sm):focus, 
.form-select:not(.shadow-sm):focus {
    border-color: #f0ad4e; /* Đổi màu focus cho hợp với nút Sửa */
    box-shadow: 0 0 5px rgba(240, 173, 78, 0.4);
}

/* 5c. Nâng cấp form có class .shadow-sm (cho cả modal và form 'Thêm') */
.form-control.shadow-sm,
.form-select.shadow-sm {
    border: 1px solid #ced4da;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04); /* Thêm bóng mờ nhẹ */
    transition: all 0.2s ease-in-out;
}

/* Hiệu ứng "nâng lên" khi focus form có .shadow-sm */
.form-control.shadow-sm:focus,
.form-select.shadow-sm:focus {
    border-color: #f0ad4e; /* Màu vàng giống nút Sửa */
    box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Bóng mờ to hơn */
    transform: translateY(-1px); /* Nâng lên một chút */
}
/* 6. CSS CHO POPUP XÓA */
#overlay { 
    position: fixed; top:0; left:0; width:100%; height:100%; 
    background: rgba(0,0,0,0.5); z-index:1050; animation: fadeIn .25s ease; 
}
.popup { 
    position: fixed; top:50%; left:50%; transform: translate(-50%,-50%) scale(1); 
    background: #fff; border-radius:10px; padding:28px 30px; z-index:1055; 
    box-shadow: 0 8px 30px rgba(0,0,0,0.25); text-align:center; animation: popupShow .25s ease; 
}
.popup h5 { margin-bottom:12px; }
.btn-popup { padding:8px 18px; border-radius:6px; }
@keyframes fadeIn { from {opacity:0} to {opacity:1} }
@keyframes popupShow { from { transform: translate(-50%,-50%) scale(.92); opacity:0 } to { transform: translate(-50%,-50%) scale(1); opacity:1 } }
</style>

<div class="container-fluid">
    <h2 class="text-center mb-4 text-primary">Quản lý loại bánh</h2>

    <!-- Hiển thị lỗi (nếu có) -->
    <?php if (!empty($errMsg)) : ?>
        <div class="alert alert-danger"><?php echo $errMsg; ?></div>
    <?php endif; ?>

 

<!-- TÌM KIẾM LOẠI BÁNH (trải rộng, không khung bao) -->
<form method="GET" action="" class="d-flex flex-wrap align-items-end gap-3 mb-4">
    <div style="min-width:250px;">
        <label for="tim" class="form-label mb-1 fw-bold">Tìm kiếm loại bánh</label>
        <input type="text" id="tim" name="tim" class="form-control" 
               placeholder="Nhập tên loại bánh..." 
               value="<?php echo htmlspecialchars($_GET['tim'] ?? '') ?>">
    </div>
    
    <div class="d-flex gap-2 align-items-end">
        <button type="submit" class="btn btn-primary shadow-rise-btn">
            <i class="fas fa-search"></i> Tìm
        </button>
        <?php if (!empty($_GET['tim'])): ?>
            <a href="QuanLyLoaiBanh.php" class="btn btn-secondary shadow-rise-btn">Xóa</a>
        <?php endif; ?>
    </div>
</form>


<!-- DANH SÁCH LOẠI BÁNH + FORM THÊM (nằm bên phải tiêu đề) -->
<div class="card shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h5 class="text-primary mb-2 mb-md-0">Danh sách loại bánh</h5>

 <form method="POST" action="" class="d-flex align-items-end flex-wrap">
    <div style="min-width:200px;">
        <label for="tenLoai" class="form-label fw-semibold mb-1">Tên loại bánh</label>
        <input type="text" 
               class="form-control rounded-3 shadow-sm" 
               id="tenLoai" 
               name="tenLoai" 
               placeholder="Nhập tên loại bánh..." 
               required>
    </div>
    
    <button type="submit" 
            name="them" 
            class="btn btn-success shadow-rise-btn rounded-3 ms-2">Thêm</button>
</form>
    </div>

    <!-- Bảng hiển thị loại bánh -->
    <table class="table table-bordered text-center align-middle table-sm">
        <thead class="table-primary">
            <tr>
                <th>Mã loại</th>
                <th>Tên loại bánh</th>
                <th>Tình trạng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tim = $conn->real_escape_string(trim($_GET['tim'] ?? ''));
            $sql = "SELECT * FROM LoaiBanh";
            if (!empty($tim)) {
                $sql .= " WHERE TenLoaiBanh LIKE '%$tim%'";
            }
            $sql .= " ORDER BY MaLoaiBanh ASC";

            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($loai = $result->fetch_assoc()) {
                    $ma = (int)$loai['MaLoaiBanh'];
                    $ten = htmlspecialchars($loai['TenLoaiBanh']);
                    $tinhtrang = (int)$loai['TinhTrang'];
                    $badge = $tinhtrang == 1
                        ? "<span class='badge bg-success text-dark px-3 py-2'>Mở</span>"
                        : "<span class='badge bg-danger text-dark px-3 py-2'>Khóa</span>";

                    echo "
                    <tr>
                        <td>{$ma}</td>
                        <td>{$ten}</td>
                        <td>{$badge}</td>
                        <td>
                            <button class='btn btn-warning btn-sm btn-edit me-2 shadow-rise-btn' 
                                    data-id='{$ma}' 
                                    data-ten=\"" . htmlspecialchars($loai['TenLoaiBanh'], ENT_QUOTES) . "\" 
                                    data-tinhtrang='{$tinhtrang}'>
                                <i class='fas fa-edit'></i> Sửa
                            </button>
                           <a href='QuanLyLoaiBanh.php?kiemtraxoa={$ma}&ten=" . urlencode($loai['TenLoaiBanh']) . "' 
   class='btn btn-danger btn-sm shadow-rise-btn'>
    <i class='fas fa-trash'></i> Xóa
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
                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4 shadow-rise-btn" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="luu_sua" class="btn btn-success rounded-3 px-4 fw-semibold shadow-rise-btn"> Lưu thay đổi </button>
                </div>
            </form>
        </div>
    </div>
</div>



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

<?php include '../include/footer.php'; 
 ?>