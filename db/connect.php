<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. ĐỊNH NGHĨA ĐƯỜNG DẪN GỐC

define('BASE_APP_PATH', ''); // Sửa thành chuỗi rỗng
$servername = "localhost";
$username = "root";
$password = "";
$database = "nhom9"; 

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>