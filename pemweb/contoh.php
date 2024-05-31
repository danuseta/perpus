
<?php
date_default_timezone_set('Asia/Jakarta');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Peminjaman Buku</title>
    <link rel="stylesheet" href="styles/styles.css">
    <style>
        .btn-pinjam {
            background-color: green;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .btn-pinjam.disabled {
            background-color: red;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <h1>Peminjaman Buku</h1>

    <?php
    include 'db.php';

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

    if ($result_check_pinjaman->num_rows > 0) {
        echo "<p>Anda sudah meminjam satu buku. Harap kembalikan buku tersebut sebelum meminjam buku lain.</p>";
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $buku_id = $_POST['buku_id'];
            $tanggal_pinjam = date('Y-m-d H:i:s');
            $tanggal_kembali = date('Y-m-d H:i:s', strtotime('+1 week'));

            $sql = "INSERT INTO peminjaman (npm, buku_id, tanggal_pinjam) VALUES ('$npm', $buku_id, '$tanggal_pinjam')";

            if ($conn->query($sql) === TRUE) {
                $update_sql = "UPDATE buku SET tersedia = FALSE WHERE id = $buku_id";
                $conn->query($update_sql);
                echo "<p>Buku berhasil dipinjam! Silakan kembalikan pada hari " . date('l, d F Y', strtotime($tanggal_kembali)) . ".</p>";
            } else {
                echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }

        $sql = "SELECT * FROM buku";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<table border="1">';
            echo '<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Status</th><th>Aksi</th></tr>';
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                $id = $row['id'];
                $judul = $row['judul'];
                $penulis = $row['penulis'];
                $tersedia = $row['tersedia'];

                echo '<tr>';
                echo "<td>{$no}</td>";
                echo "<td>{$judul}</td>";
                echo "<td>{$penulis}</td>";
                echo "<td>" . ($tersedia ? 'Tersedia' : 'Tidak Tersedia') . "</td>";
                echo '<td>';
                if ($tersedia) {
                    echo '<form action="minjam.php?npm=' . $npm . '" method="post" style="display:inline;">';
                    echo '<input type="hidden" name="buku_id" value="' . $id . '">';
                    echo '<input type="submit" value="Pinjam" class="btn-pinjam">';
                    echo '</form>';
                } else {
                    echo '<button class="btn-pinjam disabled" disabled>Pinjam</button>';
                }
                echo '</td>';
                echo '</tr>';
                $no++;
            }
            echo '</table>';
        } else {
            echo '<p>Tidak ada buku yang tersedia.</p>';
        }
    }

    $conn->close();
    ?>

    <a href="index.php">Kembali</a>
</body>

</html>



<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {

    header("Location: loginadmin.php");
    exit();
}

