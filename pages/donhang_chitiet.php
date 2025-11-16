<?php
include '../db/connect.php';

// ==========================
// (Logic PHP của bạn, giữ nguyên)
// ==========================
if (!isset($_GET['MaDon'])) {
    echo "<div class='container mt-4'><div class='alert alert-danger text-center'>Không tìm thấy mã đơn hàng.</div></div>";
    exit;
}
$maDon = $_GET['MaDon'];
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
    echo "<div class='container mt-4'><div class='alert alert-danger text-center'>Không tìm thấy đơn hàng.</div></div>";
    exit;
}
$order = $order_result->fetch_assoc();
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

<style>
    .invoice-box {
        padding: 25px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 15px;
        line-height: 1.6;
        color: #333;
    }
    .invoice-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }
    .invoice-header div {
        font-size: 0.95rem;
    }
    .invoice-header strong {
        display: block;
        color: #555;
        font-size: 0.85rem;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    .invoice-table {
        width: 100%;
        border-collapse: collapse;
    }
    .invoice-table thead th {
        border-bottom: 2px solid #333; /* Đường kẻ đậm cho header */
        padding: 10px 5px;
        text-align: left;
        background: none;
        color: #333;
    }
    .invoice-table tbody td {
        border-bottom: 1px solid #eee; /* Đường kẻ mờ cho các hàng */
        padding: 12px 5px;
    }
    /* Căn phải cho các cột số */
    .invoice-table thead th:nth-child(3),
    .invoice-table tbody td:nth-child(3),
    .invoice-table thead th:nth-child(4),
    .invoice-table tbody td:nth-child(4),
    .invoice-table thead th:nth-child(5),
    .invoice-table tbody td:nth-child(5) {
        text-align: right;
    }
    .invoice-total {
        margin-top: 25px;
        text-align: right;
    }
    .invoice-total strong {
        font-size: 1.25rem;
        color: #256176; /* Màu xanh chủ đạo */
    }
</style>

<div>
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Chi tiết đơn hàng #<?= htmlspecialchars($order['MaDon']) ?></h5>
 <a href="QuanLyDonHang.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>       
    </div>

    <div class="card-body invoice-box">
        
        <div class="invoice-header">
            <div>
                <strong>Khách hàng:</strong>
                <?= htmlspecialchars($order['TenKH']) ?>
            </div>
            <div>
                <strong>Nhân viên:</strong>
                <?= htmlspecialchars($order['TenNV']) ?>
            </div>
            <div style="text-align: right;">
                <strong>Ngày lập:</strong>
                <?= htmlspecialchars($order['NgayLap']) ?>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
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
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Không có sản phẩm.</td></tr>";
            }
            ?>
            </tbody>
        </table>
        
        <div class="invoice-total">
            <strong style="display:inline-block; margin-right: 20px;">Tổng cộng:</strong>
            <strong><?= number_format($tong, 0, ',', '.') ?> đ</strong>
        </div>

    </div>
</div>

<?php
$order_stmt->close();
$detail_stmt->close();
$conn->close();
?>