<?php
include '../koneksi.php';
include '../middleware.php';

ob_start();

function get_content_page()
{
    $allowed_pages = [
        'dashboard/dashboard.php',
        'anggota/lihatDataAnggota.php',
        'anggota/tambahDataAnggota.php',
        'buku/lihatDataBuku.php',
        'buku/tambahDataBuku.php',
        'transaksi/peminjaman.php',
        'transaksi/pengembalian.php',
        'laporan/laporanAnggota.php',
        'laporan/laporanBuku.php',
        'laporan/laporanTransaksi.php'
    ];

    $default_page = 'dashboard/dashboard.php';
    $page = isset($_GET['page']) ? $_GET['page'] : $default_page;
    $safe_page = str_replace('..', '', $page);

    if (in_array($safe_page, $allowed_pages)) {
        $file_path = "../operator/" . $safe_page;
        return file_exists($file_path) ? $file_path : '404.php';
    }

    return '404.php';
}

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_GET['page']) && isset($_GET['cetak']) && $_GET['cetak'] === 'pdf') {
    ob_start(); 

    $page = $_GET['page'];
    if ($page === 'laporan/laporanAnggota.php') {
        include '../operator/laporan/laporanAnggota.php';
        $filename = "laporan_anggota.pdf";
    } elseif ($page === 'laporan/laporanBuku.php') {
        include '../operator/laporan/laporanBuku.php';
        $filename = "laporan_buku.pdf";
    } elseif ($page === 'laporan/laporanTransaksi.php') {
        include '../operator/laporan/laporanTransaksi.php';
        $filename = "laporan_transaksi.pdf";
    } else {
        die("Laporan tidak valid."); 
    }

    $html = ob_get_clean();

    require '../vendor/autoload.php';
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream($filename, ["Attachment" => true]);
    exit;
}


$page_to_include = get_content_page();

if (isset($_GET['logout'])) {
    session_unset();  
    session_destroy();
    header("Location: ../index.php"); 
    exit(); 
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ePerpus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap");

        :root {
            --header-height: 3rem;
            --nav-width: 68px;
            --first-color: #4723D9;
            --first-color-light: #AFA5D9;
            --white-color: #F7F6FB;
            --body-font: 'Nunito', sans-serif;
            --normal-font-size: 1rem;
            --z-fixed: 100
        }

        *,
        ::before,
        ::after {
            box-sizing: border-box
        }

        body {
            position: relative;
            margin: var(--header-height) 0 0 0;
            padding: 0 1rem;
            font-family: var(--body-font);
            font-size: var(--normal-font-size);
            transition: .5s
        }

        a {
            text-decoration: none;
        }

        .header {
            width: 100%;
            height: var(--header-height);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            background-color: var(--white-color);
            z-index: var(--z-fixed);
            transition: .5s
        }

        .header_toggle {
            color: var(--first-color);
            font-size: 1.5rem;
            cursor: pointer
        }

        .header_img {
            position: absolute;
            top: 10px;
            right: 40px;
            width: 35px;
            height: 35px;
            display: flex;
            justify-content: center;
        }

        .header_img img {
            width: 150px;
            height: auto;
        }

        .l-navbar {
            position: fixed;
            top: 0;
            left: -30%;
            width: var(--nav-width);
            height: 100vh;
            background-color: var(--first-color);
            padding: .5rem 1rem 0 0;
            transition: .5s;
            z-index: var(--z-fixed)
        }

        .nav {
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden
        }

        .nav_logo,
        .nav_link {
            display: grid;
            grid-template-columns: max-content max-content;
            align-items: center;
            column-gap: 1rem;
            padding: .5rem 0 .5rem 1.5rem
        }

        .nav_logo {
            margin-bottom: 2rem
        }

        .nav_logo-icon {
            font-size: 1.25rem;
            color: var(--white-color)
        }

        .nav_logo-name {
            color: var(--white-color);
            font-weight: 700
        }

        .nav_link {
            position: relative;
            color: var(--first-color-light);
            margin-bottom: 1.5rem;
            transition: .3s
        }

        .nav_link:hover {
            color: var(--white-color)
        }

        .nav_icon {
            font-size: 1.25rem
        }

        .show {
            left: 0
        }

        .body-pd {
            padding-left: calc(var(--nav-width) + 1rem)
        }

        .active {
            color: var(--white-color)
        }

        .active::before {
            content: '';
            position: absolute;
            left: 0;
            width: 2px;
            height: 32px;
            background-color: var(--white-color)
        }

        .height-100 {
            height: 100vh
        }

        .circle-img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: transparent;
            object-fit: cover;
            border: none;
        }

        .nav_link .nav_icon,
        .dropdown .nav_icon {
            margin-right: 0.5rem;
        }

        .nav_link i,
        .dropdown i {
            margin-right: 20;
        }

        .item {
            display: none;
        }

        .dropdown-menu a i {
            font-size: 1.7rem;
            margin-right: 0;
        }




        @media screen and (min-width: 768px) {
            body {
                margin: calc(var(--header-height) + 1rem) 0 0 0;
                padding-left: calc(var(--nav-width) + 2rem)
            }

            .body-pd {
                padding-left: calc(var(--nav-width) + 188px);
            }



            .header {
                height: calc(var(--header-height) + 1rem);
                padding: 0 2rem 0 calc(var(--nav-width) + 2rem)
            }

            .header_img {
                width: 70px;
                height: 50px;
            }

            .header_img img {
                width: 200px;
            }

            .l-navbar {
                left: 0;
                padding: 1rem 1rem 0 0
            }

            .show {
                width: calc(var(--nav-width) + 156px)
            }

            .body-pd {
                padding-left: calc(var(--nav-width) + 188px)
            }

            .dropdown-menu a {
                display: flex;
                align-items: center;
            }


            .dropdown-menu a i {
                font-size: 1.7rem;
                margin-right: 0;
            }

            .item {
                display: inline;
            }

            #content-area {
                margin-top: 75px;
            }
        }
    </style>
