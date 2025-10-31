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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenDangNhap = $_POST['TenDangNhap'];
    $HoTen = $_POST['HoTen'];
    $MatKhau = $_POST['MatKhau'];
    $PhanQuyen = $_POST['PhanQuyen'];

    $sql = "INSERT INTO nhanvien (TenDangNhap, HoTen, MatKhau, PhanQuyen)
            VALUES ('$TenDangNhap', '$HoTen', '$MatKhau', '$PhanQuyen')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Thêm nhân viên thành công!'); window.location='QuanLyNhanVien.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi thêm nhân viên: " . $conn->error . "'); window.history.back();</script>";
    }
}
?>
