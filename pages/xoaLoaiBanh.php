<?php
include '../db/connect.php';

if (!isset($_GET['id'])) {
    header("Location: QuanLyLoaiBanh.php");
    exit;
}
$id = (int)$_GET['id'];

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Xóa các bánh thuộc loại này
    $stmt = $conn->prepare("DELETE FROM ThongTinBanh WHERE MaLoaiBanh = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Xóa loại
    $stmt2 = $conn->prepare("DELETE FROM LoaiBanh WHERE MaLoaiBanh = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();

    // Commit
    $conn->commit();

    echo "<script>
            alert('Đã xóa loại bánh và các bánh thuộc loại đó thành công!');
            window.location.href = 'QuanLyLoaiBanh.php';
          </script>";
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>
            alert('Lỗi khi xóa: " . addslashes($conn->error) . "');
            window.location.href = 'QuanLyLoaiBanh.php';
          </script>";
    exit;
}
?>
