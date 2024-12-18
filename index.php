<?php
session_start();

include 'koneksi.php'; 

$message = "";
$messageType = ""; 

if (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $messageType = "danger";
    unset($_SESSION['error_message']); 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['userName']);
    $password = $_POST['password'];

    try {
        $query = "SELECT id_admin, password, role FROM admin WHERE username = :username";
        $stmt = $pdo->prepare($query); 
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id_admin = $row['id_admin'];
            $hashedPassword = $row['password'];
            $role = $row['role'];

            if (password_verify($password, $hashedPassword)) {
                $_SESSION['id_admin'] = $id_admin;
                $_SESSION['role'] = $role; 

                if ($role === 'kepala_perpus') {
                    header("Location: kepalaPerpus/index.php");
                    exit;
                } elseif ($role === 'operator') {
                    header("Location: operator/index.php");
                    exit;
                }
            } else {
                $message = "Username atau Password Salah, silahkan coba lagi";
                $messageType = "danger";
            }
        } else {
            $message = "Username atau Password Salah, silahkan coba lagi";
            $messageType = "danger";
        }
    } catch (PDOException $e) {
        $message = "Terjadi kesalahan dalam query: " . $e->getMessage();
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #ecf0f3;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .wrapper {
            max-width: 350px;
            width: 100%;
            padding: 40px 30px 30px 30px;
            background-color: #ecf0f3;
            border-radius: 15px;
            box-shadow: 13px 13px 20px #cbced1, -13px -13px 20px #fff;
        }

        .logo {
            width: 80px;
            margin: auto;
        }

        .logo img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }

        .wrapper .name {
            font-weight: 600;
            font-size: 1.4rem;
            letter-spacing: 1.3px;
            text-align: center;
            color: #555;
        }

        .wrapper .form-field input {
            width: 100%;
            display: block;
            border: none;
            outline: none;
            background: none;
            font-size: 1.2rem;
            color: #666;
            padding: 10px 15px 10px 10px;
        }

        .wrapper .form-field {
            padding-left: 10px;
            margin-bottom: 20px;
            border-radius: 20px;
            box-shadow: inset 8px 8px 8px #cbced1, inset -8px -8px 8px #fff;
        }

        .wrapper .form-field .fas {
            color: #555;
        }

        .wrapper .btn {
            width: 100%;
            height: 40px;
            background-color: #03A9F4;
            color: #fff;
            border-radius: 25px;
            box-shadow: 3px 3px 3px #b1b1b1,
                        -3px -3px 3px #fff;
            letter-spacing: 1.3px;
        }

        .wrapper .btn:hover {
            background-color: #039BE5;
        }

        .wrapper a {
            text-decoration: none;
            font-size: 0.8rem;
            color: #03A9F4;
        }

        .wrapper a:hover {
            color: #039BE5;
        }

        @media(max-width: 380px) {
            .wrapper {
                padding: 40px 15px 15px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="logo">
            <img src="asset/img/logo.jpeg" alt="Logo">
        </div>
        <div class="text-center mt-4 name">
            ePerpus
        </div>
        <form class="p-3 mt-3" method="POST" action="index.php">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> text-center">
                    <?= $message ?>
                </div>
                <script>
                    setTimeout(() => {
                        document.querySelector('.alert').remove();
                    }, 2000);
                </script>
            <?php endif; ?>
            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="text" name="userName" id="userName" placeholder="Username" required>
            </div>
            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" name="password" id="pwd" placeholder="Password" required>
            </div>
            <button type="submit" class="btn mt-3">Login</button>
        </form>
        <div class="text-center fs-8">
            Belum punya akun? <a href="register.php">Sign up</a>
        </div>
    </div>
</body>
</html>