<?php
include '../koneksi.php';  // Pastikan koneksi sudah menggunakan PDO

$id_admin = $_SESSION['id_admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_anggota = $_POST['anggota'];
    $id_buku = $_POST['buku'];
    $jumlah_pinjam = $_POST['jumlahPinjam'] ?: 0;
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_kembali = $_POST['tanggalPengembalian'] ?: NULL;
    $denda = $_POST['denda'] ?: 0;

    try {
        // Cek stok buku
        $stmt =$pdo->prepare("SELECT stok FROM buku WHERE id_buku = :id_buku AND id_admin = :id_admin");
        $stmt->bindParam(':id_buku', $id_buku);
        $stmt->bindParam(':id_admin', $id_admin);
        $stmt->execute();
        $stokBuku = $stmt->fetchColumn() ?: 0;

        if ($jumlah_pinjam > $stokBuku) {
            echo "<div class='alert alert-danger'>Gagal menyimpan: Stok buku tidak mencukupi. Stok tersedia: $stokBuku.</div>";
        } else {
            // Update stok buku
            $stokBaru = $stokBuku - $jumlah_pinjam;
            $updateStokQuery = "UPDATE buku SET stok = :stok WHERE id_buku = :id_buku AND id_admin = :id_admin";
            $stmt =$pdo->prepare($updateStokQuery);
            $stmt->bindParam(':stok', $stokBaru);
            $stmt->bindParam(':id_buku', $id_buku);
            $stmt->bindParam(':id_admin', $id_admin);

            if ($stmt->execute()) {
                // Insert data transaksi
                $queryTransaksi = "INSERT INTO transaksi (id_anggota, id_buku, id_admin, tanggal_pinjam, tanggal_kembali, jumlah_buku, denda, status) 
                                   VALUES (:id_anggota, :id_buku, :id_admin, :tanggal_pinjam, :tanggal_kembali, :jumlah_buku, :denda, 'dipinjam')";
                $stmt =$pdo->prepare($queryTransaksi);
                $stmt->bindParam(':id_anggota', $id_anggota);
                $stmt->bindParam(':id_buku', $id_buku);
                $stmt->bindParam(':id_admin', $id_admin);
                $stmt->bindParam(':tanggal_pinjam', $tanggal_pinjam);
                $stmt->bindParam(':tanggal_kembali', $tanggal_kembali);
                $stmt->bindParam(':jumlah_buku', $jumlah_pinjam);
                $stmt->bindParam(':denda', $denda);

                if ($stmt->execute()) {
                    $id_transaksi =$pdo->lastInsertId();

                    // Ambil nama anggota dan judul buku
                    $stmt =$pdo->prepare("SELECT nama FROM anggota WHERE id_anggota = :id_anggota");
                    $stmt->bindParam(':id_anggota', $id_anggota);
                    $stmt->execute();
                    $nama_anggota = $stmt->fetchColumn() ?: '-';

                    $stmt =$pdo->prepare("SELECT judul FROM buku WHERE id_buku = :id_buku");
                    $stmt->bindParam(':id_buku', $id_buku);
                    $stmt->execute();
                    $judul_buku = $stmt->fetchColumn() ?: '-';

                    // Insert riwayat peminjaman
                    $queryRiwayat = "INSERT INTO riwayat (id_transaksi, id_admin, nama_anggota, judul_buku, tanggal_pinjam, tanggal_kembali, status) 
                                     VALUES (:id_transaksi, :id_admin, :nama_anggota, :judul_buku, :tanggal_pinjam, :tanggal_kembali, 'masih dipinjam')";
                    $stmt = $pdo->prepare($queryRiwayat);
                    $stmt->bindParam(':id_transaksi', $id_transaksi);
                    $stmt->bindParam(':id_admin', $id_admin);
                    $stmt->bindParam(':nama_anggota', $nama_anggota);
                    $stmt->bindParam(':judul_buku', $judul_buku);
                    $stmt->bindParam(':tanggal_pinjam', $tanggal_pinjam);
                    $stmt->bindParam(':tanggal_kembali', $tanggal_kembali);

                    $stmt->execute();

                    echo "<div class='alert alert-success'>Data berhasil disimpan, stok buku diperbarui.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Gagal menyimpan data transaksi.</div>";
                }
            } else {
                echo "<div class='alert alert-danger'>Gagal memperbarui stok buku.</div>";
            }
        }
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Kesalahan: " . $e->getMessage() . "</div>";
    }
}

// Ambil data anggota dan buku
$dataAnggota = $pdo->prepare("SELECT id_anggota, nama FROM anggota WHERE id_admin = :id_admin");
$dataAnggota->bindParam(':id_admin', $id_admin);
$dataAnggota->execute();

$dataBuku = $pdo->prepare("SELECT id_buku, judul FROM buku WHERE id_admin = :id_admin");
$dataBuku->bindParam(':id_admin', $id_admin);
$dataBuku->execute();
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        Peminjaman Buku
    </div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="anggota" class="form-label">Pilih Anggota</label>
                <select class="form-select" id="anggota" name="anggota" required>
                    <option value="" disabled selected>Pilih anggota</option>
                    <?php while ($row = $dataAnggota->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $row['id_anggota']; ?>"><?= $row['nama']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="buku" class="form-label">Pilih Buku</label>
                <select class="form-select" id="buku" name="buku" required>
                    <option value="" disabled selected>Pilih buku</option>
                    <?php while ($row = $dataBuku->fetch(PDO::FETCH_ASSOC)): ?>
                        <option value="<?= $row['id_buku']; ?>"><?= $row['judul']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="jumlahPinjam" class="form-label">Jumlah Pinjam</label>
                <input type="number" class="form-control" id="jumlahPinjam" name="jumlahPinjam" placeholder="Masukkan jumlah buku" required>
            </div>
            <div class="mb-3">
                <label for="tanggalPengembalian" class="form-label">Tanggal Pengembalian</label>
                <input type="date" class="form-control" id="tanggalPengembalian" name="tanggalPengembalian">
            </div>
            <div class="mb-3">
                <label for="denda" class="form-label">Denda (Rp)</label>
                <input type="number" class="form-control" id="denda" name="denda" placeholder="Masukkan jumlah denda (jika ada)" min="0">
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
