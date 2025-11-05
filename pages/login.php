
<?php
session_start();

<<<<<<< Updated upstream
include '../db/connect.php';
=======
// Kết nối CSDL
// Cấu hình kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9"; // thay bằng tên database của bạn
>>>>>>> Stashed changes


<<<<<<< Updated upstream
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenDangNhap = trim($_POST['username']);
    $MatKhau = trim($_POST['password']);
=======
// Nếu nhấn nút đăng nhập
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenDangNhap = trim($_POST['username']);
    $MatKhau = trim($_POST['password']);

    // Kiểm tra rỗng
    if (empty($TenDangNhap) || empty($MatKhau)) {
        echo "<script>alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!');</script>";
    } else {
        // Truy vấn kiểm tra tài khoản
        $sql = "SELECT * FROM nhanvien WHERE TenDangNhap = ? AND MatKhau = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $TenDangNhap, $MatKhau);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Lưu thông tin vào session
            $_SESSION['MaNV'] = $user['MaNV'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['PhanQuyen'] = $user['PhanQuyen'];

            // Phân quyền
            if ($user['PhanQuyen'] == 'Admin') {
                header("Location: index.php");
                exit;
            } else {
                header("Location: nhanvien_dashboard.php");
                exit;
            }
        } else {
            echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!');</script>";
        }
    }
// Kiểm tra kết nối
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}
>>>>>>> Stashed changes

    // Kiểm tra rỗng
    if (empty($TenDangNhap) || empty($MatKhau)) {
        echo "<script>alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!');</script>";
    } else {
        // Truy vấn kiểm tra tài khoản
        $sql = "SELECT * FROM nhanvien WHERE TenDangNhap = ? AND MatKhau = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $TenDangNhap, $MatKhau);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Lưu thông tin vào session
            $_SESSION['MaNV'] = $user['MaNV'];
            $_SESSION['HoTen'] = $user['HoTen'];
            $_SESSION['PhanQuyen'] = $user['PhanQuyen'];

            // Phân quyền
            if ($user['PhanQuyen'] == 'Admin') {
                header("Location: ../index.php");
                exit;
            } else {
                header("Location: nhanvien_dashboard.php");
                exit;
            }
        } else {
            echo "<script>alert('Sai tên đăng nhập hoặc mật khẩu!');</script>";
        }
    }
  }
?>

<!DOCTYPE html>
<html lang="vi">

<head>
<<<<<<< Updated upstream
   <meta charset="utf-8">
    <title>Đăng nhập hệ thống</title>
=======
    <meta charset="utf-8">
    <title>Đăng nhập hệ thống</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        .bg-login-image {
            background: url('img/login-bg.jpg');
            background-position: center;
            background-size: cover;
        }
    </style>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập hệ thống</title>
>>>>>>> Stashed changes

    <link href="/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
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
<<<<<<< Updated upstream
=======

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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
<body>
>>>>>>> Stashed changes

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