<?php
include '../header.php';
include '../koneksi.php';
include '../fungsi.php';

$settings = getWebsiteSettings($conn);

if (isset($_POST['simpan'])) {
    $data = [
        'nama_website' => $_POST['nama_website'],
        'deskripsi' => $_POST['deskripsi'],
        'alamat' => $_POST['alamat'],
        'telepon' => $_POST['telepon'],
        'email' => $_POST['email'],
        'facebook' => $_POST['facebook'],
        'twitter' => $_POST['twitter'],
        'instagram' => $_POST['instagram']
    ];
    
    if (updateWebsiteSettings($conn, $data)) {
        $settings = getWebsiteSettings($conn);
        echo "<script>alert('Pengaturan website berhasil disimpan');</script>";
    } else {
        echo "<script>alert('Gagal menyimpan pengaturan website');</script>";
    }
}
?>

<style>
.container { max-width:800px; margin:30px auto; }
.form-group { margin-bottom:20px; }
label { font-weight:bold; display:block; margin-bottom:8px; font-size:14px; }
input, textarea, select {
    width:100%;
    padding:12px;
    border-radius:5px;
    border:1px solid #ddd;
    font-size:14px;
    font-family:Arial;
}
textarea { resize:vertical; min-height:100px; }
input:focus, textarea:focus, select:focus {
    outline:none;
    border-color:#4f73df;
    box-shadow:0 0 5px rgba(79,115,223,0.3);
}
.btn-container { text-align:center; margin-top:30px; }
button {
    background:#1cc88a;
    color:white;
    border:none;
    padding:12px 30px;
    border-radius:5px;
    font-weight:bold;
    font-size:15px;
    cursor:pointer;
}
button:hover { background:#17a065; }
.back-btn {
    display:inline-block;
    background:#e74a3b !important;
    color:white !important;
    padding: 12px 24px !important;
    border-radius: 8px !important;
    font-weight: bold !important;
    text-decoration: none !important;
    margin-top: 20px !important;
}
.section-title {
    font-size:16px;
    font-weight:bold;
    margin-top:25px;
    margin-bottom:15px;
    padding-bottom:10px;
    border-bottom:2px solid #4f73df;
    color:#4f73df;
}
</style>

<div class="content">
<div class="card container">

<h2>⚙️ Pengaturan Website</h2>
<p>Kelola data dan informasi website E-Learning</p>

<form method="post">

    <!-- INFORMASI UMUM -->
    <div class="section-title">📋 Informasi Umum</div>
    
    <div class="form-group">
        <label>Nama Website</label>
        <input type="text" name="nama_website" value="<?= $settings['nama_website'] ?>" required>
    </div>

    <div class="form-group">
        <label>Deskripsi Website</label>
        <textarea name="deskripsi" required><?= $settings['deskripsi'] ?></textarea>
    </div>

    <!-- KONTAK -->
    <div class="section-title">📞 Kontak</div>
    
    <div class="form-group">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?= $settings['alamat'] ?>">
    </div>

    <div class="form-group">
        <label>Nomor Telepon</label>
        <input type="text" name="telepon" value="<?= $settings['telepon'] ?>">
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= $settings['email'] ?>">
    </div>

    <!-- MEDIA SOSIAL -->
    <div class="section-title">📱 Media Sosial</div>
    
    <div class="form-group">
        <label>Facebook</label>
        <input type="text" name="facebook" placeholder="https://facebook.com/..." value="<?= $settings['facebook'] ?>">
    </div>

    <div class="form-group">
        <label>Twitter</label>
        <input type="text" name="twitter" placeholder="https://twitter.com/..." value="<?= $settings['twitter'] ?>">
    </div>

    <div class="form-group">
        <label>Instagram</label>
        <input type="text" name="instagram" placeholder="https://instagram.com/..." value="<?= $settings['instagram'] ?>">
    </div>

    <div class="btn-container">
        <button name="simpan" type="submit">💾 Simpan Pengaturan</button>
    </div>

</form>

<a href="index.php" class="back-btn">Kembali</a>

</div>
</div>

</body>
</html>
