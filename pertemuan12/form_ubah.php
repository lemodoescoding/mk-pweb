<?php

include "koneksi.php";

$id = $_GET['id'];

$sql = $pdo->prepare("SELECT * FROM siswa WHERE id=:id");
$sql->bindParam(':id', $id);
$sql->execute();
$data = $sql->fetch();

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi CRUD dengan PHP</title>
</head>

<body>
  <h1>Ubah data mahasiswa</h1>
  <form method="post" action="proses_ubah.php?id=<?= $data['id']; ?>" enctype="multipart/form-data">
    <table cellpadding="8">
      <tr>
        <td>NIS</td>
        <td><input type="text" name="nis" value="<?= $data['nis']; ?>"></td>
      </tr>
      <tr>
        <td>Nama</td>
        <td><input type="text" name="nama" value="<?= $data['nama']; ?>"></td>
      </tr>
      <tr>
        <td>Jenis Kelamin</td>
        <td>
          <?php if ($data['jenis_kelamin'] === 'Laki-laki'): ?>
            <input type="radio" name="jenis_kelamin" value="Laki-laki" checked> Laki-laki
            <input type="radio" name="jenis_kelamin" value="Perempuan"> Perempuan
          <?php else: ?>
            <input type="radio" name="jenis_kelamin" value="Laki-laki"> Laki-laki
            <input type="radio" name="jenis_kelamin" value="Perempuan" checked> Perempuan
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td>Telepon</td>
        <td><input type="tel" name="telp" value="<?= $data['telp']; ?>"></td>
      </tr>
      <tr>
        <td>Alamat</td>
        <td><input type="text" name="alamat" value="<?= $data['alamat']; ?>"></td>
      </tr>
      <tr>
        <td>Foto</td>
        <td><input type="file" name="foto"></td>
      </tr>

      <hr>
      <input type="submit" value="Ubah">
      <a href="index.php"><input type="button" value="Batal"></a>
    </table>
  </form>
</body>

</html>
