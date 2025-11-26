<?php
include 'config.php';

$message = '';
$alertClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah email sudah ada
    $check = $conn->prepare("SELECT * FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $message = "Email sudah terpakai, silakan gunakan email lain.";
        $alertClass = "error";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
        if ($stmt->execute()) {
            $message = "Akun berhasil dibuat! Silakan login untuk melanjutkan.";
            $alertClass = "success";
            header("Refresh:3; url=index.html");
        } else {
            $message = "Terjadi kesalahan. Coba lagi nanti.";
            $alertClass = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Register Akun</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
        rel="stylesheet" />
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

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.3s ease;
        }

        .alert.success {
            background: #e9fce9;
            color: #2e7d32;
            border-left: 6px solid #4caf50;
        }

        .alert.error {
            background: #fdecea;
            color: #b71c1c;
            border-left: 6px solid #f44336;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <div class="register-container">
        <h2>Daftar Akun</h2>
        <p class="subtext">Lengkapi informasi Anda untuk membuat akun baru.</p>

        <?php if (!empty($message)): ?>
            <div class="alert <?= $alertClass ?>"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Username <span style="color: red">*</span></label>
                <input type="text" id="name" name="name" placeholder="Masukkan nama anda" required />
            </div>

            <div class="form-group">
                <label for="email">Email <span style="color: red">*</span></label>
                <input type="email" id="email" name="email" placeholder="Masukkan email anda" required />
            </div>

            <div class="form-group">
                <label for="phone">Nomor Telepon  <span style="color: red">*</span></label>
                <input type="tel" id="phone" name="phone" placeholder="Masukkan nomor telepon anda" required />
            </div>

            <div class="form-group">
                <label for="password">Password <span style="color: red">*</span></label>
                <input type="password" id="password" name="password" placeholder="Masukkan password anda" required />
            </div>

            <button type="submit" class="submit-btn">Daftar Sekarang</button>
            <a href="index.html" class="back-link">Kembali ke Beranda</a>
        </form>
    </div>

</body>

</html>