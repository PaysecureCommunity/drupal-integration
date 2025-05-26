<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'drupaldb', 3307);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}
echo "Connected successfully to database drupaldb on port 3307";
$mysqli->close();
?>
