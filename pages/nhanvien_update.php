<?php
include '../db/connect.php';

if (isset($_POST['MaNV'])) {
    $MaNV = $_POST['MaNV'];
    $TenDangNhap = $_POST['TenDangNhap'];
    $HoTen = $_POST['HoTen'];
    $MatKhau = $_POST['MatKhau'];
    $TinhTrang = $_POST['tinhtrang']; // 🟢 đúng tên field trong form
    $PhanQuyen = $_POST['PhanQuyen'];

    // ✅ Kiểm tra mật khẩu hợp lệ (6 chữ số)
    if (!preg_match('/^\d{6}$/', $MatKhau)) {
        echo "<script>alert('Mật khẩu phải gồm đúng 6 chữ số!'); window.history.back();</script>";
        exit;
    }

    // ✅ Kiểm tra trùng tên đăng nhập (trừ nhân viên hiện tại)
    $checkUser = $conn->prepare("SELECT MaNV FROM nhanvien WHERE TenDangNhap=? AND MaNV<>?");
    $checkUser->bind_param("si", $TenDangNhap, $MaNV);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo "<script>alert('Tên đăng nhập đã tồn tại!'); window.history.back();</script>";
        exit;
    }

    // ✅ Cập nhật thông tin nhân viên
    $sql = "UPDATE nhanvien 
            SET HoTen=?, MatKhau=?, PhanQuyen=?, TinhTrang=? 
            WHERE MaNV=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $HoTen, $MatKhau, $PhanQuyen, $TinhTrang, $MaNV);

    if ($stmt->execute()) {
        if ($TinhTrang == 1) {
            echo "<script>alert('Cập nhật thông tin nhân viên thành công!'); 
                  window.location.href='QuanLyNhanVien.php';</script>";
        } else {
            echo "<script>alert('Cập nhật thông tin nhân viên thành công!'); 
                  window.location.href='QuanLyNhanVien.php';</script>";
        }
    } else {
        echo "<script>alert('Lỗi khi cập nhật!'); window.history.back();</script>";
    }
}
?>
