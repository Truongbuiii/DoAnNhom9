<?php
include '../db/connect.php';


// ==========================
// Kiểm tra mã đơn hàng
// ==========================
if (!isset($_GET['MaDon'])) {
    echo "<div class='container mt-4'>
            <div class='alert alert-danger text-center'>
                Không tìm thấy mã đơn hàng.
            </div>
          </div>";
    exit;
}

$maDon = $_GET['MaDon'];

// ==========================
// Truy vấn thông tin đơn hàng
// ==========================
$order_sql = "
SELECT d.MaDon, d.NgayLap, d.TongTien, k.HoTen AS TenKH, n.HoTen AS TenNV
FROM DonHang d
JOIN KhachHang k ON d.MaKH = k.MaKH
JOIN NhanVien n ON d.MaNV = n.MaNV
WHERE d.MaDon = ?
";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $maDon);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows == 0) {
    echo "<div class='container mt-4'>
            <div class='alert alert-danger text-center'>
                Không tìm thấy đơn hàng.
            </div>
          </div>";
    exit;
}

$order = $order_result->fetch_assoc();

// ==========================
// Truy vấn chi tiết sản phẩm
// ==========================
$detail_sql = "
SELECT c.MaBanh, b.TenBanh, c.SoLuong, c.DonGia, c.ThanhTien
FROM ChiTietDonHang c
JOIN ThongTinBanh b ON c.MaBanh = b.MaBanh
WHERE c.MaDon = ?
";
$detail_stmt = $conn->prepare($detail_sql);
$detail_stmt->bind_param("i", $maDon);
$detail_stmt->execute();
$detail_result = $detail_stmt->get_result();
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết đơn hàng #<?= htmlspecialchars($order['MaDon']) ?></h5>
            <a href="QuanLyDonHang.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="card-body">
            <!-- Thông tin đơn hàng -->
            <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['TenKH']) ?></p>
            <p><strong>Nhân viên:</strong> <?= htmlspecialchars($order['TenNV']) ?></p>
            <p><strong>Ngày lập:</strong> <?= $order['NgayLap'] ?></p>

            <!-- Bảng chi tiết sản phẩm -->
            <table class="table table-bordered text-center mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Mã bánh</th>
                        <th>Tên bánh</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $tong = 0;
                if ($detail_result->num_rows > 0) {
                    while ($row = $detail_result->fetch_assoc()) {
                        $tong += $row['ThanhTien'];
                        echo "<tr>
                                <td>{$row['MaBanh']}</td>
                                <td>{$row['TenBanh']}</td>
                                <td>{$row['SoLuong']}</td>
                                <td>" . number_format($row['DonGia'], 0, ',', '.') . " đ</td>
                                <td>" . number_format($row['ThanhTien'], 0, ',', '.') . " đ</td>
                              </tr>";
                    }
                    echo "<tr class='table-warning'>
                            <td colspan='4'><strong>Tổng cộng:</strong></td>
                            <td><strong>" . number_format($tong, 0, ',', '.') . " đ</strong></td>
                          </tr>";
                } else {
                    echo "<tr><td colspan='5'>Không có sản phẩm trong đơn hàng.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$order_stmt->close();
$detail_stmt->close();
$conn->close();
?>
