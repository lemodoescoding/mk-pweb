<?php

session_start();

// cek apakah yang mengakses halaman ini sudah login
if ($_SESSION['level'] == "") {
  header("location:index.php?pesan=gagal");
}

?>
<!DOCTYPE html>
<html>

<head>
  <title>Halaman Pegawai - www.malasngoding.com</title>
</head>

<body>
  <h1>Halaman Pegawai</h1>

  <p>Halo <b><?php echo $_SESSION['username']; ?></b> Anda telah login sebagai <b><?php echo $_SESSION['level']; ?></b>.</p>
  <a href="logout.php">LOGOUT</a>

  <br />
  <br />

</body>

</html>
