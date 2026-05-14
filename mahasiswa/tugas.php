<?php
session_start();
include '../koneksi.php';
include '../header.php';

if(!isset($_SESSION['id'])){
    header("Location: ../login.php");
    exit;
}

$id_mahasiswa = $_SESSION['id'];

$ambilMatkul = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT matkul_id
FROM users
WHERE id='$id_mahasiswa'
"));

$matkul_id = $ambilMatkul['matkul_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tugas Mahasiswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">


<div class="card shadow-sm mb-4">

    <div class="card-body">

        <h5 class="fw-bold mb-3">

            <i class="bi bi-journal-text text-primary"></i>

            Daftar Tugas Dari Dosen

        </h5>

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>

                        <th>No</th>
                        <th>Judul Tugas</th>
                        <th>Mata Kuliah</th>
                        <th>Dosen</th>
                        <th>Deadline</th>
                        <th width="170">Aksi</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                $no = 1;

                $tugas = mysqli_query($conn,"
                SELECT 
                    t.id,
                    t.judul,
                    t.deadline,
                    m.nama_matkul,
                    u.nama as nama_dosen
                FROM tugas t
                LEFT JOIN matkul m ON t.matkul_id = m.id
                LEFT JOIN users u ON u.matkul_id = t.matkul_id
                WHERE 
                    t.matkul_id='$matkul_id'
                    AND u.role='dosen'
                ORDER BY t.id DESC
                ");

                if(mysqli_num_rows($tugas) == 0){

                    echo "
                    <tr>
                        <td colspan='6' class='text-center'>
                            Tidak ada tugas
                        </td>
                    </tr>
                    ";
                }

                while($t = mysqli_fetch_assoc($tugas)){

                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= $t['judul'] ?></td>

                        <td><?= $t['nama_matkul'] ?></td>

                        <td><?= $t['nama_dosen'] ?></td>

                        <td><?= $t['deadline'] ?></td>

                        <td>

                            <a href="u_tugas.php?id=<?= $t['id'] ?>"
                            class="btn btn-primary btn-sm">

                                <i class="bi bi-upload"></i>

                                Kumpulkan

                            </a>

                        </td>

                    </tr>


                    <div class="modal fade"
                         id="upload<?= $t['id'] ?>"
                         tabindex="-1">

                        <div class="modal-dialog">

                            <div class="modal-content">

                                <div class="modal-header">

                                    <h5 class="modal-title">

                                        Upload Tugas

                                    </h5>

                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"></button>

                                </div>

                                <form method="POST"
                                      enctype="multipart/form-data">

                                    <div class="modal-body">

                                        <input type="hidden"
                                               name="id_tugas"
                                               value="<?= $t['id'] ?>">

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Judul Tugas

                                            </label>

                                            <input type="text"
                                                   class="form-control"
                                                   value="<?= $t['judul'] ?>"
                                                   readonly>

                                        </div>

                                        <div class="mb-3">

                                            <label class="form-label">

                                                Upload File

                                            </label>

                                            <input type="file"
                                                   name="file"
                                                   class="form-control"
                                                   required>

                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="submit"
                                                name="kumpul"
                                                class="btn btn-primary">

                                            Upload

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

if(isset($_POST['kumpul'])){

    $id_tugas = $_POST['id_tugas'];

    $file     = $_FILES['file']['name'];

    $tmp      = $_FILES['file']['tmp_name'];

    $size     = $_FILES['file']['size'];

    $allowed = ['pdf','doc','docx','zip'];

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed)){

        echo "
        <div class='alert alert-danger'>
            Format file tidak didukung
        </div>
        ";

    }
    elseif($size > 5 * 1024 * 1024){

        echo "
        <div class='alert alert-danger'>
            Maksimal ukuran file 5MB
        </div>
        ";

    }
    else{

        $cek = mysqli_query($conn,"
        SELECT *
        FROM pengumpulan_tugas
        WHERE id_tugas='$id_tugas'
        AND id_mahasiswa='$id_mahasiswa'
        ");

        if(mysqli_num_rows($cek) > 0){

            echo "
            <div class='alert alert-warning'>
                Anda sudah mengumpulkan tugas ini
            </div>
            ";

        }
        else{

            $nama_file = time().'_'.$file;

            move_uploaded_file(
                $tmp,
                "../upload/tugas/".$nama_file
            );

            mysqli_query($conn,"
            INSERT INTO pengumpulan_tugas
            (
                id_tugas,
                id_mahasiswa,
                file,
                tanggal
            )
            VALUES
            (
                '$id_tugas',
                '$id_mahasiswa',
                '$nama_file',
                NOW()
            )
            ");

            echo "
            <div class='alert alert-success'>
                Tugas berhasil dikumpulkan
            </div>
            ";
        }
    }
}
?>


<div class="card shadow-sm">

    <div class="card-body">

        <h5 class="fw-bold mb-3">

            <i class="bi bi-list-task"></i>

            Tugas Saya

        </h5>

        <div class="table-responsive">

            <table class="table table-bordered align-middle">

                <thead class="table-light">

                    <tr>

                        <th>No</th>
                        <th>Judul</th>
                        <th>File</th>
                        <th>Waktu Upload</th>
                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                $no = 1;

                $data = mysqli_query($conn,"
                SELECT 
                    p.*,
                    t.judul
                FROM pengumpulan_tugas p
                LEFT JOIN tugas t
                ON p.tugas_id = t.id
                WHERE p.mahasiswa_id='$id_mahasiswa'
                ORDER BY p.tanggal DESC
                ");

                if(mysqli_num_rows($data) == 0){

                    echo "
                    <tr>
                        <td colspan='5' class='text-center'>
                            Belum ada tugas
                        </td>
                    </tr>
                    ";
                }

                while($d = mysqli_fetch_assoc($data)){

                ?>

                    <tr>

                        <td><?= $no++ ?></td>

                        <td><?= $d['judul'] ?></td>

                        <td>

                            <a href="../upload/tugas/<?= $d['file'] ?>"
                               class="btn btn-success btn-sm"
                               target="_blank">

                                Download

                            </a>

                        </td>

                        <td><?= $d['tanggal'] ?></td>

                        <td>

                            <a href="edit_tugas.php?id=<?= $d['id'] ?>"
                               class="btn btn-primary btn-sm">

                                Edit

                            </a>

                            <a href="hapus_tugas.php?id=<?= $d['id'] ?>"
                               onclick="return confirm('Yakin hapus tugas?')"
                               class="btn btn-danger btn-sm">

                                Hapus

                            </a>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-3">
    <a href="index.php" class="btn btn-danger">Kembali</a>
    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>