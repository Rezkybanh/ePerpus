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
        $username = htmlspecialchars($_POST['username']);
        $password = htmlspecialchars($_POST['password']);
        $role = 'operator'; 

        if (strlen($username) < 3 || strlen($username) > 50) {
            $message = 'Username harus memiliki panjang antara 3 hingga 50 karakter.';
            $alertType = 'error';
        } elseif (strlen($password) < 6 || strlen($password) > 225) {
            $message = 'Password harus memiliki panjang antara 6 hingga 225 karakter.';
            $alertType = 'error';
        } else {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO admin (username, password, role) VALUES (:username, :password, :role)";
                $stmt = $pdo->prepare($sql);
                
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) {
                    $message = 'Data admin berhasil disimpan!';
                    $alertType = 'success';
                } else {
                    $message = 'Gagal menyimpan data admin.';
                    $alertType = 'error';
                }
            } catch (PDOException $e) {
                $message = 'Error: ' . $e->getMessage();
                $alertType = 'error';
            }
        }
    }
}
?>

<div class="container-fluid mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Form Input Data User (Operator)</h4>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan Username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password" required>
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
