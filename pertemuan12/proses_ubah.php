<?php

include "koneksi.php";

$id = $_GET['id'];

$nis = $_POST['nis'];
$nama = $_POST['nama'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$telp = $_POST['telp'];
$alamat = $_POST['alamat'];

$foto = $_FILES['foto']['name'];
$tmp = $_FILES['foto']['tmp_name'];

if (empty($foto)) {
  $sql = $pdo->prepare("UPDATE siswa SET nis=:nis,nama=:nama,jenis_kelamin=:jk,telp=:telp,alamat=:alamat WHERE id = :id");
  $sql->bindParam(':nis', $nis);
  $sql->bindParam(':nama', $nama);
  $sql->bindParam(':jk', $jenis_kelamin);
  $sql->bindParam(':telp', $telp);
  $sql->bindParam(':alamat', $alamat);
  $sql->bindParam(':id', $id);
  $execute = $sql->execute();

  if ($sql) {
    header('location: index.php');
  } else {
    echo "Maaf, terjadi kesalahan saat mencoba untuk menyimpan data ke database.";
    echo '<br><a href="form_ubah.php">Kembali ke Form</a>';
  }
} else {
  $fotobaru = date('dmYHis') . $foto;
  $path = __DIR__ . "/images/" . $fotobaru;

  if (move_uploaded_file($tmp, $path)) {
    $sql = $pdo->prepare('SELECT foto FROM siswa WHERE id=:id');
    $sql->bindParam(':id', $id);
    $sql->execute();

    $data = $sql->fetch();

    if (is_file(__DIR__ . '/images/' . $data['foto'])) {
      unlink(__DIR__ . '/images/' . $data['foto']);
    }

    $sql = $pdo->prepare("UPDATE siswa SET nis=:nis,nama=:nama,jenis_kelamin=:jk,telp=:telp,alamat=:alamat,foto=:foto WHERE id = :id");

    $sql->bindParam(':nis', $nis);
    $sql->bindParam(':nama', $nama);
    $sql->bindParam(':jk', $jenis_kelamin);
    $sql->bindParam(':telp', $telp);
    $sql->bindParam(':alamat', $alamat);
    $sql->bindParam(':foto', $fotobaru);
    $sql->bindParam(':id', $id);
    $execute = $sql->execute();

    if ($sql) {
      header('location: index.php');
    } else {
      echo "Maaf, terjadi kesalahan saat mencoba untuk menyimpan data ke database.";
      echo '<br><a href="form_ubah.php">Kembali ke Form</a>';
    }
  } else {
    echo "Maaf, gambar gagal untuk diupload";
    echo '<br><a href="form_ubah.php">Kembali ke Form</a>';
  }
}
