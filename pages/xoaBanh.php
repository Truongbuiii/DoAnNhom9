<?php include 'include/config.php'; ?>

<?php
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM ThongTinBanh WHERE MaBanh = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Đã xóa bánh thành công!');
                window.location='QuanLyThongTinBanh.php';
              </script>";
    } else {
        echo "<script>
                alert('Lỗi khi xóa: " . $conn->error . "');
                window.location='QuanLyThongTinBanh.php';
              </script>";
    }
} else {
    header("Location: QuanLyThongTinBanh.php");
}
?>
