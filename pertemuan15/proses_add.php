<?php
include("config.php");

if (isset($_POST['add'])) {
  print_r($_POST);
  $nrp = $_POST['nrp'];
  $nama = $_POST['nama'];
  $tanggal_lahir = date("Y-m-d H:i:s", strtotime($_POST['tanggal_lahir']));
  echo $tanggal_lahir;
  $telp = $_POST['telp'];

  $sql = "INSERT INTO mahasiswa (nrp, nama, tanggal_lahir, telp) VALUE ('$nrp', '$nama', '$tanggal_lahir', '$telp')";
  $query = mysqli_query($db_connect, $sql);

  if ($query) {
    header('Location: add_form.php?status=success');
  } else {
    header('Location: add_process.php?status=failed');
  }
} else {
  die("Failed to connect to database: " . mysqli_connect_error());
}
