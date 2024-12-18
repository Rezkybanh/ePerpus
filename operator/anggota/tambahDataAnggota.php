<?php
include '../koneksi.php';  

$message = '';
$alertType = '';

if (!isset($_SESSION['id_admin'])) {
    $message = 'Anda belum login. Silakan login terlebih dahulu.';
    $alertType = 'error';
} else {
    $id_admin = $_SESSION['id_admin'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nisnip = htmlspecialchars($_POST['nisnip']);
        $nama = htmlspecialchars($_POST['nama']);
        $kelas_unit = htmlspecialchars($_POST['kelas_unit']);
        $kontak = htmlspecialchars($_POST['kontak']);

        // Validasi input
        if (!is_numeric($nisnip) || strlen($nisnip) < 5 || strlen($nisnip) > 50) {
            $message = 'NIS/NIP harus berupa angka dengan panjang antara 5 hingga 50 karakter.';
            $alertType = 'error';
        } elseif (strlen($nama) < 3 || strlen($nama) > 255) {
            $message = 'Nama harus memiliki panjang antara 3 hingga 255 karakter.';
            $alertType = 'error';
        } elseif (!is_numeric($kontak) || strlen($kontak) < 10 || strlen($kontak) > 15) {
            $message = 'Kontak harus berupa angka dengan panjang antara 10 hingga 15 karakter.';
            $alertType = 'error';
        } else {
            $sql = "INSERT INTO anggota (id_admin, nisnip, nama, kelas_unit, kontak) VALUES (:id_admin, :nisnip, :nama, :kelas_unit, :kontak)";
            $stmt = $pdo->prepare($sql);

            if ($stmt) {
                $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
                $stmt->bindParam(':nisnip', $nisnip, PDO::PARAM_STR);
                $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
                $stmt->bindParam(':kelas_unit', $kelas_unit, PDO::PARAM_STR);
                $stmt->bindParam(':kontak', $kontak, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $message = 'Data berhasil disimpan!';
                    $alertType = 'success';
                } else {
                    $message = 'Gagal menyimpan data.';
                    $alertType = 'error';
                }
            } else {
                $message = 'Kesalahan pada query.';
                $alertType = 'error';
            }
        }
    }
}
?>

<div class="container-fluid mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Form Input Data Anggota</h4>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="nis_nip" class="form-label">NIS/NIP</label>
                    <input type="text" class="form-control" id="nis_nip" name="nisnip" placeholder="Masukkan NIS/NIP" required>
                </div>
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama" placeholder="Masukkan Nama Lengkap" required>
                </div>
                <div class="mb-3">
                    <label for="kelas_unit" class="form-label">Kelas/Unit</label>
                    <input type="text" class="form-control" id="kelas_unit" name="kelas_unit" placeholder="Masukkan Kelas atau Unit" required>
                </div>
                <div class="mb-3">
                    <label for="kontak" class="form-label">Kontak</label>
                    <input type="text" class="form-control" id="kontak" name="kontak" placeholder="Masukkan Kontak (No. HP)" required>
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
