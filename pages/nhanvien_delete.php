<?php
include '../db/connect.php';

if (isset($_GET['MaNV'])) {
    $maNV = intval($_GET['MaNV']);

    // ✅ Kiểm tra nhân viên có trong bảng Đơn Hàng không
    $sqlCheck = "SELECT COUNT(*) AS SoLuong FROM DonHang WHERE MaNV = ?";
    $stmtCheck = $conn->prepare($sqlCheck);

    if (!$stmtCheck) {
        die("❌ Lỗi prepare SQL: " . $conn->error);
    }

    $stmtCheck->bind_param("i", $maNV);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $row = $resultCheck->fetch_assoc();
    $coDonHang = ($row && $row['SoLuong'] > 0);

    if ($coDonHang) {
        // ✅ Nếu có đơn hàng → chỉ khóa nhân viên
        $sqlKhoa = "UPDATE NhanVien SET TinhTrang = 0 WHERE MaNV = ?";
        $stmtKhoa = $conn->prepare($sqlKhoa);
        $stmtKhoa->bind_param("i", $maNV);

        if ($stmtKhoa->execute()) {
            echo "<script>
                    alert('⚠️ Nhân viên này đã có lịch sử đơn hàng nên chỉ bị KHÓA, không thể xóa!');
                    window.location.href='QuanLyNhanVien.php';
                  </script>";
        } else {
            echo "<script>
                    alert('❌ Lỗi khi khóa nhân viên!');
                    window.location.href='QuanLyNhanVien.php';
                  </script>";
        }
    } else {
        // ✅ Nếu chưa có đơn hàng → xóa hoàn toàn
        $sqlXoa = "DELETE FROM NhanVien WHERE MaNV = ?";
        $stmtXoa = $conn->prepare($sqlXoa);
        $stmtXoa->bind_param("i", $maNV);

        if ($stmtXoa->execute()) {
            echo "<script>
                    alert('🗑️ Đã xóa nhân viên thành công!');
                    window.location.href='QuanLyNhanVien.php';
                  </script>";
        } else {
            echo "<script>
                    alert('❌ Lỗi khi xóa nhân viên!');
                    window.location.href='QuanLyNhanVien.php';
                  </script>";
        }
    }
}
?>
