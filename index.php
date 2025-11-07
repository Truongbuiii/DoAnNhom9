<?php
session_start();

// ⚠️ Kiểm tra đăng nhập
if (!isset($_SESSION['MaNV'])) {
    header("Location: pages/login.php");
    exit;
}

// Kết nối CSDL
include 'db/connect.php';
$conn->set_charset("utf8");
?>

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
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                            <?php 
                                if (isset($_SESSION['PhanQuyen']) && $_SESSION['PhanQuyen'] == 'Admin') {
                                    echo 'Admin';
                                } else {
                                    echo 'Nhân viên';
                                }
                            ?>
                        </span>
                        <img class="img-profile rounded-circle" src="img/undraw_profile.svg">
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
            <?php
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
                $chonKH = $_POST['chonKH'] ?? ''; // 'moi' hoặc 'cu'
                $maKH = null;

                if($chonKH == 'moi'){
                    $tenKH = trim($_POST['TenKH'] ?? '');
                    $sdtKH = trim($_POST['SDT'] ?? '');
                    if($tenKH == ''){
                        echo "<script>alert('Vui lòng nhập tên khách hàng mới!');</script>";
                        exit;
                    }
                    mysqli_query($conn,"INSERT INTO KhachHang(HoTen, SDT) VALUES('$tenKH','$sdtKH')");
                    $maKH = mysqli_insert_id($conn);
                } elseif($chonKH == 'cu'){
                    $maKH = intval($_POST['MaKH'] ?? 0);
                }

                if(!$maKH){
                    $res = mysqli_query($conn,"SELECT MaKH FROM KhachHang WHERE HoTen='Khách lẻ' LIMIT 1");
                    if(mysqli_num_rows($res) > 0){
                        $maKH = mysqli_fetch_assoc($res)['MaKH'];
                    } else {
                        mysqli_query($conn,"INSERT INTO KhachHang(HoTen, SDT) VALUES('Khách lẻ','')");
                        $maKH = mysqli_insert_id($conn);
                    }
                }

                mysqli_query($conn,"INSERT INTO DonHang(NgayLap, TongTien, MaKH, MaNV) VALUES(NOW(),0,$maKH,{$_SESSION['MaNV']})");
                $maDon = mysqli_insert_id($conn);

                $tongTien = 0;
                foreach($_SESSION['cart'] as $maBanh => $item){
                    $tongTien += $item['ThanhTien'];
                    mysqli_query($conn,"INSERT INTO ChiTietDonHang(MaDon, MaBanh, SoLuong, DonGia, ThanhTien)
                                        VALUES($maDon,$maBanh,{$item['SoLuong']},{$item['DonGia']},{$item['ThanhTien']})");
                }

                mysqli_query($conn,"UPDATE DonHang SET TongTien=$tongTien WHERE MaDon=$maDon");
                unset($_SESSION['cart']);

                echo "<script>alert('Thanh toán thành công!'); window.location='index.php';</script>";
            }

            // ==========================
            // Lấy danh sách loại bánh
            // ==========================
            $loaiBanhRes = mysqli_query($conn,"SELECT * FROM LoaiBanh");
            $loaiBanhArr = [];
            while($loai = mysqli_fetch_assoc($loaiBanhRes)){
                $loaiBanhArr[] = $loai;
            }
            ?>

            <style>
            .product-scroll {
                max-height: 520px;
                overflow-y: auto;
                overflow-x: hidden;
                padding-right: 10px;
                scrollbar-width: thin;
            }
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

            <h1 class="h3 mb-4 text-gray-800">Trang bán hàng</h1>

            <div class="row">
                <!-- Cột trái -->
                <div class="col-lg-6">
                   

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
        <!-- /.container-fluid -->
    </div>
</div>

<?php include 'include/footer.php'; ?>
