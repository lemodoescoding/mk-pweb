<h2>Masukkan Data Mahasiswa</h2>
<form action="proses_add.php" method="POST">
  NRP: <input name="nrp"><br>
  Nama: <input name="nama"><br>
  Tanggal Lahir: <input type="date" name="tanggal_lahir"><br>
  No Telp: <input name="telp"><br>
  <button type="submit" name="add">Tambah</button>
</form>

<?php
if (isset($_GET['status'])):
?>
  <p>
    <?php
    if ($_GET['status'] == 'success') {
      echo "success!";
    } else {
      echo "failed!";
    }
    ?>
  </p>
<?php endif; ?>
