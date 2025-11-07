<?php
session_start();

// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Kiểm tra lỗi kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Khi người dùng nhấn nút đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenDangNhap = trim($_POST['username']);
    $MatKhau = trim($_POST['password']);

    if (empty($TenDangNhap) || empty($MatKhau)) {
        echo "<script>alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!');</script>";
    } else {
        // Kiểm tra tài khoản
        $sql = "SELECT * FROM nhanvien WHERE TenDangNhap = ? AND MatKhau = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $TenDangNhap, $MatKhau);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // 🔒 Lưu thông tin đăng nhập vào SESSION
                $_SESSION['MaNV'] = $user['MaNV'];
                $_SESSION['HoTen'] = $user['HoTen'];
                $_SESSION['PhanQuyen'] = $user['PhanQuyen'];
                $_SESSION['username'] = $user['TenDangNhap']; // ✅ thêm dòng này

                header("Location: ../index.php");
                exit;
            } else {
                echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Lỗi truy vấn SQL!');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng nhập hệ thống</title>
    <link href="/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700" rel="stylesheet">
    <link href="/css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .bg-login-image {
            background: url('img/login-bg.jpg');
            background-position: center;
            background-size: cover;
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
                                            <input type="text" class="form-control form-control-user"
                                                name="username" placeholder="Tên đăng nhập" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                name="password" placeholder="Mật khẩu" required>
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

    <script src="/vendor/jquery/jquery.min.js"></script>
    <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="/js/sb-admin-2.min.js"></script>
</body>
</html>
