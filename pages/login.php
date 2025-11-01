<?php
session_start();

// Cấu hình kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nhom9"; // thay bằng tên database của bạn

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

// Kiểm tra kết nối
if ($conn->connect_error) {
  die("Kết nối thất bại: " . $conn->connect_error);
}

// Khi người dùng nhấn nút Đăng nhập
if (isset($_POST['login'])) {
  $user = trim($_POST['username']);
  $pass = trim($_POST['password']);

  // Kiểm tra thông tin đăng nhập trong bảng users
  $sql = "SELECT * FROM users WHERE username=? AND password=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $user, $pass);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Lưu session
    $_SESSION['username'] = $row['username'];
    $_SESSION['role'] = $row['role']; // role = 'admin' hoặc 'nhanvien'

    // Chuyển hướng đến trang index
    header("Location: index.php");
    exit;
  } else {
    echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng!');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập hệ thống</title>

  <!-- Font & CSS -->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700" rel="stylesheet">
  <link href="css/sb-admin-2.min.css" rel="stylesheet">

  <style>
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #4e73df, #224abe);
      font-family: 'Nunito', sans-serif;
    }

    .login-container {
      background: white;
      width: 400px;
      padding: 40px 30px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 0, 0, 0.2);
      text-align: center;
    }

    .login-container h1 {
      font-size: 1.5rem;
      margin-bottom: 30px;
      color: #333;
    }

    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .form-control-user {
      border-radius: 50px;
      padding: 12px 20px;
      font-size: 14px;
      width: 100%;
      box-sizing: border-box;
    }

    .btn-user {
      border-radius: 50px;
      padding: 12px 20px;
      background-color: #4e73df;
      border: none;
      font-size: 15px;
      width: 100%;
      color: white;
    }

    .btn-user:hover {
      background-color: #375ac0;
    }
  </style>
</head>

<body>

  <div class="login-container">
    <h1>Đăng nhập hệ thống</h1>
    <form method="post" action="login.php">
      <div class="form-group">
        <input type="text" name="username" class="form-control form-control-user" placeholder="Tên đăng nhập" required>
      </div>
      <div class="form-group">
        <input type="password" name="password" class="form-control form-control-user" placeholder="Mật khẩu" required>
      </div>
      <div class="form-group">
        <button type="submit" name="login" class="btn btn-primary btn-user">Đăng nhập</button>
      </div>
    </form>
  </div>

  <!-- JS -->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

</body>
</html>
