<?php include 'include/header.php'; ?>
<?php include 'include/sidebar.php'; ?>


      
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                   

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Admin</span>
                                <img class="img-profile rounded-circle"
                                    src="img/undraw_profile.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                              
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Đăng xuất
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                       <?php
include 'db/connect.php';

// ==========================
// Xử lý thêm sản phẩm
// ==========================
if(isset($_POST['add_product'])){
    $maBanh = intval($_POST['MaBanh']);
    $soLuong = 1;

    $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT TenBanh, Gia, MaLoaiBanh FROM ThongTinBanh WHERE MaBanh=$maBanh"));

    if(isset($_SESSION['cart'][$maBanh])){
        $_SESSION['cart'][$maBanh]['SoLuong'] += $soLuong;
        $_SESSION['cart'][$maBanh]['ThanhTien'] += $row['Gia'] * $soLuong;
    } else {
        $_SESSION['cart'][$maBanh] = [
            'TenBanh' => $row['TenBanh'],
            'DonGia' => $row['Gia'],
            'SoLuong' => $soLuong,
            'ThanhTien' => $row['Gia'] * $soLuong,
            'MaLoaiBanh' => $row['MaLoaiBanh']
        ];
    }
}

// ==========================
// Xóa sản phẩm
// ==========================
if(isset($_GET['remove'])){
    $maBanh = intval($_GET['remove']);
    unset($_SESSION['cart'][$maBanh]);
}

// ==========================
// Thanh toán
// ==========================
if(isset($_POST['checkout'])){
    $tenKH = $_POST['TenKH'] ?? 'Khách lẻ';
    $sdtKH = $_POST['SDT'] ?? '';

    mysqli_query($conn,"INSERT INTO KhachHang(HoTen, SDT) VALUES('$tenKH','$sdtKH')");
    $maKH = mysqli_insert_id($conn);

    mysqli_query($conn,"INSERT INTO DonHang(NgayLap, TongTien, MaKH, MaNV) VALUES(NOW(),0,$maKH,1)");
    $maDon = mysqli_insert_id($conn);

    $tongTien = 0;
    foreach($_SESSION['cart'] as $maBanh => $item){
        $tongTien += $item['ThanhTien'];
        mysqli_query($conn,"INSERT INTO ChiTietDonHang(MaDon, MaBanh, SoLuong, DonGia, ThanhTien)
                            VALUES($maDon,$maBanh,{$item['SoLuong']},{$item['DonGia']},{$item['ThanhTien']})");
    }

    mysqli_query($conn,"UPDATE DonHang SET TongTien=$tongTien WHERE MaDon=$maDon");

    unset($_SESSION['cart']);
    echo "<script>alert('Thanh toán thành công!'); window.location='banhang.php';</script>";
}

// ==========================
// Lấy danh sách loại bánh
// ==========================
$loaiBanhRes = mysqli_query($conn,"SELECT * FROM LoaiBanh");
$loaiBanhArr = [];
while($loai = mysqli_fetch_assoc($loaiBanhRes)){
    $loaiBanhArr[] = $loai;
}
// ==========================
// Thanh toán (chỉ lúc này mới lưu khách hàng)
// ==========================
if(isset($_POST['checkout'])){
    $chonKH = $_POST['chonKH'] ?? ''; // 'moi' hoặc 'cu'
    $maKH = null;

    if($chonKH == 'moi'){
        $tenKH = trim($_POST['TenKH'] ?? '');
        $sdtKH = trim($_POST['SDT'] ?? '');

        if($tenKH == ''){
            echo "<script>alert('Vui lòng nhập tên khách hàng mới!');</script>";
            exit;
        }

        // Thêm khách hàng mới
        mysqli_query($conn,"INSERT INTO KhachHang(HoTen, SDT) VALUES('$tenKH','$sdtKH')");
        $maKH = mysqli_insert_id($conn);

    } elseif($chonKH == 'cu'){
        $maKH = intval($_POST['MaKH'] ?? 0);
    }

    // Nếu không có khách hàng nào được chọn
    if(!$maKH){
        // Tạo khách mặc định “Khách lẻ” nếu chưa có
        $res = mysqli_query($conn,"SELECT MaKH FROM KhachHang WHERE HoTen='Khách lẻ' LIMIT 1");
        if(mysqli_num_rows($res) > 0){
            $maKH = mysqli_fetch_assoc($res)['MaKH'];
        } else {
            mysqli_query($conn,"INSERT INTO KhachHang(HoTen, SDT) VALUES('Khách lẻ','')");
            $maKH = mysqli_insert_id($conn);
        }
    }

    // Tạo đơn hàng
    mysqli_query($conn,"INSERT INTO DonHang(NgayLap, TongTien, MaKH, MaNV) VALUES(NOW(),0,$maKH,1)");
    $maDon = mysqli_insert_id($conn);

    $tongTien = 0;
    foreach($_SESSION['cart'] as $maBanh => $item){
        $tongTien += $item['ThanhTien'];
        mysqli_query($conn,"INSERT INTO ChiTietDonHang(MaDon, MaBanh, SoLuong, DonGia, ThanhTien)
                            VALUES($maDon,$maBanh,{$item['SoLuong']},{$item['DonGia']},{$item['ThanhTien']})");
    }

    mysqli_query($conn,"UPDATE DonHang SET TongTien=$tongTien WHERE MaDon=$maDon");

    // Xóa giỏ hàng và reset các lựa chọn
    unset($_SESSION['cart']);

    echo "<script>
        alert('Thanh toán thành công!');
        // Reset form khách hàng
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('chonKH').value = '';
            document.getElementById('TenKH').value = '';
            document.getElementById('SDT').value = '';
            document.getElementById('MaKH').value = '';
        });
        window.location='banhang.php';
    </script>";
}

