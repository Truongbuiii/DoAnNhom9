<?php
 include '../db/connect.php'; 

if (isset($_GET['MaNV'])) {
    $MaNV = $_GET['MaNV'];

    // Kiểm tra xem nhân viên có đơn hàng không
    $check = $conn->prepare("SELECT COUNT(*) FROM donhang WHERE MaNV = ?");
    $check->bind_param("i", $MaNV);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    $check->close();

    if ($count > 0) {
        echo "<script>alert('⚠️ Nhân viên này đang có đơn hàng, không thể xóa!'); window.location='QuanLyNhanVien.php';</script>";
        exit();
    }

    // Nếu không có đơn hàng thì xóa
    $sql = "DELETE FROM nhanvien WHERE MaNV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $MaNV);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Xóa nhân viên thành công!'); window.location='QuanLyNhanVien.php';</script>";
    } else {
        echo "<script>alert('❌ Lỗi khi xóa nhân viên!'); window.location='QuanLyNhanVien.php';</script>";
    }
}
$conn->close();
?>
