<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../login/index.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $description = trim($_POST['description']);

    if (!empty($name) && !empty($category) && $price > 0 && $quantity >= 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, category, price, quantity, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $name, $category, $price, $quantity, $description);
        
        if ($stmt->execute()) {
            $message = "<div class='alert success'>New Medicine Stock Logged Successfully!</div>";
        } else {
            $message = "<div class='alert error'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        $message = "<div class='alert error'>Validation Error: Please provide valid details.</div>";
    }
}

$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PharmaQuick - Admin Hub</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background-color: #f4f6f9; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: #1a365d; color: white; padding: 20px; border-radius: 6px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .logout-btn { color: white; text-decoration: none; font-weight: bold; background: #e53e3e; padding: 8px 15px; border-radius: 4px; }
        .grid { display: flex; gap: 20px; }
        .col-4 { width: 35%; }
        .col-8 { width: 65%; }
        .card { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #4a5568; }
        input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 8px; border: 1px solid #cbd5e0; border-radius: 4px; box-sizing: border-box; }
        .btn { background: #2b6cb0; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn:hover { background: #2b4c7e; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background-color: #ebf8ff; color: #2c5282; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-danger { background-color: #fed7d7; color: #9b2c2c; }
        .badge-success { background-color: #c6f6d5; color: #22543d; }
        .alert { padding: 12px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; }
        .alert.success { background: #c6f6d5; color: #22543d; border: 1px solid #38a169; }
        .alert.error { background: #fed7d7; color: #9b2c2c; border: 1px solid #e53e3e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 style="margin:0; font-size: 24px;">PharmaQuick Enterprise Hub</h1>
                <p style="margin:5px 0 0 0;">Welcome back, Admin <?php echo $_SESSION['user_name']; ?></p>
            </div>
            <a href="../login/index.php" class="logout-btn">Logout</a>
        </div>

        <?php echo $message; ?>

        <div class="grid">
            <div class="col-4">
                <div class="card">
                    <h3 style="margin-top:0; color:#1a365d;">Stock Procurement Intake</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label>Medicine Name</label>
                            <input type="text" name="name" required placeholder="e.g., Panadol 500mg">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="Antibiotics">Antibiotics</option>
                                <option value="Analgesics">Analgesics</option>
                                <option value="Cardiology">Cardiology</option>
                                <option value="Vitamins">Vitamins</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Unit Price (LKR)</label>
                            <input type="number" step="0.01" name="price" required placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="quantity" required placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="Clinical usage guidelines..."></textarea>
                        </div>
                        <button type="submit" class="btn">Add to Inventory</button>
                    </form>
                </div>
            </div>
            
            <div class="col-8">
                <div class="card">
                    <h3 style="margin-top:0; color:#1a365d;">Dynamic Inventory Monitor</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>BA Alert Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td>
                                            <?php if($row['quantity'] <= 10): ?>
                                                <span class="badge badge-danger">CRITICAL LOW</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">STABLE</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6" style="text-align:center;">No inventory logs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>