include 'db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_buku'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM buku WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Buku berhasil dihapus!</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_buku'])) {
    $id = $_POST['id'];
    $judul_baru = $_POST['judul_baru'];
    $penulis_baru = $_POST['penulis_baru'];

    $sql = "UPDATE buku SET judul = '$judul_baru', penulis = '$penulis_baru' WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Buku berhasil diubah!</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_riwayat'])) {
    $sql_delete_riwayat = "DELETE FROM peminjaman";
    if ($conn->query($sql_delete_riwayat) === TRUE) {
        echo "<p>Semua riwayat peminjaman dan pengembalian berhasil dihapus!</p>";
    } else {
        echo "<p>Error: " . $sql_delete_riwayat . "<br>" . $conn->error . "</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tambah Buku Baru</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showEditForm(id, judul, penulis) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editId').value = id;
            document.getElementById('editJudul').value = judul;
            document.getElementById('editPenulis').value = penulis;
        }

        function hideEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1>Tambah Buku Baru</h1>
    <form action="admin.php" method="post">
        <label for="judul">Judul Buku:</label>
        <input type="text" id="judul" name="judul" required>

        <label for="penulis">Penulis:</label>
        <input type="text" id="penulis" name="penulis" required>

        <input type="submit" name="tambah_buku" value="Tambah Buku">
    </form>

    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_buku'])) {
        $judul = $_POST['judul'];
        $penulis = $_POST['penulis'];

        $sql = "INSERT INTO buku (judul, penulis, tersedia) VALUES ('$judul', '$penulis', TRUE)";

        if ($conn->query($sql) === TRUE) {
            echo "<p>Buku baru berhasil ditambahkan!</p>";
        } else {
            echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }
    }


    $sql_buku = "SELECT * FROM buku";
    $result_buku = $conn->query($sql_buku);

    if ($result_buku->num_rows > 0) {
        echo "<h2>Daftar Buku:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tersedia</th><th>Aksi</th></tr>";
        $no = 1;
        while ($row_buku = $result_buku->fetch_assoc()) {
            $id = $row_buku['id'];
            $judul = $row_buku['judul'];
            $penulis = $row_buku['penulis'];
            $tersedia = $row_buku['tersedia'] ? 'Ya' : 'Tidak';

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$judul}</td>";
            echo "<td>{$penulis}</td>";
            echo "<td>{$tersedia}</td>";
            echo "<td>";
            echo "<button onclick=\"showEditForm('{$id}', '{$judul}', '{$penulis}')\">Ubah</button>";
            echo "<form action='admin.php' method='post' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='submit' name='delete_buku' value='Hapus'>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";

            $no++;
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada buku yang tersedia.</p>";
    }


    $sql_riwayat = "SELECT mahasiswa.nama AS nama_mahasiswa, mahasiswa.npm, buku.judul AS judul_buku, peminjaman.tanggal_pinjam, peminjaman.tanggal_kembali
                FROM peminjaman
                INNER JOIN mahasiswa ON peminjaman.npm = mahasiswa.npm
                INNER JOIN buku ON peminjaman.buku_id = buku.id";
    $result_riwayat = $conn->query($sql_riwayat);

    if ($result_riwayat->num_rows > 0) {
        echo "<h2>Riwayat Peminjaman dan Pengembalian Saat Ini:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Nama</th><th>NPM</th><th>Buku</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th></tr>";
        $no = 1;
        while ($row_riwayat = $result_riwayat->fetch_assoc()) {
            $nama_mahasiswa = $row_riwayat['nama_mahasiswa'];
            $npm = $row_riwayat['npm'];
            $judul_buku = $row_riwayat['judul_buku'];
            $tanggal_pinjam = $row_riwayat['tanggal_pinjam'];
            $tanggal_kembali = $row_riwayat['tanggal_kembali'];

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$nama_mahasiswa}</td>";
            echo "<td>{$npm}</td>";
            echo "<td>{$judul_buku}</td>";
            echo "<td>{$tanggal_pinjam}</td>";
            echo "<td>";
            if ($tanggal_kembali) {
                echo $tanggal_kembali;
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";

            $no++;
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada riwayat peminjaman dan pengembalian saat ini.</p>";
    }

    echo "<form action='admin.php' method='post'>";
    echo "<input type='submit' name='delete_riwayat' value='Hapus Semua Riwayat Peminjaman dan Pengembalian'>";
    echo "</form>";

    $conn->close();
    ?>
    <div id="editForm" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideEditForm()">&times;</span>
            <h2>Ubah Buku</h2>
            <form action="admin.php" method="post">
                <input type="hidden" id="editId" name="id">
                <label for="editJudul">Judul Buku:</label>
                <input type="text" id="editJudul" name="judul_baru" required>
                <label for="editPenulis">Penulis:</label>
                <input type="text" id="editPenulis" name="penulis_baru" required>
                <input type="submit" name="ubah_buku" value="Ubah Buku">
            </form>
        </div>
    </div>

    <a href="logoutadmin.php">Logout</a> <br>
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>

</html>


<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: loginadmin.php");
    exit();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_buku'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM buku WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>Buku berhasil dihapus!</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_buku'])) {
    $id = $_POST['id'];
    $judul_baru = $_POST['judul_baru'];
    $penulis_baru = $_POST['penulis_baru'];

    $sql = "UPDATE buku SET judul = '$judul_baru', penulis = '$penulis_baru' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>Buku berhasil diubah!</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku Baru</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showEditForm(id, judul, penulis) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editId').value = id;
            document.getElementById('editJudul').value = judul;
            document.getElementById('editPenulis').value = penulis;
        }

        function hideEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Tambah Buku Baru</h1>
    <form action="admin.php" method="post">
        <label for="judul">Judul Buku:</label>
        <input type="text" id="judul" name="judul" required>

        <label for="penulis">Penulis:</label>
        <input type="text" id="penulis" name="penulis" required>

        <input type="submit" name="tambah_buku" value="Tambah Buku">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_buku'])) {
        $judul = $_POST['judul'];
        $penulis = $_POST['penulis'];

        $sql = "INSERT INTO buku (judul, penulis, tersedia) VALUES ('$judul', '$penulis', TRUE)";
        
        if ($conn->query($sql) === TRUE) {
            echo "<p>Buku baru berhasil ditambahkan!</p>";
        } else {
            echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
        }
    }

    $sql_buku = "SELECT * FROM buku";
    $result_buku = $conn->query($sql_buku);
    
    if ($result_buku->num_rows > 0) {
        echo "<h2>Daftar Buku:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tersedia</th><th>Aksi</th></tr>";
        $no = 1;
        while ($row_buku = $result_buku->fetch_assoc()) {
            $id = $row_buku['id'];
            $judul = $row_buku['judul'];
            $penulis = $row_buku['penulis'];
            $tersedia = $row_buku['tersedia'] ? 'Ya' : 'Tidak';
    
            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$judul}</td>";
            echo "<td>{$penulis}</td>";
            echo "<td>{$tersedia}</td>";
            echo "<td>";
            echo "<button onclick=\"showEditForm('{$id}', '{$judul}', '{$penulis}')\">Ubah</button>";
            echo "<form action='admin.php' method='post' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='submit' name='delete_buku' value='Hapus'>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
    
            $no++;
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada buku yang tersedia.</p>";
    }

    $sql_mahasiswa = "SELECT npm, nama FROM mahasiswa";
    $result_mahasiswa = $conn->query($sql_mahasiswa);

    if ($result_mahasiswa->num_rows > 0) {
        echo "<h2>Daftar Akun Mahasiswa:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>NPM</th><th>Nama</th></tr>";
        $no = 1;
        while ($row_mahasiswa = $result_mahasiswa->fetch_assoc()) {
            $npm = $row_mahasiswa['npm'];
            $nama = $row_mahasiswa['nama'];

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$npm}</td>";
            echo "<td>{$nama}</td>";
            echo "</tr>";

            $no++;
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada akun mahasiswa.</p>";
    }

    $sql_riwayat_count = "SELECT COUNT(*) AS count FROM peminjaman";
    $result_riwayat_count = $conn->query($sql_riwayat_count);
    $row_riwayat_count = $result_riwayat_count->fetch_assoc();
    $total_records = $row_riwayat_count['count'];

    $total_pages = ceil($total_records / 10);

    if (!isset($_GET['page'])) {
        $page = 1;
    } else {
        $page = $_GET['page'];
    }

    $offset = ($page - 1) * 10;

    $sql_riwayat = "SELECT mahasiswa.nama AS nama_mahasiswa, mahasiswa.npm, buku.judul AS judul_buku, peminjaman.tanggal_pinjam, peminjaman.tanggal_kembali
                    FROM peminjaman
                    INNER JOIN mahasiswa ON peminjaman.npm = mahasiswa.npm
                    INNER JOIN buku ON peminjaman.buku_id = buku.id
                    LIMIT $offset, 10";
    $result_riwayat = $conn->query($sql_riwayat);

    if ($result_riwayat->num_rows > 0) {
        echo "<h2>Riwayat Peminjaman dan Pengembalian Saat Ini:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Nama</th><th>NPM</th><th>Buku</th><th>Tanggal Pinjam</th><th>Tanggal Kembali</th></tr>";
        $no = 1 + ($page - 1) * 10;
        while ($row_riwayat = $result_riwayat->fetch_assoc()) {
            $nama_mahasiswa = $row_riwayat['nama_mahasiswa'];
            $npm = $row_riwayat['npm'];
            $judul_buku = $row_riwayat['judul_buku'];
            $tanggal_pinjam = $row_riwayat['tanggal_pinjam'];
            $tanggal_kembali = $row_riwayat['tanggal_kembali'];

            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$nama_mahasiswa}</td>";
            echo "<td>{$npm}</td>";
            echo "<td>{$judul_buku}</td>";
            echo "<td>{$tanggal_pinjam}</td>";
            echo "<td>";
            if ($tanggal_kembali) {
                echo $tanggal_kembali;
            } else {
                echo "-";
            }
            echo "</td>";
            echo "</tr>";

            $no++;
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada riwayat peminjaman dan pengembalian saat ini.</p>";
    }

    echo "<div style='margin-top: 20px;'>";
    if ($total_pages > 1) {
        echo "Halaman: ";
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                echo "<strong>{$i}</strong> ";
            } else {
                echo "<a href='admin.php?page={$i}'>{$i}</a> ";
            }
        }
    }
    echo "</div>";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_riwayat'])) {
        $sql_delete_riwayat = "DELETE FROM peminjaman";
        if ($conn->query($sql_delete_riwayat) === TRUE) {
            echo "<p>Semua riwayat peminjaman dan pengembalian berhasil dihapus!</p>";
        } else {
            echo "<p>Error: " . $sql_delete_riwayat . "<br>" . $conn->error . "</p>";
        }
    }

    echo "<form action='admin.php' method='post'>";
    echo "<input type='submit' name='delete_riwayat' value='Hapus Semua Riwayat Peminjaman dan Pengembalian'>";
    echo "</form>";

    $conn->close();
    ?>

    <div id="editForm" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideEditForm()">&times;</span>
            <h2>Ubah Buku</h2>
            <form action="admin.php" method="post">
                <input type="hidden" id="editId" name="id">
                <label for="editJudul">Judul Buku:</label>
                <input type="text" id="editJudul" name="judul_baru" required>
                <label for="editPenulis">Penulis:</label>
                <input type="text" id="editPenulis" name="pen

