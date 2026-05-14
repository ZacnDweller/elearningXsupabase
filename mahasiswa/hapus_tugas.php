<?php
include '../koneksi.php';

$id = $_GET['id'];

$data = mysqli_fetch_array(mysqli_query($conn,"
SELECT file
FROM pengumpulan_tugas
WHERE id='$id'
"));

if($data){

    $path = "../upload/tugas/".$data['file'];

    if(file_exists($path)){
        unlink($path);
    }

    mysqli_query($conn,"
    DELETE FROM pengumpulan_tugas
    WHERE id='$id'
    ");
}

header("Location: tugas.php");
exit;
?>