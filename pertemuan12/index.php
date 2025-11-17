<?php

include "koneksi.php";

$sql = $pdo->prepare("SELECT * FROM siswa");
$sql->execute();

?>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi CRUD Plus Upload Gambar dengan PHP</title>
</head>

<body>
  <h1>Data Siswa</h1>
  <a href="form_simpan.php">Tambah Data</a><br><br>
  <table border="1" width="100%">
    <tr>
      <th>Foto</th>
      <th>NIS</th>
      <th>Nama</th>
      <th>Jenis Kelamin</th>
      <th>Telepon</th>
      <th>Alamat</th>
      <th colspan="2">Aksi</th>
    </tr>
    <?php while ($data = $sql->fetch()): ?>
      <tr>
        <td><img src="images/<?= $data['foto']; ?>" width="100" height="100"></td>
        <td><?= $data['nis'] ?></td>
        <td><?= $data['nama'] ?></td>
        <td><?= $data['jenis_kelamin'] ?></td>
        <td><?= $data['telp'] ?></td>
        <td><?= $data['alamat'] ?></td>
        <td><a href="form_ubah.php?id=<?= $data['id'] ?>">Ubah</a></td>
        <td><a href="proses_hapus.php?id=<?= $data['id'] ?>">Hapus</a></td>
      </tr>
    <?php endwhile; ?>
  </table>
</body>

</html>
