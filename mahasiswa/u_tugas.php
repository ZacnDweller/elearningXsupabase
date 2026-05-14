<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['id'])) {
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

$query = mysqli_query($conn, "
    SELECT *
    FROM tugas
    WHERE matkul_id='$matkul_id'
    ORDER BY id DESC
");

if(isset($_GET['id'])){

    $id_tugas = mysqli_real_escape_string($conn, $_GET['id']);

    $task = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT *
        FROM tugas
        WHERE id='$id_tugas'
        AND matkul_id='$matkul_id'
    "));

    if(!$task){
        header("Location: u_tugas.php");
        exit;
    }

 
    $submitted = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT *
        FROM pengumpulan_tugas
        WHERE tugas_id='$id_tugas'
        AND mahasiswa_id='$id_mahasiswa'
    "));

   
    if(isset($_POST['submit']) && !$submitted){

        if(!empty($_FILES['file']['name'])){

            $file = $_FILES['file'];

            $ext = strtolower(
                pathinfo($file['name'], PATHINFO_EXTENSION)
            );

            $allowed = [
                'pdf',
                'doc',
                'docx',
                'txt',
                'jpg',
                'png',
                'zip'
            ];

            if(in_array($ext, $allowed)){

          
                $file_name =
                    time() . '_' .
                    $id_mahasiswa . '_' .
                    str_replace(' ', '_', $file['name']);

        
                $folder = "../upload/tugas/";

             
                if(!file_exists($folder)){
                    mkdir($folder, 0777, true);
                }

             
                $path = $folder . $file_name;

               
                if(move_uploaded_file(
                    $file['tmp_name'],
                    $path
                )){

                  
                    mysqli_query($conn, "
                    INSERT INTO pengumpulan_tugas
                    (
                        tugas_id,
                        mahasiswa_id,
                        file,
                        tanggal
                    )
                    VALUES
                    (
                        '$id_tugas',
                        '$id_mahasiswa',
                        '$file_name',
                        NOW()
                    )
                    ");


                    mysqli_query($conn, "
                    INSERT INTO tugas_kumpul
                    (
                        tugas_id,
                        mahasiswa,
                        file,
                        tanggal
                    )
                    VALUES
                    (
                        '$id_tugas',
                        '$id_mahasiswa',
                        '$file_name',
                        NOW()
                    )
                    ");

                    header("Location: u_tugas.php?id=$id_tugas");
                    exit;

                }else{

                    $error = "Gagal upload file.";

                }

            } else {

                $error = "Format file tidak didukung.";

            }

        } else {

            $error = "Pilih file untuk diupload.";

        }
    }

    $nama = $_SESSION['nama'];
    $id = $_SESSION['id'];
    $role = $_SESSION['role'];

    $aktivitas = "Mengupload tugas";

    tambahAktivitas(
        $conn,
        $id,
        $nama,
        $role,
        $aktivitas
    );

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, sans-serif;
        }

        body{
            background:#f4f6fb;
        }


        .navbar{
            width:100%;
            background:#4f6edb;
            padding:18px 35px;
            color:white;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 3px 10px rgba(0,0,0,0.1);
        }

        .logo{
            font-size:24px;
            font-weight:bold;
        }

        .btn-exit{
            background:white;
            color:#4f6edb;
            text-decoration:none;
            padding:10px 20px;
            border-radius:30px;
            font-weight:bold;
            transition:0.3s;
        }

        .btn-exit:hover{
            background:#e9edff;
        }


        .container{
            width:100%;
            max-width:1100px;
            margin:40px auto;
            padding:0 20px;
        }

        .title{
            font-size:32px;
            font-weight:bold;
            color:#222;
            margin-bottom:30px;
        }


        .card{
            background:white;
            border-radius:18px;
            padding:25px;
            margin-bottom:25px;
            box-shadow:0 5px 15px rgba(0,0,0,0.07);
            transition:0.3s;
        }

        .card:hover{
            transform:translateY(-4px);
        }

        .judul{
            font-size:24px;
            font-weight:bold;
            color:#333;
            margin-bottom:15px;
        }

        .deskripsi{
            color:#666;
            line-height:1.7;
            margin-bottom:20px;
        }

        .btn-kumpul{
            display:inline-block;
            background:#4f6edb;
            color:white;
            text-decoration:none;
            padding:12px 22px;
            border-radius:10px;
            font-weight:bold;
            transition:0.3s;
        }

        .btn-kumpul:hover{
            background:#3556c9;
        }

        .kosong{
            background:white;
            padding:40px;
            text-align:center;
            border-radius:15px;
            color:#666;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
        }

        .form-group{
            margin-bottom:15px;
        }

        .form-group label{
            display:block;
            margin-bottom:5px;
            font-weight:bold;
        }

        .form-group input[type="file"]{
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:5px;
        }

        .btn-submit{
            background:#4f6edb;
            color:white;
            border:none;
            padding:12px 22px;
            border-radius:10px;
            font-weight:bold;
            cursor:pointer;
            transition:0.3s;
        }

        .btn-submit:hover{
            background:#3556c9;
        }

        .error{
            color:red;
            margin-bottom:15px;
        }

        .submitted{
            background:#d4edda;
            color:#155724;
            padding:15px;
            border-radius:10px;
            margin-bottom:20px;
        }

        @media(max-width:768px){

            .navbar{
                flex-direction:column;
                gap:15px;
            }

            .title{
                font-size:26px;
            }

            .judul{
                font-size:20px;
            }
        }

    </style>
</head>

<body>


    <div class="navbar">

        <div class="logo">
            E-Learning
        </div>

        <a href="index.php" class="btn-exit">
            ← Exit
        </a>

    </div>


    <div class="container">

        <?php if(isset($_GET['id'])){ ?>

            <div class="title">
                Kumpulkan Tugas: <?php echo $task['judul']; ?>
            </div>

            <div class="card">

                <div class="judul">
                    <?php echo $task['judul']; ?>
                </div>

                <div class="deskripsi">
                    <?php echo $task['deskripsi']; ?>
                </div>

                <?php if($task['deadline']){ ?>
                    <p><strong>Deadline:</strong> <?php echo date('d M Y H:i', strtotime($task['deadline'])); ?></p>
                <?php } ?>

                <?php if($submitted){ ?>

                    <div class="submitted">
                        <strong>Tugas sudah dikumpulkan</strong><br>
                        File: <?php echo $submitted['file']; ?><br>
                        Waktu: <?php echo date('d M Y H:i', strtotime($submitted['tanggal'])); ?>
                    </div>

                <?php } else { ?>

                    <?php if(isset($error)){ ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php } ?>

                    <form method="post" enctype="multipart/form-data">

                        <div class="form-group">
                            <label for="file">Pilih File Tugas:</label>
                            <input type="file" name="file" id="file" required>
                            <small>Format yang didukung: PDF, DOC, DOCX, TXT, JPG, PNG, ZIP</small>
                        </div>

                        <button type="submit" name="submit" class="btn-submit">Kumpulkan Tugas</button>

                    </form>

                <?php } ?>

                <a href="u_tugas.php" class="btn-kumpul" style="margin-top:20px;">Kembali</a>

            </div>

        <?php } else { ?>

            <div class="title">
                Daftar Tugas
            </div>

            <?php if(mysqli_num_rows($query) > 0){ ?>

                <?php while($tugas = mysqli_fetch_assoc($query)){ ?>

                    <div class="card">

                        <div class="judul">
                            <?php echo $tugas['judul']; ?>
                        </div>

                        <div class="deskripsi">
                            <?php echo $tugas['deskripsi']; ?>
                        </div>

                        <?php if($tugas['deadline']){ ?>
                            <p><strong>Deadline:</strong> <?php echo date('d M Y H:i', strtotime($tugas['deadline'])); ?></p>
                        <?php } ?>

                        <a 
                            href="u_tugas.php?id=<?php echo $tugas['id']; ?>" 
                            class="btn-kumpul"
                        >
                            Kumpulkan Tugas
                        </a>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="kosong">
                    Belum ada tugas tersedia
                </div>

            <?php } ?>

        <?php } ?>

    </div>

</body>
</html>