<?php
include 'includes/db.php';

// Add the necessary columns if they don't exist
$sql = "
    SELECT COUNT(*) as count 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'cart_data'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    // Add cart_data column
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN cart_data TEXT NULL AFTER password");
    echo "Added cart_data column<br>";
}

// Check for wishlist_data column
$sql = "
    SELECT COUNT(*) as count 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'wishlist_data'
";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    // Add wishlist_data column
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN wishlist_data TEXT NULL AFTER cart_data");
    echo "Added wishlist_data column<br>";
}

echo "Database setup complete!";
?>