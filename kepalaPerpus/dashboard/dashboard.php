<?php
include '../koneksi.php'; 

if (!isset($_SESSION['id_admin'])) {
    die("Akses ditolak, Anda harus login terlebih dahulu.");
}

$id_admin = $_SESSION['id_admin'];

try {
    $sqlAnggota = "SELECT COUNT(*) AS total_anggota FROM anggota";
    $stmtAnggota = $pdo->prepare($sqlAnggota);
    $stmtAnggota->execute();
    $totalAnggota = $stmtAnggota->fetch(PDO::FETCH_ASSOC)['total_anggota'];

    $sqlBuku = "SELECT COUNT(*) AS total_buku FROM buku";
    $stmtBuku = $pdo->prepare($sqlBuku);
    $stmtBuku->execute();
    $totalBuku = $stmtBuku->fetch(PDO::FETCH_ASSOC)['total_buku'];

    $sqlTransaksi = "SELECT COUNT(*) AS total_transaksi FROM transaksi";
    $stmtTransaksi = $pdo->prepare($sqlTransaksi);
    $stmtTransaksi->execute();
    $totalTransaksi = $stmtTransaksi->fetch(PDO::FETCH_ASSOC)['total_transaksi'];

    $sqlRiwayat = "SELECT * FROM riwayat";
    $stmtRiwayat = $pdo->prepare($sqlRiwayat);
    $stmtRiwayat->execute();
    $resultRiwayat = $stmtRiwayat->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_POST['hapus'])) {
        $idTransaksi = $_POST['id_transaksi'];

        $sqlDeleteTransaksi = "DELETE FROM transaksi WHERE id_transaksi = :id_transaksi";
        $stmtDeleteTransaksi = $pdo->prepare($sqlDeleteTransaksi);
        $stmtDeleteTransaksi->bindParam(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
        $stmtDeleteTransaksi->execute();

        $sqlDeleteRiwayat = "DELETE FROM riwayat WHERE id_transaksi = :id_transaksi";
        $stmtDeleteRiwayat = $pdo->prepare($sqlDeleteRiwayat);
        $stmtDeleteRiwayat->bindParam(':id_transaksi', $idTransaksi, PDO::PARAM_INT);
        $stmtDeleteRiwayat->execute();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>


<style>
    .table-container {
        max-height: 200px;
        overflow-y: auto;
    }
</style>

<div class="container-fluid">
    <!-- Dashboard Section -->
    <div class="bg-primary text-white rounded-top p-2">
        <h1 class="h5 font-weight-bold">Dashboard</h1>
    </div>
    <div class="bg-white shadow-sm rounded-bottom p-4">
        <div class="row g-3">
            <div class="col-lg-4 col-md-6">
                <div class="bg-primary text-white rounded p-4 text-center h-100">
                    <p class="display-4 fw-bold"><?php echo $totalAnggota; ?></p>
                    <p>Anggota</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="bg-primary text-white rounded p-4 text-center h-100">
                    <p class="display-5 fw-bold"><?php echo $totalBuku; ?></p>
                    <p>Total Buku Tersedia</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="bg-primary text-white rounded p-4 text-center h-100">
                    <p class="display-5 fw-bold"><?php echo $totalTransaksi; ?></p>
                    <p>Total Transaksi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi Section -->
    <div class="bg-primary text-white rounded-top p-2 mt-4">
        <h1 class="h5 font-weight-bold">Riwayat Transaksi</h1>
    </div>
    <div class="bg-white shadow-sm rounded-bottom p-4">
        <div class="table-container">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nama Anggota</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultRiwayat as $row) : ?>
                        <tr>
                            <td><?php echo $row['id_transaksi']; ?></td>
                            <td><?php echo $row['nama_anggota']; ?></td>
                            <td><?php echo $row['judul_buku']; ?></td>
                            <td><?php echo $row['tanggal_pinjam']; ?></td>
                            <td><?php echo $row['tanggal_kembali']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="id_transaksi" value="<?php echo $row['id_transaksi']; ?>">
                                    <button type="submit" name="hapus" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function loadContent(file) {
            $("#content-area").load(file, function(response, status, xhr) {
                if (status === "error") {
                    $("#content-area").html("<p>Error loading page: " + xhr.status + " " + xhr.statusText + "</p>");
                }
            });
        }

        $(".nav_link, .dropdown-item").on("click", function(e) {
            e.preventDefault();
            const file = $(this).data("file");
            if (file) {
                loadContent(file);
            }
        });
    });
</script>