?>

<style>
.product-scroll {
    max-height: 520px;       /* chiều cao cố định vùng sản phẩm */
    overflow-y: auto;        /* bật thanh cuộn dọc */
    overflow-x: hidden;      /* ẩn cuộn ngang */
    padding-right: 10px;     /* chừa khoảng cho thanh cuộn */
    scrollbar-width: thin;   /* Firefox */
}

/* ✅ Tùy chọn: làm đẹp thanh cuộn (trình duyệt WebKit) */
.product-scroll::-webkit-scrollbar {
    width: 8px;
}
.product-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.product-scroll::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* ✅ Các ô bánh vuông */
.product-box {
    width: 100%;
    height: 130px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    border: 1px solid #ddd;
    border-radius: 10px;
    transition: 0.3s;
    background: #fff;
}
.product-box:hover {
    background: #f8f9fa;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Trang bán hàng</h1>

    <div class="row">
        <!-- Cột trái -->
        <div class="col-lg-6">
            <form method="post">
                <h5>Thông tin khách hàng</h5>

<!-- Chọn loại khách hàng -->
<div class="mb-3">
    <select id="chonLoaiKH" class="form-control" name="chonKH">
        <option value="">-- Chọn loại khách hàng --</option>
        <option value="moi">Thêm khách hàng mới</option>
        <option value="cu">Chọn khách hàng đã lưu</option>
    </select>
</div>

<!-- Form thêm khách hàng mới -->
<div id="formMoi" style="display:none;">
    <div class="mb-3">
        <input type="text" name="TenKH" id="TenKH_moi" placeholder="Tên khách hàng" class="form-control">
    </div>
    <div class="mb-3">
        <input type="text" name="SDT" id="SDT_moi" placeholder="Số điện thoại" class="form-control">
    </div>
</div>

<!-- Form chọn khách hàng đã lưu -->
<div id="formCu" style="display:none;">
    <input type="text" id="timKiemKH" class="form-control mb-2" placeholder="Tìm kiếm khách hàng">

    <select name="MaKH" id="chonKHSelect" class="form-select mb-3" size="5">
        <?php
        $khRes = mysqli_query($conn, "SELECT * FROM KhachHang ORDER BY HoTen ASC");
        while($kh = mysqli_fetch_assoc($khRes)){
            echo "<option value='{$kh['MaKH']}' data-ten='{$kh['HoTen']}' data-sdt='{$kh['SDT']}'>
                    {$kh['HoTen']} - {$kh['SDT']}
                  </option>";
        }
        ?>
    </select>

    <div class="mb-3">
        <input type="text" name="TenKH" id="TenKH_cu" class="form-control" placeholder="Tên khách hàng" readonly>
    </div>
    <div class="mb-3">
        <input type="text" name="SDT" id="SDT_cu" class="form-control" placeholder="Số điện thoại" readonly>
    </div>
</div>
<script>
document.getElementById("chonLoaiKH").addEventListener("change", function() {
    var loai = this.value;
    document.getElementById("formMoi").style.display = (loai === "moi") ? "block" : "none";
    document.getElementById("formCu").style.display = (loai === "cu") ? "block" : "none";
});

// Tìm kiếm khách hàng
document.getElementById("timKiemKH").addEventListener("keyup", function() {
    var filter = this.value.toLowerCase();
    var options = document.getElementById("chonKHSelect").options;
    for (let i = 0; i < options.length; i++) {
        let text = options[i].text.toLowerCase();
        options[i].style.display = text.includes(filter) ? "" : "none";
    }
});

// ✅ Khi chọn khách hàng trong danh sách, tự động hiển thị vào input
document.getElementById("chonKHSelect").addEventListener("change", function() {
    var selected = this.options[this.selectedIndex];
    document.getElementById("TenKH_cu").value = selected.getAttribute("data-ten");
    document.getElementById("SDT_cu").value = selected.getAttribute("data-sdt");
});
</script>

            </form>
        </div>
    </div>

<script>
    // Khi người dùng chọn loại khách hàng
    document.getElementById("chonLoaiKH").addEventListener("change", function() {
        var loai = this.value;
        document.getElementById("formMoi").style.display = (loai === "moi") ? "block" : "none";
        document.getElementById("formCu").style.display = (loai === "cu") ? "block" : "none";
    });

    // Tìm kiếm khách hàng trong danh sách
    document.getElementById("timKiemKH").addEventListener("keyup", function() {
        var filter = this.value.toLowerCase();
        var options = document.getElementById("chonKHSelect").options;

        for (let i = 0; i < options.length; i++) {
            let text = options[i].text.toLowerCase();
            options[i].style.display = text.includes(filter) ? "" : "none";
        }
    });
</script>



            <!-- Tabs danh mục -->
                <h5>Sản phẩm</h5>
            <ul class="nav nav-tabs" id="tabLoaiBanh" role="tablist">
                <?php foreach($loaiBanhArr as $index => $loai): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $index==0?'active':'' ?>" id="tab-<?= $loai['MaLoaiBanh'] ?>"
                            data-bs-toggle="tab" data-bs-target="#content-<?= $loai['MaLoaiBanh'] ?>" type="button" role="tab">
                        <?= htmlspecialchars($loai['TenLoaiBanh']) ?>
                    </button>
                </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content mt-3">
                <?php foreach($loaiBanhArr as $index => $loai): ?>
                <div class="tab-pane fade <?= $index==0?'show active':'' ?>" id="content-<?= $loai['MaLoaiBanh'] ?>" role="tabpanel">
                    <div class="product-scroll">
                        <div class="row">
                            <?php
                            $res = mysqli_query($conn,"SELECT * FROM ThongTinBanh WHERE MaLoaiBanh={$loai['MaLoaiBanh']}");
                            while($row = mysqli_fetch_assoc($res)):
                            ?>
                            <div class="col-md-3 mb-3">
                                <form method="post" class="text-center">
                                    <input type="hidden" name="MaBanh" value="<?= $row['MaBanh'] ?>">
                                    <button type="submit" name="add_product" class="btn btn-light product-box">
                                        <strong><?= htmlspecialchars($row['TenBanh']) ?></strong>
                                        <small><?= number_format($row['Gia'],0,',','.') ?> đ</small>
                                    </button>
                                </form>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cột phải -->
        <div class="col-lg-6">
            <form method="post">
                <h5>Đơn hàng tạm</h5>
                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Tên bánh</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tongTien = 0;
                        if(!empty($_SESSION['cart'])){
                            foreach($_SESSION['cart'] as $maBanh => $item){
                                $tongTien += $item['ThanhTien'];
                                echo "<tr>
                                    <td>{$item['TenBanh']}</td>
                                    <td>{$item['SoLuong']}</td>
                                    <td>".number_format($item['ThanhTien'],0,',','.')." đ</td>
                                    <td><a href='?remove=$maBanh' class='btn btn-sm btn-danger'>X</a></td>
                                </tr>";
                            }
                            echo "<tr class='table-warning'>
                                    <td colspan='2'><strong>Tổng cộng</strong></td>
                                    <td colspan='2'><strong>".number_format($tongTien,0,',','.')." đ</strong></td>
                                  </tr>";
                        } else {
                            echo "<tr><td colspan='4'>Chưa chọn sản phẩm</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <?php if(!empty($_SESSION['cart'])): ?>
                    <button type="submit" name="checkout" class="btn btn-success w-100">Thanh toán</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>



                               
                    </div>

                 

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-6 mb-4">




                        </div>

                        <div class="col-lg-6 mb-4">

    

                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

           
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->



<?php include 'include/footer.php'; ?>


