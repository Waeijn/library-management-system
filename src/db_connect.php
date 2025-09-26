<?php
$host = "db";
$user = "library_user";
$password = "library_pass";
$database = "library_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>