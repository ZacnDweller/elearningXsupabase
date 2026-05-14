<?php
include '../koneksi.php';
include '../header.php';

$id = $_GET['id'];

$data = mysqli_fetch_array(mysqli_query($conn,"
SELECT *
FROM pengumpulan_tugas
WHERE id='$id'
"));

if(!$data){
    die("Data tidak ditemukan");
}

if(isset($_POST['update'])){

    $file  = $_FILES['file']['name'];
    $tmp   = $_FILES['file']['tmp_name'];
    $size  = $_FILES['file']['size'];

    $allowed = ['pdf','doc','docx','zip'];

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed)){

        echo "
        <script>
            alert('Format file tidak didukung');
        </script>
        ";

    }
    elseif($size > 5 * 1024 * 1024){

        echo "
        <script>
            alert('Ukuran file maksimal 5MB');
        </script>
        ";

    }
    else{

        $nama_file = time().'_'.$file;

        move_uploaded_file(
            $tmp,
            "../upload/tugas/".$nama_file
        );

        $lama = "../upload/tugas/".$data['file'];

        if(file_exists($lama)){
            unlink($lama);
        }

        mysqli_query($conn,"
        UPDATE pengumpulan_tugas
        SET file='$nama_file'
        WHERE id='$id'
        ");

        echo "
        <script>
            alert('Tugas berhasil diupdate');
            window.location='tugas.php';
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Edit Tugas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-body">

            <h3 class="mb-4">
                Edit Upload Tugas
            </h3>

            <form method="POST"
                  enctype="multipart/form-data">

                <div class="mb-3">

                    <label class="form-label">
                        File Lama
                    </label>

                    <br>

                    <a href="../upload/tugas/<?= $data['file'] ?>"
                       class="btn btn-success btn-sm">

                        Download File

                    </a>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Upload File Baru
                    </label>

                    <input type="file"
                           name="file"
                           class="form-control"
                           required>

                    <small class="text-muted">

                        PDF, DOC, DOCX, ZIP
                        | Max 5MB

                    </small>

                </div>

                <button type="submit"
                        name="update"
                        class="btn btn-primary">

                    Update Tugas

                </button>

                <a href="tugas.php"
                   class="btn btn-secondary">

                    Kembali

                </a>

            </form>

        </div>

    </div>

</div>

</body>
</html>
