<?php
include 'config.php';
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if (!$name || !$email || !$phone || !$password) {
        echo json_encode(["status" => "error", "message" => "Semua field harus diisi!"]);
        exit;
    }

    // Cek apakah email sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email sudah terpakai!"]);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $hashed);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal menyimpan data!"]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Daftar Akun - Taki ID</title>
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <style>
      :root {
        --redcolor: #bf0f0f;
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
      }

      body {
        background: #f8f8f8;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
      }

      .register-container {
        background: #fff;
        padding: 2.5rem 3rem;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        width: 480px;
        text-align: center;
      }

      h2 {
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 0.8rem;
        color: #111;
      }

      p.subtext {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 1.8rem;
      }

      form {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
      }

      .form-group {
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
      }

      label {
        font-weight: 500;
        font-size: 0.95rem;
        color: #222;
      }

      input {
        padding: 0.75rem;
        border: 1.6px solid #ddd;
        border-radius: 8px;
        font-size: 0.95rem;
        outline: none;
        transition: all 0.2s ease;
      }

      input:focus {
        border-color: #c9132c;
        box-shadow: 0 0 0 3px rgba(201, 19, 44, 0.1);
      }

      .submit-btn {
        background: #c9132c;
        border: none;
        color: #fff;
        padding: 0.9rem;
        border-radius: 8px;
        font-weight: 500;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 0.8rem;
        transition: background 0.3s;
      }

      .submit-btn:hover {
        background: #a40f24;
      }

      .back-link {
        display: block;
        text-align: center;
        margin-top: 1.2rem;
        font-size: 0.9rem;
        color: #c9132c;
        text-decoration: none;
        font-weight: 500;
      }

      .back-link:hover {
        text-decoration: underline;
      }

      /* Toast Popup */
      .toast {
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        color: #333;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
        font-size: 0.95rem;
        font-weight: 500;
        opacity: 0;
        visibility: hidden;
        transition: all 0.4s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
      }

      .toast.show {
        opacity: 1;
        visibility: visible;
        top: 60px;
      }

      .toast.success {
        border-left: 5px solid #2ecc71;
      }

      .toast.error {
        border-left: 5px solid #e74c3c;
      }

      /* Mobile */
      @media (max-width: 768px) {
        body {
          background: #fff;
        }

        .register-container {
          width: 90%;
          padding: 1.5rem;
          box-shadow: none;
          border-radius: 0;
        }
      }
    </style>
    <script src="https://unpkg.com/feather-icons"></script>
  </head>
  <body>
    <div class="register-container">
      <h2>Profil Anda</h2>
      <p class="subtext">Lengkapi informasi Anda untuk membuat akun.</p>

      <form id="registerForm" method="POST">
        <div class="form-group">
          <label for="name">Nama <span style="color: red">*</span></label>
          <input type="text" name="name" id="name" placeholder="Masukkan nama anda" required />
        </div>

        <div class="form-group">
          <label for="email">Email <span style="color: red">*</span></label>
          <input type="email" name="email" id="email" placeholder="Masukkan Email anda" required />
        </div>

        <div class="form-group">
          <label for="phone">Nomor Telepon <span style="color: red">*</span></label>
          <input type="tel" name="phone" id="phone" placeholder="Masukkan nomor telepon anda" required />
        </div>

        <div class="form-group">
          <label for="password">Password <span style="color: red">*</span></label>
          <input type="password" name="password" id="password" placeholder="Masukkan password anda" required />
        </div>

        <button type="submit" class="submit-btn">Selesai</button>
      </form>
    </div>

    <div id="toast" class="toast"></div>

    <script>
      const form = document.getElementById("registerForm");
      const toast = document.getElementById("toast");

      function showToast(message, type = "success") {
        toast.textContent = message;
        toast.className = `toast show ${type}`;
        setTimeout(() => {
          toast.classList.remove("show");
        }, 2500);
      }

      form.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch("mobileregister.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            showToast("Akun berhasil dibuat! Silakan login terlebih dahulu.", "success");
            setTimeout(() => {
              window.location.href = "index.php";
            }, 2200);
          } else {
            showToast(data.message || "Email sudah terpakai!", "error");
          }
        })
        .catch(() => {
          showToast("Terjadi kesalahan koneksi!", "error");
        });
      });
    </script>
    <script>feather.replace();</script>
  </body>
</html>
