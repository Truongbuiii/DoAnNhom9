<?php
session_start();
include '../db/connect.php'; 
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ✅ Câu truy vấn đúng với bảng của bạn
    $sql = "SELECT * FROM nhanvien WHERE TenDangNhap=? AND MatKhau=?";
    $stmt = $conn->prepare($sql);

    // Nếu prepare lỗi, in thông báo
    if (!$stmt) {
        die("❌ Lỗi prepare(): " . $conn->error);
    }

    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ⚠️ Kiểm tra nếu tài khoản bị khóa
        if ($row['TinhTrang'] == 0) {
            echo "<script>alert('Tài khoản của bạn đã bị khóa!'); window.location='login.php';</script>";
            exit;
        }

        // ✅ Nếu hoạt động, lưu session
        $_SESSION['MaNV'] = $row['MaNV'];
        $_SESSION['HoTen'] = $row['HoTen'];
        $_SESSION['PhanQuyen'] = $row['PhanQuyen'];

        header("Location: ../index.php");
        exit;
    } else {
        echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!'); window.location='login.php';</script>";
    }
}
?>
