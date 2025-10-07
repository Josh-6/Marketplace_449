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

foreach ($queries as $q) {
    if (trim($q) != '') {
        $result = mysqli_query($conn, $q);
        if (!$result) {
            die("Error executing query: " . mysqli_error($conn));
        }
    }
}
echo "Database and tables created successfully";

// Close connection
mysqli_close($conn);
?>