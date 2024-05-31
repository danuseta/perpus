<?php
date_default_timezone_set('Asia/Jakarta');
include 'db.php';

$npm = $_GET['npm'];

$sql_get_nama = "SELECT nama FROM mahasiswa WHERE npm = '$npm'";
$result_get_nama = $conn->query($sql_get_nama);

if ($result_get_nama->num_rows > 0) {
    $row_nama = $result_get_nama->fetch_assoc();
    $nama_mahasiswa = $row_nama['nama'];
} else {
    echo "<p>NPM tidak ditemukan. Harap daftar sebagai mahasiswa terlebih dahulu.</p>";
    exit();
}

$sql_riwayat_peminjaman = "
SELECT peminjaman.id, buku.judul, buku.penulis, buku.tahun_terbit, peminjaman.tanggal_pinjam, peminjaman.tanggal_kembali, peminjaman.keterangan
FROM peminjaman
JOIN buku ON peminjaman.buku_id = buku.id
WHERE peminjaman.npm = '$npm'
ORDER BY peminjaman.tanggal_pinjam ASC
";
$result_riwayat_peminjaman = $conn->query($sql_riwayat_peminjaman);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Peminjaman Buku</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<style>
    .red {
        color: red;
    }

    .green {
        color: green;
    }
</style>

<body>
    <h1>Riwayat Peminjaman Buku</h1>
    <h2><?php echo $nama_mahasiswa; ?> <br> <?php echo $npm; ?></h2>
    <a href="minjam.php?npm=<?php echo $npm; ?>">Kembali</a>

    <?php
    if ($result_riwayat_peminjaman->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun Terbit</th><th>Tanggal Pinjam</th><th>Tenggat Kembali</th><th>Tanggal Kembali</th><th>Keterangan</th></tr>";
        $no = 1;
        while ($row = $result_riwayat_peminjaman->fetch_assoc()) {
            $judul = $row['judul'];
            $penulis = $row['penulis'];
            $tahun_terbit = $row['tahun_terbit'];
            $tanggal_pinjam = date('l, d F Y H:i:s', strtotime($row['tanggal_pinjam']));
            $tenggat_kembali = date('l, d F Y H:i:s', strtotime($row['tanggal_pinjam'] . ' + 10 minutes'));
            $tanggal_kembali = $row['tanggal_kembali'] ? date('l, d F Y H:i', strtotime($row['tanggal_kembali'])) : 'Belum Dikembalikan';
            $current_date = date('Y-m-d H:i');
            $tenggat_kembali_check = date('Y-m-d H:i', strtotime($row['tanggal_pinjam'] . ' + 10 minutes'));

    
            $bulan = array(
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Maret',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Juni',
                'July' => 'Juli',
                'August' => 'Agustus',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Desember'
            );
            
            $hari = array(
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            );

       
            $timestamp_pinjam = strtotime($row['tanggal_pinjam']);
            $tanggal_pinjam = date('H:i, l d F Y', $timestamp_pinjam);
            $tanggal_pinjam = strtr($tanggal_pinjam, $bulan);
            $tanggal_pinjam = strtr($tanggal_pinjam, $hari);

    
            $timestamp_tenggat = strtotime($row['tanggal_pinjam'] . ' + 10 minutes');
            $tenggat_kembali = date('H:i, l d F Y', $timestamp_tenggat);
            $tenggat_kembali = strtr($tenggat_kembali, $bulan);
            $tenggat_kembali = strtr($tenggat_kembali, $hari);

            if ($row['tanggal_kembali'] == '0000-00-00 00:00:00' || !$row['tanggal_kembali']) {
                $tanggal_kembali = 'Belum Dikembalikan';
                if ($current_date > $tenggat_kembali_check) {
                    $keterangan = "<span class='red'>Terlambat</span>";
         
                    $update_sql = "UPDATE peminjaman SET keterangan = 'Terlambat' WHERE id = " . $row['id'];
                    $conn->query($update_sql);
                } else {
                    $keterangan = "<span class='red'>Belum Dikembalikan</span>";
                }
            } else {
                $timestamp_kembali = strtotime($row['tanggal_kembali']);
                $tanggal_kembali = date('H:i:s, l d F Y', $timestamp_kembali);
                $tanggal_kembali = strtr($tanggal_kembali, $bulan);
                $tanggal_kembali = strtr($tanggal_kembali, $hari);

                if (strtotime($row['tanggal_kembali']) > strtotime($tenggat_kembali_check)) {
                    $keterangan = "<span class='red'>Terlambat</span>";
        
                    $update_sql = "UPDATE peminjaman SET keterangan = 'Terlambat' WHERE id = " . $row['id'];
                    $conn->query($update_sql);
                } else {
                    $keterangan = "<span class='green'>Tidak Terlambat</span>";
    
                    $update_sql = "UPDATE peminjaman SET keterangan = 'Tidak Terlambat' WHERE id = " . $row['id'];
                    $conn->query($update_sql);
                }
            }

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$judul}</td>";
            echo "<td>{$penulis}</td>";
            echo "<td>{$tahun_terbit}</td>";
            echo "<td>{$tanggal_pinjam}</td>";
            echo "<td>{$tenggat_kembali}</td>";
            echo "<td>{$tanggal_kembali}</td>";
            echo "<td>{$keterangan}</td>";
            echo "</tr>";
            $no++;
        }
        echo "</table>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun Terbit</th><th>Tanggal Pinjam</th><th>Tenggat Kembali</th><th>Tanggal Kembali</th><th>Keterangan</th></tr>";
        echo "<tr>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "</tr>";
        echo "</table>";

        echo '<script>';
        echo 'window.onload = function() { showAlert("Anda belum pernah meminjam buku."); };';
        echo 'function showAlert(message) {';
        echo 'var popup = document.createElement("div");';
        echo 'popup.id = "popup";';
        echo 'popup.innerHTML = "<p>" + message + "</p>";';
        echo 'var overlay = document.createElement("div");';
        echo 'overlay.id = "popup-overlay";';
        echo 'document.body.appendChild(overlay);';
        echo 'document.body.appendChild(popup);';
        echo '}';
        echo '</script>';
    }

    $conn->close();
    ?>

    <br>

    <script>
        document.addEventListener("click", function(event) {
            var popup = document.getElementById("popup");
            var overlay = document.getElementById("popup-overlay");
            if (event.target == overlay) {
                popup.parentNode.removeChild(popup);
                overlay.parentNode.removeChild(overlay);
            }
        });
    </script>
</body>

</html>
