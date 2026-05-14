<?php
include '../header.php';
include '../koneksi.php';

$nama = $_SESSION['nama'];

$materi = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM materi WHERE matkul_id='$_SESSION[matkul_id]'");
$jm = mysqli_fetch_assoc($materi);

$tugas = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM tugas WHERE matkul_id='$_SESSION[matkul_id]'");
$jt = mysqli_fetch_assoc($tugas);

$presensi = mysqli_query($conn, "SELECT * FROM presensi 
                                WHERE status='buka' AND matkul_id='$_SESSION[matkul_id]'
                                ORDER BY id DESC LIMIT 1");
$pres = mysqli_fetch_assoc($presensi);
?>

<style>
.cards {
    display:grid;
    grid-template-columns: repeat(auto-fit, minmax(220px,1fr));
    gap:20px;
}
.card-box {
    padding:20px;
    border-radius:10px;
    color:white;
}
.blue { background:#4f73df; }
.green { background:#1cc88a; }
.orange { background:#f6c23e; color:#000; }

.card-box h2 { margin:0; font-size:28px; }
.card-box p { margin:5px 0 15px; }

.card-box a {
    color:white;
    text-decoration:none;
    font-weight:bold;
}

.menu-links {
    margin-top: 25px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.menu-link {
    display: inline-block;
    padding: 10px 15px;
    background: #4f73df;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.menu-link:hover {
    background: #3b5ed7;
}
</style>

<div class="content">
<h2>🎓 Dashboard Mahasiswa</h2>
<p>Selamat datang, <b><?= $nama; ?></b></p>

<div class="cards">

    <div class="card-box blue">
        <h2><?= $jm['jml']; ?></h2>
        <p>Materi Perkuliahan</p>
        <a href="materi.php">Lihat Materi →</a>
    </div>

    <div class="card-box green">
        <h2><?= $jt['jml']; ?></h2>
        <p>Tugas</p>
        <a href="tugas.php">Lihat Tugas →</a>
    </div>

    <div class="card-box orange">
        <h2><?= $pres ? 'BUKA' : 'TUTUP'; ?></h2>
        <p>Status Presensi</p>
        <a href="presensi.php">Isi Presensi →</a>
    </div>

</div>

<div class="menu-links">
    <a href="../tentang.php" class="menu-link">ℹ️ Tentang Kami</a>
    <a href="pembayaran.php" class="menu-link">💳 Pembayaran</a>
</div>

</body>
</html>
