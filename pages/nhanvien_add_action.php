<?php
 include '../db/connect.php'; 

$TenDangNhap = trim($_POST['TenDangNhap']);
$HoTen = trim($_POST['HoTen']);
$MatKhau = trim($_POST['MatKhau']);
$PhanQuyen = $_POST['PhanQuyen'];

// ✅ Kiểm tra mật khẩu có đúng 6 chữ số
if (!preg_match('/^\d{6}$/', $MatKhau)) {
    echo "<script>alert('❌ Mật khẩu phải gồm đúng 6 chữ số!'); history.back();</script>";
    exit;
}

// ✅ Kiểm tra trùng tên đăng nhập
$sql_check = "SELECT * FROM nhanvien WHERE TenDangNhap = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("s", $TenDangNhap);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<script>alert('⚠️ Tên đăng nhập này đã tồn tại, vui lòng chọn tên khác!'); history.back();</script>";
    exit;
}

// ✅ Nếu hợp lệ -> thêm vào CSDL
$sql = "INSERT INTO nhanvien (TenDangNhap, HoTen, MatKhau, PhanQuyen) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $TenDangNhap, $HoTen, $MatKhau, $PhanQuyen);

if ($stmt->execute()) {
    echo "<script>alert('✅ Thêm nhân viên thành công!'); window.location='QuanLyNhanVien.php';</script>";
} else {
    echo "<script>alert('❌ Lỗi khi thêm nhân viên!'); history.back();</script>";
}

$stmt->close();
$conn->close();
?>
