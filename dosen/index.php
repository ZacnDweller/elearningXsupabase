<?php
include '../header.php';
include '../koneksi.php';


$q = mysqli_query($conn,"
SELECT 
    tugas_kumpul.mahasiswa,
    tugas_kumpul.file,
    tugas_kumpul.tanggal,
    tugas.judul
FROM tugas_kumpul
JOIN tugas 
    ON tugas_kumpul.tugas_id = tugas.id
ORDER BY tugas_kumpul.tanggal DESC
");


$rekap = mysqli_query($conn,
"SELECT 
    pm.mahasiswa,
    SUM(CASE WHEN pm.keterangan = 'hadir' THEN 1 ELSE 0 END) AS hadir,
    SUM(CASE WHEN pm.keterangan = 'sakit' THEN 1 ELSE 0 END) AS sakit,
    SUM(CASE WHEN pm.keterangan = 'izin' THEN 1 ELSE 0 END) AS izin,
    COUNT(*) AS total
FROM presensi_mahasiswa pm
JOIN presensi p ON pm.presensi_id = p.id
WHERE p.matkul_id = '$_SESSION[matkul_id]'
GROUP BY pm.mahasiswa
ORDER BY total DESC
");
?>

<style>
.dashboard-title {
    font-size: 26px;
    margin-bottom: 10px;
}

.menu {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}
.menu-box {
    flex: 1;
    background: #1cc88a;
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-decoration: none;
    min-height: 80px; 
}
.menu-box.blue { background:#4f73df; }
.menu-box.orange { background:#f6c23e; color:black; }
.menu-box h3 { margin:0; }
.menu-box p { margin-top:5px; }

table {
    width:100%;
    border-collapse: collapse;
    margin-top:20px;
    margin-bottom: 40px; 
}

table th {
    background:#4f73df;
    color:white;
    padding:10px;
    text-align: left;
}

table td {
    padding:8px;
    border-bottom:1px solid #ddd;
}

.download {
    background:#1cc88a;
    color:white;
    padding:5px 12px;
    border-radius:5px;
    text-decoration:none;
    font-weight:bold;
}
</style>

<div class="content">
<div class="card">

    <div class="dashboard-title">📊 Dashboard Dosen</div>
    <p>Login sebagai: <b><?= $_SESSION['nama']; ?></b></p>

    <div class="menu">
        <a href="upload_materi.php" class="menu-box blue">
            <h3>📘 Materi</h3>
            <p>Upload & lihat materi</p>
        </a>
        <a href="tugas.php" class="menu-box">
            <h3>📝 Tugas</h3>
            <p>Kelola tugas</p>
        </a>
        <a href="presensi.php" class="menu-box orange">
            <h3>📅 Presensi</h3>
            <p>Kelola absensi</p>
        </a>
        <a href="../tentang.php" class="menu-box" style="background:#1f9cf0;">
            <h3>ℹ️ Tentang Kami</h3>
            <p>Informasi website</p>
        </a>
    </div>


    <h3>📥 Pengumpulan Tugas Mahasiswa</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Mahasiswa</th>
            <th>Judul Tugas</th>
            <th>File</th>
            <th>Tanggal Upload</th>
        </tr>

        <?php
        if ($q && mysqli_num_rows($q) > 0) {
            $no=1;
            while($d=mysqli_fetch_assoc($q)){
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $d['mahasiswa']; ?></td>
            <td><?= $d['judul']; ?></td>
            <td>
                <a class="download" href="../upload/tugas/<?= $d['file']; ?>" target="_blank">
                    Download
                </a>
            </td>
            <td><?= $d['tanggal']; ?></td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='5' align='center'>❌ Belum ada tugas dikumpulkan</td></tr>";
        }
        ?>
    </table>


    <h3>📅 Rekap Presensi Mahasiswa</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Nama Mahasiswa</th>
            <th>Hadir</th>
            <th>Sakit</th>
            <th>Izin</th>
            <th>Total</th>
        </tr>

        <?php
        if ($rekap && mysqli_num_rows($rekap) > 0) {
            $no=1;
            while($r=mysqli_fetch_assoc($rekap)){
        ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $r['mahasiswa']; ?></td>
            <td><?= $r['hadir']; ?></td>
            <td><?= $r['sakit']; ?></td>
            <td><?= $r['izin']; ?></td>
            <td><?= $r['total']; ?></td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='6' align='center'>❌ Belum ada presensi</td></tr>";
        }
        ?>
    </table>

</div>
</div>

</body>
</html>
