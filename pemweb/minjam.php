<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Peminjaman Buku</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .green {
            color: green;
        }

        .red {
            color: red;
        }

        .left-align {
            text-align: left;
            display: block;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <h1>Peminjaman Buku</h1>

    <?php
    include 'db.php';
    date_default_timezone_set('Asia/Jakarta');

    $npm = $_GET['npm'];

    $sql_get_nama = "SELECT nama FROM mahasiswa WHERE npm = '$npm'";
    $result_get_nama = $conn->query($sql_get_nama);

    if ($result_get_nama->num_rows > 0) {
        $row_nama = $result_get_nama->fetch_assoc();
        $nama_mahasiswa = $row_nama['nama'];
        echo "<h2>Selamat Datang, $nama_mahasiswa</h2>";
    } else {
        echo "<p>NPM tidak ditemukan. Harap daftar sebagai mahasiswa terlebih dahulu.</p>";
        exit();
    }

    $sql_check_pinjaman = "SELECT * FROM peminjaman WHERE npm = '$npm' AND tanggal_kembali IS NULL";
    $result_check_pinjaman = $conn->query($sql_check_pinjaman);
    ?>

    <div class="menu">
        <a href="riwayatminjam.php?npm=<?= $npm ?>">
            <h3><i class="fas fa-history"></i> Riwayat Peminjaman</h3>
        </a>
        <a href="index.php">Keluar</a>
    </div>

    <?php
    if ($result_check_pinjaman->num_rows > 0) {
        echo '<script>';
        echo 'window.onload = function() { showAlert("Anda sudah meminjam satu buku. Harap kembalikan buku tersebut sebelum meminjam buku lain."); };';
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
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buku_id'])) {
            $buku_id = $_POST['buku_id'];


            $sql_check_ketersediaan = "SELECT tersedia FROM buku WHERE id = $buku_id";
            $result_check_ketersediaan = $conn->query($sql_check_ketersediaan);

            if ($result_check_ketersediaan->num_rows > 0) {
                $row_ketersediaan = $result_check_ketersediaan->fetch_assoc();
                $tersedia = $row_ketersediaan['tersedia'];
                if ($tersedia) {
                    $tanggal_pinjam = date('Y-m-d H:i:s');
                    $tanggal_kembali = date('Y-m-d H:i:s', strtotime('+1 week'));

                    $sql = "INSERT INTO peminjaman (npm, buku_id, tanggal_pinjam) VALUES ('$npm', $buku_id, '$tanggal_pinjam')";

                    if ($conn->query($sql) === TRUE) {
                        $update_sql = "UPDATE buku SET tersedia = FALSE WHERE id = $buku_id";
                        $conn->query($update_sql);
                        echo '<script>';
                        echo 'window.onload = function() { showAlert("Buku berhasil dipinjam. Jangan lupa dikembalikan!"); };';
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
                    } else {
                        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
                    }
                } else {
                    echo '<script>';
                    echo 'window.onload = function() { showAlert("Maaf, buku tidak tersedia untuk dipinjam saat ini."); };';
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
            } else {
                echo "<p>Error: " . $sql_check_ketersediaan . "<br>" . $conn->error . "</p>";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['peminjaman_id'])) {
        $peminjaman_id = $_POST['peminjaman_id'];
        $tanggal_pinjam = date('Y-m-d H:i:s');
        $tanggal_kembali = date('Y-m-d H:i:s');


        $sql = "UPDATE peminjaman SET tanggal_kembali = '$tanggal_kembali' WHERE id = $peminjaman_id";
        if ($conn->query($sql) === TRUE) {
            $buku_id_sql = "SELECT buku_id FROM peminjaman WHERE id = $peminjaman_id";
            $buku_id_result = $conn->query($buku_id_sql);
            $buku_id_row = $buku_id_result->fetch_assoc();
            $buku_id = $buku_id_row['buku_id'];

            $update_sql = "UPDATE buku SET tersedia = TRUE WHERE id = $buku_id";
            $conn->query($update_sql);

            $tenggat_kembali = date('Y-m-d H:i:s', strtotime($tanggal_pinjam . ' + 10 minutes'));

            if ($tanggal_kembali > $tenggat_kembali) {
                $keterangan = "Terlambat";
            } else {
                $keterangan = "Tidak Terlambat";
            }

            $update_keterangan_sql = "UPDATE peminjaman SET keterangan = '" . mysqli_real_escape_string($conn, $keterangan) . "' WHERE id = $peminjaman_id";
            $conn->query($update_keterangan_sql);



            echo '<script>';
            echo 'window.onload = function() { showAlert("Buku berhasil dikembalikan! Silahkan meminjam buku yang lainnya yang diperlukan."); };';
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
        } else {
            echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }
    }


    $sql_peminjaman = "SELECT peminjaman.id, buku.judul, buku.penulis, buku.tahun_terbit, peminjaman.tanggal_pinjam, buku.gambar
    FROM peminjaman
    JOIN buku ON peminjaman.buku_id = buku.id
    WHERE peminjaman.npm = '$npm' AND peminjaman.tanggal_kembali IS NULL";
    $result_peminjaman = $conn->query($sql_peminjaman);


    if ($result_peminjaman->num_rows > 0) {
        echo "<h2>Buku yang Sedang Dipinjam</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun Terbit</th><th>Tanggal Pinjam</th><th>Tenggat Kembali</th><th>Keterangan</th><th>Aksi</th></tr>";
        $no = 1;
        while ($row = $result_peminjaman->fetch_assoc()) {
            $judul = $row['judul'];
            $penulis = $row['penulis'];
            $tahun_terbit = $row['tahun_terbit'];
            $timestamp_pinjam = strtotime($row['tanggal_pinjam']);
            $tanggal_pinjam = date('H:i, l d F Y', $timestamp_pinjam);

            $timestamp_kembali = strtotime($row['tanggal_pinjam'] . ' + 10 minutes');
            $tanggal_kembali = date('H:i, l d F Y', $timestamp_kembali);

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

            $tanggal_pinjam = strtr($tanggal_pinjam, $bulan);
            $tanggal_pinjam = strtr($tanggal_pinjam, $hari);

            $tanggal_kembali = strtr($tanggal_kembali, $bulan);
            $tanggal_kembali = strtr($tanggal_kembali, $hari);

            $current_timestamp = time();
            if ($current_timestamp > $timestamp_kembali) {
                $keterangan = "<span class='red'>Terlambat</span>";
            } else {
                $keterangan = "<span class='green'>Belum Terlambat</span>";
            }

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$judul}</td>";
            echo "<td>{$penulis}</td>";
            echo "<td>{$tahun_terbit}</td>";
            echo "<td>{$tanggal_pinjam}</td>";
            echo "<td>{$tanggal_kembali}</td>";
            echo "<td>{$keterangan}</td>";
            echo '<td>';
            echo '<form action="" method="post" style="display:inline;">';
            echo '<input type="hidden" name="peminjaman_id" value="' . $row['id'] . '">';
            echo '<input type="submit" value="Kembalikan" class="btn-kembalikan">';
            echo '</form>';
            echo '</td>';
            echo "</tr>";
            $no++;
        }
        echo "</table>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun Terbit</th><th>Tanggal Pinjam</th><th>Tenggat Kembali</th><th>Keterangan</th><th>Aksi</th></tr>";
        echo "<h2>Anda belum meminjam buku.</h2>";
        echo "<tr>";
        echo "<td>-</td>";
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

    $sql_buku = "SELECT * FROM buku";
    $result_buku = $conn->query($sql_buku);

    if ($result_buku->num_rows > 0) {
        echo "<h2 style='margin-top: 50px;'>Daftar Buku</h2>";
        echo "<div class='book-container'>";
        $counter = 0;
        echo "<div class='book-row'>";
        while ($row = $result_buku->fetch_assoc()) {
            $id = $row['id'];
            $judul = $row['judul'];
            $penulis = $row['penulis'];
            $tahun_terbit = $row['tahun_terbit'];
            $tersedia = $row['tersedia'];
            $gambar = $row['gambar'];

            echo '<div class="book-card">';
            echo "<div class='book-image'>";
            echo "<img src='$gambar' alt='$judul'>";
            echo "</div>";
            echo "<div class='book-details'>";
            echo "<div class='book-title'>$judul</div>";
            echo "<div class='book-author'>$penulis</div>";
            echo "<div class='book-year'>$tahun_terbit</div>";
            echo "<div class='book-status'>" . ($tersedia ? 'Tersedia' : 'Tidak Tersedia') . "</div>";
            echo "</div>";
            echo "<div class='book-actions'>";
            if ($tersedia) {
                echo "<form action='minjam.php?npm=$npm' method='post'>";
                echo "<input type='hidden' name='buku_id' value='$id'>";
                echo "<input type='submit' value='Pinjam' class='btn-pinjam'>";
            } else {
                echo "<form action='minjam.php?npm=$npm' method='post' class='disabled'>";
                echo '<button class="btn-pinjam disabled" disabled>Tidak Tersedia</button>';
            }
            echo '</form>';
            echo '</div>';
            echo '</div>';

            $counter++;

            if ($counter % 6 == 0) {
                echo "</div><div class='book-row'>";
            }
        }
        echo "</div>";
        echo "</div>";
    } else {
        echo '<p>Tidak ada buku yang tersedia.</p>';
    }

    $conn->close();
    ?>

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
</body>

</html>