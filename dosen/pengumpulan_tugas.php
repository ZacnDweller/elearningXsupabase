<?php
include '../header.php';
include '../koneksi.php';

$where = "";

if(isset($_GET['id'])){

    $id_tugas = mysqli_real_escape_string(
        $conn,
        $_GET['id']
    );

    $where = " AND tugas.id = '$id_tugas' ";
}

$q = mysqli_query($conn,"
SELECT 
    tugas_kumpul.mahasiswa,
    tugas_kumpul.file,
    tugas_kumpul.tanggal,
    tugas.judul,
    matkul.nama_matkul
FROM tugas_kumpul
JOIN tugas 
    ON tugas_kumpul.tugas_id = tugas.id
JOIN matkul
    ON tugas.matkul_id = matkul.id
WHERE tugas.matkul_id = '$_SESSION[matkul_id]' $where
ORDER BY tugas_kumpul.tanggal DESC
");

$title = "📥 Pengumpulan Tugas Mahasiswa";

if(isset($_GET['id'])){

    $task = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT judul
    FROM tugas
    WHERE id='$id_tugas'
    "));

    if($task){

        $title = "Pengumpulan Tugas: " . $task['judul'];
    }
}
?>

<style>

.title{
    font-size:24px;
    margin-bottom:15px;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
}

th, td{
    padding:12px;
    border-bottom:1px solid #ddd;
}

th{
    background:#f8f9fc;
    text-align:left;
}

.download{
    background:#1cc88a;
    color:white;
    padding:6px 12px;
    border-radius:5px;
    text-decoration:none;
    font-weight:bold;
}

.empty{
    text-align:center;
    padding:30px;
    color:#999;
}

.btn-danger {
    display:inline-block;
    margin-top:15px;
    text-decoration:none;
    background:#e74a3b;
    color:white;
    padding:10px 18px;
    border-radius:5px;
}

</style>

<div class="content">

    <div class="title">
        <?= $title; ?>
    </div>

    <div class="card">

        <table>

            <tr>

                <th>No</th>
                <th>Mahasiswa</th>
                <th>Mata Kuliah</th>
                <th>Judul Tugas</th>
                <th>File</th>
                <th>Tanggal</th>

            </tr>

            <?php

            $no = 1;

            if(mysqli_num_rows($q) == 0){

                echo "
                <tr>

                    <td colspan='6'
                        class='empty'>

                        Belum ada pengumpulan tugas

                    </td>

                </tr>
                ";
            }

            while($d = mysqli_fetch_assoc($q)){

            ?>

            <tr>

                <td>
                    <?= $no++ ?>
                </td>

                <td>
                    <?= $d['mahasiswa'] ?>
                </td>

                <td>
                    <?= $d['nama_matkul'] ?>
                </td>

                <td>
                    <?= $d['judul'] ?>
                </td>

                <td>

                    <a href="../upload/tugas/<?= $d['file'] ?>"
                       class="download">

                        Download

                    </a>

                </td>

                <td>
                    <?= $d['tanggal'] ?>
                </td>

            </tr>

            <?php } ?>

        </table>

        <a href="tugas.php"
           class="btn-danger">

            Kembali

        </a>

    </div>

</div>

</body>
</html>