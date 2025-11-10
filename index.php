<?php
session_start();

include 'db/connect.php';
$conn->set_charset("utf8");

include 'include/header.php';
include 'include/sidebar.php';

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
// Chọn khách hàng
// ==========================
if(isset($_POST['select_customer'])){
    $_SESSION['selected_customer'] = intval($_POST['MaKH']);
}

// ==========================
// Thêm khách hàng mới
// ==========================
if(isset($_POST['add_customer'])){
    $tenKH = trim($_POST['TenKH']);
    $sdt = trim($_POST['SDT']);
    if($tenKH != ''){
        mysqli_query($conn,"INSERT INTO KhachHang(HoTen, SDT) VALUES('$tenKH','$sdt')");
    }
    // Chuyển hướng sau khi thêm xong để tránh thêm trùng khi reload
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==========================
// Thanh toán
// ==========================
if(isset($_POST['checkout'])){
    $maKH = $_SESSION['selected_customer'] ?? null;
    if(!$maKH){
        echo "<script>alert('Vui lòng chọn khách hàng!');</script>";
    } else {
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
        unset($_SESSION['selected_customer']);

        echo "<script>alert('Thanh toán thành công!'); window.location='index.php';</script>";
    }
}

// ==========================
// Dữ liệu hiển thị
// ==========================
$khachHangRes = mysqli_query($conn,"SELECT * FROM KhachHang ORDER BY MaKH DESC");
$loaiBanhRes = mysqli_query($conn,"SELECT * FROM LoaiBanh");
$loaiBanhArr = [];
while($loai = mysqli_fetch_assoc($loaiBanhRes)){ $loaiBanhArr[] = $loai; }

$selectedCustomer = null;
if(isset($_SESSION['selected_customer'])){
    $selectedCustomer = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM KhachHang WHERE MaKH=".$_SESSION['selected_customer']));
}
?>

<style>
.left-column { display: flex; flex-direction: column; height: 85vh; }
.left-top { flex: 1; overflow-y: auto; }
.left-bottom { flex: 2; overflow-y: auto; margin-top: 10px; }
.right-column { height: 85vh; overflow-y: auto; }
.product-btn {
    width: 100%; height: 120px; border: 1px solid #ccc;
    border-radius: 8px; display: flex; flex-direction: column;
    justify-content: center; align-items: center; background: #fff;
    transition: .3s;
}
.product-btn:hover { background: #f5f5f5; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
</style>

<div class="container-fluid">
    <h1 class="h4 mb-4 text-gray-800">Trang bán hàng</h1>

    <div class="row">
        <!-- BÊN TRÁI -->
        <div class="col-lg-7 left-column">
            <!-- KHÁCH HÀNG -->
            <div class="card left-top">
                <div class="card-header"><strong>Chọn khách hàng</strong></div>
                <div class="card-body">
                    <form method="post" class="mb-3 d-flex">
                        <select name="MaKH" class="form-select me-2">
                            <option value="">-- Chọn khách hàng có sẵn --</option>
                            <?php while($kh = mysqli_fetch_assoc($khachHangRes)): ?>
                                <option value="<?= $kh['MaKH'] ?>" <?= isset($_SESSION['selected_customer']) && $_SESSION['selected_customer']==$kh['MaKH'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kh['HoTen']) ?> (<?= $kh['SDT'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button name="select_customer" class="btn btn-primary">Chọn</button>
                    </form>

                    <form method="post" class="row g-2">
                        <div class="col-md-5">
                            <input type="text" name="TenKH" class="form-control" placeholder="Tên KH mới">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="SDT" class="form-control" placeholder="SĐT">
                        </div>
                        <div class="col-md-2">
                            <button name="add_customer" class="btn btn-success w-100">Thêm</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- DANH SÁCH SẢN PHẨM -->
            <div class="card left-bottom mt-3">
                <div class="card-header"><strong>Danh sách sản phẩm</strong></div>
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <?php foreach($loaiBanhArr as $index=>$loai): ?>
                            <li class="nav-item">
                                <button class="nav-link <?= $index==0?'active':'' ?>" data-bs-toggle="tab"
                                    data-bs-target="#tab-<?= $loai['MaLoaiBanh'] ?>"><?= htmlspecialchars($loai['TenLoaiBanh']) ?></button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="tab-content mt-3">
                        <?php foreach($loaiBanhArr as $index=>$loai): ?>
                        <div class="tab-pane fade <?= $index==0?'show active':'' ?>" id="tab-<?= $loai['MaLoaiBanh'] ?>">
                            <div class="row">
                                <?php
                                $banhRes = mysqli_query($conn,"SELECT * FROM ThongTinBanh WHERE MaLoaiBanh={$loai['MaLoaiBanh']}");
                                while($b = mysqli_fetch_assoc($banhRes)):
                                ?>
                                <div class="col-md-3 mb-3">
                                    <form method="post">
                                        <input type="hidden" name="MaBanh" value="<?= $b['MaBanh'] ?>">
                                        <button type="submit" name="add_product" class="product-btn">
                                            <strong><?= htmlspecialchars($b['TenBanh']) ?></strong>
                                            <small><?= number_format($b['Gia'],0,',','.') ?> đ</small>
                                        </button>
                                    </form>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- BÊN PHẢI -->
        <div class="col-lg-5 right-column">
            <div class="card h-100">
                <div class="card-header"><strong>Đơn hàng tạm</strong></div>
                <div class="card-body">
                  <?php if($selectedCustomer): ?>
    <div class="mb-2">
        <h6>Khách hàng: <strong><?= htmlspecialchars($selectedCustomer['HoTen']) ?></strong></h6>
        <h6>Số điện thoại: <strong><?= htmlspecialchars($selectedCustomer['SDT']) ?></strong></h6>
    </div>
<?php else: ?>
    <h6><em>Chưa chọn khách hàng</em></h6>
<?php endif; ?>

                    <hr>
                    <table class="table table-bordered text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>Tên bánh</th>
                                <th>SL</th>
                                <th>Thành tiền</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tongTien = 0;
                            if(!empty($_SESSION['cart'])){
                                foreach($_SESSION['cart'] as $maBanh=>$item){
                                    $tongTien += $item['ThanhTien'];
                                    echo "<tr>
                                        <td>{$item['TenBanh']}</td>
                                        <td>{$item['SoLuong']}</td>
                                        <td>".number_format($item['ThanhTien'],0,',','.')." đ</td>
                                        <td><a href='?remove=$maBanh' class='btn btn-sm btn-danger'>X</a></td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Chưa có sản phẩm</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="text-end mb-3"><strong>Tổng: <?= number_format($tongTien,0,',','.') ?> đ</strong></div>
                    <?php if(!empty($_SESSION['cart'])): ?>
                        <form method="post">
                            <button name="checkout" class="btn btn-success w-100">Thanh toán</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'include/footer.php'; ?>
