<?php
// Prevent redeclaration of functions
if (!function_exists('getWebsiteSettings')) {

// Get website settings
function getWebsiteSettings($conn) {
    $q = mysqli_query($conn, "SELECT * FROM website_settings LIMIT 1");
    $settings = mysqli_fetch_assoc($q);
    
    if (!$settings) {
        // Set default if table is empty
        $settings = [
            'nama_website' => 'E-Learning',
            'deskripsi' => 'Platform pembelajaran online',
            'alamat' => '',
            'telepon' => '',
            'email' => '',
            'facebook' => '',
            'twitter' => '',
            'instagram' => ''
        ];
    }
    return $settings;
}

// Update website settings
function updateWebsiteSettings($conn, $data) {
    $nama = mysqli_real_escape_string($conn, $data['nama_website']);
    $desc = mysqli_real_escape_string($conn, $data['deskripsi']);
    $alamat = mysqli_real_escape_string($conn, $data['alamat']);
    $telp = mysqli_real_escape_string($conn, $data['telepon']);
    $email = mysqli_real_escape_string($conn, $data['email']);
    $fb = mysqli_real_escape_string($conn, $data['facebook']);
    $tw = mysqli_real_escape_string($conn, $data['twitter']);
    $ig = mysqli_real_escape_string($conn, $data['instagram']);
    
    $sql = "UPDATE website_settings SET 
        nama_website='$nama',
        deskripsi='$desc',
        alamat='$alamat',
        telepon='$telp',
        email='$email',
        facebook='$fb',
        twitter='$tw',
        instagram='$ig'
        WHERE id=1";
    
    return mysqli_query($conn, $sql);
}

function kalender_indonesia($bulan = null, $tahun = null) {
    if ($bulan === null) $bulan = date('n');
    if ($tahun === null) $tahun = date('Y');

    $nama_hari = array('Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu');

    $nama_bulan = array(
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    );

    $jumlah_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

    $hari_pertama = date('w', mktime(0, 0, 0, $bulan, 1, $tahun));

    $html = '<div class="kalender">';
    $html .= '<h2>' . $nama_bulan[$bulan] . ' ' . $tahun . '</h2>';
    $html .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
    $html .= '<tr>';

    foreach ($nama_hari as $hari) {
        $html .= '<th style="padding: 10px; background-color: #f0f0f0;">' . $hari . '</th>';
    }
    $html .= '</tr><tr>';

    for ($i = 0; $i < $hari_pertama; $i++) {
        $html .= '<td style="padding: 10px;"></td>';
    }

    $hari_counter = $hari_pertama;
    for ($tanggal = 1; $tanggal <= $jumlah_hari; $tanggal++) {
        if ($hari_counter % 7 == 0 && $tanggal != 1) {
            $html .= '</tr><tr>';
        }
        $html .= '<td style="padding: 10px; text-align: center;">' . $tanggal . '</td>';
        $hari_counter++;
    }

    while ($hari_counter % 7 != 0) {
        $html .= '<td style="padding: 10px;"></td>';
        $hari_counter++;
    }

    $html .= '</tr>';
    $html .= '</table>';
    $html .= '</div>';

    return $html;
}

function tambahAktivitas($conn, $user_id, $nama, $role, $aktivitas)
{
    $nama = mysqli_real_escape_string($conn, $nama);
    $role = mysqli_real_escape_string($conn, $role);
    $aktivitas = mysqli_real_escape_string($conn, $aktivitas);

    mysqli_query($conn, "
        INSERT INTO aktivitas(
            user_id,
            nama,
            role,
            aktivitas
        ) VALUES(
            '$user_id',
            '$nama',
            '$role',
            '$aktivitas'
        )
    ");
}

}
?>