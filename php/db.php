<?php
$db_server = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "tle_larry";

$conn = mysqli_connect($db_server, $db_user, $db_password, $db_name);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");   // so special characters (é, ü, emoji) are saved correctly
?>