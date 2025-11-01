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

if (isset($_GET['MaDon'])) {
    $MaDon = $_GET['MaDon'];

    // Xóa chi tiết trước, rồi xóa đơn
    $conn->query("DELETE FROM chitietdonhang WHERE MaDon = '$MaDon'");
    $conn->query("DELETE FROM donhang WHERE MaDon = '$MaDon'");

    echo "<script>alert('Đã xóa đơn hàng thành công!'); window.location='QuanLyDonHang.php';</script>";
} else {
    echo "<script>alert('Không xác định được mã đơn!'); window.history.back();</script>";
}
?>