</head>

<body id="body-pd">
    <header class="header" id="header">
        <div class="header_toggle"> <i class='bx bx-menu' id="header-toggle"></i> </div>
        <div class="header_img"> <img src="../asset/img/logotxt.png" alt=""> </div>
    </header>
    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="?page=dashboard/dashboard.php" class="nav_logo">
                    <img src="../asset/img/logobnw.png" alt="" class="circle-img">
                    <span class="nav_logo-name">
                        <h2><b>ePerpus</b></h2>
                    </span>
                </a>
                <div class="nav_list">
                    <!-- Dashboard -->
                    <a href="?page=dashboard/dashboard.php" class="nav_link">
                        <i class='bx bx-grid-alt nav_icon'></i>
                        <span class="nav_name">Dashboard</span>
                    </a>

                    <!-- Anggota -->
                    <div class="nav_link dropdown">
                        <a href="#" class="dropdown-toggle text-decoration-none text-light" id="dropdownAnggota" data-bs-toggle="dropdown">
                            <i class='bx bx-user nav_icon'></i>
                            <span class="nav_name toggle">Anggota</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="?page=anggota/lihatDataAnggota.php" class="dropdown-item">
                                    <i class="bx bx-list-ul nav_icon"></i> Lihat Data</a></li>
                            <li><a href="?page=anggota/tambahDataAnggota.php" class="dropdown-item">
                                    <i class="bx bx-plus nav_icon"></i> Tambah Data</a></li>
                        </ul>
                    </div>

                    <!-- Buku -->
                    <div class="nav_link dropdown">
                        <a href="#" class="dropdown-toggle text-decoration-none text-light" id="dropdownBuku" data-bs-toggle="dropdown">
                            <i class='bx bx-book nav_icon'></i>
                            <span class="nav_name toggle">Buku</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="?page=buku/lihatDataBuku.php" class="dropdown-item">
                                    <i class="bx bx-list-ul nav_icon"></i> Lihat Data</a></li>
                            <li><a href="?page=buku/tambahDataBuku.php" class="dropdown-item">
                                    <i class="bx bx-plus nav_icon"></i> Tambah Data</a></li>
                        </ul>
                    </div>

                    <!-- Transaksi -->
                    <div class="nav_link dropdown">
                        <a href="#" class="dropdown-toggle text-decoration-none text-light" id="dropdownTransaksi" data-bs-toggle="dropdown">
                            <i class='bx bx-wallet nav_icon'></i>
                            <span class="nav_name toggle">Transaksi</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="?page=transaksi/peminjaman.php" class="dropdown-item">
                                    <i class="bx bx-cart nav_icon"></i> Peminjaman</a></li>
                            <li><a href="?page=transaksi/pengembalian.php" class="dropdown-item">
                                    <i class="bx bx-box nav_icon"></i> Pengembalian</a></li>
                        </ul>
                    </div>

                    <!-- Cetak Laporan -->
                    <div class="nav_link dropdown">
                        <a href="#" class="dropdown-toggle text-decoration-none text-light" id="dropdownLaporan" data-bs-toggle="dropdown">
                            <i class='bx bx-file nav_icon'></i>
                            <span class="nav_name toggle">Cetak Laporan</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="?page=laporan/laporanAnggota.php&cetak=pdf" class="dropdown-item">
                                    <i class="bx bx-user nav_icon"></i> Anggota</a>
                            </li>
                            <li>
                                <a href="?page=laporan/laporanBuku.php&cetak=pdf" class="dropdown-item">
                                    <i class="bx bx-book nav_icon"></i> Buku</a>
                            </li>
                            <li>
                                <a href="?page=laporan/laporanTransaksi.php&cetak=pdf" class="dropdown-item">
                                    <i class="bx bx-wallet nav_icon"></i> Transaksi</a>
                            </li>

                        </ul>
                    </div>
                </div>
            </div>
            <a href="?logout=true" class="nav_link">
                <i class='bx bx-log-out nav_icon'></i> <span class="nav_name">SignOut</span>
            </a>
        </nav>
    </div>

    <div class="height-100 bg-light" style="margin-top: 75px;" id="content-area">
        <?php include $page_to_include; ?>
    </div>
    <!-- SweetAlert untuk menampilkan pesan -->
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <script>
            Swal.fire({
                icon: '<?php echo htmlspecialchars($_GET['status']); ?>',
                title: '<?php echo ucfirst(htmlspecialchars($_GET['status'])); ?>!',
                text: '<?php echo htmlspecialchars($_GET['message']); ?>',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        </script>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function(event) {
            const showNavbar = (toggleId, navId, bodyId, headerId) => {
                const toggle = document.getElementById(toggleId),
                    nav = document.getElementById(navId),
                    bodypd = document.getElementById(bodyId),
                    headerpd = document.getElementById(headerId)
                if (toggle && nav && bodypd && headerpd) {
                    toggle.addEventListener('click', () => {
                        nav.classList.toggle('show')
                        toggle.classList.toggle('bx-x')
                        bodypd.classList.toggle('body-pd')
                        headerpd.classList.toggle('body-pd')
                    })
                }
            }
            showNavbar('header-toggle', 'nav-bar', 'body-pd', 'header')
            const linkColor = document.querySelectorAll('.nav_link')

            function colorLink() {
                if (linkColor) {
                    linkColor.forEach(l => l.classList.remove('active'))
                    this.classList.add('active')
                }
            }
            linkColor.forEach(l => l.addEventListener('click', colorLink))
        });
    </script>


    <script>

    </script>
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        $status = $_GET['status']; 
        $message = $_GET['message']; 
    ?>
        <!-- SweetAlert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            Swal.fire({
                icon: '<?php echo $status; ?>',
                title: '<?php echo ucfirst($status); ?>!',
                text: '<?php echo $message; ?>',
                showConfirmButton: true,
                confirmButtonText: 'OK'
            });
        </script>
    <?php
    }
    ?>


</body>

</html>

<?php
ob_end_flush(); 
?>