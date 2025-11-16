<?php
include '../db/connect.php';
include '../include/header.php'; 
include '../include/sidebar.php'; 

// ============================
// (TOÀN BỘ LOGIC PHP CỦA BẠN - GIỮ NGUYÊN)
// ============================
if (!isset($_GET['MaDon']) || empty($_GET['MaDon'])) {
    echo "<p style='color:red; padding:10px;'>⚠️ Không tìm thấy mã đơn hàng!</p>";
    exit;
}
$maDon = intval($_GET['MaDon']);
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
$sqlChiTiet = "
    SELECT ct.MaBanh, b.TenBanh, ct.SoLuong, ct.DonGia, (ct.SoLuong * ct.DonGia) AS ThanhTien
    FROM ChiTietDonHang ct
    JOIN ThongTinBanh b ON ct.MaBanh = b.MaBanh
    WHERE ct.MaDon = $maDon
";
$chiTiet = $conn->query($sqlChiTiet);
?>

<div class="container-fluid"> <h1 class="h3 mb-4 text-gray-800">Chi tiết đơn hàng #<?= $donHang['MaDon'] ?></h1>

    <div class="card shadow mb-4">
        
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin hóa đơn</h6>
          <button class="btn btn-secondary" onclick="history.back()">Quay lại</button>

        </div>
        
        <div class="card-body">
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <strong>Khách hàng:</strong><br>
                    <?= htmlspecialchars($donHang['TenKH']) ?><br>
                    SĐT: <?= htmlspecialchars($donHang['SDT']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Nhân viên:</strong><br>
                    <?= htmlspecialchars($donHang['TenNV']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Ngày lập:</strong><br>
                    <?= date('d/m/Y H:i:s', strtotime($donHang['NgayLap'])) ?>
                </div>
            </div>

            <hr>

            <h5 class="mb-3">Danh sách sản phẩm</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="bg-primary text-white"> <tr>
                            <th>Mã bánh</th>
                            <th>Tên bánh</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Đơn giá</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($chiTiet && $chiTiet->num_rows > 0): ?>
                            <?php while ($row = $chiTiet->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['MaBanh'] ?></td>
                                <td><?= $row['TenBanh'] ?></td>
                                <td class="text-center"><?= $row['SoLuong'] ?></td>
                                <td class="text-right"><?= number_format($row['DonGia'], 0, ',', '.') ?> đ</td>
                                <td class="text-right"><?= number_format($row['ThanhTien'], 0, ',', '.') ?> đ</td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Không có sản phẩm nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8f9fa;">
                            <td colspan="4" class="text-right"><strong>Tổng cộng:</strong></td>
                            <td class="text-right"><strong><?= number_format($donHang['TongTien'], 0, ',', '.') ?> đ</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div> </div> </div> </div> <?php 
include '../include/footer.php'; 
// Thẻ </div> thừa ở file gốc đã được xóa
?>