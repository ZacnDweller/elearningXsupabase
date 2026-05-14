<?php
include '../header.php';
include '../koneksi.php';


$presensiAktif = mysqli_query($conn, "
    SELECT * FROM presensi 
    WHERE status='buka' AND matkul_id='$_SESSION[matkul_id]'
    ORDER BY id DESC 
    LIMIT 1
");
$data = mysqli_fetch_assoc($presensiAktif);


if (isset($_POST['buka'])) {
    $tgl = date('Y-m-d');


    mysqli_query($conn,"UPDATE presensi SET status='tutup' WHERE status='buka' AND matkul_id='$_SESSION[matkul_id]'");

    mysqli_query($conn,"
        INSERT INTO presensi (tanggal,status,matkul_id)
        VALUES ('$tgl','buka','$_SESSION[matkul_id]')
    ");

    header("Location: presensi.php");
    exit;
}


if (isset($_POST['tutup']) && $data) {
    $id = $data['id'];
    mysqli_query($conn,"
        UPDATE presensi 
        SET status='tutup' 
        WHERE id='$id'
    ");

    header("Location: presensi.php");
    exit;
}
?>

<style>
.title { font-size:24px; margin-bottom:10px; }
.status {
    padding:12px;
    border-radius:6px;
    margin:15px 0;
    font-weight:bold;
}
.buka { background:#d4edda; color:#155724; }
.tutup { background:#f8d7da; color:#721c24; }

button {
    padding:10px 20px;
    border:none;
    border-radius:5px;
    font-weight:bold;
    cursor:pointer;
}
.btn-buka { background:#1cc88a; color:white; }
.btn-tutup { background:#e74a3b; color:white; }

.btn-danger {
    background:#e74a3b !important;
    color:white !important;
    padding: 12px 24px !important;
    border-radius: 8px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    display: inline-block !important;
    margin-top: 20px !important;
}
</style>

<div class="content">
<div class="card">

    <div class="title">📅 Presensi Perkuliahan</div>
    <p>Login sebagai: <b><?= $_SESSION['nama']; ?></b></p>

    <?php if ($data) { ?>
        <div class="status buka">
            Presensi DIBUKA <br>
            Tanggal: <?= $data['tanggal']; ?>
        </div>

        <form method="post">
            <button name="tutup" class="btn-tutup">✖ Tutup Presensi</button>
        </form>

    <?php } else { ?>
        <div class="status tutup">
            Presensi belum dibuka
        </div>

        <form method="post">
            <button name="buka" class="btn-buka">✔ Buka Presensi</button>
        </form>
    <?php } ?>

<div style="margin-top:20px;">
    <a href="index.php" class="btn btn-danger">Kembali</a>
</div>
</div>

</body>
</html>
