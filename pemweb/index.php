<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Perpustakaan Mini</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/minjam.css">
</head>

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
<body>
    <h1>Selamat Datang di Perpustakaan Mini</h1>
    <form action="index.php" method="post">
        <label for="npm">NPM:</label>
        <input type="text" id="npm" name="npm" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Login" class='login-button'>


    </form>
    <br>
    <a href="registrasi.php">Buat Akun?</a> 
    <a href="lupapw.php">Lupa Password?</a>

    <?php
    include 'db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $npm = $_POST['npm'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM mahasiswa WHERE npm = '$npm'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];

            if (password_verify($password, $hashed_password)) {
                header("Location: minjam.php?npm=$npm");
                exit();
            } else {

                echo '<script>';
                echo 'window.onload = function() { showAlert("Password salah. Silahkan coba lagi!"); };';
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

            echo '<script>';
            echo 'window.onload = function() { showAlert("Akun Tidak Ditemukan!"); };';
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
    }

    $conn->close();
    ?>

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
