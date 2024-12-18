<style>
    body {
        font-family: Arial, sans-serif;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #343a40;
        color: white;
    }

    td {
        border: 1px solid #ddd;
    }

    .text-center {
        text-align: center;
    }

    .text-end {
        text-align: right;
    }
</style>

<h1 class="text-center mb-4">Laporan Data Transaksi</h1>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>ID Riwayat</th>
            <th>Nama Anggota</th>
            <th>Judul Buku</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
            <th>Status</th>
            <th>Total Denda</th>
        </tr>
    </thead>
    <tbody>
        <?php
        require '../koneksi.php';

        $idAdmin = $_SESSION['id_admin'];

        try {
            $queryRiwayat = "
                SELECT * 
                FROM riwayat 
                WHERE id_admin = :id_admin
            ";

            $stmtRiwayat = $pdo->prepare($queryRiwayat);
            $stmtRiwayat->bindParam(':id_admin', $idAdmin, PDO::PARAM_INT);
            $stmtRiwayat->execute();

            if ($stmtRiwayat->rowCount() > 0) {
                $no = 1;
                while ($row = $stmtRiwayat->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['id_riwayat']}</td>
                        <td>{$row['nama_anggota']}</td>
                        <td>{$row['judul_buku']}</td>
                        <td>{$row['tanggal_pinjam']}</td>
                        <td>{$row['tanggal_kembali']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['total_denda']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Data riwayat tidak ditemukan.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='8' class='text-center'>Terjadi kesalahan: {$e->getMessage()}</td></tr>";
        }
        ?>
    </tbody>
</table>

<div class="text-end mt-4">
    <p><b>Tanggal:</b> <?php echo date("d-m-Y"); ?></p>
    <p><b>Ditandatangani oleh:</b></p>
    <br><br>
    <p><b>Administrator Perpustakaan</b></p>
</div>