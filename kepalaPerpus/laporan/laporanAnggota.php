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

<h1 class="text-center mb-4">Laporan Data Anggota</h1>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>ID Anggota</th>
            <th>NIS/NIP</th>
            <th>Nama</th>
            <th>Kelas/Unit</th>
            <th>Kontak</th>
        </tr>
    </thead>
    <tbody>
        <?php
        require '../koneksi.php';

        try {
            $queryAnggota = "SELECT * FROM anggota";
            $stmt = $pdo->prepare($queryAnggota);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $no = 1;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>
                        <td>{$no}</td>
                        <td>{$row['id_anggota']}</td>
                        <td>{$row['nisnip']}</td>
                        <td>{$row['nama']}</td>
                        <td>{$row['kelas_unit']}</td>
                        <td>{$row['kontak']}</td>
                    </tr>";
                    $no++;
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>Data anggota tidak ditemukan.</td></tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='6' class='text-center'>Error: " . $e->getMessage() . "</td></tr>";
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
