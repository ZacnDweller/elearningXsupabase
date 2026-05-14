<?php
include '../header.php';
include '../koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}


if (isset($_POST['pilih_matkul'])) {
    $_SESSION['matkul_id'] = $_POST['matkul_id'];
    header("Location: index.php");
    exit;
}

$prodi_id = $_SESSION['prodi_id'];


$matkul = mysqli_query($conn, "
    SELECT 
        matkul.id,
        matkul.nama_matkul,
        users.nama AS nama_dosen
    FROM matkul
    LEFT JOIN users 
        ON users.matkul_id = matkul.id 
        AND users.role = 'dosen'
    WHERE matkul.prodi_id = '$prodi_id'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pilih Mata Kuliah</title>
    <style>
        .content {
            padding: 20px;
        }
        .courses {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .course-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .course-card h3 {
            margin-bottom: 8px;
            color: #4f73df;
        }
        .course-card p {
            margin-bottom: 15px;
            color: #555;
            font-size: 14px;
        }
        .btn {
            background: #4f73df;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #3e5bbf;
        }
        .btn-danger {
            background: #e74a3b;
            color: white;
            border-radius: 5px;
            display: inline-block;
            text-decoration: none;
        }
        .btn-danger:hover {
            background: #d63a2b;
        }
    </style>
</head>
<body>

<div class="content">
    <h2>🎓 Pilih Mata Kuliah</h2>
    <p>Silakan pilih mata kuliah yang ingin Anda akses:</p>

    <div class="courses">
        <?php while ($m = mysqli_fetch_assoc($matkul)) { ?>
            <div class="course-card">
                <h3><?= $m['nama_matkul']; ?></h3>
                <p>
                    <strong>Dosen Pengampu:</strong><br>
                    <?= $m['nama_dosen'] ?? 'Belum ditentukan'; ?>
                </p>
                <form method="post">
                    <input type="hidden" name="matkul_id" value="<?= $m['id']; ?>">
                    <button type="submit" name="pilih_matkul" class="btn">
                        Pilih
                    </button>
                </form>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
