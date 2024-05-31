<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {

    header("Location: loginadmin.php");
    exit();
}


include 'db.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku Baru</title>
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">
    <script>
        function showEditForm(id, judul, penulis, tahun_terbit, gambar) {
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editId').value = id;
            document.getElementById('editJudul').value = judul;
            document.getElementById('editPenulis').value = penulis;
            document.getElementById('editTahunTerbit').value = tahun_terbit;
            document.getElementById('editGambar').value = gambar;
        }

        function hideEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }

        document.addEventListener("click", function(event) {
            var popup = document.getElementById("popup");
            var overlay = document.getElementById("popup-overlay");
            if (event.target == overlay) {
                popup.parentNode.removeChild(popup);
                overlay.parentNode.removeChild(overlay);
            }
        });
    </script>
    <style>
        /* CSS modal */
        /* .modal {
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
        } */

        .center {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
    }

    .center input[type="submit"] {
        background-color: #007BFF;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .edit-button {
            background-color: blue;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .delete-button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_buku'])) {
    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $gambar = $_FILES['gambar'];

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($gambar["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($gambar["tmp_name"]);
    if ($check !== false) {
        if (move_uploaded_file($gambar["tmp_name"], $target_file)) {
            $sql = "INSERT INTO buku (judul, penulis, tahun_terbit, gambar, tersedia) VALUES ('$judul', '$penulis', $tahun_terbit, '$target_file', TRUE)";
            
            if ($conn->query($sql) === TRUE) {
                echo '<script>';
                echo 'window.onload = function() { showAlert("Buku berhasil ditambahkan!"); };';
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
            echo "<p>Sorry, there was an error uploading your file.</p>";
        }
    } else {
        echo "<p>File is not an image.</p>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ubah_buku'])) {
    $id = $_POST['id'];
    $judul_baru = $_POST['judul_baru'];
    $penulis_baru = $_POST['penulis_baru'];
    $tahun_terbit_baru = $_POST['tahun_terbit_baru'];
    $gambar_baru = $_FILES['gambar_baru'];
    $tersedia_baru = $_POST['tersedia_baru'];

    $update_fields = "judul = '$judul_baru', penulis = '$penulis_baru', tahun_terbit = $tahun_terbit_baru, tersedia = $tersedia_baru";

    if ($gambar_baru && $gambar_baru['size'] > 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($gambar_baru["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $check = getimagesize($gambar_baru["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($gambar_baru["tmp_name"], $target_file)) {
                $update_fields .= ", gambar = '$target_file'";
            } else {
                echo "<p>Sorry, there was an error uploading your file.</p>";
            }
        } else {
            echo "<p>File is not an image.</p>";
        }
    }

    $sql = "UPDATE buku SET $update_fields WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        echo '<script>';
        echo 'window.onload = function() { showAlert("Buku berhasil diubah!"); };';
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_buku'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM buku WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo '<script>';
        echo 'window.onload = function() { showAlert("Buku telah dihapus!"); };';
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
?>
    <h1>Tambah Buku Baru</h1>
    
    <form action="admin.php" method="post" enctype="multipart/form-data" class="my-form">
        <label for="judul">Judul Buku:</label>
        <input type="text" id="judul" name="judul" required>

        <label for="penulis">Penulis:</label>
        <input type="text" id="penulis" name="penulis" required>

        <label for="tahun_terbit">Tahun Terbit:</label>
        <input type="number" id="tahun_terbit" name="tahun_terbit" required>

        <label for="gambar">Gambar Buku:</label>
        <input type="file" id="gambar" name="gambar" accept="image/png, image/jpeg" required>

        <div class="center">
    <input type="submit" name="tambah_buku" value="Tambah Buku">
</div>
    </form>


    <br>
    <a href='liatakun.php'>Lihat Informasi Akun Mahasiswa</a>
    <a href="riwayatbyadmin.php">Riwayat Peminjaman</a> 
    <br><a href="logoutadmin.php">Logout</a> <br>
    <?php



    $sql_buku = "SELECT * FROM buku";
    $result_buku = $conn->query($sql_buku);
    
    if ($result_buku->num_rows > 0) {
        echo "<h2>Daftar Buku:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun Terbit</th><th>Gambar</th><th>Ketersediaan</th><th>Aksi</th></tr>";
        $no = 1;
        while ($row_buku = $result_buku->fetch_assoc()) {
            $id = $row_buku['id'];
            $judul = $row_buku['judul'];
            $penulis = $row_buku['penulis'];
            $tahun_terbit = $row_buku['tahun_terbit'];
            $gambar = $row_buku['gambar'];
            $tersedia = $row_buku['tersedia'] ? 'Tersedia' : 'Tidak Tersedia';
    
            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$judul}</td>";
            echo "<td>{$penulis}</td>";
            echo "<td>{$tahun_terbit}</td>";
            echo "<td><img src='{$gambar}' alt='Gambar Buku' style='width:50px;height:50px;'></td>";
            echo "<td style='color: " . ($tersedia == 'Tersedia' ? 'green' : 'red') . ";'>{$tersedia}</td>";
            echo "<td>";
            echo "<button style='background-color: #007BFF; color: white; border: none; padding: 5px 10px; cursor: pointer; margin-right: 5px;' onclick=\"showEditForm('{$id}', '{$judul}', '{$penulis}', '{$tahun_terbit}', '{$gambar}')\">Ubah</button>";
            echo "<form action='admin.php' method='post' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='{$id}'>";
            echo "<input type='submit' name='delete_buku' value='Hapus' style='background-color: palevioletred; color: white; border: none; padding: 5px 10px; cursor: pointer;'>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
            
            
    
            $no++;
        }
        echo "</table>";
    } else {
        echo "<h2>Daftar Buku:</h2>";
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>Judul</th><th>Penulis</th><th>Tahun Terbit</th><th>Gambar</th><th>Ketersediaan</th><th>Aksi</th></tr>";
        echo "<tr>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "</tr>";
    }

    $conn->close();
    ?>


    <div id="editForm" class="modal">
    <div class="modal-content">
        <span class="close" onclick="hideEditForm()">&times;</span>
        <h2>Ubah Buku</h2>
        <form action="admin.php" method="post" enctype="multipart/form-data">
            <input type="hidden" id="editId" name="id">
            <label for="editJudul">Judul Buku:</label>
            <input type="text" id="editJudul" name="judul_baru" required>
            <label for="editPenulis">Penulis:</label>
            <input type="text" id="editPenulis" name="penulis_baru" required>
            <label for="editTahunTerbit">Tahun Terbit:</label>
            <input type="number" id="editTahunTerbit" name="tahun_terbit_baru" required>
            <label for="editTersedia">Ketersediaan:</label>
            <select id="editTersedia" name="tersedia_baru">
                <option value="1">Tersedia</option>
                <option value="0">Tidak Tersedia</option>
            </select>
            <label for="editGambar">Gambar Buku:</label>
            <input type="file" id="editGambar" name="gambar_baru" accept="image/png, image/jpeg">
            <input type="submit" name="ubah_buku" value="Ubah Buku">
        </form>
    </div>
</div>


    
</body>
</html>