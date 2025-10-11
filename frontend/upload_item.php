<!-- Still needs testing after the login and image integration -->
<?php
session_start();

//init database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
// NOTE: Call out this if statement to debug !!!!!!
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// // Get the username from the session
// $username = $_SESSION['username'];

// Check if the form has been submitted
// NOTE: Image still needs to be added !!!!!!!!!!!!!!!!!!!!!!!!!
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $price = floatval(str_replace(['$'], [''],$_POST['currencyInput']));    
    $name = $_POST['name'];
    $description = $_POST['description'];
    // $image = $_FILES['image'];
    $tags = $_POST['category'];
    $quantity = $_POST['quantity'];
    $new_item_id = 0;
    
    //getting last item id
    $sql = "SELECT Item_ID FROM Items ORDER BY Item_ID DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result){
        $row = $result->fetch_assoc();
        $last_item_id = $row['Item_ID'];
        $new_item_id = $last_item_id + 1;
    }

    $stmt = $conn->prepare(
        "INSERT INTO Item (Item_ID, Seller_ID, Item_Name, Item_Description, Item_Price, Item_Tags, Item_Quantity, Added_On) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );

    // Bind the parameters
    $stmt->bind_param(
        "iissfsd", $new_item_id, $_SESSION['user_id'], $name, $description, $price, $tags, $quantity 
    );

    // Execute the statement
    $stmt->execute();

    // Close the statement
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Cart</title>
  <link rel="stylesheet" href="css/style.css?v=1">
  <style>
    table { width: 80%; margin: 40px auto; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
    th { background: #00695c; color: white; }
    button { background: #00695c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
  </style>
</head>
<body>

    <header>
        <div class="logo">Marketplace</div>
        <nav>
            <a href="index.php">Go Back</a>
        </nav>
    </header>
    <h2 style="text-align:center; margin-top:30px;">Your Item</h2>
    <form action="upload_item.php" method="post" enctype="multipart/form-data">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="description">Product Description:</label>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea><br><br>
        
        <label for="currencyInput">Price (USD):</label>
        <input type="text" id="currencyInput" name ="currencyInput" placeholder="$0.00"  required oninput="formatCurrencyInput(event)">
        <br>
        
        <!-- <label for="image">Product Image:</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif" required><br><br> -->
        
        <label for="category">Category:</label>
        <select id="category" name="category" required>
            <option value="">Select a category</option>
            <option value="Electronics">Electronics</option>
            <option value="Clothing">Clothing</option>
            <option value="Books">Books</option>
            <option value="Toys">Toys</option>
            <!-- Add more options as needed -->
        </select><br><br>
        <input type="submit" value="Upload">
    </form>

</body>
 <script>
    function formatCurrencyInput(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, ''); // Remove all non-digit characters

    // If the value is empty, clear the input
    if (!value) {
        input.value = '';
        return;
    }

    // Convert the value to cents (integer representation)
    // This helps avoid floating-point issues
    let cents = parseInt(value, 10);

    // Divide by 100 to get the dollar/major unit value
    let majorUnit = cents / 100;

    // Use Intl.NumberFormat for locale-sensitive currency formatting
    const formatter = new Intl.NumberFormat('en-US', { // Adjust locale and currency as needed
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    // Format the major unit value
    input.value = formatter.format(majorUnit);
    }
    document.getElementById('currencyInput').addEventListener('input', formatCurrencyInput);
</script>

<?php
// close connection
mysqli_close($conn);
?>