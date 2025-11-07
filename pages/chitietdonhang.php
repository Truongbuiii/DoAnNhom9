<?php
include '../db/connect.php';
include '../include1/header.php';
include '../include1/sidebar.php';

// ============================
// KIỂM TRA MÃ ĐƠN HÀNG
// ============================
if (!isset($_GET['MaDon']) || empty($_GET['MaDon'])) {
    echo "<p style='color:red; padding:10px;'>⚠️ Không tìm thấy mã đơn hàng!</p>";
    exit;
}

$maDon = intval($_GET['MaDon']); // ép kiểu an toàn để tránh SQL injection

// ============================
// TRUY VẤN THÔNG TIN ĐƠN HÀNG
// ============================
$sqlDonHang = "
    SELECT d.MaDon, d.NgayLap, d.TongTien, 
           kh.HoTen AS TenKH, kh.SDT, 
           nv.HoTen AS TenNV
    FROM DonHang d
    JOIN KhachHang kh ON d.MaKH = kh.MaKH
    JOIN NhanVien nv ON d.MaNV = nv.MaNV
    WHERE d.MaDon = $maDon
";

$result = $conn->query($sqlDonHang);
$donHang = $result ? $result->fetch_assoc() : null;

if (!$donHang) {
    echo "<p style='color:red; padding:10px;'>⚠️ Không tìm thấy dữ liệu cho đơn hàng #$maDon.</p>";
    exit;
}

// ============================
// TRUY VẤN CHI TIẾT CÁC MẶT HÀNG
// ============================
$sqlChiTiet = "
    SELECT ct.MaBanh, b.TenBanh, ct.SoLuong, ct.DonGia, (ct.SoLuong * ct.DonGia) AS ThanhTien
    FROM ChiTietDonHang ct
    JOIN ThongTinBanh b ON ct.MaBanh = b.MaBanh
    WHERE ct.MaDon = $maDon
";
$chiTiet = $conn->query($sqlChiTiet);
?>

<div class="container" style="padding: 20px;">
    <h2>Chi tiết đơn hàng #<?= $donHang['MaDon'] ?></h2>
    <p><b>Ngày lập:</b> <?= $donHang['NgayLap'] ?></p>
    <p><b>Khách hàng:</b> <?= $donHang['TenKH'] ?> (<?= $donHang['SDT'] ?>)</p>
    <p><b>Nhân viên:</b> <?= $donHang['TenNV'] ?></p>
    <p><b>Tổng tiền:</b> <?= number_format($donHang['TongTien'], 0, ',', '.') ?> VND</p>

    <h4>Danh sách bánh trong đơn hàng</h4>
    <table border="1" cellspacing="0" cellpadding="8" width="100%">
        <tr style="background-color: #f2f2f2;">
            <th>Mã bánh</th>
            <th>Tên bánh</th>
            <th>Số lượng</th>
            <th>Đơn giá</th>
            <th>Thành tiền</th>
        </tr>
        <?php if ($chiTiet && $chiTiet->num_rows > 0): ?>
            <?php while ($row = $chiTiet->fetch_assoc()): ?>
            <tr>
                <td><?= $row['MaBanh'] ?></td>
                <td><?= $row['TenBanh'] ?></td>
                <td><?= $row['SoLuong'] ?></td>
                <td><?= number_format($row['DonGia'], 0, ',', '.') ?> VND</td>
                <td><?= number_format($row['ThanhTien'], 0, ',', '.') ?> VND</td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align:center; color:red;">Không có sản phẩm nào trong đơn hàng này!</td>
            </tr>
        <?php endif; ?>
    </table>

    <br>

<!-- Nút quay lại căn giữa -->
<div style="text-align: center; margin-top: 20px;">
    <button class="btn btn-secondary" onclick="history.back()">Quay lại</button>
</div>

<?php include '../include1/footer.php'; ?>


</div>
