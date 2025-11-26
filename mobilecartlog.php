<?php
session_start();
include "config.php";

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: cart.php");
    exit;
}

$error = "";

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);

    // Query ke database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password (support plain text & hash)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            
            // Redirect ke profil atau halaman sebelumnya
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'cart.php';
            header("Location: $redirect");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Masuk - Taki ID</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
    
    <style>
        :root {
            --redcolor: #bf0f0f;
            --greencolor: #89c946;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-btn:hover {
            background: #e0e0e0;
            transform: rotate(90deg);
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
            font-size: 0.9rem;
        }

        .form-group label span {
            color: var(--redcolor);
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--redcolor);
            box-shadow: 0 0 0 3px rgba(191, 15, 15, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: var(--redcolor);
        }

        .forgot-password {
            text-align: right;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: var(--redcolor);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: #ffe2e2;
            border-left: 4px solid #ff4d4d;
            color: #b10000;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: shake 0.3s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: var(--redcolor);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #a00d0d;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(191, 15, 15, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #999;
            font-size: 0.85rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 1rem;
        }

        .btn-google {
            width: 100%;
            padding: 0.9rem;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
        }

        .btn-google:hover {
            border-color: #4285f4;
            background: #f8f9ff;
        }

        .btn-google img {
            width: 20px;
            height: 20px;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .register-link a {
            color: var(--greencolor);
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1.5rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }

        .bottomnav {
            display: flex;
            justify-content: space-around;
            align-items: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70px;
            background-color: #fff;
            border-top: 1px solid #ddd;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.08);
            z-index: 9999;
        }

        .bottomnav a {
            flex: 1;
            text-align: center;
            color: #777;
            font-size: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            gap: 4px;
        }

        .bottomnav a i {
            font-size: 1.4rem;
        }

        .bottomnav a.active {
            color: var(--redcolor);
            font-weight: 600;
        }

    </style>
</head>

<body>
    <div class="login-container">
        <button class="close-btn" onclick="window.history.back()">
            <i class="fa-solid fa-times"></i>
        </button>

        <div class="login-header">
            <h1>Masuk ke Taki ID</h1>
            <p>Login untuk menikmati fitur lengkap Taki!</p>
        </div>

        <?php if ($error): ?>
        <div class="error-message">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Email<span>*</span></label>
                <input 
                    type="email" 
                    name="email" 
                    placeholder="Masukkan email anda" 
                    required 
                    autofocus
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                />
            </div>

            <div class="form-group">
                <label>Password<span>*</span></label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        placeholder="Masukkan password anda" 
                        required 
                    />
                    <i class="fa-solid fa-eye toggle-password" id="togglePassword"></i>
                </div>
            </div>

            <div class="forgot-password">
                <a href="forgot_password.php">Lupa password?</a>
            </div>

            <button type="submit" name="login" class="btn-login">
                Masuk
            </button>
        </form>

        <div class="divider">
            <span>atau</span>
        </div>

        <button class="btn-google" onclick="alert('Fitur Google Login akan segera hadir!')">
            <svg width="20" height="20" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Masuk dengan Google
        </button>

        <div class="register-link">
            Belum punya akun? <a href="mobileregister.php">Buat akun sekarang</a>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="bottomnav">
        <a href="index.php">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="menu.php">
            <i class="fa-solid fa-book-open"></i>
            <span>Menu</span>
        </a>
        <a href="cart.php" class="active">
            <i class="fa-solid fa-shopping-cart"></i>
            <span>Pesanan</span>
        </a>
        <a href="mobilelogin.php" >
            <i class="fa-solid fa-user"></i>
            <span>Profil</span>
        </a>
    </div>

    <script>
        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Auto hide error message
        setTimeout(() => {
            const error = document.querySelector('.error-message');
            if (error) {
                error.style.transition = 'opacity 0.5s';
                error.style.opacity = '0';
                setTimeout(() => error.remove(), 500);
            }
        }, 4000);
    </script>
</body>
</html>