<?php
include '../header.php';
include '../koneksi.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID tidak ditemukan";
    exit;
}

$q = mysqli_query($conn,"SELECT * FROM users WHERE id='$id'");
$user = mysqli_fetch_assoc($q);
if (!$user) {
    echo "Data user tidak ditemukan";
    exit;
}

$prodi  = mysqli_query($conn,"SELECT * FROM prodi");
$matkul = mysqli_query($conn,"SELECT * FROM matkul");

if (isset($_POST['update'])) {
    $nama      = $_POST['nama'];
    $username  = $_POST['username'];
    $prodi_id  = $_POST['prodi_id'] ?: NULL;
    $matkul_id = $_POST['matkul_id'] ?: NULL;

    $sql = "
        UPDATE users SET
            nama='$nama',
            username='$username',
            prodi_id=" . ($prodi_id ? "'$prodi_id'" : "NULL") . ",
            matkul_id=" . ($matkul_id ? "'$matkul_id'" : "NULL") . "
        WHERE id='$id'
    ";
    mysqli_query($conn,$sql);

    echo "<script>alert('Data berhasil diupdate');location='index.php';</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Data User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
        }
        .container {
            width: 420px;
            margin: 50px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,.1);
        }
        h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            font-size: 14px;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #0d6efd;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #0d6efd;
            border: none;
            color: white;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0b5ed7;
        }
        .back {
            text-align: center;
            margin-top: 15px;
        }
        .back a {
            display: inline-block;
            background: #e74a3b;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
        }
        .back a:hover {
            background: #d63a2b;
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Edit Data User</h3>

    <form method="post">
        <label>Nama</label>
        <input type="text" name="nama" value="<?= $user['nama'] ?>" required>

        <label>Username</label>
        <input type="text" name="username" value="<?= $user['username'] ?>" required>

        <label>Prodi</label>
        <select name="prodi_id">
            <option value="">- Pilih Prodi -</option>
            <?php while($p=mysqli_fetch_assoc($prodi)){ ?>
                <option value="<?= $p['id'] ?>"
                    <?= ($user['prodi_id']==$p['id'])?'selected':'' ?>>
                    <?= $p['nama_prodi'] ?>
                </option>
            <?php } ?>
        </select>

        <?php if ($user['role']=='dosen'){ ?>
            <label>Mata Kuliah</label>
            <select name="matkul_id">
                <option value="">- Pilih Matkul -</option>
                <?php while($m=mysqli_fetch_assoc($matkul)){ ?>
                    <option value="<?= $m['id'] ?>"
                        <?= ($user['matkul_id']==$m['id'])?'selected':'' ?>>
                        <?= $m['nama_matkul'] ?>
                    </option>
                <?php } ?>
            </select>
        <?php } ?>

        <button type="submit" name="update">💾 Update Data</button>
    </form>

    <div class="back">
        <a href="index.php">Kembali</a>
    </div>
</div>

</body>
</html>
