<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (isset($_POST['MaNV'])) {
    $MaNV = $_POST['MaNV'];
    $HoTen = $_POST['HoTen'];
    $MatKhau = $_POST['MatKhau'];
    $PhanQuyen = $_POST['PhanQuyen'];

    $sql = "UPDATE nhanvien SET HoTen=?, MatKhau=?, PhanQuyen=? WHERE MaNV=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $HoTen, $MatKhau, $PhanQuyen, $MaNV);

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='QuanLyNhanVien.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật!'); window.history.back();</script>";
    }
}
?>
