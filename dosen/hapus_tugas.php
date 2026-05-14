<?php
include '../koneksi.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];


    $q = mysqli_query($conn,"
        SELECT file FROM tugas_kumpul WHERE tugas_id='$id'
    ");
    while($d=mysqli_fetch_assoc($q)){
        $file = "../tugas/".$d['file'];
        if(file_exists($file)){
            unlink($file);
        }
    }


    mysqli_query($conn,"DELETE FROM tugas_kumpul WHERE tugas_id='$id'");
    mysqli_query($conn,"DELETE FROM tugas WHERE id='$id'");
}

header("Location: tugas.php");
exit;
