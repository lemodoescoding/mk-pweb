<?php

define("USER", "root");
define("PASSWORD", "testingA123_");

$koneksi = mysqli_connect("localhost", USER, PASSWORD, "user_level");

// Check connection
if (mysqli_connect_errno()) {
  echo "Koneksi database gagal : " . mysqli_connect_error();
}
