<?php
session_start();

// Database සම්බන්ධතාවය
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. පරිශීලකයාගේ විස්තර Database එකෙන් ලබා ගැනීම (ඔබේ DB columns වලට අදාළව)
$user_query = "SELECT first_name, last_name, phone_no, address, city, province FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
$user_data = $user_result->fetch_assoc();

// First name සහ Last name එකතු කර Full Name එක සෑදීම
$firstNameVal = $user_data['first_name'] ?? '';
$lastNameVal = $user_data['last_name'] ?? '';
$fullName = trim($firstNameVal . ' ' . $lastNameVal);
if (empty($fullName)) {
    $fullName = $_SESSION['user_name'] ?? 'Customer';
}

$phone = $user_data['phone_no'] ?? 'Not Provided';
$address = $user_data['address'] ?? '';
$city = $user_data['city'] ?? '';
$province = $user_data['province'] ?? '';

// සම්පූර්ණ ලිපිනය එකතු කිරීම
$fullAddress = trim($address . ($city ? ', ' . $city : '') . ($province ? ', ' . $province : ''));
if (empty($fullAddress)) {
    $fullAddress = 'Not Provided';
}

// Item එකක් මකා දැමීම (Delete)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['cart_id'])) {
    $cart_id = intval($_GET['cart_id']);
    $del_query = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt_del = $conn->prepare($del_query);
    $stmt_del->bind_param("ii", $cart_id, $user_id);
    $stmt_del->execute();
    header("Location: cart.php?action=deleted");
    exit();
}

$delete_message = "";
if (isset($_GET['action']) && $_GET['action'] == 'deleted') {
    $delete_message = "Item deleted successfully!";
}

// 2. Cart එකට අදාළ අයිතම Database එකෙන් ලබා ගැනීම
$cart_query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name as product_name, p.price, p.category 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$stmt_cart = $conn->prepare($cart_query);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$cart_result = $stmt_cart->get_result();

$subtotal = 0.00;
$cart_items = [];
while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row;
    $subtotal += ($row['price'] * $row['quantity']);
}

