<?php

$host = "localhost";
$dbname = "online_resibo";
$username = "root";
$password = "1234";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>