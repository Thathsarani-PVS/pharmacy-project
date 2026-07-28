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

// 1. පරිශීලකයාගේ නම ලබා ගැනීම (Navbar එකේ පෙන්වීමට)
$user_query = "SELECT first_name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($user_query);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_result = $stmt_user->get_result()->fetch_assoc();
$firstName = $user_result['first_name'] ?? $_SESSION['user_name'] ?? 'Customer';

// 2. Navbar එකේ Cart Count එක ලබා ගැනීම
$cart_count_query = "SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?";
$stmt_count = $conn->prepare($cart_count_query);
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$cart_item_count = $count_result['total_items'] ?? 0;

// 3. Search සහ Category Filter සඳහා Query එක සකස් කිරීම
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$products_query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $products_query .= " AND name LIKE ?";
    $search_term = "%" . $search . "%";
    $params[] = $search_term;
    $types .= "s";
}

if (!empty($category)) {
    $products_query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$products_query .= " ORDER BY id DESC";

$stmt_prod = $conn->prepare($products_query);
if (!empty($params)) {
    $stmt_prod->bind_param($types, ...$params);
}
$stmt_prod->execute();
$products_result = $stmt_prod->get_result();

// 4. වම් පැත්තේ පෙන්වීම සඳහා Categories ලැයිස්තුව ලබා ගැනීම
$cat_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' LIMIT 10";
$cat_result = $conn->query($cat_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - 24h Health</title>
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

        /* Left Side Categories Sidebar Box */
        .category-sidebar {
            background: white;
            border: 1px solid rgba(0, 168, 150, 0.15);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 168, 150, 0.03);
        }
        .category-item {
            display: block;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 12px;
            color: #444;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            background: #fdfefe;
            border: 1px solid #f0f4f2;
        }
        .category-item:hover, .category-item.active {
            background: #f4fbf9;
            color: #00a896;
            border-color: rgba(0, 168, 150, 0.2);
            padding-left: 20px;
        }

        .product-card {
            background: white;
            border: 1px solid rgba(0, 168, 150, 0.08);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 168, 150, 0.03);
            transition: 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 168, 150, 0.08);
        }
        .btn-add-cart {
            background: linear-gradient(135deg, #00a896 0%, #028074 100%);
            color: white;
            border-radius: 12px;
            padding: 10px;
            font-weight: 600;
            border: none;
            width: 100%;
            text-align: center;
            text-decoration: none !important;
            display: block;
            transition: 0.3s;
        }
        .btn-add-cart:hover {
            background: linear-gradient(135deg, #028074 0%, #016258 100%);
            color: white;
        }
    </style>
</head>
<body>

<!-- 🌐 Navbar with 24h Health SVG Icon -->
<nav class="navbar-custom-shop">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="brand-logo-text" href="shop.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#00a896" class="me-2">
                <path d="M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V13H5V6.3l7-2.33v9.02z" fill="#00a896"/>
                <path d="M12 7h-2v3H7v2h3v3h2v-3h3v-2h-3V7z" fill="#ffffff"/>
            </svg>
            24h Health
        </a>

        <ul class="nav-links-custom">
            <li>
                <a href="shop.php" class="nav-link-item active">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    Shop
                </a>
            </li>
            <li>
                <a href="cart.php" class="nav-link-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg>
                    Cart 
                    <?php if ($cart_item_count > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?php echo $cart_item_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="user-profile-badge">
            <svg class="user-svg-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM7.07 18.28c.43-.9 3.05-1.78 4.93-1.78s4.5.88 4.93 1.78C15.57 19.35 13.86 20 12 20s-3.57-.65-4.93-1.72zm11.29-1.45c-1.43-1.74-4.9-2.33-6.36-2.33s-4.93.59-6.36 2.33C4.62 15.49 4 13.82 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8c0 1.82-.62 3.49-1.64 4.83zM12 6c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3zm0 4c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1z"/>
            </svg>
            <span class="user-greeting-name">
                <?php echo "Hi, " . htmlspecialchars($firstName); ?>
            </span>
            <a href="../login/logout.php" class="logout-btn-premium">Logout</a>
        </div>
    </div>
</nav>

<!-- 🛍️ Shop Content -->
<div class="container my-5">
    
    <!-- Success Alert Message -->
    <?php if (isset($_GET['cart']) && $_GET['cart'] == 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <strong>Success!</strong> Medicine added to your cart successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Left Side: Categories List (Up to 10 Categories) & Search Bar -->
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="category-sidebar mb-4">
                <h5 class="fw-bold mb-3" style="color: #007a78;">Categories</h5>
                <div class="list-group list-group-flush">
                    <a href="shop.php" class="category-item <?php echo empty($category) ? 'active' : ''; ?>">All Categories</a>
                    <?php if ($cat_result && $cat_result->num_rows > 0): ?>
                        <?php while ($cat = $cat_result->fetch_assoc()): ?>
                            <a href="shop.php?category=<?php echo urlencode($cat['category']); ?>" class="category-item <?php echo ($category == $cat['category']) ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </a>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Search Box on Left Side -->
            <div class="category-sidebar">
                <h5 class="fw-bold mb-3" style="color: #007a78;">Search Medicine</h5>
                <form method="GET" action="shop.php">
                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <input type="text" name="search" class="form-control" placeholder="Search name..." value="<?php echo htmlspecialchars($search); ?>" style="border-radius: 12px; padding: 10px;">
                    </div>
                    <button type="submit" class="btn-add-cart mb-2">Search</button>
                    <?php if (!empty($search) || !empty($category)): ?>
                        <a href="shop.php" class="btn btn-light w-100 border text-muted fw-bold" style="border-radius: 12px; padding: 8px; font-size: 0.9rem;">Reset Filters</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Right Side: Available Medicines Grid (Fills the page properly) -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4" style="color: #007a78;">
                <?php echo !empty($category) ? htmlspecialchars($category) : 'Available Medicines'; ?>
            </h2>

            <div class="row g-4">
                <?php if ($products_result && $products_result->num_rows > 0): ?>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="product-card">
                                <div>
                                    <span class="badge bg-light border mb-2" style="color: #028074;"><?php echo htmlspecialchars($product['category']); ?></span>
                                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <h6 class="text-success fw-bold mb-3">Rs. <?php echo number_format($product['price'], 2); ?></h6>
                                </div>
                                <div>
                                    <a href="shop.php?action=add&product_id=<?php echo $product['id']; ?><?php echo !empty($category) ? '&category='.urlencode($category) : ''; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>" class="btn-add-cart">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="bg-white p-5 rounded-4 shadow-sm border">
                            <p class="text-muted mb-0">No medicines available matching your selection.</p>
                            <a href="shop.php" class="btn btn-sm btn-outline-secondary mt-3">View All Medicines</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Add to Cart Logic when button is clicked
if (isset($_GET['action']) && $_GET['action'] == 'add' && isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    // Check if item already exists in cart for this user
    $check_cart = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt_check = $conn->prepare($check_cart);
    $stmt_check->bind_param("ii", $user_id, $product_id);
    $stmt_check->execute();
    $cart_res = $stmt_check->get_result();

    if ($cart_res->num_rows > 0) {
        $cart_row = $cart_res->fetch_assoc();
        $new_qty = $cart_row['quantity'] + 1;
        $update_cart = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt_up = $conn->prepare($update_cart);
        $stmt_up->bind_param("ii", $new_qty, $cart_row['id']);
        $stmt_up->execute();
    } else {
        $insert_cart = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
        $stmt_ins = $conn->prepare($insert_cart);
        $stmt_ins->bind_param("ii", $user_id, $product_id);
        $stmt_ins->execute();
    }

    // Redirect back preserving filters if any
    $redirect_url = 'shop.php?cart=success';
    if (!empty($category)) $redirect_url .= '&category=' . urlencode($category);
    if (!empty($search)) $redirect_url .= '&search=' . urlencode($search);

    echo "<script>window.location.href='$redirect_url';</script>";
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>