<?php
session_start();

if (!isset($_SESSION['id_admin'])) {
    $_SESSION['error_message'] = "Silahkan Login Terlebih Dahulu!";
    header("Location: ../index.php");
    exit();
}

include 'koneksi.php'; 

try {
    $query = "SELECT role FROM admin WHERE id_admin = :id_admin";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_admin', $_SESSION['id_admin'], PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $role = $row['role'];

        $currentPage = basename($_SERVER['PHP_SELF']); 

        if ($role === 'kepala_perpus') {
            if (strpos($_SERVER['REQUEST_URI'], '/kepalaPerpus/') === false) {
                header("Location: ../index.php"); 
                exit();
            }
        } elseif ($role === 'operator') {
            if (strpos($_SERVER['REQUEST_URI'], '/operator/') === false) {
                header("Location: ../index.php"); 
                exit();
            }
        }

    } else {
        $_SESSION['error_message'] = "Pengguna tidak ditemukan!";
        header("Location: ../index.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Terjadi kesalahan database: " . $e->getMessage();
    header("Location: ../index.php");
    exit();
}
?>
