<?php
// ==========================
// Kết nối cơ sở dữ liệu
// ==========================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

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
// Truy vấn chi tiết đơn hàng
// ==========================
$sql = "SELECT c.*, b.TenBanh 
        FROM chitietdonhang c 
        JOIN thongtinbanh b ON c.MaBanh = b.MaBanh 
        WHERE c.MaDon = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

$stmt->bind_param("i", $maDon);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- ========================== -->
<!-- GIAO DIỆN CHI TIẾT ĐƠN HÀNG -->
<!-- ========================== -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết đơn hàng #<?= htmlspecialchars($maDon) ?></h5>
            <a href="QuanLyDonHang.php" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>

        <div class="card-body">
            <table class="table table-bordered text-center">
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
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
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
                    echo "<tr><td colspan='5'>Không có dữ liệu chi tiết đơn hàng.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
