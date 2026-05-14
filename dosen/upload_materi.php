<?php
include '../header.php';
include '../koneksi.php';


if (isset($_POST['hapus']) && !empty($_POST['pilih'])) {
    foreach ($_POST['pilih'] as $id) {
        $q = mysqli_query($conn,"SELECT file FROM materi WHERE id='$id'");
        $d = mysqli_fetch_assoc($q);
        if ($d) {
            @unlink("../materi/".$d['file']);
            mysqli_query($conn,"DELETE FROM materi WHERE id='$id'");
        }
    }
}


$edit_id = null;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $q = mysqli_query($conn,"SELECT * FROM materi WHERE id='$edit_id' AND matkul_id='$_SESSION[matkul_id]'");
    $edit_data = mysqli_fetch_assoc($q);
}

if (isset($_POST['update_judul'])) {
    $new_judul = $_POST['judul'];
    $materi_id = $_POST['materi_id'];
    mysqli_query($conn,"UPDATE materi SET judul='$new_judul' WHERE id='$materi_id' AND matkul_id='$_SESSION[matkul_id]'");
    header("Location: upload_materi.php");
    exit;
}


if (isset($_POST['upload'])) {
    $judul = $_POST['judul'];

    if (!is_dir("../materi")) {
        mkdir("../materi");
    }

    $nama = time().'_'.str_replace(' ','_',$_FILES['file']['name']);
    move_uploaded_file($_FILES['file']['tmp_name'], "../materi/".$nama);

    mysqli_query($conn,"INSERT INTO materi (judul,file,matkul_id) VALUES ('$judul','$nama','$_SESSION[matkul_id]')");

    $nama = $_SESSION['nama'];
    $id = $_SESSION['id'];
    $role = $_SESSION['role'];

    $aktivitas = "Mengupload materi";

    tambahAktivitas(
        $conn,
        $id,
        $nama,
        $role,
        $aktivitas
    );
}

$data = mysqli_query($conn,"SELECT * FROM materi WHERE matkul_id='$_SESSION[matkul_id]' ORDER BY id DESC");
?>

<style>
.container { max-width:1000px; margin:auto; }
h2 { margin-bottom:15px; }

.card-box {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
    margin-bottom:25px;
}

.form-group { margin-bottom:15px; }
input[type=text], input[type=file] {
    width:100%; padding:10px;
    border-radius:5px; border:1px solid #ccc;
}

.btn {
    padding:10px 18px;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-weight:bold;
}
.btn-primary { background:#4f73df; color:white; }
.btn-danger { background:#e74a3b; color:white; }

table {
    width:100%;
    border-collapse:collapse;
}
th {
    background:#4f73df;
    color:white;
    padding:12px;
}
td {
    padding:10px;
    border-bottom:1px solid #ddd;
    text-align:center;
}
td a { color:#4f73df; font-weight:bold; }

</style>

<div class="content">
<div class="container">

<div class="card-box">
<h2>� Upload Materi</h2>
<form method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label>Judul Materi</label>
        <input type="text" name="judul" required>
    </div>
    <div class="form-group">
        <label>File</label>
        <input type="file" name="file" required>
    </div>
    <button class="btn btn-primary" name="upload">⬆ Upload</button>
</form>
</div>
<?php if ($edit_data) { ?>
<div class="card-box">
<h2>✏️ Edit Judul Materi</h2>
<form method="post">
    <input type="hidden" name="materi_id" value="<?= $edit_data['id'] ?>">
    <div class="form-group">
        <label>Judul Materi</label>
        <input type="text" name="judul" value="<?= $edit_data['judul'] ?>" required>
    </div>
    <button class="btn btn-primary" name="update_judul">💾 Update</button>
    <a href="upload_materi.php" class="btn btn-primary" style="background:#999; text-decoration:none;">Batal</a>
</form>
</div>
<?php } ?>
<div class="card-box">
<h2>📂 Daftar Materi</h2>
<form method="post">
<table>
<tr>
    <th>Pilih</th>
    <th>No</th>
    <th>Judul</th>
    <th>File</th>
    <th>Aksi</th>
</tr>

<?php $no=1; while($m=mysqli_fetch_assoc($data)){ ?>
<tr>
    <td><input type="checkbox" name="pilih[]" value="<?= $m['id']; ?>"></td>
    <td><?= $no++; ?></td>
    <td><?= $m['judul']; ?></td>
    <td><a href="../materi/<?= $m['file']; ?>" target="_blank">Download</a></td>
    <td><a href="?edit=<?= $m['id']; ?>" style="color:#1cc88a;">✏️ Edit</a></td>
</tr>
<?php } ?>
</table>

<br>
<button class="btn btn-danger" name="hapus"
onclick="return confirm('Hapus materi terpilih?')">
🗑 Hapus Materi Terpilih
</button>
</form>
</div>

<div style="margin-top:20px;">
    <a href="index.php" class="btn btn-danger">Kembali</a>
</div>
</div>

</body>
</html>
