<?php
date_default_timezone_set('Asia/Jakarta');
include 'db.php';

function getSortOrder($column, $defaultOrder = 'ASC') {
    if (isset($_GET['sort']) && $_GET['sort'] == $column) {
        return (isset($_GET['order']) && $_GET['order'] == 'ASC') ? 'DESC' : 'ASC';
    }
    return $defaultOrder;
}

$validColumns = ['nama_mahasiswa', 'npm', 'judul_buku', 'tanggal_pinjam', 'tanggal_kembali', 'keterangan'];
$sortColumn = isset($_GET['sort']) && in_array($_GET['sort'], $validColumns) ? $_GET['sort'] : 'tanggal_pinjam';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

$sql_riwayat_count = "SELECT COUNT(*) AS count FROM peminjaman";
$result_riwayat_count = $conn->query($sql_riwayat_count);
$row_riwayat_count = $result_riwayat_count->fetch_assoc();
$total_records = $row_riwayat_count['count'];

$total_pages = ceil($total_records / 10);

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * 10;

$sql_riwayat = "SELECT mahasiswa.nama AS nama_mahasiswa, mahasiswa.npm, buku.judul AS judul_buku, peminjaman.tanggal_pinjam, peminjaman.tanggal_kembali, peminjaman.keterangan
    FROM peminjaman
    INNER JOIN mahasiswa ON peminjaman.npm = mahasiswa.npm
    INNER JOIN buku ON peminjaman.buku_id = buku.id
    ORDER BY $sortColumn $sortOrder
    LIMIT $offset, 10";
$result_riwayat = $conn->query($sql_riwayat);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Peminjaman Buku</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .delete-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .red { color: red; }
        .green { color: green; }
        .pagination {
            position: absolute;
            bottom: 50px;
            left: 0; 
            right: 0; 
            display: flex;
            justify-content: center; 
        }
        .pagination a {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
            border-radius: 3px;
        }
        .pagination a:hover {
            background-color: #f0f0f0;
        }
        .pagination strong {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            background-color: #007bff;
            color: #fff;
            border: 1px solid #007bff;
            border-radius: 3px;
        }
        .pagination a:last-child {
            margin-right: 0;
        }
    </style>
    <script>
        document.addEventListener("click", function (event) {
            var popup = document.getElementById("popup");
            var overlay = document.getElementById("popup-overlay");
            if (event.target == overlay) {
                popup.parentNode.removeChild(popup);
                overlay.parentNode.removeChild(overlay);
            }
        });
    </script>
</head>

<body>
    <?php
    if ($result_riwayat->num_rows > 0) {
        echo "<h1>Riwayat Peminjaman dan Pengembalian Saat Ini:</h1>";
        echo "<a href='admin.php'>Kembali</a>";
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>No</th>";
        echo "<th><a href='?sort=nama_mahasiswa&order=" . getSortOrder('nama_mahasiswa') . "&page=$page'>Nama</a></th>";
        echo "<th><a href='?sort=npm&order=" . getSortOrder('npm') . "&page=$page'>NPM</a></th>";
        echo "<th><a href='?sort=judul_buku&order=" . getSortOrder('judul_buku') . "&page=$page'>Buku</a></th>";
        echo "<th><a href='?sort=tanggal_pinjam&order=" . getSortOrder('tanggal_pinjam') . "&page=$page'>Tanggal Pinjam</a></th>";
        echo "<th><a href='?sort=tenggat_kembali&order=" . getSortOrder('tenggat_kembali') . "&page=$page'>Tenggat Kembali</a></th>";
        echo "<th><a href='?sort=tanggal_kembali&order=" . getSortOrder('tanggal_kembali') . "&page=$page'>Tanggal Kembali</a></th>";
        echo "<th><a href='?sort=keterangan&order=" . getSortOrder('keterangan') . "&page=$page'>Keterangan</a></th>";
        echo "</tr>";

        $no = 1 + ($page - 1) * 10;
        while ($row_riwayat = $result_riwayat->fetch_assoc()) {
            $nama_mahasiswa = $row_riwayat['nama_mahasiswa'];
            $npm = $row_riwayat['npm'];
            $judul_buku = $row_riwayat['judul_buku'];

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

            $timestamp_pinjam = strtotime($row_riwayat['tanggal_pinjam']);
            $tanggal_pinjam = date('H:i, l d F Y', $timestamp_pinjam);
            $tanggal_pinjam = strtr($tanggal_pinjam, $bulan);
            $tanggal_pinjam = strtr($tanggal_pinjam, $hari);

            $timestamp_tenggat = strtotime($row_riwayat['tanggal_pinjam'] . ' + 10 minutes');
            $tenggat_kembali = date('H:i, l d F Y', $timestamp_tenggat);
            $tenggat_kembali = strtr($tenggat_kembali, $bulan);
            $tenggat_kembali = strtr($tenggat_kembali, $hari);

            if ($row_riwayat['tanggal_kembali'] == '0000-00-00 00:00:00' || !$row_riwayat['tanggal_kembali']) {
                $tanggal_kembali = 'Belum Dikembalikan';
            } else {
                $timestamp_kembali = strtotime($row_riwayat['tanggal_kembali']);
                $tanggal_kembali = date('H:i, l d F Y', $timestamp_kembali);
                $tanggal_kembali = strtr($tanggal_kembali, $bulan);
                $tanggal_kembali = strtr($tanggal_kembali, $hari);
            }

            $keterangan = $row_riwayat['keterangan'];
            if ($keterangan == 'Terlambat') {
                $keterangan = "<span class='red'>Terlambat</span>";
            } elseif ($keterangan == 'Tidak Terlambat') {
                $keterangan = "<span class='green'>Tidak Terlambat</span>";
            } else {
                $keterangan = "<span class='red'>Belum Dikembalikan</span>";
            }

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$nama_mahasiswa}</td>";
            echo "<td>{$npm}</td>";
            echo "<td>{$judul_buku}</td>";
            echo "<td>{$tanggal_pinjam}</td>";
            echo "<td>{$tenggat_kembali}</td>";
            echo "<td>{$tanggal_kembali}</td>";
            echo "<td>{$keterangan}</td>";
            echo "</tr>";

            $no++;
        }
        echo "</table>";
    } else {
        echo "<h2>Riwayat Peminjaman dan Pengembalian Saat Ini:</h2>";
        echo "<a href='admin.php'>Kembali</a>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Nama</th><th>NPM</th><th>Buku</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th><th>Keterangan</th></tr>";
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
    }

    echo "<div class='pagination'>";
    if ($total_pages > 1) {
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                echo "<strong>{$i}</strong> ";
            } else {
                echo "<a href='riwayatbyadmin.php?sort=$sortColumn&order=$sortOrder&page={$i}'>{$i}</a> ";
            }
        }
    }
    echo "</div>";

    if ($total_records < 10) {
        echo "<style>.pagination { margin-top: 10px; }</style>";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_riwayat'])) {
        $sql_delete_riwayat = "DELETE FROM peminjaman";
        if ($conn->query($sql_delete_riwayat) === TRUE) {
            echo '<script>';
            echo 'window.onload = function() { showAlert("Semua riwayat berhasil dihapus!"); };';
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

            echo '<script>';
            echo 'setTimeout(function() { window.location.href = "riwayatbyadmin.php"; }, 3000);';
            echo '</script>';
        } else {
            echo "<p>Error: " . $sql_delete_riwayat . "<br>" . $conn->error . "</p>";
        }
    }

    $conn->close();
    ?>
</body>
</html>
