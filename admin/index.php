<?php
include '../header.php';
include '../koneksi.php';

$namaLogin = $_SESSION['nama'] ?? 'Admin';


$qMahasiswa = mysqli_query($conn,"
SELECT 
    u.id,
    u.nama,
    u.username,
    p.nama_prodi
FROM users u
LEFT JOIN prodi p ON u.prodi_id = p.id
WHERE u.role = 'mahasiswa'
");

$qDosen = mysqli_query($conn,"
SELECT 
    u.id,
    u.nama,
    u.username,
    p.nama_prodi,
    m.nama_matkul
FROM users u
LEFT JOIN prodi p ON u.prodi_id = p.id
LEFT JOIN matkul m ON u.matkul_id = m.id
WHERE u.role = 'dosen'
");

$admin = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM users WHERE role='admin'"));
$dosen = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM users WHERE role='dosen'"));
$mhs   = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM users WHERE role='mahasiswa'"));


$dataLabel = [];
$dataTotal = [];

$qGrafik = mysqli_query($conn,"
SELECT 
    MONTH(created_at) as bulan,
    COUNT(id) as total
FROM users
WHERE role='mahasiswa'
GROUP BY MONTH(created_at)
ORDER BY MONTH(created_at)
");

$namaBulan = [
    1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',
    5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',
    9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
];

while($g = mysqli_fetch_assoc($qGrafik)){

    $dataLabel[] = $namaBulan[$g['bulan']];
    $dataTotal[] = $g['total'];
}


$qAktivitas = mysqli_query($conn,"
SELECT *
FROM aktivitas
ORDER BY id DESC
LIMIT 5
");
?>

<style>

.dashboard-title {
    font-size:26px;
    margin-bottom:10px;
}

.stats {
    display:flex;
    gap:20px;
    margin:20px 0;
    flex-wrap:wrap;
}

.stat-box {
    flex:1;
    background:#4f73df;
    color:white;
    padding:20px;
    border-radius:10px;
    min-width:200px;
}

.stat-box.green {
    background:#1cc88a;
}

.stat-box.orange {
    background:#f6c23e;
}

.stat-box h3 {
    margin:0;
    font-size:18px;
}

.stat-box p {
    font-size:28px;
    margin:5px 0 0;
}

.menu {
    margin:20px 0;
}

.menu a {
    display:inline-block;
    padding:10px 15px;
    background:#4f73df;
    color:white;
    border-radius:5px;
    text-decoration:none;
    margin-right:10px;
}

.menu a:hover {
    background:#3b5ed7;
}

.table-wrapper {
    margin-top:25px;
}

table {
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:8px;
    overflow:hidden;
}

table th {
    background:#4f73df;
    color:white;
    padding:12px;
    text-align:left;
}

table td {
    padding:10px 12px;
    border-bottom:1px solid #eee;
}

table tr:hover {
    background:#f5f7ff;
}

.hapus {
    color:red;
    text-decoration:none;
    font-weight:bold;
}

/* GRAFIK */

.dashboard-grid{
    display:flex;
    gap:20px;
    margin-top:30px;
    flex-wrap:wrap;
}

.card-box{
    flex:1;
    min-width:350px;
    background:white;
    border-radius:10px;
    padding:20px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}

.aktivitas-item{
    padding:12px 0;
    border-bottom:1px solid #eee;
}

.aktivitas-time{
    float:right;
    color:gray;
    font-size:13px;
}

.aktivitas-item{
    padding:12px;
    border-bottom:1px solid #eee;
    font-size:15px;
}

.aktivitas-time{
    float:right;
    color:gray;
    font-size:12px;
}
</style>

<div class="content">
<div class="card">

    <div class="dashboard-title">
        Dashboard Admin
    </div>

    <p>
        Selamat datang,
        <b><?= $namaLogin ?></b>
    </p>

    <!-- STAT -->
    <div class="stats">

        <div class="stat-box">
            <h3>Admin</h3>
            <p><?= $admin ?></p>
        </div>

        <div class="stat-box green">
            <h3>Dosen</h3>
            <p><?= $dosen ?></p>
        </div>

        <div class="stat-box orange">
            <h3>Mahasiswa</h3>
            <p><?= $mhs ?></p>
        </div>

    </div>

 
    <div class="menu">

        <a href="tambah_user.php">
            ➕ Tambah Akun
        </a>

        <a href="pengaturan.php">
            ⚙️ Pengaturan Website
        </a>

        <a href="kelola_pembayaran.php">
            💳 Kelola Pembayaran
        </a>

        <a href="../tentang.php">
            ℹ️ Tentang Kami
        </a>

    </div>

   
    <div class="dashboard-grid">

        <!-- GRAFIK -->
        <div class="card-box">

            <h3>Grafik Mahasiswa Aktif</h3>

            <canvas id="grafikMahasiswa"></canvas>

        </div>

  
        <div class="card-box">

            <h3>Aktivitas Terbaru</h3>

            <?php while($a = mysqli_fetch_assoc($qAktivitas)) { ?>

            <div class="aktivitas-item">
                🔵
                <b><?= $a['nama'] ?></b>
                (<?= $a['role'] ?>)
                -
                <?= $a['aktivitas'] ?>

                <span class="aktivitas-time">
                    <?= date('d M Y H:i', strtotime($a['created_at'])) ?>
                </span>
            </div>

            <?php } ?>

        </div>

    </div>


    <div class="table-wrapper">

        <h3>Data Dosen</h3>

        <table>

            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Prodi</th>
                <th>Mata Kuliah</th>
                <th>Aksi</th>
            </tr>

            <?php $no=1; while($d=mysqli_fetch_assoc($qDosen)){ ?>

            <tr>

                <td><?= $no++ ?></td>
                <td><?= $d['nama'] ?></td>
                <td><?= $d['username'] ?></td>
                <td><?= $d['nama_prodi'] ?? '-' ?></td>
                <td><?= $d['nama_matkul'] ?? '-' ?></td>

                <td>

                    <a href="edit_user.php?id=<?= $d['id'] ?>">
                        ✏️ Edit
                        | 
                    </a>

                    | 

                    <a href="reset_password.php?id=<?= $d['id'] ?>">
                    🔑 Reset Password
                    </a>

                    |

                    <a class="hapus"
                       href="hapus_user.php?id=<?= $d['id'] ?>"
                       onclick="return confirm('Hapus akun dosen ini?')">

                        🗑 Hapus

                    </a>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>


    <div class="table-wrapper">

        <h3>Data Mahasiswa</h3>

        <table>

            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Prodi</th>
                <th>Aksi</th>
            </tr>

            <?php $no=1; while($m=mysqli_fetch_assoc($qMahasiswa)){ ?>

            <tr>

                <td><?= $no++ ?></td>
                <td><?= $m['nama'] ?></td>
                <td><?= $m['username'] ?></td>
                <td><?= $m['nama_prodi'] ?? '-' ?></td>

                <td>

                    <a href="edit_user.php?id=<?= $m['id'] ?>">
                        ✏️ Edit
                    </a>

                    |
                    <a href="reset_password.php?id=<?= $m['id'] ?>">
                    🔑 Reset Password
                    </a>

                    |

                    <a class="hapus"
                       href="hapus_user.php?id=<?= $m['id'] ?>"
                       onclick="return confirm('Hapus akun mahasiswa ini?')">

                        🗑 Hapus

                    </a>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx = document.getElementById('grafikMahasiswa');

new Chart(ctx, {

    type: 'line',

    data: {

        labels: <?= json_encode($dataLabel) ?>,

        datasets: [{
            label: 'Mahasiswa Aktif',
            data: <?= json_encode($dataTotal) ?>,
            borderWidth: 3,
            tension: 0.3,
            fill: false
        }]
    },

    options: {
        responsive: true
    }

});

</script>