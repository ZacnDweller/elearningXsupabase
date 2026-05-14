<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'koneksi.php';
include 'fungsi.php';

$website_settings = getWebsiteSettings($conn);

if (isset($_POST['login'])) {
    $u = $_POST['username'];
    $p = md5($_POST['password']);

    $q = mysqli_query($conn, "SELECT * FROM users WHERE username='$u' AND password='$p'");
    $data = mysqli_fetch_assoc($q);

    if ($data) {
        $_SESSION['login'] = true;
        $_SESSION['id'] = $data['id'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['prodi_id'] = $data['prodi_id'];
        $_SESSION['matkul_id'] = $data['matkul_id'];

        $ket = "Login ke sistem";
        tambahAktivitas(
            $conn,
            $data['id'],
            $data['nama'],
            $data['role'],
            $ket
        );

        if ($data['role'] == 'admin') header("Location: admin/index.php");
        if ($data['role'] == 'dosen') header("Location: dosen/index.php");
        if ($data['role'] == 'mahasiswa') header("Location: mahasiswa/pilih_matkul.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $website_settings['nama_website'] ?> - Login</title>
    <style>
        body {
            background: linear-gradient(135deg, #4f73df, #1cc88a);
            font-family: Arial, Helvetica, sans-serif;
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-box {
            background: white;
            width: 350px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }
        .login-box h2 {
            margin-bottom: 20px;
            color: #4f73df;
        }
        .login-box input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .login-box button {
            width: 100%;
            padding: 10px;
            background: #4f73df;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .login-box button:hover {
            background: #3b5ed7;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2><?= $website_settings['nama_website'] ?></h2>

    <?php if (isset($error)) { ?>
        <div class="error"><?= $error; ?></div>
    <?php } ?>

    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button name="login">Login</button>
    </form>
</div>

</body>
</html>
