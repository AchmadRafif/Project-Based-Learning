<?php
session_start();
include "config.php";

// Redirect jika sudah login
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboardadmin.php");
    exit;
}

$error = "";

// Proses Login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Query ke database
    $query = mysqli_query($conn, "SELECT * FROM admin WHERE email = '$email' OR username = '$email' LIMIT 1");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $admin = mysqli_fetch_assoc($query);
        
        // Verifikasi password
        if ($password === $admin['password']) {
            // Login berhasil - Set SESSION
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            
            // ✅ SET LOCALSTORAGE VIA JAVASCRIPT
            echo "<script>
                localStorage.setItem('isAdminLoggedIn', 'true');
                window.location.href = 'dashboardadmin.php';
            </script>";
            exit;
        } else {
            $error = "Email atau password salah!";
        }
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Admin - TaKi</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(135deg, #ffffffff 0%, #b9b9b9ff 100%);
      display: flex;
      height: 100vh;
      align-items: center;
      justify-content: center;
    }

    .container {
      display: flex;
      width: 900px;
      background: #fff;
      box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
      border-radius: 20px;
      overflow: hidden;
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

    .left {
      flex: 1;
      background: url(img/AboutTaki.jpg) center/cover no-repeat;
      position: relative;
    }

    .left::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(128, 0, 0, 0.6);
    }

    .left-content {
      position: relative;
      z-index: 1;
      color: #fff;
      padding: 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      height: 100%;
    }

    .left-content h1 {
      font-size: 2.5rem;
      margin-bottom: 15px;
    }

    .left-content p {
      font-size: 1.1rem;
      opacity: 0.9;
    }

    .right {
      flex: 1;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 30px;
    }

    .logo img {
      width: 50px;
      height: 50px;
      border-radius: 50%;
    }

    .logo h2 {
      color: #800000;
      font-size: 1.8rem;
    }

    h3 {
      color: #333;
      font-weight: 600;
      margin-bottom: 8px;
      font-size: 1.4rem;
    }

    p.sub {
      color: #777;
      font-size: 14px;
      margin-bottom: 30px;
    }

    .errorbox {
      background: #ffe2e2;
      border-left: 5px solid #ff4d4d;
      color: #b10000;
      padding: 12px 15px;
      margin-bottom: 20px;
      border-radius: 8px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: shake 0.3s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .input-group {
      margin-bottom: 20px;
    }

    label {
      font-weight: 500;
      color: #333;
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
    }

    input {
      width: 100%;
      padding: 14px 16px;
      border: 2px solid #e0e0e0;
      border-radius: 10px;
      font-size: 15px;
      font-family: inherit;
      transition: all 0.3s ease;
    }

    input:focus {
      border-color: #800000;
      outline: none;
      box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
    }

    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #b00000 0%, #800000 100%);
      border: none;
      border-radius: 10px;
      color: #fff;
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      margin-top: 10px;
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(128, 0, 0, 0.3);
    }

    button:active {
      transform: translateY(0);
    }

    .footer {
      text-align: center;
      margin-top: 25px;
      font-size: 13px;
      color: #999;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        width: 95%;
        max-width: 400px;
      }

      .left {
        height: 200px;
      }

      .left-content {
        padding: 30px;
      }

      .left-content h1 {
        font-size: 1.8rem;
      }

      .right {
        padding: 40px 30px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left">
      <div class="left-content">
        <h1>TaKi Admin</h1>
        <p>Kelola kedai Anda dengan mudah dan efisien</p>
      </div>
    </div>
    
    <div class="right">
      <div class="logo">
        <img src="img/LogoTaki.png" alt="Logo TaKi" />
        <h2>TaKi ID</h2>
      </div>

      <h3>Selamat Datang Kembali!</h3>
      <p class="sub">Silakan login untuk mengakses dashboard admin</p>

      <?php if ($error): ?>
      <div class="errorbox">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span><?= $error ?></span>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="input-group">
          <label for="email">Email atau Username</label>
          <input type="text" id="email" name="email" placeholder="Masukkan email atau username" required autofocus>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Masukkan password" required>
        </div>

        <button type="submit">Masuk ke Dashboard</button>
      </form>

      <div class="footer">© 2025 TaKi Admin Panel. All rights reserved.</div>
    </div>
  </div>
</body>
</html>