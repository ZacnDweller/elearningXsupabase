<?php
include '../header.php';
include '../koneksi.php';

$q = mysqli_query($conn, "SELECT * FROM materi WHERE matkul_id='$_SESSION[matkul_id]'");
?>

<style>
.title {
    font-size:24px;
    margin-bottom:15px;
}

.card-box {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

table {
    width:100%;
    border-collapse:collapse;
}

th, td {
    padding:12px;
    border-bottom:1px solid #ddd;
}

th {
    background:#f8f9fc;
    text-align:left;
}

.download {
    background:#1cc88a;
    color:white;
    padding:6px 12px;
    border-radius:5px;
    text-decoration:none;
    font-weight:bold;
}

.download:hover {
    opacity:0.9;
}

.back {
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

<div class="title">📘 Materi Perkuliahan</div>
<p>Daftar materi yang dibagikan dosen</p>

<div class="card-box">

<table>
    <tr>
        <th>No</th>
        <th>Judul Materi</th>
        <th>File</th>
    </tr>

    <?php if(mysqli_num_rows($q) > 0){ ?>
        <?php $no=1; while($m=mysqli_fetch_assoc($q)){ ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $m['judul']; ?></td>
            <td>
                <a class="download" 
                   href="../materi/<?= $m['file']; ?>" 
                   target="_blank">
                   ⬇ Download
                </a>
            </td>
        </tr>
        <?php } ?>
    <?php } else { ?>
        <tr>
            <td colspan="3" align="center">
                ❌ Belum ada materi yang dibagikan
            </td>
        </tr>
    <?php } ?>

</table>

</div>

<a href="index.php" class="back">Kembali</a>

</div>

</body>
</html>

