<?php
include '../header.php';
include '../koneksi.php';


if (isset($_GET['hapus'])) {
    mysqli_query($conn,"DELETE FROM tugas WHERE id='$_GET[hapus]'");
    header("Location: tugas.php");
    exit;
}

$edit_id = null;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $q = mysqli_query($conn,"SELECT * FROM tugas WHERE id='$edit_id' AND matkul_id='$_SESSION[matkul_id]'");
    $edit_data = mysqli_fetch_assoc($q);
}

if (isset($_POST['simpan'])) {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];
    $file_name = null;

    // Handle file upload
    if (!empty($_FILES['file_tugas']['name'])) {
        $file = $_FILES['file_tugas'];
        $allowed = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'png', 'zip'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if (!is_dir("../tugas_file")) {
                mkdir("../tugas_file", 0755, true);
            }
            $file_name = time() . '_' . str_replace(' ', '_', $file['name']);
            move_uploaded_file($file['tmp_name'], "../tugas_file/" . $file_name);
        }
    }

    if (isset($_POST['edit_id']) && $_POST['edit_id']) {
        $query = "UPDATE tugas SET judul='$judul', deskripsi='$deskripsi', deadline='$deadline'";
        if ($file_name) {
            $query .= ", file='$file_name'";
        }
        $query .= " WHERE id='$_POST[edit_id]' AND matkul_id='$_SESSION[matkul_id]'";
        mysqli_query($conn, $query);
    } else {
        mysqli_query($conn,"INSERT INTO tugas (judul, deskripsi, deadline, matkul_id, file)
        VALUES ('$judul', '$deskripsi', '$deadline', '$_SESSION[matkul_id]', '$file_name')");
    }
    header("Location: tugas.php");
    exit;
}

$tugas = mysqli_query($conn,"SELECT * FROM tugas WHERE matkul_id='$_SESSION[matkul_id]' ORDER BY id DESC");
?>

<style>
.container {
    max-width: 1000px;
    margin: auto;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
    margin-bottom: 30px;
}

.card h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.btn {
    background: #4f73df;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: bold;
}

.table-wrap {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background: #f8f9fc;
    padding: 14px;
    text-align: center;
    font-weight: bold;
}

tbody td {
    padding: 14px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

tbody tr:hover {
    background: #f8f9fc;
}

.aksi {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.aksi a {
    text-decoration: none;
    font-weight: bold;
    color: #4f73df;
    padding: 6px 12px;
    border-radius: 5px;
}

.aksi a.hapus {
    color: #e74a3b;
}

.btn-danger {
    background:#e74a3b !important;
    color:white !important;
}
</style>

<div class="content">
<div class="container">

<div class="card">
    <h2><?= $edit_data ? '✏️ Edit Tugas' : '➕ Tambah Tugas' ?></h2>

    <form method="post" enctype="multipart/form-data">
        <?php if ($edit_data) { ?>
            <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
        <?php } ?>
        
        <div class="form-group">
            <input type="text" name="judul" placeholder="Judul tugas" value="<?= $edit_data['judul'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <textarea name="deskripsi" placeholder="Deskripsi tugas" rows="4" style="width:100%; padding:12px; border-radius:8px; border:1px solid #ccc;"><?= $edit_data['deskripsi'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label>File Tugas (Opsional)</label>
            <input type="file" name="file_tugas" accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip">
            <?php if ($edit_data && isset($edit_data['file'])) { ?>
                <p style="font-size:12px; color:#666; margin-top:5px;">File saat ini: <strong><?= $edit_data['file'] ?></strong></p>
            <?php } ?>
        </div>

        <div class="form-group">
            <input type="text" id="deadline" name="deadline" placeholder="Pilih deadline" value="<?= $edit_data['deadline'] ?? '' ?>" required>
        </div>

        <button class="btn" name="simpan"><?= $edit_data ? 'Update' : 'Simpan' ?></button>
        <?php if ($edit_data) { ?>
            <a href="tugas.php" class="btn" style="background:#999; text-decoration:none; display:inline-block;">Batal</a>
        <?php } ?>
    </form>
</div>

<div class="card">
    <h2>📋 Daftar Tugas</h2>

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th style="width:8%">No</th>
                <th style="width:25%">Judul</th>
                <th style="width:25%">Deskripsi</th>
                <th style="width:12%">Deadline</th>
                <th style="width:10%">Pengumpulan</th>
                <th style="width:20%">Aksi</th>
            </tr>
        </thead>
        <tbody>

        <?php if(mysqli_num_rows($tugas) > 0){ ?>
            <?php $no=1; while($t=mysqli_fetch_assoc($tugas)){ 
                // Hitung jumlah pengumpulan
                $q_count = mysqli_query($conn,"SELECT COUNT(*) as jml FROM tugas_kumpul WHERE tugas_id='$t[id]'");
                $count = mysqli_fetch_assoc($q_count);
                $jml_kumpul = $count['jml'];
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $t['judul']; ?></td>
                <td><?= $t['deskripsi'] ?: '-'; ?></td>
                <td><?= $t['deadline']; ?></td>
                <td>
                    <span style="background:#1cc88a; color:white; padding:6px 12px; border-radius:5px; font-weight:bold;">
                        <?= $jml_kumpul ?> 📤
                    </span>
                </td>
                <td>
                    <div class="aksi" style="flex-direction:column; gap:8px;">
                        <?php if ($t['file']) { ?>
                            <a href="../tugas_file/<?= $t['file']; ?>" download style="background:#1cc88a; padding:6px 12px; border-radius:5px; color:white;">⬇️ Download File</a>
                        <?php } else { ?>
                            <span style="background:#ccc; padding:6px 12px; border-radius:5px; color:#666; font-size:12px;">Tidak ada file</span>
                        <?php } ?>
                        <a href="pengumpulan_tugas.php?id=<?= $t['id']; ?>" style="background:#ff9800; padding:6px 12px; border-radius:5px; color:white;">📤 Pengumpulan</a>
                        <a href="?edit=<?= $t['id']; ?>" style="background:#4f73df; padding:6px 12px; border-radius:5px; color:white;">✏️ Edit</a>
                        <a href="?hapus=<?= $t['id']; ?>" class="hapus" style="background:#e74a3b; padding:6px 12px; border-radius:5px; color:white;"
                           onclick="return confirm('Hapus tugas ini?')">
                           🗑 Hapus
                        </a>
                    </div>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="6">Belum ada tugas</td>
            </tr>
        <?php } ?>

        </tbody>
    </table>
    </div>
</div>

<div style="margin-top:20px;">
    <a href="index.php" class="btn btn-danger">← Kembali ke Dashboard</a>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
$(function(){
  $("#deadline").datepicker({
    dateFormat: "yy-mm-dd",
    dayNamesMin: ["Min","Sen","Sel","Rab","Kam","Jum","Sab"],
    monthNames: [
      "Januari","Februari","Maret","April","Mei","Juni",
      "Juli","Agustus","September","Oktober","November","Desember"
    ]
  });
});
</script>

</html>