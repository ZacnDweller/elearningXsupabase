<?php
include '../header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = $_POST['transaction_id'];

    $query = "INSERT INTO payments (student_id, amount, description, payment_method, transaction_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "idsss", $student_id, $amount, $description, $payment_method, $transaction_id);

    if (mysqli_stmt_execute($stmt)) {
        $success = "Pembayaran berhasil diajukan. Menunggu konfirmasi admin.";
        $student_name = $_SESSION['nama'] ?? 'Mahasiswa';
        tambahAktivitas($conn, $student_id, $student_name, 'mahasiswa', "Mengajukan pembayaran Rp " . number_format($amount, 0, ',', '.') . " dengan deskripsi: {$description}");
    } else {
        $error = "Gagal mengajukan pembayaran.";
    }
}


$query = "SELECT p.*, u.nama as confirmed_by_name FROM payments p LEFT JOIN users u ON p.confirmed_by = u.id WHERE p.student_id = ? ORDER BY p.payment_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-4">
    <a href="index.php" class="btn btn-danger mb-3">← Kembali ke Dashboard</a>
    <h2>Pembayaran</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Ajukan Pembayaran Baru</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Jumlah (Rp)</label>
                            <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Metode Pembayaran</label>
                            <select class="form-control" id="payment_method" name="payment_method" required>
                                <option value="">Pilih Metode</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="Kartu Kredit">Kartu Kredit</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">ID Transaksi</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajukan Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Riwayat Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['payment_date'])); ?></td>
                                        <td>Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="badge bg-<?php
                                                echo $row['status'] == 'confirmed' ? 'success' :
                                                     ($row['status'] == 'pending' ? 'warning' : 'danger');
                                            ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <a href="index.php" class="btn btn-danger">Kembali</a>
    </div>
</div>

<?php include '../footer.php'; ?>