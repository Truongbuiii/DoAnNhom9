<?php include '../include1/header.php'; ?>
<?php include '../include/sidebar.php'; ?>
<?php
$search = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $conn->real_escape_string($_GET['search']);
    $sql = "
        SELECT dh.MaDon, dh.NgayLap, dh.TongTien,
               kh.HoTen AS TenKH,
               nv.HoTen AS TenNV
        FROM DonHang dh
        LEFT JOIN KhachHang kh ON dh.MaKH = kh.MaKH
        LEFT JOIN NhanVien nv ON dh.MaNV = nv.MaNV
        WHERE dh.MaDon LIKE '%$search%' 
           OR kh.HoTen LIKE '%$search%' 
           OR nv.HoTen LIKE '%$search%'
        ORDER BY dh.NgayLap DESC
    ";
} else {
    $sql = "
        SELECT dh.MaDon, dh.NgayLap, dh.TongTien,
               kh.HoTen AS TenKH,
               nv.HoTen AS TenNV
        FROM DonHang dh
        LEFT JOIN KhachHang kh ON dh.MaKH = kh.MaKH
        LEFT JOIN NhanVien nv ON dh.MaNV = nv.MaNV
        ORDER BY dh.NgayLap DESC
    ";
}

$result = $conn->query($sql);
if ($result === false) {
    echo "<div class='alert alert-danger'>Lỗi SQL: " . htmlspecialchars($conn->error) . "</div>";
}
?>


<div id="content-wrapper" class="d-flex flex-column">
  <div id="content">

    <!-- Thanh topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
          <a class="nav-link dropdown-toggle" href="#" id="userDropdown">
            <span class="mr-2 d-none d-lg-inline text-gray-600 small">Quản trị viên</span>
            <img class="img-profile rounded-circle" src="../img/undraw_profile.svg">
          </a>
        </li>
      </ul>
    </nav>

    <!-- Nội dung chính -->
    <div class="container-fluid">

      <!-- Tiêu đề -->
      <h1 class="h3 mb-2 text-gray-800">Quản lý đơn hàng</h1>

      <!-- Thanh tìm kiếm -->
<form method="GET" class="mb-3 d-flex align-items-center">
        <input type="text" name="search" class="form-control w-50" 
               placeholder="Tìm theo mã đơn hoặc mã khách hàng..." 
               value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-primary ml-2" type="submit"><i class="fas fa-search"></i> Tìm</button>
        <a href="QuanLyDonHang.php" class="btn btn-secondary ml-2">Làm mới</a>
      </form>

      <!-- Bảng dữ liệu -->
      <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
          <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6>
        </div>

        <div class="card-body">
          <div class="table-responsive">
  <table class="table table-bordered text-center table-hover">
  <thead class="bg-primary text-white">
    <tr>
      <th>Mã đơn</th>
      <th>Ngày lập</th>
      <th>Tổng tiền</th>
      <th>Khách hàng</th>
      <th>Nhân viên</th>
      <th>Hành động</th>
    </tr>
  </thead>
  <tbody>
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // dùng toán tử null-coalescing để tránh "undefined index"
            $maDon = htmlspecialchars($row['MaDon'] ?? '');
            $ngayLap = !empty($row['NgayLap']) ? date('d/m/Y', strtotime($row['NgayLap'])) : '';
            $tong = isset($row['TongTien']) ? number_format($row['TongTien'], 0, ',', '.') . ' ₫' : '';
            $tenKH = htmlspecialchars($row['TenKH'] ?? '—');
            $tenNV = htmlspecialchars($row['TenNV'] ?? '—');

            echo "<tr>
                    <td>{$maDon}</td>
                    <td>{$ngayLap}</td>
                    <td><b>{$tong}</b></td>
                    <td>{$tenKH}</td>
                    <td>{$tenNV}</td>
                    <td>
                      <button class='btn btn-info btn-sm btn-detail' data-id='{$maDon}'>
                        <i class='fas fa-eye'></i> Xem
                      </button>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='6'>Không có đơn hàng nào!</td></tr>";
    }
    ?>
  </tbody>
</table>

          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Modal xem chi tiết -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-body" id="order-detail-content">
        <p class="text-center text-muted">Đang tải dữ liệu...</p>
      </div>
    </div>
  </div>
</div>

<script>
// Xem chi tiết đơn hàng (AJAX)
document.querySelectorAll('.btn-detail').forEach(btn => {
  btn.addEventListener('click', () => {
    const maDon = btn.dataset.id;
    $('#detailModal').modal('show');
    document.getElementById('order-detail-content').innerHTML = '<p class="text-center text-muted">Đang tải...</p>';
    fetch('donhang_chitiet.php?MaDon=' + maDon)
      .then(res => res.text())
      .then(data => document.getElementById('order-detail-content').innerHTML = data)
      .catch(() => document.getElementById('order-detail-content').innerHTML = '<p class="text-danger text-center">Lỗi tải dữ liệu!</p>');
  });
});
</script>

<?php include '../include1/footer.php'; ?>
