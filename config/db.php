
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pharmacy_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}
?>