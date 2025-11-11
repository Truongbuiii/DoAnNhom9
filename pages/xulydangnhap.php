<?php
session_start();
include '../db/connect.php'; 
$conn->set_charset("utf8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)) {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin'); window.location='login.php';</script>";
        exit;
    }

    $sql = "SELECT * FROM nhanvien WHERE TenDangNhap=?";
    $stmt = $conn->prepare($sql);

    if(!$stmt) die("Lỗi prepare(): " . $conn->error);

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();

        // Kiểm tra tài khoản bị khóa
        if($row['TinhTrang'] == 0){
            echo "<script>alert('Tài khoản của bạn đã bị khóa!'); window.location='login.php';</script>";
            exit;
        }

        // Kiểm tra mật khẩu (nếu chưa hash)
        if($password === $row['MatKhau']){
            // Lưu session
            $_SESSION['MaNV'] = $row['MaNV'];
            $_SESSION['HoTen'] = $row['HoTen'];
            $_SESSION['PhanQuyen'] = $row['PhanQuyen'];
            $_SESSION['username'] = $row['TenDangNhap']; // ✅ Thêm session username

            header("Location: ../index.php");
            exit;
        } else {
            echo "<script>alert('Sai mật khẩu!'); window.location='login.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Tên đăng nhập không tồn tại!'); window.location='login.php';</script>";
        exit;
    }
}
?>
