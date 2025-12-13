<?php

//will need to change or make a env file for these variables
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$query = file_get_contents(__DIR__ . '/buildModel.sql');
$queries = explode(";", $query);

if (!mysqli_multi_query($conn, $query)) {
    die("Error executing SQL file: " . mysqli_error($conn));
}

do {
    if ($result = mysqli_store_result($conn)) {
        mysqli_free_result($result);
    }
} while (mysqli_next_result($conn));

echo "Database and tables created successfully";

// Close connection
mysqli_close($conn);
?>