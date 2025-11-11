<?php 
 

// 2. Sửa đường dẫn sang thư mục 'include'
include '../include/header.php'; 
include '../include/sidebar.php'; 
?>
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

if (isset($_GET['xoa'])) {
    $maDon = (int)$_GET['xoa'];

    // Xóa chi tiết đơn hàng trước
    $conn->query("DELETE FROM ChiTietDonHang WHERE MaDon = $maDon");

    // Sau đó xóa đơn hàng chính
    if ($conn->query("DELETE FROM DonHang WHERE MaDon = $maDon")) {
        echo "<script>
            alert('Đã xóa đơn hàng #{$maDon} và toàn bộ chi tiết liên quan!');
            window.location='QuanLyDonHang.php';
        </script>";
    } else {
        echo "<script>
            alert('Lỗi khi xóa đơn hàng!');
            window.location='QuanLyDonHang.php';
        </script>";
    }

    exit;
}

?>


    <div class="container-fluid">

      <h1 class="h3 mb-2 text-gray-800">Quản lý đơn hàng</h1>

      <form method="GET" class="mb-3 d-flex align-items-center">
        <input type="text" name="search" class="form-control w-50" 
               placeholder="Tìm theo mã đơn, tên khách hàng, tên nhân viên,..." 
               value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-primary ml-2" type="submit"><i class="fas fa-search"></i> Tìm</button>
        <a href="QuanLyDonHang.php" class="btn btn-secondary ml-2">Làm mới</a>
      </form>

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
        <button class='btn btn-info btn-sm btn-detail' data-id='{$maDon}'>Xem</button>
<a href='?xoa={$maDon}' 
    class='btn btn-danger btn-sm btn-delete'
    data-id='{$maDon}'
    data-kh='{$tenKH}'
    data-tong='{$tong}'>Xóa</a>
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

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      
      <div class="modal-body" id="order-detail-content">
        <p class="text-center text-muted">Đang tải dữ liệu...</p>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Xác nhận xóa đơn hàng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body">
        <p id="deleteMessage" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Xóa</button>
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
    
    // Đường dẫn 'donhang_chitiet.php' này là tương đối
    // Nó sẽ tìm file tại 'pages/donhang_chitiet.php', điều này là đúng
    fetch('donhang_chitiet.php?MaDon=' + maDon)
      .then(res => res.text())
      .then(data => document.getElementById('order-detail-content').innerHTML = data)
      .catch(() => document.getElementById('order-detail-content').innerHTML = '<p class="text-danger text-center">Lỗi tải dữ liệu!</p>');
  });
});


// Xử lý xóa đơn hàng
let deleteId = null;

document.querySelectorAll('.btn-delete').forEach(btn => {
  // Thêm 'event' vào đây
  btn.addEventListener('click', (event) => {
    // Dòng quan trọng nhất: Ngăn thẻ <a> điều hướng ngay lập tức
    event.preventDefault(); 

    deleteId = btn.dataset.id;
    // Sửa lại cho đúng tên dataset bạn đã đặt (data-kh)
    const tenKH = btn.dataset.kh || 'Không rõ'; 
    // Lấy data-tong (bạn cần thêm nó vào HTML, xem bước 2)
const tongTien = btn.dataset.tong || '0 ₫';
    // Hiển thị nội dung xác nhận rõ ràng
    document.getElementById('deleteMessage').innerHTML =
      `<strong>Bạn có chắc chắn muốn xóa đơn hàng <span class="text-danger">#${deleteId}</span>?</strong><br>
       Khách hàng: <b>${tenKH}</b><br>
       Tổng tiền: <b>${tongTien}</b><br><br>
       <span class="text-danger fw-semibold">Hành động này không thể hoàn tác!</span>`;

    // Mở popup xác nhận (Code của bạn đã đúng)
    new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
  });
});

// Khi người dùng xác nhận xóa (Code này đã đúng, giữ nguyên)
document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
  if (deleteId) {
    // Chỉ điều hướng KHI người dùng bấm nút "Xóa" trong modal
    window.location.href = `QuanLyDonHang.php?xoa=${deleteId}`;
  }
});

</script>

<?php 
// 3. Sửa đường dẫn sang thư mục 'include'
include '../include/footer.php'; 
?>