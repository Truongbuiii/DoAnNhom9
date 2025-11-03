<?php

include 'db/connect.php';
include 'include/header.php';
include 'include/sidebar.php';

// Thêm sản phẩm vào cart
if(isset($_POST['add_product'])){
    $maBanh = intval($_POST['MaBanh']);
    $soLuong = 1; // mặc định 1

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

// Xóa sản phẩm
if(isset($_GET['remove'])){
    $maBanh = intval($_GET['remove']);
    unset($_SESSION['cart'][$maBanh]);
}

// Thanh toán
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

// Lấy danh sách loại bánh
$loaiBanhRes = mysqli_query($conn,"SELECT * FROM LoaiBanh");
$loaiBanhArr = [];
while($loai = mysqli_fetch_assoc($loaiBanhRes)){
    $loaiBanhArr[] = $loai;
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Cột trái: thông tin khách + sản phẩm dạng tab -->
        <div class="col-lg-8">
            <form method="post">
                <h5>Thông tin khách hàng</h5>
                <div class="mb-3">
                    <input type="text" name="TenKH" placeholder="Tên khách" class="form-control" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="SDT" placeholder="Số điện thoại" class="form-control">
                </div>
            </form>

            <!-- Tabs danh mục -->
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
                    <div class="row">
                        <?php
                        $res = mysqli_query($conn,"SELECT * FROM ThongTinBanh WHERE MaLoaiBanh={$loai['MaLoaiBanh']}");
                        while($row = mysqli_fetch_assoc($res)):
                        ?>
                        <div class="col-md-3 mb-3">
                            <form method="post" class="text-center">
                                <input type="hidden" name="MaBanh" value="<?= $row['MaBanh'] ?>">
                                <button type="submit" name="add_product" class="btn btn-light p-0 border rounded" style="width:100%; height:120px; display:flex; align-items:center; justify-content:center; flex-direction:column;">
                                    <strong><?= htmlspecialchars($row['TenBanh']) ?></strong>
                                    <small><?= number_format($row['Gia'],0,',','.') ?> đ</small>
                                </button>
                            </form>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cột phải: đơn hàng tạm -->
        <div class="col-lg-4">
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

<?php include 'include/footer.php'; ?>
