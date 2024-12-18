<?php
include '../koneksi.php'; 

$message = '';
$alertType = '';

if (!isset($_SESSION['id_admin'])) {
    die("Akses ditolak, Anda harus login terlebih dahulu.");
}

$id_admin = $_SESSION['id_admin']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = htmlspecialchars($_POST['judul']);
    $pengarang = htmlspecialchars($_POST['pengarang']);
    $penerbit = htmlspecialchars($_POST['penerbit']);
    $tahun_terbit = htmlspecialchars($_POST['tahun_terbit']);
    $isbn = htmlspecialchars($_POST['isbn']);
    $stok = htmlspecialchars($_POST['stok']);

    // Validasi data
    if (strlen($judul) < 3 || strlen($judul) > 255) {
        $message = 'Judul harus memiliki panjang antara 3 hingga 255 karakter.';
        $alertType = 'error';
    } elseif (strlen($pengarang) < 3 || strlen($pengarang) > 255) {
        $message = 'Pengarang harus memiliki panjang antara 3 hingga 255 karakter.';
        $alertType = 'error';
    } elseif (strlen($penerbit) < 3 || strlen($penerbit) > 255) {
        $message = 'Penerbit harus memiliki panjang antara 3 hingga 255 karakter.';
        $alertType = 'error';
    } elseif (!preg_match('/^[0-9]{4}$/', $tahun_terbit) || $tahun_terbit < 1900 || $tahun_terbit > date('Y')) {
        $message = 'Tahun terbit harus berupa angka valid (1900 hingga tahun saat ini).';
        $alertType = 'error';
    } elseif (!is_numeric($stok) || $stok < 0) {
        $message = 'Stok harus berupa angka dan minimal 0.';
        $alertType = 'error';
    } elseif (strlen($isbn) < 10 || strlen($isbn) > 20) {
        $message = 'ISBN harus memiliki panjang antara 10 hingga 20 karakter.';
        $alertType = 'error';
    } else {
        try {
            $sql = "INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, isbn, stok, id_admin) VALUES (:judul, :pengarang, :penerbit, :tahun_terbit, :isbn, :stok, :id_admin)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':pengarang', $pengarang);
            $stmt->bindParam(':penerbit', $penerbit);
            $stmt->bindParam(':tahun_terbit', $tahun_terbit);
            $stmt->bindParam(':isbn', $isbn);
            $stmt->bindParam(':stok', $stok);
            $stmt->bindParam(':id_admin', $id_admin);

            if ($stmt->execute()) {
                $message = 'Data buku berhasil disimpan!';
                $alertType = 'success';
            } else {
                $message = 'Gagal menyimpan data buku. ISBN mungkin sudah ada.';
                $alertType = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Kesalahan pada query: ' . $e->getMessage();
            $alertType = 'error';
        }
    }
}
?>

<div class="container-fluid mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Form Input Data Buku</h4>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Buku</label>
                    <input type="text" class="form-control" id="judul" name="judul" placeholder="Masukkan Judul Buku" required>
                </div>
                <div class="mb-3">
                    <label for="pengarang" class="form-label">Pengarang</label>
                    <input type="text" class="form-control" id="pengarang" name="pengarang" placeholder="Masukkan Nama Pengarang" required>
                </div>
                <div class="mb-3">
                    <label for="penerbit" class="form-label">Penerbit</label>
                    <input type="text" class="form-control" id="penerbit" name="penerbit" placeholder="Masukkan Nama Penerbit" required>
                </div>
                <div class="mb-3">
                    <label for="tahun_terbit" class="form-label">Tahun Terbit</label>
                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" placeholder="Masukkan Tahun Terbit (misal: 2023)" required>
                </div>
                <div class="mb-3">
                    <label for="isbn" class="form-label">ISBN</label>
                    <input type="text" class="form-control" id="isbn" name="isbn" placeholder="Masukkan ISBN Buku" required>
                </div>
                <div class="mb-3">
                    <label for="stok" class="form-label">Stok</label>
                    <input type="number" class="form-control" id="stok" name="stok" placeholder="Masukkan Jumlah Stok Buku" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (!empty($message)): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo ($alertType === "success") ? "Berhasil" : "Gagal"; ?>',
            text: '<?php echo $message; ?>',
            confirmButtonText: 'OK'
        });
    </script>
<?php endif; ?>
