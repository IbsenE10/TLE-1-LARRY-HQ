<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "larry_database";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("connection failed: ", $conn->connect_error);
}

?>