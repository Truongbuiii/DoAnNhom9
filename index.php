<?php
session_start();
include 'db/connect.php';
$conn->set_charset("utf8");
if(isset($_POST['ajax_action'])) {
    $action = $_POST['ajax_action'];

    switch($action){
        case 'add_product':
            $maBanh = intval($_POST['MaBanh']);
            $row = mysqli_fetch_assoc(mysqli_query($conn,"SELECT TenBanh, Gia FROM ThongTinBanh WHERE MaBanh=$maBanh"));
            if(!$row) exit(json_encode(['status'=>'error']));

            if(isset($_SESSION['cart'][$maBanh])){
                $_SESSION['cart'][$maBanh]['SoLuong']++;
                $_SESSION['cart'][$maBanh]['ThanhTien'] += $row['Gia'];
            } else {
                $_SESSION['cart'][$maBanh] = [
                    'TenBanh'=>$row['TenBanh'],
                    'DonGia'=>$row['Gia'],
                    'SoLuong'=>1,
                    'ThanhTien'=>$row['Gia']
                ];
            }
            echo json_encode(['status'=>'success']);
            exit;

        case 'remove_product':
            $maBanh = intval($_POST['MaBanh']);
            unset($_SESSION['cart'][$maBanh]);
            echo json_encode(['status'=>'success']);
            exit;

        case 'change_qty':
            $maBanh = intval($_POST['MaBanh']);
            $type = $_POST['type'];
            if(isset($_SESSION['cart'][$maBanh])){
                if($type==='increase') $_SESSION['cart'][$maBanh]['SoLuong']++;
                if($type==='decrease') $_SESSION['cart'][$maBanh]['SoLuong']--;
                if($_SESSION['cart'][$maBanh]['SoLuong']<=0){
                    unset($_SESSION['cart'][$maBanh]);
                } else {
                    $_SESSION['cart'][$maBanh]['ThanhTien']=$_SESSION['cart'][$maBanh]['DonGia']*$_SESSION['cart'][$maBanh]['SoLuong'];
                }
            }
            echo json_encode(['status'=>'success']);
            exit;

        case 'get_cart':
            $cart = $_SESSION['cart'] ?? [];
            $tong = 0;
            foreach($cart as $item) $tong += $item['ThanhTien'];
            echo json_encode(['cart'=>$cart,'tong'=>$tong]);
            exit;
    }
}
// ==========================
// Kiểm tra đăng nhập
// ==========================
if (!isset($_SESSION['MaNV'])) {
    header("Location: pages/login.php");
    exit;
}

// ==========================
// Kiểm tra trạng thái tài khoản
// ==========================
$MaNV = $_SESSION['MaNV'];
$stmt = $conn->prepare("SELECT TinhTrang, HoTen, PhanQuyen, TenDangNhap FROM nhanvien WHERE MaNV=?");
$stmt->bind_param("i", $MaNV);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['TinhTrang'] != 1) {
    session_destroy();
    header("Location: pages/login.php?msg=locked");
    exit;
}

// ==========================
// Lưu thông tin người dùng
// ==========================
$HoTen = $user['HoTen'];
$PhanQuyen = $user['PhanQuyen'];
$username = $user['TenDangNhap'];


