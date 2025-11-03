<?php include '../include1/header.php'; ?>
<?php include '../include1/sidebar.php'; ?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <h2 class="text-center mb-4 text-primary">Quản lý loại bánh</h2>

    <div class="card mb-4 shadow-sm p-4">
        <h5 class="mb-3 text-primary">Thêm loại bánh</h5>
        <form method="POST" action="">
            <div class="row mb-3 align-items-end">
                <div class="col-md-8">
                    <label for="tenLoai" class="form-label">Tên loại bánh</label>
                    <input type="text" class="form-control" id="tenLoai" name="tenLoai" placeholder="Nhập tên loại bánh..." required>
                </div>
                <div class="col-md- d-flex justify-content-end">
                    <button type="submit" name="them" class="btn btn-success mt-2">Thêm</button>
                </div>
            </div>
        </form>
    </div>

    <?php
    if (isset($_POST['them'])) {
        $tenLoai = $_POST['tenLoai'];
        $sqlThem = "INSERT INTO LoaiBanh (TenLoaiBanh) VALUES ('$tenLoai')";
        if ($conn->query($sqlThem) === TRUE) {
            echo "<div class='alert alert-success'>Đã thêm loại bánh mới thành công!</div>";
        } else {
            echo "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
        }
    }
    ?>

    <div class="card shadow-sm p-4 mb-4">
        <h5 class="mb-3 text-primary">Danh sách loại bánh</h5>
        <table class="table table-bordered text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Mã loại bánh</th>
                    <th>Tên loại bánh</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM LoaiBanh";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($loai = $result->fetch_assoc()) {
                        $ma = htmlspecialchars($loai['MaLoaiBanh']);
                        $ten = htmlspecialchars($loai['TenLoaiBanh']);
                        echo "
                        <tr>
                            <td>$ma</td>
                            <td>$ten</td>
                            <td>
                                <a href='suaLoaiBanh.php?id=$ma' class='btn btn-warning btn-sm'>Sửa</a>
                                <a href='xoaLoaiBanh.php?id=$ma' class='btn btn-danger btn-sm'
                                   onclick='return confirm(\"Bạn có chắc muốn xóa loại bánh này không?\")'>Xóa</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo '<tr><td colspan="3">Chưa có loại bánh nào.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</div>
<!-- End container-fluid -->

<?php include '../include1/footer.php'; ?>
