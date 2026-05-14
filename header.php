<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}

include_once dirname(__FILE__) . '/koneksi.php';
include_once dirname(__FILE__) . '/fungsi.php';
$website_settings = getWebsiteSettings($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{margin:0;font-family:Arial;background:#f4f6f9}
.navbar{
    background:#4f73df;
    color:white;
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
}
.content{padding:30px}
.card{background:white;padding:20px;border-radius:8px}
a{color:white;text-decoration:none}
</style>
</head>
<body>

<div class="navbar">
    <div><b><?= $website_settings['nama_website'] ?></b></div>
    <div>
        Login sebagai <b><?= $_SESSION['nama']; ?></b> |
        <a href="../logout.php">Logout</a>
    </div>
</div>