$shopping_fee = 300.00; 
$grand_total = count($cart_items) > 0 ? ($subtotal + $shopping_fee) : 0.00;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - 24h Health</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5fbf7 0%, #eefaf7 100%); min-height: 100vh; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        .navbar-custom-shop {
            background: #ffffff;
            padding: 15px 0;
            border-bottom: 1px solid #e0e6e4;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        .brand-logo-text {
            font-weight: 800;
            color: #00a896 !important;
            text-decoration: none !important;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        .nav-links-custom {
            list-style: none !important;
            display: flex !important;
            align-items: center;
            gap: 25px;
            margin: 0 !important;
            padding: 0 !important;
        }
        .nav-links-custom li { list-style: none !important; }
        
        .nav-link-item {
            text-decoration: none !important;
            color: #555;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 30px;
            background-color: transparent;
        }
        .nav-link-item svg { fill: #777; transition: all 0.3s ease; }
        .nav-link-item:hover, .nav-link-item.active {
            color: #00a896 !important;
            background-color: #f4fbf9;
            border: 1px solid rgba(0, 168, 150, 0.15);
        }
        .nav-link-item:hover svg, .nav-link-item.active svg { fill: #00a896; }

        .user-profile-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: #f4fbf9;
            padding: 6px 6px 6px 15px;
            border-radius: 50px;
            border: 1px solid rgba(0, 168, 150, 0.15);
        }
        .user-svg-icon { fill: #00a896; }
        .user-greeting-name { font-weight: 700; color: #2c3e50; font-size: 0.95rem; }

        .logout-btn-premium {
            text-decoration: none !important;
            color: #ffffff !important;
            background-color: #ff4d4d;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(255, 77, 77, 0.2);
        }
        .logout-btn-premium:hover {
            background-color: #e60000;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(230, 0, 0, 0.3);
            transform: translateY(-1px);
        }

        .cart-container { margin-top: 40px; }
        .cart-item-card {
            background: white;
            border: 1px solid rgba(0, 168, 150, 0.08);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 168, 150, 0.03);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid #ced4da;
            border-radius: 10px;
            overflow: hidden;
            background: #f8f9fa;
        }
        .quantity-control button {
            background: #ffffff;
            border: none;
            padding: 5px 12px;
            font-weight: bold;
            color: #00a896;
            cursor: pointer;
            transition: 0.2s;
        }
        .quantity-control button:hover { background: #00a896; color: white; }
        .quantity-control input {
            width: 45px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
        }

        .pharmacy-bill-card {
            background: #ffffff;
            border-radius: 24px;
            border: 1px solid rgba(0, 168, 150, 0.15);
            box-shadow: 0 10px 30px rgba(0, 168, 150, 0.08);
            padding: 25px;
            position: sticky;
            top: 30px;
        }
        .bill-header {
            border-bottom: 2px dashed #e0e6e4;
            padding-bottom: 15px;
            margin-bottom: 15px;
            text-align: center;
        }
        .customer-details-box {
            background: #f4fbf9;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #444;
            border: 1px solid rgba(0, 168, 150, 0.1);
        }
        .customer-details-box p { margin-bottom: 4px; }
        .customer-details-box p:last-child { margin-bottom: 0; }

        .bill-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
            color: #555;
        }
        .bill-total-row {
            display: flex;
            justify-content: space-between;
            border-top: 2px solid #e0e6e4;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: 800;
            font-size: 1.2rem;
            color: #007a78;
        }
        
        .btn-proceed-payment {
            background: linear-gradient(135deg, #00a896 0%, #028074 100%);
            color: white;
            border-radius: 14px;
            padding: 14px 20px;
            font-weight: 700;
            width: 100%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none !important;
            box-shadow: 0 4px 15px rgba(0, 168, 150, 0.3);
            transition: all 0.3s ease;
            font-size: 1.05rem;
        }
        .btn-proceed-payment:hover {
            background: linear-gradient(135deg, #028074 0%, #016258 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(0, 168, 150, 0.4);
            transform: translateY(-2px);
        }

        .btn-back-shop {
            background-color: #00a896;
            color: white;
            border-radius: 12px;
            padding: 10px 25px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-back-shop:hover { background-color: #028074; color: white; }
    </style>
</head>
<body>

<!-- 🌐 Navbar -->
<nav class="navbar-custom-shop">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="brand-logo-text" href="shop.php">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#00a896" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 8v4l3 3"></path>
            </svg>
            24h Health
        </a>

        <ul class="nav-links-custom">
            <li>
                <a href="shop.php" class="nav-link-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    Shop
                </a>
            </li>
            <li>
                <a href="cart.php" class="nav-link-item active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Cart
                </a>
            </li>
        </ul>

        <div class="user-profile-badge">
            <svg class="user-svg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM7.07 18.28c.43-.9 3.05-1.78 4.93-1.78s4.5.88 4.93 1.78C15.57 19.35 13.86 20 12 20s-3.57-.65-4.93-1.72zm11.29-1.45c-1.43-1.74-4.9-2.33-6.36-2.33s-4.93.59-6.36 2.33C4.62 15.49 4 13.82 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8c0 1.82-.62 3.49-1.64 4.83zM12 6c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zm0 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
            </svg>
            <span class="user-greeting-name">
                <?php 
                $firstNameOnly = explode(' ', trim($fullName))[0];
                echo "Hi, " . htmlspecialchars($firstNameOnly); 
                ?>
            </span>
            <a href="../logout.php" class="logout-btn-premium">Logout</a>
        </div>
    </div>
</nav>

<!-- 🛒 Cart Contents -->
<div class="container cart-container mb-5">
    
    <?php if (!empty($delete_message)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <strong>Success!</strong> <?php echo $delete_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Side: Cart Items List -->
        <div class="col-lg-8">
            <h3 class="fw-bold mb-4" style="color: #007a78;">Your Shopping Cart</h3>

            <?php if (count($cart_items) > 0): ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item-card" id="cart-item-<?php echo $item['cart_id']; ?>" data-price="<?php echo $item['price']; ?>">
                        <div>
                            <span class="badge bg-light text-teal border mb-1" style="color: #028074;"><?php echo htmlspecialchars($item['category']); ?></span>
                            <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($item['product_name']); ?></h5>
                            <p class="text-muted small mb-0">Rs. <span class="unit-price"><?php echo number_format($item['price'], 2); ?></span> per unit</p>
                        </div>
                        <div class="d-flex align-items-center gap-4">
                            <div class="quantity-control">
                                <button type="button" onclick="updateQty(this, -1)">-</button>
                                <input type="text" class="item-qty-input" value="<?php echo $item['quantity']; ?>" readonly>
                                <button type="button" onclick="updateQty(this, 1)">+</button>
                            </div>
                            <div class="fw-bold text-dark item-total-price" style="min-width: 90px; text-align: right;">
                                Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </div>
                            <a href="cart.php?action=delete&cart_id=<?php echo $item['cart_id']; ?>" class="btn btn-sm text-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="alert alert-warning border-0 shadow-sm p-4 rounded-4 mb-4">
                        <h5 class="fw-bold text-dark mb-2">Your cart is empty!</h5>
                        <p class="text-muted small mb-0">You have not added any medicines to your cart yet.</p>
                    </div>
                    <a href="shop.php" class="btn-back-shop">Go to Shop</a>
                </div>
            <?php endif; ?>

        </div>

        <!-- Right Side: Iconic Pharmacy Bill Summary & User Info -->
        <div class="col-lg-4">
            <div class="pharmacy-bill-card">
                <div class="bill-header">
                    <svg width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="#00a896" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="mb-2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <h5 class="fw-bold text-dark mb-0">Pharmacy Invoice 24H</h5>
                </div>

                <!-- 📇 Customer Details Section in Invoice -->
                <div class="customer-details-box">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($fullName); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($fullAddress); ?></p>
                </div>

                <div class="bill-row">
                    <span>Items  Subtotal</span>
                    <span class="fw-bold" id="bill-subtotal">Rs. <?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="bill-row">
                    <span>Shopping / Delivery Fee</span>
                    <span class="fw-bold">Rs. <?php echo number_format($shopping_fee, 2); ?></span>
                </div>

                <div class="bill-total-row">
                    <span>Grand Total</span>
                    <span id="bill-grand-total">Rs. <?php echo number_format(count($cart_items) > 0 ? ($subtotal + $shopping_fee) : 0, 2); ?></span>
                </div>

                <?php if (count($cart_items) > 0): ?>
                    <div class="mt-4">
                        <a href="payment.php" class="btn-proceed-payment">
                            <span>Proceed to Payment</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // 🧮 Quantity වැඩි/අඩු කළ විට Live Bill එක වෙනස් කරන JavaScript ෆන්ක්ෂන් එක
    function updateQty(btn, change) {
        let card = btn.closest('.cart-item-card');
        let input = card.querySelector('.item-qty-input');
        let unitPrice = parseFloat(card.getAttribute('data-price'));
        
        let currentVal = parseInt(input.value);
        let newVal = currentVal + change;

        if (newVal >= 1) {
            input.value = newVal;
            
            // Item එකේ මුළු මිල යාවත්කාලීන කිරීම
            let itemTotal = unitPrice * newVal;
            card.querySelector('.item-total-price').innerText = 'Rs. ' + itemTotal.toFixed(2);
            
            recalculateBill();
        }
    }

    function recalculateBill() {
        let subtotal = 0;
        document.querySelectorAll('.cart-item-card').forEach(card => {
            let unitPrice = parseFloat(card.getAttribute('data-price'));
            let qty = parseInt(card.querySelector('.item-qty-input').value);
            subtotal += (unitPrice * qty);
        });

        let deliveryFee = 300.00;
        let grandTotal = subtotal + deliveryFee;

        document.getElementById('bill-subtotal').innerText = 'Rs. ' + subtotal.toFixed(2);
        document.getElementById('bill-grand-total').innerText = 'Rs. ' + grandTotal.toFixed(2);
    }
</script>

</body>
</html>