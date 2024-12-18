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

<h1 class="text-center mb-4">Laporan Data Buku</h1>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>ID Buku</th>
            <th>Judul</th>
            <th>Pengarang</th>
            <th>Penerbit</th>
            <th>Tahun Terbit</th>
            <th>ISBN</th>
            <th>Stok</th>
        </tr>
    </thead>
    <tbody>
        <?php
        require '../koneksi.php'; 

        $idAdmin = $_SESSION['id_admin'];

        try {
            $queryBuku = "SELECT * FROM buku";
            $stmt = $pdo->prepare($queryBuku);
            $stmt->execute();


            if ($stmt->rowCount() > 0) {
                $no = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['id_buku']}</td>
                        <td>{$row['judul']}</td>
                        <td>{$row['pengarang']}</td>
                        <td>{$row['penerbit']}</td>
                        <td>{$row['tahun_terbit']}</td>
                        <td>{$row['isbn']}</td>
                        <td>{$row['stok']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Data buku tidak ditemukan.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='8' class='text-center'>Terjadi kesalahan: " . $e->getMessage() . "</td></tr>";
        }
        ?>
    </tbody>
</table>

<div class="text-end mt-4">
    <p><b>Tanggal:</b> <?php echo date("d-m-Y"); ?></p>
    <p><b>Ditandatangani oleh:</b></p>
    <br><br>
    <p><b>Kepala Perpustakaan</b></p>
</div>