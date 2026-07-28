<?php
session_start();
require_once '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['first_name'];
            $_SESSION['user_role'] = $row['role'];
            
            header("Location: ../customer/shop.php");
            exit();
        } else {
            $error = "Incorrect Password!";
        }
    } else {
        $error = "User not found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - 24h Health</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #f0fdf4 0%, #e6fffa 100%);
            font-family: 'Poppins', sans-serif;
            color: #2d3748;
            min-height: 100vh;
        }
        .navbar-custom { 
            background: rgba(255,255,255,0.9); 
            backdrop-filter: blur(10px);
            padding: 15px 0; 
            border-bottom: 2px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02); 
        }
        .brand-logo-text { 
            font-weight: 800; 
            color: #007a78 !important; 
            text-decoration: none; 
            font-size: 1.5rem; 
            letter-spacing: -0.5px;
        }
        
        .login-wrapper {
            min-height: 82vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }
        .login-card { 
            background: #ffffff; 
            border-radius: 24px; 
            border: 1px solid rgba(0, 168, 150, 0.15); 
            box-shadow: 0 20px 40px rgba(0, 168, 150, 0.08); 
            overflow: hidden;
        }
        
        .login-sidebar {
            background: linear-gradient(135deg, #00a896, #007a78);
            color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form-side {
            padding: 45px;
        }

        .btn-primary { 
            background-color: #00a896 !important; 
            border: none; 
            padding: 14px; 
            font-weight: 700; 
            border-radius: 12px; 
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #007a78 !important;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 168, 150, 0.3);
        }

        .form-control { 
            border-radius: 12px; 
            padding: 13px 18px; 
            border: 1.5px solid #cbd5e1;
            font-size: 0.95rem;
            background-color: #f8fafc;
            transition: all 0.2s;
        }
        .form-control:focus { 
            border-color: #00a896; 
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(0, 168, 150, 0.15); 
        }
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #334155;
            margin-bottom: 8px;
        }
        
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
            user-select: none;
        }
        .toggle-password:hover {
            color: #00a896;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-custom">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 brand-logo-text" href="index.php">
            <!-- Modern Medical Plus Shield SVG Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="#00a896">
                <path d="M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V13H5V6.3l7-2.33v9.02z" fill="#00a896"/>
                <path d="M12 7h-2v3H7v2h3v3h2v-3h3v-2h-3V7z" fill="#ffffff"/>
            </svg>
            <span>24h Health</span>
        </a>
    </div>
</nav>

<div class="container">
    <div class="login-wrapper">
        <div class="row justify-content-center w-100">
            <div class="col-lg-10">
                <div class="login-card row g-0">
                    
                    <!-- Left Side: Modern Banner Sidebar -->
                    <div class="col-lg-5 login-sidebar d-none d-lg-flex">
                        <div class="mb-4">
                            <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-bold mb-3" style="color: #007a78 !important;">Verified Pharmacy</span>
                            <h2 class="fw-bold mb-3" style="letter-spacing: -0.5px;">Your Health, Our Priority.</h2>
                            <p class="opacity-75 small mb-0" style="line-height: 1.7;">Access your 24/7 digital healthcare portal, manage medicine orders, and connect instantly.</p>
                        </div>
                        <div class="mt-auto pt-4 border-top border-light border-opacity-25">
                            <p class="small mb-0 opacity-75">Emergency Support 24/7</p>
                            <span class="fw-bold">+94 11 234 5678</span>
                        </div>
                    </div>

                    <!-- Right Side: Login Form -->
                    <div class="col-lg-7 login-form-side">
                        <div class="text-center text-lg-start mb-4">
                            <h3 class="fw-bold mb-1" style="color: #0f172a;">Welcome Back! 👋</h3>
                            <p class="text-muted small">Please sign in to continue to your dashboard.</p>
                        </div>

                        <?php if(!empty($error)): ?>
                            <div class="alert alert-danger py-2 text-center small rounded-3 mb-4"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="password-container">
                                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Enter your password" required>
                                    <span class="toggle-password" onclick="togglePasswordVisibility()">Show</span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                        </form>

                        <p class="text-center text-muted small mt-4 mb-0">
                            Don't have an account? Register here<a href="register.php" style="color: #00a896; text-decoration:none; font-weight:700;">Register here</a>
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Show/Hide JavaScript -->
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('passwordInput');
        const toggleSpan = document.querySelector('.toggle-password');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleSpan.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            toggleSpan.textContent = 'Show';
        }
    }
</script>

</body>
</html>