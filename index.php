<?php

include 'include/header.php';
include 'include/sidebar.php';
// ==========================
// Bỏ chọn khách hàng
// ==========================
if(isset($_POST['clear_customer'])){
    unset($_SESSION['selected_customer']);
    unset($_SESSION['new_customer']);
}


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
// ==========================
// Thêm khách hàng mới
// ==========================
// Chọn khách hàng mới vừa nhập
// Chọn khách hàng mới vừa nhập (lưu tạm session, chưa lưu DB)
if(isset($_POST['select_new_customer'])){
    $tenKH = trim($_POST['TenKH'] ?? '');
    $sdt   = trim($_POST['SDT'] ?? '');

    if($tenKH != ''){
        $_SESSION['new_customer'] = [
            'HoTen' => $tenKH,
            'SDT' => $sdt
        ];
        unset($_SESSION['selected_customer']); // nếu trước đó chọn khách cũ
    }
}


// ==========================
// Thanh toán
// ==========================
if(isset($_POST['checkout'])){
    $maKH = null;

    if(isset($_SESSION['new_customer'])){
        $tenKH = mysqli_real_escape_string($conn, $_SESSION['new_customer']['HoTen']);
        $sdt   = mysqli_real_escape_string($conn, $_SESSION['new_customer']['SDT']);
        mysqli_query($conn, "INSERT INTO KhachHang(HoTen, SDT) VALUES('$tenKH', '$sdt')");
        $maKH = mysqli_insert_id($conn);
    }
    elseif(isset($_SESSION['selected_customer'])){
        $maKH = intval($_SESSION['selected_customer']);
    }

    if(!$maKH){
        echo "<script>alert('Vui lòng chọn hoặc nhập khách hàng!');</script>";
        exit;
    }

    // Lưu đơn hàng
    $tongTien = 0;
    foreach($_SESSION['cart'] as $item){
        $tongTien += $item['ThanhTien'];
    }

    $maNV = $_SESSION['MaNV'];
    mysqli_query($conn,"INSERT INTO DonHang(NgayLap, TongTien, MaKH, MaNV) 
                        VALUES(NOW(), $tongTien, $maKH, $maNV)");
    $maDon = mysqli_insert_id($conn);

    foreach($_SESSION['cart'] as $maBanh => $item){
        mysqli_query($conn,"INSERT INTO ChiTietDonHang(MaDon, MaBanh, SoLuong, DonGia, ThanhTien)
                            VALUES($maDon, $maBanh, {$item['SoLuong']}, {$item['DonGia']}, {$item['ThanhTien']})");
    }

    // Xóa session tạm
    unset($_SESSION['cart']);
    unset($_SESSION['selected_customer']);
    unset($_SESSION['new_customer']);

    echo "<script>alert('Thanh toán thành công!'); window.location='index.php';</script>";
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
.left-column { display: flex; flex-direction: column; height: 85vh; gap: 10px; }
.left-top { flex: 1; overflow-y: auto; }
.left-bottom { flex: 2; overflow-y: auto; }

.right-column { height: 85vh; display: flex; flex-direction: column; }
.right-column .card-body { display: flex; flex-direction: column; flex: 1; }

.product-btn {
    width: 100%; height: 120px; border: 1px solid #ccc;
    border-radius: 8px; display: flex; flex-direction: column;
    justify-content: center; align-items: center; background: #fff;
    transition: .3s;
}
.product-btn:hover { background: #f5f5f5; box-shadow: 0 0 5px rgba(0,0,0,0.2); }

/* Form khách hàng */
#existing_customer_form form,
#new_customer_form form {
    display: flex;
    gap: 10px;
    align-items: center;
}
#existing_customer_form select,
#new_customer_form input {
    flex: 1;
}
#new_customer_form button,
#existing_customer_form button {
    height: 38px; /* đồng bộ với input */
}

</style>

<div class="container-fluid">
    <div class="row">
        <!-- BÊN TRÁI -->
        <div class="col-lg-7 left-column">
            <!-- KHÁCH HÀNG -->
<!-- KHÁCH HÀNG -->
<!-- KHÁCH HÀNG -->
<div class="card left-top">
  <div class="card-header"><strong>Thông tin khách hàng</strong></div>
  <div class="card-body d-flex flex-column gap-3">

    <!-- Lựa chọn loại khách hàng -->
    <div class="mb-2">
      <label class="form-label"><strong>Chọn loại khách hàng:</strong></label><br>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="customer_type" id="existing_customer" value="existing">
        <label class="form-check-label" for="existing_customer">Khách hàng có sẵn</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="customer_type" id="new_customer" value="new">
        <label class="form-check-label" for="new_customer">Thêm khách hàng mới</label>
      </div>
    </div>

    <!-- KHÁCH HÀNG CÓ SẴN -->
    <div id="existing_customer_form" style="display:none;">
      <form method="post" class="d-flex gap-2">
        <select name="MaKH" class="form-select flex-grow-1">
          <option value="">-- Chọn khách hàng có sẵn --</option>
          <?php
          mysqli_data_seek($khachHangRes, 0);
          while($kh = mysqli_fetch_assoc($khachHangRes)): ?>
            <option value="<?= $kh['MaKH'] ?>" <?= isset($_SESSION['selected_customer']) && $_SESSION['selected_customer']==$kh['MaKH'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($kh['HoTen']) ?> (<?= $kh['SDT'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
        <button type="submit" name="select_customer" class="btn btn-primary">Chọn</button>
      </form>
    </div>

    <!-- KHÁCH HÀNG MỚI -->
    <div id="new_customer_form" style="display:none;">
      <form method="post" class="d-flex gap-2">
        <input type="text" name="TenKH" class="form-control flex-grow-1" placeholder="Tên khách hàng mới" required>
        <input type="text" name="SDT" class="form-control flex-grow-1" placeholder="Số điện thoại">
        <button type="submit" name="select_new_customer" class="btn btn-success">Chọn</button>
      </form>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const existingForm = document.getElementById('existing_customer_form');
  const newForm = document.getElementById('new_customer_form');
  const radios = document.getElementsByName('customer_type');

  radios.forEach(radio => {
    radio.addEventListener('change', function(){
      if(this.value === 'existing'){
        existingForm.style.display = 'block';
        newForm.style.display = 'none';
      } else if(this.value === 'new'){
        newForm.style.display = 'block';
        existingForm.style.display = 'none';
      }
    });
  });
});
</script>


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
        <div class="card-body d-flex flex-column">
            <!-- Thông tin khách hàng -->
            <div class="mb-3">
                <?php
                if(isset($_SESSION['selected_customer'])){
                    $selectedCustomer = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM KhachHang WHERE MaKH=".$_SESSION['selected_customer']));
                    echo "<h6>Khách hàng: <strong>".htmlspecialchars($selectedCustomer['HoTen'])."</strong></h6>";
                    echo "<h6>Số điện thoại: <strong>".htmlspecialchars($selectedCustomer['SDT'])."</strong></h6>";
                }
                elseif(isset($_SESSION['new_customer'])){
                    echo "<h6>Khách hàng mới: <strong>".htmlspecialchars($_SESSION['new_customer']['HoTen'])."</strong></h6>";
                    echo "<h6>Số điện thoại: <strong>".htmlspecialchars($_SESSION['new_customer']['SDT'])."</strong></h6>";
                }
                else{
                    echo "<h6><em>Chưa chọn khách hàng</em></h6>";
                }
                ?>
                <?php if(isset($_SESSION['selected_customer']) || isset($_SESSION['new_customer'])): ?>
                    <form method="post" style="display:inline;">
                        <button type="submit" name="clear_customer" class="btn btn-sm btn-warning mt-2">Bỏ chọn khách hàng</button>
                    </form>
                <?php endif; ?>
            </div>

            <hr>

            <!-- Bảng sản phẩm -->
            <div class="table-responsive mb-3" style="flex-grow:1; overflow-y:auto; max-height:350px;">
                <table class="table table-bordered text-center mb-0">
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
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($item['TenBanh']) ?></td>
                            <td><?= $item['SoLuong'] ?></td>
                            <td><?= number_format($item['ThanhTien'],0,',','.') ?> đ</td>
                            <td>
                                <a href='?remove=<?= $maBanh ?>' class='btn btn-sm btn-danger'>X</a>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='4'>Chưa có sản phẩm</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Tổng tiền -->
            <div class="text-end mb-3">
                <strong>Tổng: <?= number_format($tongTien,0,',','.') ?> đ</strong>
            </div>

            <!-- Thanh toán -->
            <?php if(!empty($_SESSION['cart'])): ?>
                <form method="post" class="mt-auto">
                    <button name="checkout" class="btn btn-success w-100">Thanh toán</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

        </div>
    </div>
</div>


<?php include 'include/footer.php'; ?>
