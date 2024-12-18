<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    die("Akses ditolak, Anda harus login terlebih dahulu.");
}

$id_admin = $_SESSION['id_admin'];

$notification = '';
$notificationClass = '';

$transactionDetails = '<p>Silakan pilih anggota dan buku untuk melihat detail transaksi.</p>';
$cekDendaDetails = '';

// Menggunakan PDO untuk mengambil data anggota
$queryAnggota = "
    SELECT DISTINCT anggota.id_anggota, anggota.nama 
    FROM anggota
    JOIN transaksi ON anggota.id_anggota = transaksi.id_anggota
    WHERE transaksi.id_admin = :id_admin
";

$stmtAnggota = $pdo->prepare($queryAnggota);
$stmtAnggota->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
$stmtAnggota->execute();
$anggotaList = $stmtAnggota->fetchAll(PDO::FETCH_ASSOC);  // Mengambil semua hasil anggota

// Proses ketika tombol cari transaksi ditekan
if (isset($_POST['cari_transaksi'])) {
    $idAnggota = $_POST['member'];
    $idBuku = $_POST['book'];

    if (!empty($idAnggota) && !empty($idBuku)) {
        $queryTransaksi = "
        SELECT transaksi.id_transaksi, 
               transaksi.tanggal_pinjam, 
               transaksi.tanggal_kembali, 
               transaksi.jumlah_buku, 
               transaksi.jumlah_dikembalikan, 
               transaksi.denda, 
               transaksi.status,
               buku.judul AS judul_buku,
               anggota.nama AS nama_anggota
        FROM transaksi
        JOIN anggota ON transaksi.id_anggota = anggota.id_anggota
        JOIN buku ON transaksi.id_buku = buku.id_buku
        WHERE transaksi.id_anggota = :id_anggota 
        AND transaksi.id_buku = :id_buku
        AND transaksi.id_admin = :id_admin
    ";

        $stmtTransaksi = $pdo->prepare($queryTransaksi);
        $stmtTransaksi->bindParam(':id_anggota', $idAnggota, PDO::PARAM_INT);
        $stmtTransaksi->bindParam(':id_buku', $idBuku, PDO::PARAM_INT);
        $stmtTransaksi->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
        $stmtTransaksi->execute();

        if ($stmtTransaksi->rowCount() > 0) {
            $row = $stmtTransaksi->fetch(PDO::FETCH_ASSOC);

            $transactionDetails = "
                <p>Nama Anggota: {$row['nama_anggota']}</p>
                <p>Judul Buku: {$row['judul_buku']}</p>
                <p>ID Transaksi: {$row['id_transaksi']}</p>
                <p>Tanggal Peminjaman: {$row['tanggal_pinjam']}</p>
                <p>Tanggal Pengembalian: " . ($row['tanggal_kembali'] ?? '-') . "</p>
                <p>Jumlah Buku yang Dipinjam: {$row['jumlah_buku']}</p>
                <p>Jumlah Buku Dikembalikan: {$row['jumlah_dikembalikan']}</p>
                <p>Status: {$row['status']}</p>
            ";
        } else {
            $notification = 'Data transaksi tidak ditemukan untuk anggota dan buku yang dipilih.';
            $notificationClass = 'alert-danger';
        }
    } else {
        $notification = 'Harap pilih anggota dan buku terlebih dahulu.';
        $notificationClass = 'alert-danger';
    }
}

// Proses ketika tombol cari buku ditekan
if (isset($_POST['cari_buku'])) {
    $idAnggota = $_POST['member'];

    if (!empty($idAnggota)) {
        $queryBuku = "
            SELECT DISTINCT buku.id_buku, buku.judul
            FROM buku
            JOIN transaksi ON buku.id_buku = transaksi.id_buku
            WHERE transaksi.id_anggota = :id_anggota
        ";

        $stmtBuku = $pdo->prepare($queryBuku);
        $stmtBuku->bindParam(':id_anggota', $idAnggota, PDO::PARAM_INT);
        $stmtBuku->execute();
        $resultBuku = $stmtBuku->fetchAll(PDO::FETCH_ASSOC);  // Mengambil semua hasil buku
    }
}

