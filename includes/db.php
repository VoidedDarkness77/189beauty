<?php
$conn = mysqli_connect("localhost", "root", "", "189beauty");

if(!$conn){
    die("Database connection failed: " . mysqli_connect_error());
}
?>
