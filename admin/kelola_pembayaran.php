<?php
include '../header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$admin_id = $_SESSION['id'];
$admin_name = $_SESSION['nama'] ?? 'Admin';


$studentsQuery = "SELECT id, nama, username FROM users WHERE role='mahasiswa' ORDER BY nama";
$studentsResult = mysqli_query($conn, $studentsQuery);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'create_payment') {
        $student_id = $_POST['student_id'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $payment_method = $_POST['payment_method'];
        $transaction_id = $_POST['transaction_id'];

        $query = "INSERT INTO payments (student_id, amount, description, payment_method, transaction_id, status, payment_date) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "idsss", $student_id, $amount, $description, $payment_method, $transaction_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Permintaan pembayaran berhasil ditambahkan untuk mahasiswa.";
            $studentName = '';
            $studentRes = mysqli_query($conn, "SELECT nama FROM users WHERE id = '" . intval($student_id) . "' LIMIT 1");
            if ($studentRes) {
                $studentRow = mysqli_fetch_assoc($studentRes);
                $studentName = $studentRow['nama'] ?? '';
            }
            tambahAktivitas($conn, $admin_id, $admin_name, 'admin', "Menambahkan tagihan pembayaran Rp " . number_format($amount, 0, ',', '.') . " untuk mahasiswa {$studentName}.");
        } else {
            $error = "Gagal menambahkan permintaan pembayaran.";
        }
    } elseif (isset($_POST['action'])) {
        $payment_id = $_POST['payment_id'];
        $action = $_POST['action'];
        $notes = $_POST['notes'] ?? '';

        if ($action == 'confirm') {
            $status = 'confirmed';
        } elseif ($action == 'reject') {
            $status = 'rejected';
        }

        $query = "UPDATE payments SET status = ?, confirmed_by = ?, confirmed_date = NOW(), notes = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sisi", $status, $admin_id, $notes, $payment_id);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Pembayaran berhasil " . ($status == 'confirmed' ? 'dikonfirmasi' : 'ditolak') . ".";
            $actionText = $status == 'confirmed' ? 'mengonfirmasi' : 'menolak';
            tambahAktivitas($conn, $admin_id, $admin_name, 'admin', ucfirst($actionText) . " pembayaran ID {$payment_id}.");
        } else {
            $error = "Gagal memproses pembayaran.";
        }
    }
}


$query = "SELECT p.*, u.nama as student_name, u.username as student_username,
                 c.nama as confirmed_by_name
          FROM payments p
          JOIN users u ON p.student_id = u.id
          LEFT JOIN users c ON p.confirmed_by = c.id
          ORDER BY p.payment_date DESC";
$result = mysqli_query($conn, $query);
?>

    <h2>Kelola Pembayaran</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5>Tambah Permintaan Pembayaran</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="form_type" value="create_payment">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="student_id" class="form-label">Pilih Mahasiswa</label>
                        <select class="form-control" id="student_id" name="student_id" required>
                            <option value="">Pilih Mahasiswa</option>
                            <?php while ($student = mysqli_fetch_assoc($studentsResult)): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['nama'] . ' (' . $student['username'] . ')'); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Jumlah (Rp)</label>
                        <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="2" required></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran</label>
                        <select class="form-control" id="payment_method" name="payment_method" required>
                            <option value="">Pilih Metode</option>
                            <option value="Transfer Bank">Transfer Bank</option>
                            <option value="E-Wallet">E-Wallet</option>
                            <option value="Kartu Kredit">Kartu Kredit</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="transaction_id" class="form-label">ID Transaksi / Kode Tagihan</label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Permintaan Pembayaran</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Daftar Pembayaran</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Mahasiswa</th>
                            <th>Jumlah</th>
                            <th>Metode</th>
                            <th>ID Transaksi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?> (<?php echo htmlspecialchars($row['student_username']); ?>)</td>
                                <td>Rp <?php echo number_format($row['amount'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $row['status'] == 'confirmed' ? 'success' :
                                             ($row['status'] == 'pending' ? 'warning' : 'danger');
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal" data-payment-id="<?php echo $row['id']; ?>" data-action="confirm">Konfirmasi</button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal" data-payment-id="<?php echo $row['id']; ?>" data-action="reject">Tolak</button>
                                    <?php else: ?>
                                        <?php if ($row['confirmed_by_name']): ?>
                                            Dikonfirmasi oleh <?php echo htmlspecialchars($row['confirmed_by_name']); ?>
                                            pada <?php echo date('d/m/Y H:i', strtotime($row['confirmed_date'])); ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <a href="index.php" class="btn btn-danger">Kembali</a>
    </div>
</div>


<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="modal_payment_id">
                    <input type="hidden" name="action" id="modal_action">
                    <p id="modal_text"></p>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var confirmModal = document.getElementById('confirmModal');
    confirmModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        var paymentId = button.getAttribute('data-payment-id');
        var action = button.getAttribute('data-action');

        document.getElementById('modal_payment_id').value = paymentId;
        document.getElementById('modal_action').value = action;

        if (action === 'confirm') {
            document.getElementById('modal_text').textContent = 'Apakah Anda yakin ingin mengkonfirmasi pembayaran ini?';
            document.getElementById('modal_submit_btn').textContent = 'Konfirmasi';
            document.getElementById('modal_submit_btn').className = 'btn btn-success';
        } else {
            document.getElementById('modal_text').textContent = 'Apakah Anda yakin ingin menolak pembayaran ini?';
            document.getElementById('modal_submit_btn').textContent = 'Tolak';
            document.getElementById('modal_submit_btn').className = 'btn btn-danger';
        }
    });
});
</script>

<?php include '../footer.php'; ?>