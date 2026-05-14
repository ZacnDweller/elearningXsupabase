<?php
include '../header.php';
include '../koneksi.php';

$nama = $_SESSION['nama'];


$p = mysqli_query($conn,"
    SELECT * FROM presensi
    WHERE status='buka' AND matkul_id='$_SESSION[matkul_id]'
    ORDER BY id DESC LIMIT 1
");
$data = mysqli_fetch_assoc($p);


$cek = null;
if($data){
    $c = mysqli_query($conn,
        "SELECT * FROM presensi_mahasiswa
        WHERE presensi_id='$data[id]'
        AND mahasiswa='$nama'"
    );
    $cek = mysqli_fetch_assoc($c);
}


if($data && !$cek){
    $ket = null;
    if(isset($_POST['hadir'])) $ket = 'hadir';
    elseif(isset($_POST['sakit'])) $ket = 'sakit';
    elseif(isset($_POST['izin'])) $ket = 'izin';

    if($ket){
        mysqli_query($conn,
            "INSERT INTO presensi_mahasiswa
            (presensi_id, mahasiswa, keterangan)
            VALUES ('$data[id]', '$nama', '$ket')"
        );
        header("Location: presensi.php");
        exit;
    }

    $nama = $_SESSION['nama'];
    $id = $_SESSION['id'];
    $role = $_SESSION['role'];

    $aktivitas = "Melakukan absensi";

    tambahAktivitas(
        $conn,
        $id,
        $nama,
        $role,
        $aktivitas
    );
}
?>

<style>
.box {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}
.hadir { background:#d4edda; color:#155724; padding:10px; border-radius:5px; }
.belum { background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; }

.sakit { background:#fff3cd; color:#856404; padding:10px; border-radius:5px; }
.izin { background:#d1ecf1; color:#0c5460; padding:10px; border-radius:5px; }

button {
    background:#4f73df;
    color:white;
    border:none;
    padding:10px 25px;
    border-radius:5px;
    font-weight:bold;
}

.btn-back {
    display:inline-block;
    background:#e74a3b !important;
    color:white !important;
    padding: 12px 24px !important;
    border-radius: 8px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    margin-top: 20px !important;
}
</style>

<div class="content">
<div class="box">

<h2>📋 Presensi Mahasiswa</h2>
<p>Nama: <b><?= $nama; ?></b></p>

<?php if($data){ ?>

    <?php if($cek){ ?>
        <?php if($cek['keterangan'] == 'hadir'){ ?>
            <div class="hadir">
                ✔ Kamu sudah absen: Hadir
            </div>
        <?php } elseif($cek['keterangan'] == 'sakit'){ ?>
            <div class="sakit">
                🤒 Kamu terdaftar absen: Sakit
            </div>
        <?php } else { ?>
            <div class="izin">
                📝 Kamu terdaftar absen: Izin
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="belum">
            ❗ Presensi dibuka (<?= $data['tanggal']; ?>)
        </div>
        <form method="post">
            <button name="hadir">✔ Absen Hadir</button>
            <button name="sakit">🤒 Sakit</button>
            <button name="izin">📝 Izin</button>
        </form>
    <?php } ?>

<?php } else { ?>
    <div class="belum">
        ❌ Presensi belum dibuka
    </div>
<?php } ?>

<a href="index.php" class="btn-back">Kembali</a>

</div>
</div>

</body>
</html>
