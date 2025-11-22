<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
 
$query = mysqli_query($conn, "SELECT * FROM wisata");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wisata Madura - Hidden Paradise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .hero-section {
            background: url('https://images.unsplash.com/photo-1596401057633-565652b5e66c?q=80&w=1933&auto=format&fit=crop') center/cover no-repeat;
            height: 400px;
            position: relative;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;  
        }
        .narasi {
            font-size: 0.9rem;
            color: #555;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Batasi text cuma 3 baris */
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-umbrella-beach"></i> MaduraTrip</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="smart.php">Cari Rekomendasi (SMART)</a></li>
                    
                    <?php if(isset($_SESSION['login'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Halo, <b><?= $_SESSION['username']; ?></b>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Dashboard Admin</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link btn btn-light text-primary ms-2 px-3 rounded-pill" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container position-relative z-1">
            <h1 class="display-4 fw-bold">Jelajahi Keindahan Madura</h1>
            <p class="lead">Temukan destinasi wisata terbaik, dari pantai eksotis hingga wisata religi penuh makna.</p>
            <a href="#katalog" class="btn btn-warning btn-lg fw-bold mt-3">Mulai Menjelajah</a>
        </div>
    </div>

    <div class="container my-5" id="katalog">
        <div class="row text-center mb-4">
            <div class="col">
                <h2>Destinasi Populer</h2>
                <p class="text-muted">Berikut adalah daftar wisata yang tersedia di database kami.</p>
            </div>
        </div>

        <div class="row">
            <?php while($row = mysqli_fetch_assoc($query)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 hover-shadow">
                    <img src="img/<?= $row['gambar_url']; ?>" class="card-img-top" alt="<?= $row['nama_wisata']; ?>" onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'">
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title fw-bold mb-0"><?= $row['nama_wisata']; ?></h5>
                            <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> <?= $row['rating']; ?></span>
                        </div>
                        
                        <p class="text-muted small mb-2"><i class="fas fa-map-marker-alt text-danger"></i> <?= $row['alamat']; ?></p>
                        
                        <p class="narasi">
                            Nikmati pengalaman seru di <b><?= $row['nama_wisata']; ?></b>. 
                            Destinasi ini hanya berjarak <?= $row['jarak_km']; ?> KM dari pusat kota. 
                            Dengan tiket masuk terjangkau Rp<?= number_format($row['biaya_masuk']); ?>, 
                            Anda bisa menikmati fasilitas seperti <?= strtolower($row['fasilitas']); ?>.
                            Telah direview oleh <?= $row['jumlah_review']; ?> pengunjung.
                        </p>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary btn-sm">Lihat Detail</button>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <small class="text-muted">Harga Tiket: <b>Rp<?= number_format($row['biaya_masuk']); ?></b></small>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 Wisata Madura.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>