<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $result = $conn->query("SELECT * FROM users WHERE email='$email'");

  if ($result->num_rows > 0) {
    $_SESSION['reset_email'] = $email;
    header("Location: resetpassword.php");
    exit();
  } else {
    $error = "Email tidak ditemukan!";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Lupa Password</title>
  <style>
    body { font-family: Poppins, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; background:#f8f8f8; }
    .box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; text-align:center; }
    input { width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:6px; }
    button { background:#bf0f0f; color:#fff; border:none; padding:10px 20px; border-radius:6px; margin-top:15px; cursor:pointer; }
    button:hover { background:#a00e0e; }
    .error { color:#c8102e; margin-top:10px; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Lupa Password</h2>
    <form method="POST">
      <input type="email" name="email" placeholder="Masukkan email anda" required>
      <button type="submit">Lanjut</button>
      <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    </form>
  </div>
</body>
</html>
