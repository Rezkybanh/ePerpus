<?php
include '../koneksi.php';

if (!isset($_SESSION['id_admin'])) {
  header("Location: login.php");
  exit;
}

$id_admin_login = $_SESSION['id_admin'];

$alertMessage = '';
$alertType = '';

$search = "";
$query = "SELECT * FROM anggota WHERE id_admin = :id_admin";

if (isset($_GET['search']) && !empty($_GET['search'])) {
  $search = $_GET['search'];
  $query .= " AND (nama LIKE :search OR nisnip LIKE :search OR kelas_unit LIKE :search)";
}

$stmt = $pdo->prepare($query);
$stmt->bindParam(':id_admin', $id_admin_login, PDO::PARAM_INT);

// Bind :search hanya jika ada nilai pencarian
if (!empty($search)) {
  $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Update Action
if (isset($_POST['action']) && $_POST['action'] == 'update') {
  $id = $_POST['id'];
  $nisnip = trim($_POST['nisnip']);
  $nama = trim($_POST['nama']);
  $kelas_unit = trim($_POST['kelas_unit']);
  $kontak = trim($_POST['kontak']);

  $isValid = true;
  $alertMessage = '';
  $alertType = 'danger';

  if (empty($nisnip)) {
    $isValid = false;
    $alertMessage = 'NIS/NIP tidak boleh kosong.';
  } elseif (!preg_match('/^[0-9]+$/', $nisnip)) {
    $isValid = false;
    $alertMessage = 'NIS/NIP harus berupa angka.';
  }

  if (empty($nama)) {
    $isValid = false;
    $alertMessage = 'Nama tidak boleh kosong.';
  } elseif (strlen($nama) > 255) {
    $isValid = false;
    $alertMessage = 'Nama tidak boleh lebih dari 255 karakter.';
  }

  if (empty($kelas_unit)) {
    $isValid = false;
    $alertMessage = 'Kelas/Unit tidak boleh kosong.';
  } elseif (strlen($kelas_unit) > 100) {
    $isValid = false;
    $alertMessage = 'Kelas/Unit tidak boleh lebih dari 100 karakter.';
  }

  if (empty($kontak)) {
    $isValid = false;
    $alertMessage = 'Kontak tidak boleh kosong.';
  } elseif (!preg_match('/^[0-9]+$/', $kontak)) {
    $isValid = false;
    $alertMessage = 'Kontak harus berupa angka.';
  }

  if ($isValid) {
    $updateQuery = "UPDATE anggota 
                    SET nama = :nama, kelas_unit = :kelas_unit, kontak = :kontak 
                    WHERE id_anggota = :id_anggota AND id_admin = :id_admin";
    $stmt = $pdo->prepare($updateQuery);
    $stmt->bindParam(':nama', $nama, PDO::PARAM_STR);
    $stmt->bindParam(':kelas_unit', $kelas_unit, PDO::PARAM_STR);
    $stmt->bindParam(':kontak', $kontak, PDO::PARAM_STR);
    $stmt->bindParam(':id_anggota', $id, PDO::PARAM_INT);
    $stmt->bindParam(':id_admin', $id_admin_login, PDO::PARAM_INT);

    if ($stmt->execute()) {
      $alertMessage = 'Data anggota berhasil diperbarui.';
      $alertType = 'success';
    } else {
      $alertMessage = 'Gagal memperbarui data anggota.';
    }
  }
}

// Handle Delete Action
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
  $id = $_POST['id'];
  $deleteQuery = "DELETE FROM anggota WHERE id_anggota = :id_anggota AND id_admin = :id_admin";
  $stmt = $pdo->prepare($deleteQuery);
  $stmt->bindParam(':id_anggota', $id, PDO::PARAM_INT);
  $stmt->bindParam(':id_admin', $id_admin_login, PDO::PARAM_INT);

  if ($stmt->execute()) {
    $alertMessage = 'Data berhasil dihapus.';
    $alertType = 'success';
  } else {
    $alertMessage = 'Gagal menghapus data.';
    $alertType = 'danger';
  }
}
?>

<div class="container-fluid mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">Data Anggota</h4>
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
        <input type="hidden" name="page" value="anggota/lihatDataAnggota.php">
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
                <th scope="col">NIS/NIP</th>
                <th scope="col">Nama Lengkap</th>
                <th scope="col">Kelas/Unit</th>
                <th scope="col">Kontak</th>
                <th scope="col" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($result as $row): ?>
                <tr>
                  <td><?= $row['nisnip']; ?></td>
                  <td><?= $row['nama']; ?></td>
                  <td><?= $row['kelas_unit']; ?></td>
                  <td><?= $row['kontak']; ?></td>
                  <td class="text-center">
                    <button class="btn btn-warning btn-sm me-1" title="Edit" data-bs-toggle="collapse" data-bs-target="#accordionEdit<?= $row['id_anggota']; ?>">
                      <i class="fas fa-edit"></i> Edit
                    </button>

                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete(event, this)">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= $row['id_anggota']; ?>">
                      <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                        <i class="fas fa-trash-alt"></i> Hapus
                      </button>
                    </form>
                  </td>
                </tr>
                <tr id="accordionEdit<?= $row['id_anggota']; ?>" class="collapse">
                  <td colspan="5">
                    <div class="p-3 bg-light border">
                      <form class="row g-3" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $row['id_anggota']; ?>">

                        <div class="col-md-6">
                          <label class="form-label">NIS/NIP</label>
                          <input type="text" class="form-control" name="nisnip" value="<?= $row['nisnip']; ?>" readonly>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" class="form-control" name="nama" value="<?= $row['nama']; ?>">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Kelas/Unit</label>
                          <input type="text" class="form-control" name="kelas_unit" value="<?= $row['kelas_unit']; ?>">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Kontak</label>
                          <input type="text" class="form-control" name="kontak" value="<?= $row['kontak']; ?>">
                        </div>
                        <div class="col-12">
                          <button type="submit" class="btn btn-primary">Save Changes</button>
                          <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#accordionEdit<?= $row['id_anggota']; ?>">Cancel</button>
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
