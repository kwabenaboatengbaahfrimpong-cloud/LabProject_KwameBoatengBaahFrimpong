
<?php
$host = 'localhost';
$db = 'webtech_2025A_kwame_boateng';
$user = 'kwame.boateng';
$pass = 'Zoom@0011';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed");
}
?>
