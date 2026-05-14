<?php
include 'header.php';
include 'koneksi.php';
include 'fungsi.php';

$settings = getWebsiteSettings($conn);
?>

<style>
.about-container {
    max-width: 900px;
    margin: 30px auto;
}

.about-header {
    background: linear-gradient(135deg, #4f73df, #1cc88a);
    color: white;
    padding: 40px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.about-header h1 {
    margin: 0;
    font-size: 36px;
}

.about-header p {
    margin: 10px 0 0;
    font-size: 16px;
    opacity: 0.95;
}

.about-section {
    background: white;
    padding: 25px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 18px;
    font-weight: bold;
    color: #4f73df;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4f73df;
}

.info-row {
    display: flex;
    margin-bottom: 15px;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: bold;
    width: 140px;
    color: #333;
}

.info-value {
    flex: 1;
    color: #555;
}

.info-value a {
    color: #4f73df;
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

.description-text {
    line-height: 1.8;
    color: #555;
    font-size: 15px;
}

.social-links {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.social-link {
    display: inline-block;
    padding: 10px 20px;
    background: #4f73df;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}

.social-link:hover {
    background: #3b5ed7;
}

.back-btn {
    display: inline-block;
    background: #e74a3b;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: bold;
    text-decoration: none;
    margin-top: 30px;
}

.back-btn:hover {
    background: #d63a2b;
}
</style>

<div class="content">

<div class="about-container">

    <div class="about-header">
        <h1><?= $settings['nama_website'] ?></h1>
        <p><?= $settings['deskripsi'] ?></p>
    </div>


    <div class="about-section">
        <div class="section-title">📋 Informasi Website</div>
        <p class="description-text">
            <?= $settings['deskripsi'] ?>
        </p>
    </div>


    <div class="about-section">
        <div class="section-title">📞 Kontak Kami</div>
        
        <?php if ($settings['alamat']) { ?>
        <div class="info-row">
            <div class="info-label">📍 Alamat:</div>
            <div class="info-value"><?= $settings['alamat'] ?></div>
        </div>
        <?php } ?>

        <?php if ($settings['telepon']) { ?>
        <div class="info-row">
            <div class="info-label">📱 Telepon:</div>
            <div class="info-value"><a href="tel:<?= $settings['telepon'] ?>"><?= $settings['telepon'] ?></a></div>
        </div>
        <?php } ?>

        <?php if ($settings['email']) { ?>
        <div class="info-row">
            <div class="info-label">✉️ Email:</div>
            <div class="info-value"><a href="mailto:<?= $settings['email'] ?>"><?= $settings['email'] ?></a></div>
        </div>
        <?php } ?>
    </div>


    <div class="about-section">
        <div class="section-title">📱 Media Sosial</div>
        
        <div class="social-links">
            <?php if ($settings['facebook']) { ?>
            <a href="<?= $settings['facebook'] ?>" target="_blank" class="social-link">👍 Facebook</a>
            <?php } ?>

            <?php if ($settings['twitter']) { ?>
            <a href="<?= $settings['twitter'] ?>" target="_blank" class="social-link">🐦 Twitter</a>
            <?php } ?>

            <?php if ($settings['instagram']) { ?>
            <a href="<?= $settings['instagram'] ?>" target="_blank" class="social-link">📸 Instagram</a>
            <?php } ?>
        </div>

        <?php if (!$settings['facebook'] && !$settings['twitter'] && !$settings['instagram']) { ?>
        <p style="color: #999;">Media sosial belum dikonfigurasi.</p>
        <?php } ?>
    </div>

    <a href="javascript:history.back()" class="back-btn">Kembali</a>

</div>

</div>

</body>
</html>