// ==========================
// Xóa sản phẩm
// ==========================
if(isset($_GET['remove'])){
    $maBanh = intval($_GET['remove']);
    unset($_SESSION['cart'][$maBanh]);

    // Sau khi xóa xong, làm sạch URL (xóa ?remove=...) và reload lại trang
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

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
// Cập nhật số lượng sản phẩm
// ==========================
if(isset($_POST['update_quantity'])){
    $maBanh = intval($_POST['MaBanh']);
    $newQty = intval($_POST['SoLuong']);

    if($newQty > 0 && isset($_SESSION['cart'][$maBanh])){
        $_SESSION['cart'][$maBanh]['SoLuong'] = $newQty;
        $_SESSION['cart'][$maBanh]['ThanhTien'] = $_SESSION['cart'][$maBanh]['DonGia'] * $newQty;
    } elseif($newQty == 0){
        unset($_SESSION['cart'][$maBanh]); // nếu nhập 0 thì coi như xóa
    }
}

// ==========================
// Chọn khách hàng
// ==========================
if(isset($_POST['select_customer'])){
    $maKH = intval($_POST['MaKH']);
    
    // Chỉ lưu nếu MaKH là một ID hợp lệ (lớn hơn 0)
    if ($maKH > 0) {
        $_SESSION['selected_customer'] = $maKH;
        // Xóa session khách hàng mới (nếu có) để tránh xung đột
        unset($_SESSION['new_customer']);
    }
}
// ==========================
// ==========================
// Thêm khách hàng mới
// ==========================
// Chọn khách hàng mới vừa nhập(lưu tạm session, chưa lưu DB)s
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
// Tăng/Giảm số lượng sản phẩm
// ==========================
if(isset($_POST['change_qty'])){
    $maBanh = intval($_POST['MaBanh']);
    $action = $_POST['change_qty']; // 'increase' hoặc 'decrease'

    if(isset($_SESSION['cart'][$maBanh])){
        if($action === 'increase'){
            $_SESSION['cart'][$maBanh]['SoLuong'] += 1;
        } elseif($action === 'decrease'){
            $_SESSION['cart'][$maBanh]['SoLuong'] -= 1;
            if($_SESSION['cart'][$maBanh]['SoLuong'] <= 0){
                unset($_SESSION['cart'][$maBanh]); // nếu số lượng = 0 thì xóa luôn
            }
        }
        // Cập nhật lại thành tiền
        if(isset($_SESSION['cart'][$maBanh])){
            $_SESSION['cart'][$maBanh]['ThanhTien'] =
                $_SESSION['cart'][$maBanh]['SoLuong'] * $_SESSION['cart'][$maBanh]['DonGia'];
        }
    }
}

// ==========================
// Thanh toán
// ==========================
if (isset($_POST['checkout'])) {
    $maKH = null;

    // --- Xác định khách hàng ---
    if (isset($_SESSION['new_customer'])) {
        $tenKH = mysqli_real_escape_string($conn, $_SESSION['new_customer']['HoTen']);
        $sdt   = mysqli_real_escape_string($conn, $_SESSION['new_customer']['SDT']);
        mysqli_query($conn, "INSERT INTO KhachHang(HoTen, SDT) VALUES('$tenKH', '$sdt')");
        $maKH = mysqli_insert_id($conn);
    } elseif (isset($_SESSION['selected_customer'])) {
        $maKH = intval($_SESSION['selected_customer']);
    }

    // --- Nếu chưa có khách hàng ---
    if (!$maKH) {
        echo "<script>
            alert('Vui lòng chọn hoặc nhập khách hàng!');
            window.location = 'index.php';
        </script>";
        return;
    }

    // --- Kiểm tra số lượng tồn trước khi tạo đơn ---
    foreach ($_SESSION['cart'] as $maBanh => $item) {
        $check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SoLuong, TenBanh FROM ThongTinBanh WHERE MaBanh = $maBanh"));
        if ($check && $check['SoLuong'] < $item['SoLuong']) {
            echo "<script>
                alert('Sản phẩm {$check['TenBanh']} chỉ còn {$check['SoLuong']} chiếc, không đủ để thanh toán!');
                window.location = 'index.php';
            </script>";
            return;
        }
    }

    // --- Tạo đơn hàng ---
    $tongTien = 0;
    foreach ($_SESSION['cart'] as $item) {
        $tongTien += $item['ThanhTien'];
    }

    $maNV = $_SESSION['MaNV'];
    mysqli_query($conn, "INSERT INTO DonHang(NgayLap, TongTien, MaKH, MaNV) 
                         VALUES(NOW(), $tongTien, $maKH, $maNV)");
    $maDon = mysqli_insert_id($conn);

    // --- Lưu chi tiết và trừ tồn kho ---
    foreach ($_SESSION['cart'] as $maBanh => $item) {
        // Lưu chi tiết đơn hàng
        mysqli_query($conn, "INSERT INTO ChiTietDonHang(MaDon, MaBanh, SoLuong, DonGia, ThanhTien)
                             VALUES($maDon, $maBanh, {$item['SoLuong']}, {$item['DonGia']}, {$item['ThanhTien']})");

        // Cập nhật tồn kho thực tế
        mysqli_query($conn, "UPDATE ThongTinBanh 
                             SET SoLuong = SoLuong - {$item['SoLuong']}
                             WHERE MaBanh = $maBanh");
    }

    // --- Xóa session tạm ---
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
    /* ==========================
   CSS CHO CARD SẢN PHẨM MỚI
   ========================== */
.product-card {
    width: 100%;
height: 170px; /* <--- SỬA Ở ĐÂY */    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #ffffff;
    padding: 12px;
    text-align: left; /* Quan trọng: Đảm bảo căn lề trái */
    display: flex;
    flex-direction: column;
    justify-content: space-between; 
    transition: all 0.2s ease-in-out;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
}

.product-card:hover {
    transform: translateY(-4px); /* Hiệu ứng nhấc lên khi hover */
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    border-color: #256176; /* Viền màu xanh khi hover */
}

/* Tên sản phẩm */
.product-card-name {
    font-size: 1.1rem; 
    font-weight: 600; 
    color: #333;
    display: block;
height: 90px; /* <--- SỬA Ở ĐÂY */    overflow: hidden; /* Tự động ẩn nếu tên dài hơn nữa */
}

/* Giá sản phẩm */
.product-card-price {
    display: block; 
    font-size: 1.1rem;
    font-weight: 700; /* Giá tiền in đậm */
    color: #256176; /* Màu xanh chủ đạo */
    margin-top: 10px; 
}

/* Số lượng tồn kho */
.product-card-stock {
    display: block; 
    font-size: 0.9rem; 
    color: #757575; /* Màu xám */
    margin-top: 4px;
}

/* ==========================
   CSS CHO SẢN PHẨM HẾT HÀNG
   ========================== */
.product-card.out-of-stock {
    background: #f8f9fa; /* Nền xám nhạt */
    color: #adb5bd;
    cursor: not-allowed;
    box-shadow: none;
}
/* Tắt hiệu ứng hover cho thẻ hết hàng */
.product-card.out-of-stock:hover {
    transform: none;
    box-shadow: none;
    border-color: #e0e0e0;
}
/* Giá mờ đi */
.product-card.out-of-stock .product-card-price {
    color: #adb5bd;
}
/* Chữ "Hết hàng" màu đỏ */
.product-card-stock.out-of-stock-text {
    color: #dc3545;
    font-weight: 700;
}
    .btn-skew-arrow {
    display: flex;
    padding: 12px 28px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    background: #256176;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transform: skewX(-15deg);
    transition: 1s;
    box-shadow: 6px 6px 0 #c1d2d9;
    }
    
    .btn-skew-arrow:focus {
    outline: none;
    }
    
    .btn-skew-arrow:hover {
    transition: 0.5s;
    box-shadow: 10px 10px 0 #25aaaa;
    }
    
    .btn-skew-arrow .arrow {
    transition: 0.5s;
    margin-right: 0;
    }
    
    .btn-skew-arrow:hover .arrow {
    margin-right: 45px;
    }
    
    .btn-skew-arrow .text {
    transform: skewX(15deg);
    }
    
    .btn-skew-arrow .one {
    transition: 0.4s;
    transform: translateX(-60%);
    }
    
    .btn-skew-arrow .two {
    transition: 0.5s;
    transform: translateX(-30%);
    }
    
    .btn-skew-arrow:hover .three {
    animation: color_anim 1s infinite 0.2s;
    }
    
    .btn-skew-arrow:hover .one {
    transform: translateX(0%);
    animation: color_anim 1s infinite 0.6s;
    }
    
    .btn-skew-arrow:hover .two {
    transform: translateX(0%);
    animation: color_anim 1s infinite 0.4s;
    }
    
    @keyframes color_anim {
    0% { fill: white; }
    50% { fill: #25aaaa; }
    100% { fill: white; }
    }
.left-column { display: flex; flex-direction: column; height: 85vh; gap: 10px; }
.left-top { 
    flex-shrink: 0; /* Không co lại, giữ kích thước nội dung */
}
.left-bottom { 
    flex: 1; /* Lấp đầy toàn bộ không gian còn lại */
    overflow-y: auto; /* Chỉ mục này được phép cuộn */
}

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
.btn.btn-sm.btn-secondary {
    width: 28px;
    height: 28px;
    padding: 0;
    line-height: 1;
    font-weight: bold;
}
.product-btn {
    width: 100%;
    height: 150px; /* tăng chiều cao để vừa chữ dài và số lượng */
    border: 1px solid #ccc;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* đều khoảng cách trên-dưới */
    align-items: center;
    background: #fff;
    padding: 10px;
    text-align: center;
    transition: .3s;
}

.product-btn:hover {
    background: #f5f5f5;
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}
.product-btn strong {
    display: block; /* Bắt buộc để áp dụng width */
    width: 95%; /* Chiều rộng tối đa của tên */
    white-space: nowrap; /* Không cho phép xuống dòng */
    overflow: hidden; /* Ẩn phần bị tràn */
    text-overflow: ellipsis; /* Hiển thị dấu "..." */
}

/* Nếu hết hàng */
.product-btn.out-of-stock {
    background: #eee;
    color: #999;
    cursor: not-allowed;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 150px; /* giống nhau */
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

  <!-- Khách hàng có sẵn -->
  <div class="checkbox-wrapper-11 d-inline-block me-3">
    <input 
        type="radio" 
        name="customer_type" 
        id="existing_customer" 
        value="existing"
        class="input-11"
    />
    <label for="existing_customer">Khách hàng có sẵn</label>
  </div>

  <!-- Khách hàng mới -->
  <div class="checkbox-wrapper-11 d-inline-block">
    <input 
        type="radio" 
        name="customer_type" 
        id="new_customer" 
        value="new"
        class="input-11"
    />
    <label for="new_customer">Thêm khách hàng mới</label>
  </div>
</div>

<style>
.checkbox-wrapper-11 {
   position: relative;
   z-index: 1;
}

.checkbox-wrapper-11 .input-11 {
   display: none;
   visibility: hidden;
}

.checkbox-wrapper-11 label {
   position: relative;
   padding-left: 2em;
   padding-right: 1em;
   line-height: 2;
   cursor: pointer;
   display: inline-flex;
}

.checkbox-wrapper-11 label:before {
   box-sizing: border-box;
   content: " ";
   position: absolute;
   top: 0.3em;
   left: 0;
   display: block;
   width: 1.4em;
   height: 1.4em;
   border: 2px solid #9098A9;
   border-radius: 6px;
   z-index: -1;
}

/* Khi chọn radio */
.checkbox-wrapper-11 .input-11:checked + label {
   padding-left: 1em;
   color: #000102;
}

.checkbox-wrapper-11 .input-11:checked + label:before {
   top: 0;
   width: 100%;
   height: 2em;
   background: #65b3cf;
   border-color: #256176;
}

.checkbox-wrapper-11 label,
.checkbox-wrapper-11 label::before {
   transition: 0.25s all ease;
}

/* ==========================
   HIỆU ỨNG CHO NÚT BỎ CHỌN
   ========================== */
.btn-hover-lift {
    transition: all 0.2s ease-in-out;
}

.btn-hover-lift:hover {
    transform: translateY(-2px); /* Nhấc nút lên 2px */
    box-shadow: 0 4px 8px rgba(0,0,0,0.15); /* Thêm bóng mờ */
}
</style>


    <!-- KHÁCH HÀNG CÓ SẴN -->
    <div id="existing_customer_form" style="display:none;">
      <form method="post" class="d-flex gap-2">
<select name="MaKH" class="form-control flex-grow-1">       
       <option value="">-- Chọn khách hàng có sẵn --</option>
          <?php
          mysqli_data_seek($khachHangRes, 0);
          while($kh = mysqli_fetch_assoc($khachHangRes)): ?>
            <option value="<?= $kh['MaKH'] ?>" <?= isset($_SESSION['selected_customer']) && $_SESSION['selected_customer']==$kh['MaKH'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($kh['HoTen']) ?> (<?= $kh['SDT'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
<button type="submit" name="select_customer" class="pressable-btn" role="button">
<span class="text">chọn</span>
</button>
<style>
    .pressable-btn {
    touch-action: manipulation;
    position: relative;
    display: inline-block;
    cursor: pointer;
    outline: none;
    border: 2px solid #256176;
    vertical-align: middle;
    text-decoration: none;
 font-weight: 600;
    font-size: 14px; /* <--- Giảm cỡ chữ */
    color: #256176;
padding: 5px 16px;
    background: #f4f7f8;
    border-radius: 8px;
    transform-style: preserve-3d;
    transition: transform 150ms cubic-bezier(0, 0, 0.58, 1), background 150ms cubic-bezier(0, 0, 0.58, 1);
    }
    
    .pressable-btn::before {
    position: absolute;
    content: "";
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: #256176;
    border-radius: inherit;
box-shadow: 0 0 0 2px #256176, 0 4px 0 0 #c1d2d9;
    transform: translate3d(0, 6px, -1em);
    transition: transform 150ms cubic-bezier(0, 0, 0.58, 1), box-shadow 150ms cubic-bezier(0, 0, 0.58, 1);
    }
    
    .pressable-btn:hover {
    background: #f4f7f8;
    transform: translate(0, 2px);
    }
    
    .pressable-btn:hover::before {
    box-shadow: 0 0 0 2px #256176, 0 2px 0 0 #c1d2d9;
    transform: translate3d(0, 4px, -1em);
    }
    
    .pressable-btn:active {
    background: #c1d2d9;
    transform: translate(0em, 6px);
    }
    
    .pressable-btn:active::before {
    box-shadow: 0 0 0 2px #256176, 0 0 #c1d2d9;
    transform: translate3d(0, 0, -1em);
    }
    /* ==========================
   CSS CHO GIỎ HÀNG (BILL)
   ========================== */

/* Nút [Bỏ chọn khách hàng] */
.btn-clear-customer {
    background: none;
    border: none;
    color: #dc3545; /* Màu đỏ */
    font-size: 0.9em;
    font-weight: bold;
    padding: 0;
    cursor: pointer;
    margin-top: 5px;
}
.btn-clear-customer:hover {
    text-decoration: underline;
}

/* Table giỏ hàng */
.table-cart {
    border: none; /* Bỏ viền table */
}

/* Căn trái tên bánh cho dễ đọc */
.table-cart tbody tr td:first-child {
    text-align: left;
}
/* Đường kẻ mờ giữa các item */
.table-cart tbody tr {
    border-bottom: 1px solid #dee2e6;
}
/* Bỏ viền ở item cuối */
.table-cart tbody tr:last-child {
    border-bottom: none; 
}
.table-cart tbody td {
    vertical-align: middle;
    border-right: 1px solid #dee2e6; /* <--- THÊM VIỀN BÊN PHẢI */
    padding-top: 15px;
    padding-bottom: 15px;
}
/* Xóa viền phải ở cột cuối cùng */
.table-cart tbody td:last-child {
    border-right: none;
}
/* Nút SL +/- */
.btn-qty {
    background-color: #f1f3f5; /* Màu xám siêu nhạt */
    border: none;
    color: #343a40;
    font-weight: bold;
    width: 28px;
    height: 28px;
    padding: 0;
    line-height: 1;
    border-radius: 4px;
}
.btn-qty:hover {
    background-color: #e9ecef;
}

/* Tổng tiền (giống bill) */
.cart-summary {
    border-top: 2px dashed #adb5bd; /* Đường kẻ đứt */
    padding-top: 15px;
}
.total-row {
    display: flex;
    justify-content: space-between;
    font-size: 1.25rem; /* To rõ ràng */
    font-weight: 600;
    color: #256176; /* Màu xanh chủ đạo */
}
</style>  
    </form>
    </div>

    <!-- KHÁCH HÀNG MỚI -->
    <div id="new_customer_form" style="display:none;">
      <form method="post" class="d-flex gap-2">
        <input type="text" name="TenKH" class="form-control flex-grow-1" placeholder="Tên khách hàng mới" required>
        <input type="text" name="SDT" class="form-control flex-grow-1" placeholder="Số điện thoại">     
<button type="submit" name="select_new_customer" class="pressable-btn" role="button">
<span class="text">chọn</span>
</button>
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
                                // Số lượng còn lại = tồn kho - số lượng đã thêm vào giỏ
                                $soLuongConLai = $b['SoLuong'] - ($_SESSION['cart'][$b['MaBanh']]['SoLuong'] ?? 0);
                            ?>
                            <div class="col-md-3 mb-3">
                                <?php if($soLuongConLai > 0): ?>
                                    
                                    <form method="post" style="height: 100%;">
                                        <input type="hidden" name="MaBanh" value="<?= $b['MaBanh'] ?>">
                                        
                                        <button type="submit" name="add_product" class="product-card" title="<?= htmlspecialchars($b['TenBanh']) ?>">
                                            
                                            <span class="product-card-name">
                                                <?= htmlspecialchars($b['TenBanh']) ?>
                                            </span>
                                            
                                            <div>
                                                <span class="product-card-price"><?= number_format($b['Gia'],0,',','.') ?> đ</span>
                                                <span class="product-card-stock">Còn: <?= $soLuongConLai ?></span>
                                            </div>
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <div class="product-card out-of-stock" title="<?= htmlspecialchars($b['TenBanh']) ?>">
                                        
                                        <span class="product-card-name">
                                            <?= htmlspecialchars($b['TenBanh']) ?>
                                        </span>
                                        
                                        <div>
                                            <span class="product-card-price"><?= number_format($b['Gia'],0,',','.') ?> đ</span>
                                            <span class="product-card-stock out-of-stock-text">Hết hàng</span>
                                        </div>
                                    </div>
                                    
                                <?php endif; ?>
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
            
           <div class="mb-3 d-flex justify-content-between align-items-center">
                
                <div> 
                    <?php
                    if(isset($_SESSION['selected_customer'])){
                        $selectedCustomer = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM KhachHang WHERE MaKH=".$_SESSION['selected_customer']));
                        // Giữ 2 dòng riêng biệt
                        echo "<h6>Khách hàng: <strong>".htmlspecialchars($selectedCustomer['HoTen'])."</strong></h6>";
                        echo "<h6>Số điện thoại: <strong>".htmlspecialchars($selectedCustomer['SDT'])."</strong></h6>";
                    }
                    elseif(isset($_SESSION['new_customer'])){
                        // Giữ 2 dòng riêng biệt
                        echo "<h6>Khách hàng mới: <strong>".htmlspecialchars($_SESSION['new_customer']['HoTen'])."</strong></h6>";
                        echo "<h6>Số điện thoại: <strong>".htmlspecialchars($_SESSION['new_customer']['SDT'])."</strong></h6>";
                    }
                    else{
                        echo "<h6><em>Chưa chọn khách hàng</em></h6>";
                    }
                    ?>
                </div>

                <div> 
                    <?php if(isset($_SESSION['selected_customer']) || isset($_SESSION['new_customer'])): ?>
                        <form method="post" style="display:inline;">
                            <button type="submit" name="clear_customer" class="btn btn-sm btn-warning btn-hover-lift">Bỏ chọn khách hàng</button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>

            <div class="table-responsive mb-3" style="flex-grow:1; overflow-y:auto; max-height:350px;">
                <table class="table table-cart text-center mb-0">
                    
                    <thead class="table-dark">
                        <tr>
                            <th>Tên bánh</th>
                            <th>Số lượng</th>
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
                            <td style="text-align:center;">
                                <form method="post" style="display:flex; justify-content:center; align-items:center; gap:5px;">
                                    <input type="hidden" name="MaBanh" value="<?= $maBanh ?>">
                                    <button type="submit" name="change_qty" value="decrease" class="btn btn-sm btn-qty">–</button>
                                    <span style="width:30px; text-align:center;"><?= $item['SoLuong'] ?></span>
                                    <button type="submit" name="change_qty" value="increase" class="btn btn-sm btn-qty">+</button>
                                </form>
                            </td>
                            <td><?= number_format($item['ThanhTien'],0,',','.') ?> đ</td>
                            <td>
                                <a href='?remove=<?= $maBanh ?>' class='btn btn-sm btn-outline-danger'>X</a>
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

            <div class="cart-summary mt-auto">
                <div class="total-row">
                    <strong>Tổng cộng:</strong>
                    <strong><?= number_format($tongTien,0,',','.') ?> đ</strong>
                </div>
            </div>

            <?php if(!empty($_SESSION['cart'])): ?>
<form method="post" class="d-flex justify-content-end mt-3">
                            <button class="btn-skew-arrow"  name="checkout" role="button"><span class="text">Thanh toán</span><span class="arrow"><svg width="50px" height="20px" viewBox="0 0 66 43" version="1" xmlns="http://www.w3.org/2000/svg"><g id="arrow" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><path class="one" d="M40.1543933,3.89485454 L43.9763149,0.139296592 C44.1708311,-0.0518420739 44.4826329,-0.0518571125 44.6771675,0.139262789 L65.6916134,20.7848311 C66.0855801,21.1718824 66.0911863,21.8050225 65.704135,22.1989893 L44.677098,42.8607841 C44.4825957,43.0519059 44.1708242,43.0519358 43.9762853,42.8608513 L40.1545186,39.1069479 C39.9575152,38.9134427 39.9546793,38.5968729 40.1481845,38.3998695 L56.9937789,21.8567812 C57.1908028,21.6632968 57.193672,21.3467273 57.0001876,21.1497035 L40.1545208,4.60825197 C39.9574869,4.41477773 39.9546013,4.09820839 40.1480756,3.90117456 Z" fill="#FFFFFF"></path><path class="two" d="M20.1543933,3.89485454 L23.9763149,0.139296592 C24.1708311,-0.0518420739 24.4826329,-0.0518571125 24.6771675,0.139262789 L45.6916134,20.7848311 C46.0855801,21.1718824 46.0911863,21.8050225 45.704135,22.1989893 L24.677098,42.8607841 C24.4825957,43.0519059 24.1708242,43.0519358 23.9762853,42.8608513 L20.1545186,39.1069479 C19.9575152,38.9134427 19.9546793,38.5968729 20.1481845,38.3998695 L36.9937789,21.8567812 C37.1908028,21.6632968 37.193672,21.3467273 37.0001876,21.1497035 L20.1545208,4.60825197 C19.9574869,4.41477773 19.9546013,4.09820839 20.1480756,3.90117456 Z" fill="#FFFFFF"></path><path class="three" d="M0.154393339,3.89485454 L3.97631488,0.139296592 C4.17083111,-0.0518420739 4.48263286,-0.0518571125 4.67716753,0.139262789 L25.6916134,20.7848311 C26.0855801,21.1718824 26.0911863,21.8050225 25.704135,22.1989893 L4.67709797,42.8607841 C4.48259567,43.0519059 4.17082418,43.0519358 3.97628526,42.8608513 L0.154518591,39.1069479 C-0.0424848215,38.9134427 -0.0453206733,38.5968729 0.148184538,38.3998695 L16.9937789,21.8567812 C17.1908028,21.6632968 17.193672,21.3467273 17.0001876,21.1497035 L0.15452076,4.60825197 C-0.0425130651,4.41477773 -0.0453986756,4.09820839 0.148075568,3.90117456 Z" fill="#FFFFFF"></path></g></svg></span></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

        </div>
    </div>
</div>

<script>
// ==========================
// Ghi nhớ tab đang mở bằng Local Storage
// ==========================
document.addEventListener('DOMContentLoaded', function() {
    const activeTabId = localStorage.getItem('activeTabId');
    if (activeTabId) {
        const tabButton = document.querySelector(`[data-bs-target="${activeTabId}"]`);
        if (tabButton) {
            const tab = new bootstrap.Tab(tabButton);
            tab.show();
        }
    }

    // Khi người dùng bấm đổi tab thì lưu lại
    const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
    tabButtons.forEach(btn => {
        btn.addEventListener('shown.bs.tab', function(e) {
            const target = e.target.getAttribute('data-bs-target');
            localStorage.setItem('activeTabId', target);
        });
    });
});
</script>

<?php include 'include/footer.php'; ?>  