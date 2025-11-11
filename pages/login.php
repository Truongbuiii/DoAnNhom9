<?php
session_start();
include '../db/connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $TenDangNhap = trim($_POST['username']);
    $MatKhau = trim($_POST['password']);

    if (empty($TenDangNhap) || empty($MatKhau)) {
        echo "<script>alert('Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!');</script>";
    } else {
        $sql = "SELECT * FROM nhanvien WHERE TenDangNhap = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) { die("Prepare failed: " . $conn->error); }
        
        $stmt->bind_param("s", $TenDangNhap);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Kiểm tra trạng thái tài khoản
            if ($user['TinhTrang'] == 0) {
                echo "<script>alert('Tài khoản đang bị khóa!'); window.location.href='login.php';</script>";
                exit;
            }

            // Kiểm tra mật khẩu
            if ($MatKhau === $user['MatKhau']) {
                $_SESSION['MaNV'] = $user['MaNV'];
                $_SESSION['HoTen'] = $user['HoTen'];
                $_SESSION['PhanQuyen'] = $user['PhanQuyen'];
                $_SESSION['username'] = $user['TenDangNhap'];

                header("Location: ../index.php");
                exit;
            } else {
                echo "<script>alert('Sai mật khẩu!'); window.location.href='login.php';</script>";
            }
        } else {
            echo "<script>alert('Tên đăng nhập không tồn tại!'); window.location.href='login.php';</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng nhập hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #4e73df;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login {
            width: 100%;
            max-width: 900px;
            border-radius: 1.2rem;
            padding: 3rem 2.5rem;
            background-color: #fff;
            box-shadow: 0 1rem 2rem rgba(0,0,0,0.3);
            transition: transform 0.2s;
        }
        .card-login:hover { transform: scale(1.02); }
        .card-login .text-center h1 { font-size: 2rem; font-weight: 700; color: #224abe; margin-bottom: 2rem; }
        .form-control-user { border-radius: 50px; padding: 1rem 1.5rem; font-size: 1rem; }
        .btn-user { border-radius: 50px; padding: 0.75rem; font-size: 1rem; font-weight: 600; }
        .form-wrapper { display: flex; flex-direction: column; gap: 1rem; }
        @media (max-width: 576px) {
            .card-login { width: 95%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="card card-login">
        <div class="form-wrapper">
            <div class="text-center">
                <h1>Đăng nhập vào hệ thống</h1>
            </div>
            <form method="POST" action="xulydangnhap.php">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-user" 
                           name="username" placeholder="Tên đăng nhập" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control form-control-user" 
                           name="password" placeholder="Mật khẩu" required>
                </div>
                <button type="submit" class="btn btn-primary btn-user w-100 mt-3 mb-3">
                    Đăng nhập
                </button>
            </form>
            <div class="text-center mt-2">
                <small>CakeShop Management System</small>
            </div>
        </div>
    </div>
</body>
</html>

