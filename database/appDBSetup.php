<?php
try {
    require_once __DIR__."/DBsetup.php";
    require_once __DIR__."/sampleData.php";
} catch (mysqli_sql_exception $e) {
    echo "Error: " . $e->getMessage();
}

// Load configuration settings
$servername = "localhost";
$username = "root";
$password = "";

$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

// views created...


// close connection
mysqli_close($conn);
header("Location: ../frontend/");
?>


