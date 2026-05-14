<?php
include '../koneksi.php';


if (!empty($_POST['id'])) {

    foreach ($_POST['id'] as $id) {


        $q = mysqli_query($conn, "SELECT file FROM materi WHERE id='$id'");
        $data = mysqli_fetch_assoc($q);


        $file = "../materi/" . $data['file'];
        if (file_exists($file)) {
            unlink($file);
        }

 
        mysqli_query($conn, "DELETE FROM materi WHERE id='$id'");
    }
}

header("Location: upload_materi.php");
exit;
