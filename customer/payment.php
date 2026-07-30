<?php
session_start();

// Database සම්බන්ධතාවය
require_once '../config/db.php';

// යූසර් ලොග් වී ඇද්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. පරිශීලකයාගේ විස්තර ලබා ගැනීම
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result()->fetch_assoc();
$firstName = $user_result['first_name'] ?? 'Customer';

// 2. Cart එකේ ඇති අයිතම සහ මුළු එකතුව ලබා ගැනීම
$cart_query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$stmt_cart = $conn->prepare($cart_query);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$cart_result = $stmt_cart->get_result();

$total_amount = 0;
$cart_items = [];
while ($row = $cart_result->fetch_assoc()) {
    $subtotal = $row['price'] * $row['quantity'];
    $total_amount += $subtotal;
    $cart_items[] = $row;
}

// කාට් එක හිස් නම් ෂොප් එකට රීඩිරෙක්ට් කිරීම
if (count($cart_items) == 0) {
    header("Location: shop.php");
    exit();
}

// 3. Order එක Submit කළ විට ක්‍රියාත්මක වන ලොජික් එක
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $error_msg = "Please select a payment method.";
    } else {
        // Database Transaction එක ආරම්භ කිරීම
        $conn->begin_transaction();

        try {
            // පේමන්ට් ස්ටේටස් එක සැකසීම (COD නම් Pending, අනෙක්വෙලාවට Paid හෝ Processing)
            $payment_status = ($payment_method == 'cod') ? 'Pending' : 'Processing';
            $order_status = 'Processing';

            // Orders ටේබල් එකට දත්ත ඇතුළත් කිරීම
            $insert_order = "INSERT INTO orders (user_id, total_amount, payment_method, payment_status, order_status) VALUES (?, ?, ?, ?, ?)";
            $stmt_ord = $conn->prepare($insert_order);
            $stmt_ord->bind_param("dssss", $user_id, $total_amount, $payment_method, $payment_status, $order_status);
            $stmt_ord->execute();
            $order_id = $conn->insert_id;

            // Order Items ටේබල් එකට කාට් එකේ අයිතම ඇතුළත් කිරීම සහ Stock එක අඩු කිරීම
            foreach ($cart_items as $item) {
                $p_id = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];

                // Order item එක දැමීම
                $insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt_item = $conn->prepare($insert_item);
                $stmt_item->bind_param("iiid", $order_id, $p_id, $qty, $price);
                $stmt_item->execute();

                // Products ටේබල් එකේ quantity එක අඩු කිරීම
                $update_stock = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
                $stmt_stock = $conn->prepare($update_stock);
                $stmt_stock->bind_param("ii", $qty, $p_id);
                $stmt_stock->execute();
            }

            // Cart එක හිස් කිරීම (Clear cart)
            $clear_cart = "DELETE FROM cart WHERE user_id = ?";
            $stmt_clear = $conn->prepare($clear_cart);
            $stmt_clear->bind_param("i", $user_id);
            $stmt_clear->execute();

            // වෙනස්කම් තහවුරු කිරීම (Commit)
            $conn->commit();

            // සාර්ථකව ඕර්ඩර් වූ පසු සක්සස් පේජ් එකකට යැවීම
            echo "<script>window.location.href='order_success.php?order_id=$order_id';</script>";
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = "Something went wrong! Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - 24h Health</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5fbf7 0%, #eefaf7 100%); min-height: 100vh; font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-custom-shop { background: #ffffff; padding: 15px 0; border-bottom: 1px solid #e0e6e4; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .brand-logo-text { font-weight: 800; color: #00a896 !important; text-decoration: none !important; font-size: 1.5rem; display: flex; align-items: center; }
        .checkout-card { background: white; border: 1px solid rgba(0, 168, 150, 0.08); border-radius: 20px; padding: 25px; box-shadow: 0 5px 15px rgba(0, 168, 150, 0.03); margin-bottom: 20px; }
        .payment-option-box { border: 2px solid #e0e6e4; border-radius: 14px; padding: 15px; margin-bottom: 12px; cursor: pointer; transition: all 0.3s ease; }
        .payment-option-box:hover { border-color: #00a896; background-color: #f9fdfc; }
        .payment-details-section { display: none; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid #00a896; }
        .btn-place-order { background: linear-gradient(135deg, #00b4d8 0%, #0077b6 100%); color: white; border-radius: 14px; padding: 12px; font-weight: 600; border: none; width: 100%; text-align: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0, 119, 182, 0.2); }
        .btn-place-order:hover { background: linear-gradient(135deg, #0077b6 0%, #03045e 100%); color: white; box-shadow: 0 6px 20px rgba(0, 119, 182, 0.35); transform: translateY(-2px); }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar-custom-shop">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="brand-logo-text" href="shop.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#00a896" class="me-2">
                <path d="M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V13H5V6.3l7-2.33v9.02z" fill="#00a896"/>
                <path d="M12 7h-2v3H7v2h3v3h2v-3h3v-2h-3V7z" fill="#ffffff"/>
            </svg>
            24h Health
        </a>
        <a href="cart.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">Back to Cart</a>
    </div>
</nav>

<!-- Checkout Content -->
<div class="container my-5">
    <h2 class="fw-bold mb-4" style="color: #007a78;">Checkout & Payment</h2>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger rounded-4 shadow-sm mb-4"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="payment.php" method="POST">
        <div class="row">
            <!-- Left Side: Delivery Details & Payment Methods -->
            <div class="col-lg-8">
                <!-- Delivery Information Box -->
                <div class="checkout-card">
                    <h5 class="fw-bold mb-3" style="color: #007a78;">Delivery Information</h5>
                    <p class="mb-1 text-dark"><strong>Name:</strong> <?php echo htmlspecialchars($user_result['first_name'] . ' ' . $user_result['last_name']); ?></p>
                    <p class="mb-1 text-dark"><strong>Address:</strong> <?php echo htmlspecialchars($user_result['address'] . ', ' . $user_result['city'] . ', ' . $user_result['province']); ?></p>
                    <p class="mb-0 text-dark"><strong>Phone:</strong> <?php echo htmlspecialchars($user_result['phone_no']); ?></p>
                </div>

                <!-- Payment Methods Box -->
                <div class="checkout-card">
                    <h5 class="fw-bold mb-3" style="color: #007a78;">Select Payment Method</h5>
                    
                    <!-- 1. Cash on Delivery -->
                    <div class="payment-option-box">
                        <div class="form-check">
                            <input class="form-check-input payment-radio" type="radio" name="payment_method" id="cod" value="cod" checked>
                            <label class="form-check-label fw-bold text-dark w-100" for="cod">
                                Cash on Delivery (COD)
                                <div class="text-muted fw-normal small">Pay with cash when your medicine package arrives at your door.</div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Card Payment -->
                    <div class="payment-option-box">
                        <div class="form-check">
                            <input class="form-check-input payment-radio" type="radio" name="payment_method" id="card" value="card">
                            <label class="form-check-label fw-bold text-dark w-100" for="card">
                                Credit / Debit Card Payment
                                <div class="text-muted fw-normal small">Secure online payment via Visa, Master, or AMEX.</div>
                            </label>
                        </div>
                        <!-- Card Details Form -->
                        <div id="card-section" class="payment-details-section">
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Card Number</label>
                                <input type="text" class="form-control form-control-sm" placeholder="4111 2222 3333 4444">
                            </div>
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <label class="form-label small fw-bold">Expiry Date</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="MM/YY">
                                </div>
                                <div class="col-6 mb-2">
                                    <label class="form-label small fw-bold">CVV</label>
                                    <input type="password" class="form-control form-control-sm" placeholder="123">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Bank Transfer -->
                    <div class="payment-option-box">
                        <div class="form-check">
                            <input class="form-check-input payment-radio" type="radio" name="payment_method" id="bank" value="bank">
                            <label class="form-check-label fw-bold text-dark w-100" for="bank">
                                Bank Transfer
                                <div class="text-muted fw-normal small">Transfer funds directly to our bank account.</div>
                            </label>
                        </div>
                        <!-- Bank Details Info -->
                        <div id="bank-section" class="payment-details-section">
                            <p class="mb-1 small text-dark"><strong>Bank Name:</strong> Commercial Bank</p>
                            <p class="mb-1 small text-dark"><strong>Account Name:</strong> 24h Health (Pvt) Ltd</p>
                            <p class="mb-1 small text-dark"><strong>Account Number:</strong> 1000854210</p>
                            <p class="mb-0 small text-dark"><strong>Branch:</strong> Colombo-02 Main Branch</p>
                            <small class="text-muted mt-2 d-block">Please transfer the total amount and keep your receipt for verification.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Order Summary -->
            <div class="col-lg-4">
                <div class="checkout-card">
                    <h5 class="fw-bold mb-3" style="color: #007a78;">Order Summary</h5>
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($cart_items as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-transparent">
                                <div>
                                    <h6 class="mb-0 text-dark fs-6"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="fw-bold text-success">Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold text-dark">Total Amount</span>
                        <span class="fw-bold fs-5 text-success">Rs. <?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <button type="submit" name="place_order" class="btn-place-order">Confirm & Place Order</button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- JavaScript to toggle Card and Bank details visibility -->
<script>
    document.querySelectorAll('.payment-radio').forEach((radio) => {
        radio.addEventListener('change', function() {
            document.getElementById('card-section').style.display = 'none';
            document.getElementById('bank-section').style.display = 'none';

            if (this.value === 'card') {
                document.getElementById('card-section').style.display = 'block';
            } else if (this.value === 'bank') {
                document.getElementById('bank-section').style.display = 'block';
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>