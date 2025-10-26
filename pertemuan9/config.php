<?php

// [IMPORTANT] DONT FORGET TO REPLACE OR ADD THE CREDENTIALS BASED ON YOUR CONFIG

$server = "localhost"; //
$user = ""; // 
$password = ""; //
$nama_database = "pendaftaran_siswa"; //

$db = mysqli_connect($server, $user, $password, $nama_database);

if (!$db) {
  die("Gagal terhubung dengan database: " . mysqli_connect_error());
}
