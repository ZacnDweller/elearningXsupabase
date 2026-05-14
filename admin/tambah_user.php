<?php
include '../header.php';
include '../koneksi.php';

$prodi = mysqli_query($conn,"SELECT * FROM prodi");
$matkul = mysqli_query($conn,"SELECT * FROM matkul");

if (isset($_POST['simpan'])) {
    $nama   = $_POST['nama'];
    $user   = $_POST['username'];
    $pass   = md5($_POST['password']);
    $role   = $_POST['role'];
    $prodi_id = $_POST['prodi'];
    $matkul_id = ($role == 'dosen') ? $_POST['matkul'] : NULL;

    $umur   = $_POST['umur'];
    $no_hp  = $_POST['no_hp'];
    $agama  = $_POST['agama'];
    $alamat = $_POST['alamat'];

    $nisn = ($role == 'mahasiswa') ? $_POST['nisn'] : NULL;
    $nidn = ($role == 'dosen') ? $_POST['nidn'] : NULL;

    mysqli_query($conn,"
        INSERT INTO users 
        (nama, username, password, role, prodi_id, matkul_id,
         umur, no_hp, agama, alamat, nisn, nidn)
        VALUES 
        ('$nama','$user','$pass','$role','$prodi_id','$matkul_id',
         '$umur','$no_hp','$agama','$alamat','$nisn','$nidn')
    ");

    header("Location: index.php");
}
?>

<style>
.form-container { max-width:600px;margin:30px auto; }
.form-group { margin-bottom:15px; }
label { font-weight:bold;display:block;margin-bottom:5px; }
input, select, textarea {
    width:100%;padding:10px;
    border-radius:5px;border:1px solid #ccc;
}
.btn {
    background:#4f73df;color:white;
    padding:10px;border:none;
    border-radius:5px;width:100%;
}
.back { 
    display:inline-block;
    background:#e74a3b;
    color:white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    margin-top: 20px;
}
</style>

<div class="content">
<div class="card form-container">

<h2>➕ Tambah Akun</h2>

<form method="post">

    <div class="form-group">
        <label>Nama Lengkap</label>
        <input name="nama" required>
    </div>

    <div class="form-group">
        <label>Username</label>
        <input name="username" required>
    </div>

    <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
    </div>

    <div class="form-group">
        <label>Umur</label>
        <input type="number" name="umur" required>
    </div>

    <div class="form-group">
        <label>No HP</label>
        <input name="no_hp" required>
    </div>

    <div class="form-group">
        <label>Agama</label>
        <select name="agama" required>
            <option value="">-- Pilih Agama --</option>
            <option>Islam</option>
            <option>Kristen</option>
            <option>Katolik</option>
            <option>Hindu</option>
            <option>Buddha</option>
            <option>Konghucu</option>
        </select>
    </div>

    <div class="form-group">
        <label>Alamat</label>
        <textarea name="alamat" rows="3" required></textarea>
    </div>

    <div class="form-group">
        <label>Role</label>
        <select name="role" id="role" onchange="toggleField()" required>
            <option value="">-- Pilih Role --</option>
            <option value="mahasiswa">Mahasiswa</option>
            <option value="dosen">Dosen</option>
        </select>
    </div>

    <div class="form-group">
        <label>Prodi</label>
        <select name="prodi" required>
            <option value="">-- Pilih Prodi --</option>
            <?php while($p=mysqli_fetch_assoc($prodi)){ ?>
                <option value="<?= $p['id'] ?>"><?= $p['nama_prodi'] ?></option>
            <?php } ?>
        </select>
    </div>


    <div class="form-group" id="nisn-box" style="display:none;">
        <label>NISN</label>
        <input name="nisn">
    </div>


    <div class="form-group" id="nidn-box" style="display:none;">
        <label>NIDN</label>
        <input name="nidn">
    </div>

    <div class="form-group" id="matkul-box" style="display:none;">
        <label>Mata Kuliah</label>
        <select name="matkul">
            <option value="">-- Pilih Matkul --</option>
            <?php while($m=mysqli_fetch_assoc($matkul)){ ?>
                <option value="<?= $m['id'] ?>"><?= $m['nama_matkul'] ?></option>
            <?php } ?>
        </select>
    </div>

    <button name="simpan" class="btn">💾 Simpan</button>
</form>

<a href="index.php" class="back">Kembali</a>

</div>
</div>

<script>
function toggleField(){
    let role = document.getElementById('role').value;
    document.getElementById('nisn-box').style.display =
        (role === 'mahasiswa') ? 'block' : 'none';
    document.getElementById('nidn-box').style.display =
        (role === 'dosen') ? 'block' : 'none';
    document.getElementById('matkul-box').style.display =
        (role === 'dosen') ? 'block' : 'none';
}
</script>

</body>
</html>
