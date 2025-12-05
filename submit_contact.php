<?php
include 'includes/db.php';

$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

$query = "INSERT INTO messages (name,email,message)
          VALUES ('$name','$email','$message')";
mysqli_query($conn,$query);

header("Location: contact.php?success=1");
?>
