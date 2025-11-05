<?php
session_start();

include '../db/connect.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9"; // Đặt đúng tên database của bạn

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ====== XỬ LÝ ĐĂNG NHẬP ======
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenDangNhap = trim($_POST['username']);
    $MatKhau = trim($_POST['password']);

    if (empty($TenDangNhap) || empty($MatKhau)) {
        echo "<script>alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!');</script>";
    } else {
        // Câu lệnh SQL
        $sql = "SELECT * FROM nhanvien WHERE TenDangNhap = ? AND MatKhau = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die("Lỗi prepare: " . $conn->error);
        }

        $stmt->bind_param("ss", $TenDangNhap, $MatKhau);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Lưu session
            $_SESSION['MaNV'] = $user['MaNV'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['PhanQuyen'] = $user['PhanQuyen'];

            // Phân quyền chuyển trang
            if ($user['PhanQuyen'] == 'Admin') {
                header("Location: ../index.php");
            } else {
                header("Location: nhanvien_dashboard.php");
            }
            exit;
        } else {
            echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng nhập hệ thống</title>

    <!-- Font & CSS -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="../css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .bg-login-image {
            background: url('../img/login-bg.jpg') center/cover no-repeat;
        }
    </style>
</head>

<body class="bg-gradient-primary">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-xl-10 col-lg-12 col-md-9">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h4 text-gray-900">Đăng nhập hệ thống</h1>
                                </div>

                                <!-- Form đăng nhập -->
                                <form class="user" method="POST" action="">
                                    <div class="form-group">
                                        <input type="text" name="username" class="form-control form-control-user"
                                               placeholder="Tên đăng nhập" required>
                                    </div>
                                    <div class="form-group">
                                        <input type="password" name="password" class="form-control form-control-user"
                                               placeholder="Mật khẩu" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-user btn-block">
                                        Đăng nhập
                                    </button>
                                </form>

                                <hr>
                                <div class="text-center">
                                    <small class="text-muted">Hệ thống quản lý CakeShop</small>
                                </div>
                            </div>
                        </div>
                    </div> <!-- end row -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="../vendor/jquery/jquery.min.js"></script>
<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="../js/sb-admin-2.min.js"></script>

</body>
</html>
