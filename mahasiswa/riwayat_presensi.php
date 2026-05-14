<?php
include '../header.php';
include '../koneksi.php';

$nama = $_SESSION['nama'];
$q = mysqli_query($conn,"SELECT * FROM presensi_mahasiswa
                         WHERE mahasiswa='$nama'");
?>

<h2>📋 Riwayat Presensi</h2>

<table border="1" cellpadding="8">
<tr>
<th>No</th><th>Status</th>
</tr>

<?php $no=1; while($p=mysqli_fetch_assoc($q)){ ?>
<tr>
<td><?= $no++; ?></td>
<td><?= $p['keterangan']; ?></td>
</tr>
<?php } ?>
</table>

<a href="index.php">Kembali</a>
