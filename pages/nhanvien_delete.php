<?php
include '../db/connect.php';

// Kiểm tra tham số MaNV
if (!isset($_GET['MaNV'])) {
    echo "<script>
            alert('Không tìm thấy mã nhân viên!');
            window.location.href='QuanLyNhanVien.php';
          </script>";
    exit;
}

$maNV = intval($_GET['MaNV']);

// Nếu có hành động KHÓA
if (isset($_GET['action']) && $_GET['action'] === 'khoa') {
    $sqlKhoa = "UPDATE NhanVien SET TinhTrang = 0 WHERE MaNV = ?";
    $stmtKhoa = $conn->prepare($sqlKhoa);
    $stmtKhoa->bind_param("i", $maNV);

    if ($stmtKhoa->execute()) {
        echo "<script>
                alert('Đã KHÓA nhân viên thành công!');
                window.location.href='QuanLyNhanVien.php';
              </script>";
    } else {
        echo "<script>
                alert('Lỗi khi KHÓA nhân viên!');
                window.location.href='QuanLyNhanVien.php';
              </script>";
    }
    exit;
}

// Ngược lại, kiểm tra nhân viên có đơn hàng không
$sqlCheck = "SELECT COUNT(*) AS SoLuong FROM DonHang WHERE MaNV = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $maNV);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$row = $resultCheck->fetch_assoc();
$coDonHang = ($row && $row['SoLuong'] > 0);

// Nếu có đơn hàng → hỏi người dùng có muốn khóa thay vì xóa
if ($coDonHang) {
    echo "<script>
        if (confirm('Nhân viên này đã có thực hiện đơn hàng. Bạn có muốn KHÓA nhân viên này thay vì xóa không?')) {
            window.location.href = 'nhanvien_delete.php?action=khoa&MaNV={$maNV}';
        } else {
            window.location.href = 'QuanLyNhanVien.php';
        }
    </script>";
    exit;
}

// Nếu KHÔNG có đơn hàng → cho phép xóa hoàn toàn
$sqlXoa = "DELETE FROM NhanVien WHERE MaNV = ?";
$stmtXoa = $conn->prepare($sqlXoa);
$stmtXoa->bind_param("i", $maNV);

if ($stmtXoa->execute()) {
    echo "<script>
            alert('Đã xóa nhân viên thành công!');
            window.location.href='QuanLyNhanVien.php';
          </script>";
} else {
    echo "<script>
            alert('Lỗi khi xóa nhân viên!');
            window.location.href='QuanLyNhanVien.php';
          </script>";
}
?>
