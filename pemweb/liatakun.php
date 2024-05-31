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
    <title>Info Akun Mahasiswa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <h1>Info Akun Mahasiswa</h1>
    <a href="admin.php">Kembali ke Halaman Admin</a>
    <?php
    $sql_mahasiswa = "SELECT m.npm, m.nama, m.jurusan, m.fakultas, m.jenis_kelamin FROM mahasiswa m";
    $result_mahasiswa = $conn->query($sql_mahasiswa);

    if ($result_mahasiswa->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>No</th><th>NPM</th><th>Nama</th><th>Jurusan</th><th>Fakultas</th><th>Jenis Kelamin</th></tr>";
        
        $no = 1;
        while ($row_mahasiswa = $result_mahasiswa->fetch_assoc()) {
            $npm = $row_mahasiswa['npm'];
            $nama = $row_mahasiswa['nama'];
            $jurusan = $row_mahasiswa['jurusan'];
            $fakultas = $row_mahasiswa['fakultas'];
            $jenis_kelamin = $row_mahasiswa['jenis_kelamin'];
            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$npm}</td>";
            echo "<td>{$nama}</td>";
            echo "<td>{$jurusan}</td>";
            echo "<td>{$fakultas}</td>";
            echo "<td>{$jenis_kelamin}</td>";
            echo "</tr>";
            $no++;
        }
        echo "</table>";
    }
     else {
        echo "<table border='1'>";
        echo "<tr><th>NPM</th><th>Nama</th></tr>";
        echo "<tr>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "<td>-</td>";
        echo "</tr>";
        echo "</table>";

        echo '<script>';
        echo 'window.onload = function() { showAlert("Belum ada Akun!"); };';
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
