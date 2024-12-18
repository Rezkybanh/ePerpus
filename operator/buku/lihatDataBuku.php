<?php
include '../koneksi.php';

$alertMessage = '';
$alertType = '';

if (!isset($_SESSION['id_admin'])) {
    die("Akses ditolak, Anda harus login terlebih dahulu.");
}

$id_admin = $_SESSION['id_admin'];

$search = "";
$query = "SELECT * FROM buku WHERE id_admin = :id_admin";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    $query .= " AND (judul LIKE :search OR pengarang LIKE :search OR penerbit LIKE :search OR isbn LIKE :search)";
}

$stmt = $pdo->prepare($query);
$stmt->bindValue(':id_admin', $id_admin, PDO::PARAM_INT);

// Bind :search hanya jika ada pencarian
if (!empty($search)) {
    $searchTerm = "%" . $search . "%";
    $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $penerbit = trim($_POST['penerbit']);
    $tahun_terbit = trim($_POST['tahun_terbit']);
    $isbn = trim($_POST['isbn']);
    $stok = intval($_POST['stok']);

    if (
        strlen($judul) > 255 || strlen($pengarang) > 255 || strlen($penerbit) > 255 ||
        strlen($isbn) > 20 || !ctype_digit($tahun_terbit) || strlen($tahun_terbit) != 4 ||
        $stok < 0
    ) {
        $alertMessage = 'Validasi gagal! Pastikan data sesuai dengan batasan.';
        $alertType = 'danger';
    } else {
        $stmt = $pdo->prepare("UPDATE buku SET judul=:judul, pengarang=:pengarang, penerbit=:penerbit, tahun_terbit=:tahun_terbit, isbn=:isbn, stok=:stok WHERE id_buku=:id AND id_admin=:id_admin");
        $stmt->bindValue(':judul', $judul, PDO::PARAM_STR);
        $stmt->bindValue(':pengarang', $pengarang, PDO::PARAM_STR);
        $stmt->bindValue(':penerbit', $penerbit, PDO::PARAM_STR);
        $stmt->bindValue(':tahun_terbit', $tahun_terbit, PDO::PARAM_STR);
        $stmt->bindValue(':isbn', $isbn, PDO::PARAM_STR);
        $stmt->bindValue(':stok', $stok, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':id_admin', $id_admin, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $alertMessage = 'Data buku berhasil diperbarui.';
            $alertType = 'success';
        } else {
            $alertMessage = 'Gagal memperbarui data buku.';
            $alertType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM buku WHERE id_buku = :id AND id_admin = :id_admin");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':id_admin', $id_admin, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $alertMessage = 'Data buku berhasil dihapus.';
        $alertType = 'success';
    } else {
        $alertMessage = 'Gagal menghapus data buku.';
        $alertType = 'danger';
    }
}
?>

<div class="container-fluid mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Data Buku</h4>
        </div>
        <div class="card-body">
            <?php if ($alertMessage): ?>
                <div class="alert alert-<?= $alertType; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?= $alertType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?= $alertMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="GET" action="index.php" class="d-flex justify-content-end mb-3">
                <input type="hidden" name="page" value="buku/lihatDataBuku.php">
                <div class="input-group w-50">
                    <input type="text" name="search" class="form-control" placeholder="Search" aria-label="Search" value="<?= htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <?php if (count($result) == 0): ?>
                <div class="alert alert-danger">Data tidak ditemukan!</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">Judul</th>
                                <th scope="col">Pengarang</th>
                                <th scope="col">Penerbit</th>
                                <th scope="col">Tahun Terbit</th>
                                <th scope="col">ISBN</th>
                                <th scope="col">Stok</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?= $row['judul']; ?></td>
                                    <td><?= $row['pengarang']; ?></td>
                                    <td><?= $row['penerbit']; ?></td>
                                    <td><?= $row['tahun_terbit']; ?></td>
                                    <td><?= $row['isbn']; ?></td>
                                    <td><?= $row['stok']; ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm me-1" data-bs-toggle="collapse" data-bs-target="#editForm<?= $row['id_buku']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="confirmDelete(event, this)">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $row['id_buku']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <tr id="editForm<?= $row['id_buku']; ?>" class="collapse">
                                    <td colspan="7">
                                        <form method="POST" class="p-3 bg-light" onsubmit="confirmSave(event, this)">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="id" value="<?= $row['id_buku']; ?>">
                                            <div class="mb-3">
                                                <label>Judul</label>
                                                <input type="text" name="judul" class="form-control" value="<?= $row['judul']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label>Pengarang</label>
                                                <input type="text" name="pengarang" class="form-control" value="<?= $row['pengarang']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label>Penerbit</label>
                                                <input type="text" name="penerbit" class="form-control" value="<?= $row['penerbit']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label>Tahun Terbit</label>
                                                <input type="text" name="tahun_terbit" class="form-control" value="<?= $row['tahun_terbit']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label>ISBN</label>
                                                <input type="text" name="isbn" class="form-control" value="<?= $row['isbn']; ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label>Stok</label>
                                                <input type="number" name="stok" class="form-control" value="<?= $row['stok']; ?>">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#editForm<?= $row['id_buku']; ?>">Batal</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Yakin ingin menghapus?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    function confirmSave(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Simpan perubahan?',
            text: "Pastikan semua data sudah benar!",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, simpan!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>