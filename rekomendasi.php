<?php
include "koneksi.php";

// --- FUNGSI BANTUAN (Ditaruh di luar loop) ---
// Fungsi untuk menghitung nilai utilitas dan mencegah error division by zero
function get_utility($val, $min, $max) {
    if ($max - $min == 0) {
        return 1; // Jika nilai max dan min sama, kembalikan 1 (atau 0, sesuai preferensi)
    }
    return ($val - $min) / ($max - $min);
}

// 1. Ambil Data dari Database
$query = "
    SELECT 
        w.id_wisata, 
        w.nama_wisata, 
        w.gambar_url,
        w.alamat,
        w.biaya_masuk,
        s.skor_rating, 
        s.skor_review, 
        s.skor_jarak, 
        s.skor_biaya, 
        s.skor_fasilitas 
    FROM wisata w 
    JOIN skor_kriteria s ON w.id_wisata = s.id_wisata
";

$data = mysqli_query($conn, $query);

$rating_arr = $review_arr = $jarak_arr = $biaya_arr = $fasilitas_arr = [];
$rows = [];

// 2. Kumpulkan Data
while ($row = mysqli_fetch_assoc($data)) {
    $rating_arr[]    = $row['skor_rating'];
    $review_arr[]    = $row['skor_review'];
    $jarak_arr[]     = $row['skor_jarak'];
    $biaya_arr[]     = $row['skor_biaya'];
    $fasilitas_arr[] = $row['skor_fasilitas'];

    $rows[] = $row;
}

// Cek jika data kosong
if (empty($rows)) {
    echo "<div class='alert alert-warning m-5'>Belum ada data wisata yang dinilai.</div>";
    exit;
}

// 3. Cari Nilai Min & Max
$cmin = [
    'rating'    => min($rating_arr),
    'review'    => min($review_arr),
    'jarak'     => min($jarak_arr),
    'biaya'     => min($biaya_arr),
    'fasilitas' => min($fasilitas_arr)
];

$cmax = [
    'rating'    => max($rating_arr),
    'review'    => max($review_arr),
    'jarak'     => max($jarak_arr),
    'biaya'     => max($biaya_arr),
    'fasilitas' => max($fasilitas_arr)
];

// 4. Definisi Bobot (W)
$w = [
    'rating'    => 25,
    'review'    => 20,
    'jarak'     => 15,
    'biaya'     => 20,
    'fasilitas' => 20
];

$total_w = array_sum($w);

// 5. Normalisasi Bobot
$nk = [
    'rating'    => $w['rating']    / $total_w,
    'review'    => $w['review']    / $total_w,
    'jarak'     => $w['jarak']     / $total_w,
    'biaya'     => $w['biaya']     / $total_w,
    'fasilitas' => $w['fasilitas'] / $total_w
];

// 6. Hitung Nilai Akhir (SMART)
$hasil = [];

foreach ($rows as $r) {
    // Memanggil fungsi yang sudah didefinisikan di atas
    $u_rating    = get_utility($r['skor_rating'], $cmin['rating'], $cmax['rating']);
    $u_review    = get_utility($r['skor_review'], $cmin['review'], $cmax['review']);
    $u_jarak     = get_utility($r['skor_jarak'], $cmin['jarak'], $cmax['jarak']);
    $u_biaya     = get_utility($r['skor_biaya'], $cmin['biaya'], $cmax['biaya']);
    $u_fasilitas = get_utility($r['skor_fasilitas'], $cmin['fasilitas'], $cmax['fasilitas']);

    $NA = ($nk['rating'] * $u_rating) +
          ($nk['review'] * $u_review) +
          ($nk['jarak'] * $u_jarak) +
          ($nk['biaya'] * $u_biaya) +
          ($nk['fasilitas'] * $u_fasilitas);

    $hasil[] = [
        'id' => $r['id_wisata'],
        'nama' => $r['nama_wisata'],
        'alamat' => $r['alamat'],
        'biaya_masuk' => $r['biaya_masuk'],
        'gambar' => $r['gambar_url'],
        'nilai' => $NA
    ];
}

// 7. Ranking
usort($hasil, function($a, $b){
    return $b['nilai'] <=> $a['nilai'];
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Wisata - Metode SMART</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-wisata { transition: transform 0.2s; }
        .card-wisata:hover { transform: translateY(-5px); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .badge-rank { position: absolute; top: 10px; left: 10px; font-size: 1.2em; z-index: 1; }
        .img-cover { height: 200px; object-fit: cover; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="text-center mb-4 fw-bold">Rekomendasi Wisata Terbaik</h2>
    <p class="text-center text-muted mb-5">Diurutkan berdasarkan metode SMART (Rating, Review, Jarak, Biaya, Fasilitas)</p>

    <div class="row mb-5 justify-content-center">
        <?php 
        $top3 = array_slice($hasil, 0, 3);
        foreach ($top3 as $index => $wisata): 
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 card-wisata border-0 shadow-sm">
                <span class="badge bg-primary badge-rank">#<?= $index + 1 ?></span>
                <img src="<?= !empty($wisata['gambar']) ? $wisata['gambar'] : 'https://via.placeholder.com/400x250?text=No+Image' ?>" class="card-img-top img-cover" alt="<?= $wisata['nama'] ?>">
                <div class="card-body">
                    <h5 class="card-title fw-bold"><?= $wisata['nama'] ?></h5>
                    <p class="card-text text-muted small text-truncate"><?= $wisata['alamat'] ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-success">Nilai: <?= number_format($wisata['nilai'], 4) ?></span>
                        <span class="text-primary fw-bold">Rp <?= number_format($wisata['biaya_masuk']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Ranking Lengkap</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Rank</th>
                            <th>Nama Wisata</th>
                            <th>Lokasi</th>
                            <th class="text-center">Nilai SMART</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil as $index => $h): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $index + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?= !empty($h['gambar']) ? $h['gambar'] : 'https://via.placeholder.com/50' ?>" class="rounded me-2" width="50" height="50" style="object-fit: cover;">
                                    <span><?= $h['nama'] ?></span>
                                </div>
                            </td>
                            <td class="small text-muted"><?= substr($h['alamat'], 0, 60) ?>...</td>
                            <td class="text-center fw-bold text-primary"><?= number_format($h['nilai'], 5) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>