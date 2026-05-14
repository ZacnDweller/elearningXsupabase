<?php
include '../koneksi.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM users WHERE id='$id'
"));

if(isset($_POST['reset'])){

    $passwordBaru = md5($_POST['password']);

    mysqli_query($conn,"
    UPDATE users 
    SET password='$passwordBaru'
    WHERE id='$id'
    ");

    echo "
    <script>
        alert('Password berhasil direset');
        window.location='index.php';
    </script>
    ";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>

    <style>

    body{
        font-family:Arial;
        background:#f5f5f5;
    }

    .box{
        width:400px;
        margin:80px auto;
        background:white;
        padding:25px;
        border-radius:10px;
    }

    input{
        width:100%;
        padding:10px;
        margin-top:10px;
    }

    button{
        margin-top:15px;
        padding:10px;
        width:100%;
        background:#4f73df;
        color:white;
        border:none;
        border-radius:5px;
    }

    .back-button {
        display: inline-block;
        margin-top: 15px;
        padding: 10px 20px;
        background: #e74a3b;
        color: white;
        border-radius: 5px;
        text-decoration: none;
    }

    .back-button:hover {
        background: #d63a2b;
    }

    </style>
</head>
<body>

<div class="box">

    <h2>Reset Password</h2>

    <p>
        User:
        <b><?= $data['nama'] ?></b>
    </p>

    <form method="POST">

        <input type="password"
               name="password"
               placeholder="Password baru"
               required>

        <button name="reset">
            Reset Password
        </button>

    </form>

    <a href="index.php" class="back-button">Kembali</a>

</div>

</body>
</html>