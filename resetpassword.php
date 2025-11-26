<?php
require 'config.php';
session_start();

if (!isset($_SESSION['reset_email'])) {
  header("Location: forgotpassword.php");
  exit();
}

$email = $_SESSION['reset_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newpass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
  $conn->query("UPDATE users SET password='$newpass' WHERE email='$email'");
  unset($_SESSION['reset_email']);
  echo "<script>alert('Password berhasil diubah! Silakan login kembali.'); window.location='index.html';</script>";
  exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <style>
    body { font-family: Poppins, sans-serif; display:flex; align-items:center; justify-content:center; height:100vh; background:#f8f8f8; }
    .box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 0 10px rgba(0,0,0,0.1); width:300px; text-align:center; }
    input { width:100%; padding:10px; margin-top:10px; border:1px solid #ccc; border-radius:6px; }
    button { background:#bf0f0f; color:#fff; border:none; padding:10px 20px; border-radius:6px; margin-top:15px; cursor:pointer; }
    button:hover { background:#a00e0e; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Reset Password</h2>
    <form method="POST">
      <input type="password" name="new_password" placeholder="Masukkan password baru" required>
      <button type="submit">Simpan</button>
    </form>
  </div>
</body>
</html>
