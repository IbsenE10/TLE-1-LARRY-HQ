<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "users"

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("connection failed: ", $conn->connect_error);
}

?>