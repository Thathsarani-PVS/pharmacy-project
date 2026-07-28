<?php
session_start();
require_once '../config/db.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_no = trim($_POST['phone_no']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $password = $_POST['password'];

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($phone_no) && !empty($address) && !empty($city) && !empty($province) && !empty($password)) {
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "This email is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role = 'customer'; 

            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone_no, address, city, province, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssssssss", $first_name, $last_name, $email, $phone_no, $address, $city, $province, $hashed_password, $role);
            
            if ($insert_stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $first_name;
                $_SESSION['user_role'] = $role;

                header("Location: ../customer/shop.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>24h Health - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0fbf9; font-family: 'Segoe UI', sans-serif; padding: 40px 0; }
        .auth-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 168, 150, 0.05); max-width: 650px; margin: auto; padding: 40px; }
        .btn-medical { background: #00a896; color: white; border-radius: 12px; font-weight: 600; width: 100%; padding: 12px; border: none; }
        .btn-medical:hover { background: #028074; color: white; }
    </style>
</head>
<body>
<div class="auth-card">
    <h3 class="text-center fw-bold mb-2" style="color: #007a78;">Create Account</h3>
    <p class="text-center text-muted small mb-4">Please fill in your details</p>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger py-2"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Phone Number</label>
                <input type="text" name="phone_no" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Street Address</label>
                <input type="text" name="address" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">City</label>
                <input type="text" name="city" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Province</label>
                <select name="province" class="form-select" required>
                    <option value="">Select Province</option>
                    <option value="Western">Western</option>
                    <option value="Central">Central</option>
                    <option value="Southern">Southern</option>
                    <option value="North Western">North Western</option>
                    <option value="Sabaragamuwa">Sabaragamuwa</option>
                    <option value="Eastern">Eastern</option>
                    <option value="Uva">Uva</option>
                    <option value="North Central">North Central</option>
                    <option value="Northern">Northern</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-medical mt-4">Register Account</button>
        <p class="text-center mb-0 mt-3 small text-muted">Already have an account? <a href="login_form.php" style="color: #00a896;" class="fw-bold">Login here</a></p>
    </form>
</div>
</body>
</html>