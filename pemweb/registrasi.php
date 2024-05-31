<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Daftar Mahasiswa</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/minjam.css">
    <link rel="stylesheet" href="css/admin.css">

    <style>
        .regist-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        select {
            width: 22%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #fff;
            cursor: pointer;
        }


        select:hover {
            border-color: #007BFF;
        }


        select:focus {
            outline: none;
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }


        input,
        select {
            opacity: 0.6;
        }


        input:focus,
        select:focus {
            opacity: 1;
        }


        input::placeholder,
        select option[disabled]:first-child {
            color: rgba(0, 0, 0, 0.8);
            font-size: 16px;
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
            message.innerText = 'Berhasil';
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
    <h1>Daftar Mahasiswa</h1>
    <form action="registrasi.php" method="post">
        <label for="npm">NPM:</label>
        <input type="text" id="npm" name="npm" placeholder="NPM" required>

        <label for="nama">Nama:</label>
        <input type="text" id="nama" name="nama" placeholder="Nama" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Password" required>

        <label for="jurusan">Jurusan:</label>
        <input type="text" id="jurusan" name="jurusan" placeholder="Jurusan" required>

        <label for="fakultas">Fakultas:</label>
        <select id="fakultas" name="fakultas" placeholder="Fakultas" required>
            <option value="" selected disabled>Fakultas</option>
            <option value="Fakultas Kedokteran">Kedokteran</option>
            <option value="Fakultas Teknik">Teknik</option>
            <option value="Fakultas Matematika IPA">Matematika IPA</option>
            <option value="Fakultas Pertanian">Pertanian</option>
            <option value="Fakultas Keguruan dan Ilmu Pendidikan">Keguruan dan Ilmu Pendidikan</option>
            <option value="Fakultas Hukum">Hukum</option>
            <option value="Fakultas Ilmu Sosial dan Ilmu Politik">Ilmu Sosial dan Ilmu Politik</option>
            <option value="Fakultas Ekonomi dan Bisnis">Ekonomi dan Bisnis</option>
        </select>

        <label for="jenis_kelamin">Jenis Kelamin:</label>
        <select id="jenis_kelamin" name="jenis_kelamin">
            <option value="" selected>Jenis Kelamin (opsional)</option>
            <option value="L">Laki-laki</option>
            <option value="P">Perempuan</option>
        </select>

        <br>

        <input type="submit" value="Daftar" class='regist-button'>
    </form>



    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $npm = $_POST['npm'];
        $nama = $_POST['nama'];
        $password_plain = $_POST['password'];
        $jurusan = $_POST['jurusan'];
        $fakultas = $_POST['fakultas'];
        $jenis_kelamin = $_POST['jenis_kelamin'];


        $jenis_kelamin_db = !empty($jenis_kelamin) ? "'$jenis_kelamin'" : "NULL";

        // echo "Password plain: $password_plain<br>";
    
        if (empty($password_plain)) {
            echo '<script>';
            echo 'window.onload = function() { showAlert("Password tidak boleh kosong!"); };';
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
            exit();
        }

        $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

        // echo "Password hashed: $password_hashed<br>";
    
        include 'db.php';

        $sql_check_npm = "SELECT * FROM mahasiswa WHERE npm = '$npm'";
        $result_check_npm = $conn->query($sql_check_npm);

        if ($result_check_npm->num_rows > 0) {
            echo '<script>';
            echo 'window.onload = function() { showAlert("NPM sudah terdaftar!"); };';
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
            $sql = "INSERT INTO mahasiswa (npm, nama, password, jurusan, fakultas, jenis_kelamin) VALUES ('$npm', '$nama', '$password_hashed', '$jurusan', '$fakultas', '$jenis_kelamin')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>showAlert();</script>";
            } else {
                echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }

        $conn->close();
    }
    ?>


    <br>
    <a href="index.php">Sudah Punya Akun? <b>Masuk</b></a>
</body>

</html>