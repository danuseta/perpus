<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">

    <style>
        .login-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
    <script>
        function showAlert() {
            var popup = document.createElement('div');
            popup.id = 'popup';
            popup.style.position = 'fixed';
            popup.style.left = '50%';
            popup.style.top = '50%';
            popup.style.transform = 'translate(-50%, -50%)';
            popup.style.backgroundColor = '#007BFF';
            popup.style.color = 'white';
            popup.style.padding = '40px';
            popup.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.1)';
            popup.style.textAlign = 'center';
            popup.style.borderRadius = '8px';
            popup.style.zIndex = '1000';

            var message = document.createElement('p');
            message.innerText = 'Password Berhasil diubah!';
            message.style.fontSize = '24px';
            popup.appendChild(message);

            var button = document.createElement('button');
            button.innerText = 'OK';
            button.style.padding = '10px 20px';
            button.style.fontSize = '16px';
            button.style.marginTop = '20px';
            button.style.backgroundColor = 'white';
            button.style.color = '#007BFF';
            button.style.border = 'none';
            button.style.cursor = 'pointer';
            button.onclick = function () {
                window.location.href = 'index.php';
            };
            popup.appendChild(button);

            document.body.appendChild(popup);
        }

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
    <h1>Lupa Password</h1>
    <form action="lupapw.php" method="post">
        <label for="npm">Masukkan NPM Anda:</label>
        <input type="text" id="npm" name="npm" required>

        <label for="new_password">Password Baru:</label>
        <input type="password" id="new_password" name="new_password" required>

        <input type="submit" value="Ubah Password" class='login-button'>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $npm = $_POST['npm'];
        $new_password = $_POST['new_password'];


        include 'db.php';


        $sql_check_npm = "SELECT * FROM mahasiswa WHERE npm = '$npm'";
        $result = $conn->query($sql_check_npm);

        if ($result->num_rows > 0) {

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $sql_update_password = "UPDATE mahasiswa SET password = '$hashed_password' WHERE npm = '$npm'";
            if ($conn->query($sql_update_password) === TRUE) {
                echo "<script>showAlert();</script>";
            } else {
                echo "<p>Error updating password: " . $conn->error . "</p>";
            }
        } else {

            echo '<script>';
            echo 'window.onload = function() { showAlert("NPM Tidak ditemukan!"); };';
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
    }
    ?>
    <br>
    <a href="index.php">Sudah Punya Akun? <b>Masuk</b></a>
    <a href="registrasi.php">Belum Punya Akun? <b>Buat Akun</b></a>

</body>

</html>