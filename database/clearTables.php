<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Marketplace";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $query = "drop tables IF EXISTS Users, Review, Payment_Method, Payment, Item, Seller, Cart, Buyer, Customer_Support;";
    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error deleting data: " . mysqli_error($conn);
    }
    else {
        echo "Data deleted successfully.";
    }
    mysqli_close($conn);
?>