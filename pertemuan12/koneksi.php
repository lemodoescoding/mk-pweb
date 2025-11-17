<?php

$host = "localhost";
$username = "root"; // change this
$password = "testingA123_"; // change this
$db = "pertemuan12";

$pdo = new PDO('mysql:host=' . $host . ';dbname=' . $db, $username, $password);
