<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit;
}

$alertMessage = '';
$alertType = '';

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

try {
    if ($search != "") {
        $query = "SELECT * FROM admin WHERE username LIKE :search OR role LIKE :search";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    } else {
        $query = "SELECT * FROM admin";
        $stmt = $pdo->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $alertMessage = 'Gagal mengambil data: ' . $e->getMessage();
    $alertType = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $role = trim($_POST['role']);

    $isValid = true;
    $alertMessage = '';
    $alertType = 'danger';

    if (empty($username)) {
        $isValid = false;
        $alertMessage = 'Username tidak boleh kosong.';
    } elseif (strlen($username) > 50) {
        $isValid = false;
        $alertMessage = 'Username tidak boleh lebih dari 50 karakter.';
    }

    if (empty($role)) {
        $isValid = false;
        $alertMessage = 'Role tidak boleh kosong.';
    }

    if ($isValid) {
        try {
            $updateQuery = "UPDATE admin SET username = :username, role = :role WHERE id_admin = :id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $alertMessage = 'Data admin berhasil diperbarui.';
            $alertType = 'success';
        } catch (PDOException $e) {
            $alertMessage = 'Gagal memperbarui data admin: ' . $e->getMessage();
            $alertType = 'danger';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    try {
        $deleteQuery = "DELETE FROM admin WHERE id_admin = :id";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $alertMessage = 'Data admin berhasil dihapus.';
        $alertType = 'success';
    } catch (PDOException $e) {
        $alertMessage = 'Gagal menghapus data admin: ' . $e->getMessage();
        $alertType = 'danger';
    }
}
?>


<div class="container-fluid mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Data Admin</h4>
        </div>
        <div class="card-body">
            <!-- Alert Message -->
            <?php if ($alertMessage): ?>
                <div class="alert alert-<?= $alertType; ?> alert-dismissible fade show" role="alert">
                    <?= $alertMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Form Search -->
            <form method="GET" action="index.php" class="d-flex justify-content-end mb-3">
                <input type="hidden" name="page" value="user/lihatDataUser.php">
                <div class="input-group w-50">
                    <input type="text" name="search" class="form-control" placeholder="Search" aria-label="Search" value="<?= htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <?php if (count($result) == 0): ?>
                <div class="alert alert-danger">Data tidak ditemukan! Menampilkan semua data.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">Username</th>
                                <th scope="col">Role</th>
                                <th scope="col" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td><?= htmlspecialchars($row['role']); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm me-1" title="Edit" data-bs-toggle="collapse" data-bs-target="#accordionEdit<?= $row['id_admin']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <form method="POST" style="display:inline;" onsubmit="return confirmDelete(event, this)">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $row['id_admin']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <tr id="accordionEdit<?= $row['id_admin']; ?>" class="collapse">
                                    <td colspan="3">
                                        <div class="p-3 bg-light border">
                                            <form class="row g-3" method="POST">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id" value="<?= $row['id_admin']; ?>">

                                                <div class="col-md-6">
                                                    <label class="form-label">Username</label>
                                                    <input type="text" class="form-control" name="username" value="<?= $row['username']; ?>" readonly>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Role</label>
                                                    <select class="form-select" name="role">
                                                        <option value="kepala_perpus" <?= $row['role'] === 'kepala_perpus' ? 'selected' : ''; ?>>Kepala Perpus</option>
                                                        <option value="operator" <?= $row['role'] === 'operator' ? 'selected' : ''; ?>>Operator</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#accordionEdit<?= $row['id_admin']; ?>">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
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

<script>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data ini akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
