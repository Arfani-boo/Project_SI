<?php
include "koneksi.php";

$query = "
    SELECT w.id_wisata, w.nama_wisata, s.skor_rating, s.skor_review, s.skor_jarak,  s.skor_biaya, s.skor_fasilitas FROM wisata w JOIN skor_kriteria s ON w.id_wisata = s.id_wisata
";

$data = mysqli_query($conn, $query);

$rating_arr = $review_arr = $jarak_arr = $biaya_arr = $fasilitas_arr = [];

while ($row = mysqli_fetch_assoc($data)) {
    $rating_arr[]    = $row['skor_rating'];
    $review_arr[]    = $row['skor_review'];
    $jarak_arr[]     = $row['skor_jarak'];
    $biaya_arr[]     = $row['skor_biaya'];
    $fasilitas_arr[] = $row['skor_fasilitas'];

    $rows[] = $row;
}

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

$w = [
    'rating'    => 25,
    'review'    => 20,
    'jarak'     => 15,
    'biaya'     => 20,
    'fasilitas' => 20
];

$total_w = array_sum($w);

$nk = [
    'rating'    => $w['rating']    / $total_w,
    'review'    => $w['review']    / $total_w,
    'jarak'     => $w['jarak']     / $total_w,
    'biaya'     => $w['biaya']     / $total_w,
    'fasilitas' => $w['fasilitas'] / $total_w
];

$hasil = [];

foreach ($rows as $r) {
    $u_rating =    ($r['skor_rating']    - $cmin['rating'])    / ($cmax['rating']    - $cmin['rating']);
    $u_review =    ($r['skor_review']    - $cmin['review'])    / ($cmax['review']    - $cmin['review']);
    $u_jarak =     ($r['skor_jarak']     - $cmin['jarak'])     / ($cmax['jarak']     - $cmin['jarak']);
    $u_biaya =     ($r['skor_biaya']     - $cmin['biaya'])     / ($cmax['biaya']     - $cmin['biaya']);
    $u_fasilitas = ($r['skor_fasilitas'] - $cmin['fasilitas']) / ($cmax['fasilitas'] - $cmin['fasilitas']);
    $NA = 
        ($nk['rating']    * $u_rating) +
        ($nk['review']    * $u_review) +
        ($nk['jarak']     * $u_jarak) +
        ($nk['biaya']     * $u_biaya) +
        ($nk['fasilitas'] * $u_fasilitas);

    $hasil[] = [
        'nama' => $r['nama_wisata'],
        'nilai' => $NA
    ];
}

usort($hasil, function($a, $b){
    return $b['nilai'] <=> $a['nilai'];
});

echo "<h2>Hasil Rekomendasi Wisata (Metode SMART)</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr>
        <th>Ranking</th>
        <th>Nama Wisata</th>
        <th>Nilai Akhir</th>
      </tr>";

$rank = 1;
foreach ($hasil as $h) {
    echo "<tr>
            <td>".$rank++."</td>
            <td>".$h['nama']."</td>
            <td>".round($h['nilai'], 3)."</td>
          </tr>";
}

echo "</table>";
?>
