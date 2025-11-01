<?php include '../include1/header.php'; ?>
<?php include '../include/sidebar.php'; ?>
<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý tìm kiếm
$search = "";
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = $_GET['search'];
    $sql = "SELECT * FROM donhang 
            WHERE MaDon LIKE '%$search%' OR MaKH LIKE '%$search%'
            ORDER BY NgayLap DESC";
} else {
    $sql = "SELECT * FROM donhang ORDER BY NgayLap DESC";
}

$result = $conn->query($sql);
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
      <form method="GET" class="mb-3 d-flex justify-content-between">
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
                  <th>Mã KH</th>
                  <th>Mã NV</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['MaDon']}</td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['NgayLap'])) . "</td>";
                        echo "<td><b>" . number_format($row['TongTien'], 0, ',', '.') . " ₫</b></td>";
                        echo "<td>{$row['MaKH']}</td>";
                        echo "<td>{$row['MaNV']}</td>";
                        echo "<td>
                                <button class='btn btn-info btn-sm btn-detail' data-id='{$row['MaDon']}'>
                                  <i class='fas fa-eye'></i> Xem
                                </button>
                                <a href='donhang_delete.php?MaDon={$row['MaDon']}' 
                                   class='btn btn-danger btn-sm'
                                   onclick='return confirm(\"Bạn có chắc muốn xóa đơn hàng này không?\")'>
                                   <i class='fas fa-trash'></i> Xóa
                                </a>
                              </td>";
                        echo "</tr>";
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