// Proses cek denda
if (isset($_POST['cek_denda'])) {
    $idTransaksi = intval($_POST['transaction_id']);
    $jumlahDikembalikan = intval($_POST['return_count']);

    $queryTransaksi = "SELECT * FROM transaksi WHERE id_transaksi = :id_transaksi";
    $stmtTransaksi = $pdo->prepare($queryTransaksi);
    $stmtTransaksi->bindParam(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
    $stmtTransaksi->execute();

    if ($stmtTransaksi->rowCount() > 0) {
        $transaksi = $stmtTransaksi->fetch(PDO::FETCH_ASSOC);

        $tanggalKembali = $transaksi['tanggal_kembali'];
        $tanggalHariIni = date('Y-m-d');
        $hariTerlambat = 0;

        if ($tanggalKembali) {
            $datetimeKembali = new DateTime($tanggalKembali);
            $datetimeHariIni = new DateTime($tanggalHariIni);

            if ($datetimeHariIni > $datetimeKembali) {
                $interval = $datetimeKembali->diff($datetimeHariIni);
                $hariTerlambat = $interval->days;
            }
        }

        $totalDenda = $hariTerlambat * $transaksi['denda'] * $jumlahDikembalikan;

        $cekDendaDetails = "
            <p><strong>Hari Terlambat:</strong> {$hariTerlambat} hari</p>
            <p><strong>Jumlah Buku Dikembalikan:</strong> {$jumlahDikembalikan}</p>
            <p><strong>Total Denda:</strong> Rp " . number_format($totalDenda, 0, ',', '.') . "</p>
        ";
        $notification = 'Perhitungan denda berhasil.';
        $notificationClass = 'alert-success';
    } else {
        $notification = 'Data transaksi tidak ditemukan untuk menghitung denda.';
        $notificationClass = 'alert-danger';
    }
}

// Proses pengembalian buku
if (isset($_POST['proses_pengembalian'])) {
    $idTransaksi = intval($_POST['transaction_id']);
    $jumlahDikembalikan = intval($_POST['return_count']);
    $hariTerlambat = intval($_POST['hari_terlambat']);
    $totalDenda = intval($_POST['total_denda']);

    $queryTransaksi = "SELECT * FROM transaksi WHERE id_transaksi = :id_transaksi";
    $stmtTransaksi = $pdo->prepare($queryTransaksi);
    $stmtTransaksi->bindParam(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
    $stmtTransaksi->execute();

    if ($stmtTransaksi->rowCount() > 0) {
        $transaksi = $stmtTransaksi->fetch(PDO::FETCH_ASSOC);

        $idBuku = $transaksi['id_buku'];
        $jumlahBukuPinjam = $transaksi['jumlah_buku'];
        $jumlahDikembalikanSebelumnya = $transaksi['jumlah_dikembalikan'];

        if ($jumlahDikembalikan <= 0 || $jumlahDikembalikan > $jumlahBukuPinjam) {
            $notification = 'Jumlah buku yang dikembalikan tidak valid.';
            $notificationClass = 'alert-danger';
        } else {
            $jumlahTotalDikembalikan = $jumlahDikembalikanSebelumnya + $jumlahDikembalikan;
            $jumlahBukuTersisa = $jumlahBukuPinjam - $jumlahDikembalikan;

            $status = ($jumlahBukuTersisa == 0) ? 'dikembalikan' : 'dipinjam';

            // Update transaksi
            $queryUpdateTransaksi = "
                UPDATE transaksi
                SET jumlah_dikembalikan = :jumlah_total_dikembalikan,
                    jumlah_buku = :jumlah_buku_tersisa,
                    status = :status
                WHERE id_transaksi = :id_transaksi
            ";
            $stmtUpdateTransaksi = $pdo->prepare($queryUpdateTransaksi);
            $stmtUpdateTransaksi->bindParam(':jumlah_total_dikembalikan', $jumlahTotalDikembalikan, PDO::PARAM_INT);
            $stmtUpdateTransaksi->bindParam(':jumlah_buku_tersisa', $jumlahBukuTersisa, PDO::PARAM_INT);
            $stmtUpdateTransaksi->bindParam(':status', $status, PDO::PARAM_STR);
            $stmtUpdateTransaksi->bindParam(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
            $stmtUpdateTransaksi->execute();

            // Update stok buku
            $queryUpdateStok = "UPDATE buku SET stok = stok + :jumlah_dikembalikan WHERE id_buku = :id_buku";
            $stmtUpdateStok = $pdo->prepare($queryUpdateStok);
            $stmtUpdateStok->bindParam(':jumlah_dikembalikan', $jumlahDikembalikan, PDO::PARAM_INT);
            $stmtUpdateStok->bindParam(':id_buku', $idBuku, PDO::PARAM_INT);
            $stmtUpdateStok->execute();

            // Update riwayat jika status sudah dikembalikan
            if ($status === 'dikembalikan') {
                $queryUpdateRiwayat = "
                    UPDATE riwayat
                    SET hari_terlambat = :hari_terlambat,
                        total_denda = :total_denda,
                        status = 'sudah dikembalikan'
                    WHERE id_transaksi = :id_transaksi
                ";
                $stmtUpdateRiwayat = $pdo->prepare($queryUpdateRiwayat);
                $stmtUpdateRiwayat->bindParam(':hari_terlambat', $hariTerlambat, PDO::PARAM_INT);
                $stmtUpdateRiwayat->bindParam(':total_denda', $totalDenda, PDO::PARAM_INT);
                $stmtUpdateRiwayat->bindParam(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
                $stmtUpdateRiwayat->execute();
            }

            $notification = 'Pengembalian berhasil diproses.';
            $notificationClass = 'alert-success';
        }
    } else {
        $notification = 'Data transaksi tidak ditemukan.';
        $notificationClass = 'alert-danger';
    }
}
?>
<div class="container-fluid mt-5">
    <div class="row">
        <?php if ($notification): ?>
            <div id="notification" class="alert <?= $notificationClass; ?> text-center">
                <?= $notification; ?>
            </div>
        <?php endif; ?>

        <!-- Form Cek Transaksi (Cari Anggota dan Buku) -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Cek Transaksi</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="member">Nama Anggota</label>
                            <select id="member" name="member" class="form-select">
                                <option value="">Pilih Anggota</option>
                                <?php foreach ($anggotaList as $rowAnggota): ?>
                                    <option value="<?= $rowAnggota['id_anggota']; ?>" <?= isset($idAnggota) && $idAnggota == $rowAnggota['id_anggota'] ? 'selected' : '' ?>>
                                        <?= $rowAnggota['nama']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button name="cari_buku" class="btn btn-primary mt-2">Cari Buku</button>
                        </div>
                    </form>

                    <?php if (isset($resultBuku) && !empty($resultBuku)): ?>
                        <form method="POST">
                            <input type="hidden" name="member" value="<?= $idAnggota; ?>">
                            <select name="book" class="form-select">
                                <option value="">Pilih Buku</option>
                                <?php foreach ($resultBuku as $rowBuku): ?>
                                    <option value="<?= $rowBuku['id_buku']; ?>" <?= isset($idBuku) && $idBuku == $rowBuku['id_buku'] ? 'selected' : '' ?>>
                                        <?= $rowBuku['judul']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button name="cari_transaksi" class="btn btn-primary mt-2">Cari Transaksi</button>
                        </form>
                    <?php endif; ?>

                    <div class="mt-3">
                        <?= $transactionDetails; ?>
                    </div>

                    <?php if (isset($row) && !empty($row)): ?>
                        <form method="POST" class="mt-3">
                            <div class="mb-3">
                                <label for="return-count">Jumlah Buku Dikembalikan</label>
                                <input type="number" id="return-count" name="return_count" class="form-control"
                                    min="1" max="<?= isset($row['jumlah_buku']) && isset($row['jumlah_dikembalikan']) ? $row['jumlah_buku'] - $row['jumlah_dikembalikan'] : 0; ?>"
                                    value="<?= isset($_POST['return_count']) ? $_POST['return_count'] : 1; ?>" required>
                            </div>
                            <input type="hidden" name="transaction_id" value="<?= isset($row['id_transaksi']) ? $row['id_transaksi'] : ''; ?>">
                            <button type="submit" name="cek_denda" class="btn btn-warning">Cek Denda</button>
                        </form>
                    <?php else: ?>
                        <p>Transaksi tidak ditemukan atau belum dipilih.</p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <?= $cekDendaDetails; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian Pengembalian Buku -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">Pengembalian Buku</div>
                <div class="card-body">
                    <!-- Form Pengembalian Buku, ID Transaksi Diisi Manual -->
                    <form method="POST">
                        <div class="mb-3">
                            <label for="transaction-id-input">ID Transaksi</label>
                            <input type="text" id="transaction-id-input" name="transaction_id" class="form-control" placeholder="Masukkan ID Transaksi" required>
                        </div>
                        <div class="mb-3">
                            <label for="return-count">Jumlah Buku Dikembalikan</label>
                            <input type="number" id="return-count" name="return_count" class="form-control"
                                min="1" value="<?= isset($_POST['return_count']) ? $_POST['return_count'] : 1; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="hari-terlambat">Hari Terlambat</label>
                            <input type="number" id="hari-terlambat" name="hari_terlambat" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="total-denda">Total Denda</label>
                            <input type="number" id="total-denda" name="total_denda" class="form-control" required>
                        </div>
                        <button type="submit" name="proses_pengembalian" class="btn btn-success">Proses Pengembalian</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    setTimeout(() => {
        const notification = document.getElementById('notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }, 1000);
</script>