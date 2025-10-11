<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    //inserting data

    // customer support 
    $query = "INSERT INTO Marketplace.Customer_Support (CS_ID, CS_Name, CS_Employee_Date, CS_Phone_Number, CS_Location)
        VALUES (1, 'Alice Johnson', '2020-01-15', '555-1000', 'New York'),
        (2, 'Bob Smith', '2021-06-20', '555-2000', 'Los Angeles');";
    
    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    //users
    $query = "INSERT INTO Marketplace.Users (User_ID, Username, User_Email, PasswordHash, Role, Created_At, Valid_ID, Full_Name, User_dob)
        VALUES
        (1, 'alice', 'Ou0Xb@example.com', 'password1', 'buyer', '2023-01-01', '123456789', 'Alice Johnson', '1990-05-15'),
        (2, 'bob', 'yE2t6@example.com', 'password2', 'buyer', '2023-02-01', '987654321', 'Bob Smith', '1995-08-20'),
        (3, 'admin', 'E2t6@example.com', 'password3', 'admin', '2023-02-01', '987654321', 'Bob Smith', '1995-08-20');";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    //buyers
    $query = "INSERT INTO Marketplace.Buyer (Buyer_ID, CS_ID, Buyer_Phone_Number, Buyer_Location)
        VALUES
        (1, 1, '555-3000',  'Boston'),
        (2, 2, '555-4000', 'Chicago');";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    //Carts
    $query = "INSERT INTO Marketplace.Cart (Cart_ID, Buyer_ID, Cart_Cost_Amount)
        VALUES
        (1, 1, 150.00),
        (2, 2, 75.50);";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    //sellers
    $query = "INSERT INTO Marketplace.Seller (Seller_ID, CS_ID, Seller_Phone_Number, Seller_Stars, Seller_Location)
        VALUES
        (1, 1, '555-5000', 5, 'San Francisco'),
        (2, 2, '555-6000', 4, 'Seattle');";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    //Items
    $query = "INSERT INTO Marketplace.Item (Item_ID, Seller_ID, Cart_ID, Item_Name, Item_Description, Item_Price, Item_Tags, Item_Quantity, Added_On)
        VALUES
        (1, 1, 1, 'Laptop', 'High performance laptop', 1200.00, 'electronics,computer', 10, CURRENT_TIMESTAMP),
        (2, 2, 2, 'Novel', 'Bestselling fiction novel', 20.00, 'books,reading', 50, CURRENT_TIMESTAMP),
        (3, 1, NULL, 'Headphones', 'Noise-cancelling headphones', 150.00, 'electronics,audio', 25, CURRENT_TIMESTAMP);";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    // Payments
    $query = "INSERT INTO Marketplace.Payment (Payment_ID, Cart_ID, Payment_Date, Payment_Type, Payment_Total)
        VALUES
        (1, 1, '2023-03-15', 1, 150.00),
        (2, 2, '2023-03-16', 2, 75.50);";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    // Reviews
    $query = "INSERT INTO Marketplace.Review (Review_ID, Review_text, Review_Rating, Review_Date, Buyer_ID, Item_ID)
        VALUES
        (1, 'Great laptop, very fast!', 5, '2023-03-20', 1, 1),
        (2, 'The book was enjoyable.', 4, '2023-03-22', 2, 2);";

    $queryStatus = mysqli_query($conn, $query);
    if (!$queryStatus) {
        echo "Error inserting data: " . mysqli_error($conn);
    }

    //close connection
    mysqli_close($conn);

    header("Location: ../frontend/");
?>