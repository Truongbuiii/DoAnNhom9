<?php
include '../db/connect.php';


if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (isset($_GET['MaNV'])) {
    $MaNV = $_GET['MaNV'];
    $sql = "SELECT PhanQuyen FROM nhanvien WHERE MaNV = $MaNV";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    $newRole = ($row['PhanQuyen'] == 'Admin') ? 'NhanVien' : 'Admin';
    $update = "UPDATE nhanvien SET PhanQuyen = '$newRole' WHERE MaNV = $MaNV";
    if ($conn->query($update) === TRUE) {
        echo "<script>alert('Đã thay đổi quyền thành công!'); window.location='QuanLyNhanVienphp';</script>";
    } else {
        echo "Lỗi: " . $conn->error;
    }
}

$conn->close();
?>
