<!-- Still needs testing after the login and image integration -->
<?php
session_start();

//init database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marketplace";

// Check if the user is logged in
// NOTE: Call out this if statement to debug !!!!!!
if (!isset($_SESSION['username'])) {
    header("Location: signin.php");
    exit();
}
if (!isset($_SESSION['seller_registered'])) {
    header("Location: enroll_as_seller.php");
    exit();
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Sanitize and validate input
    $price = floatval(str_replace(['$'], [''],$_POST['currencyInput']));    
    $name = strip_tags($_POST['name']);
    $description = strip_tags($_POST['description']);
    $tags = trim($_POST['category']);
    $quantity = intval($_POST['quantity']);
    $new_item_id = 0;
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($name) || empty($description) || empty($tags) || $price <= 0 || $quantity <= 0) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px auto; max-width: 600px; text-align: center;'>";
        echo "<strong>Error!</strong> Please fill in all required fields with valid values.";
        echo "</div>";
    } else {
    
        //getting last item id
        $query = "SELECT * FROM Item ORDER BY Item_ID DESC LIMIT 1;";
        $result = $conn->query($query);
        if ($result && $result->num_rows > 0){
            $row = $result->fetch_assoc();
            $last_item_id = $row['Item_ID'];
            $new_item_id = $last_item_id + 1;
        } else {
            $new_item_id = 1; // First item
        }
        
        // Handle image upload
        $image_uploaded = false;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            
            // Validate file type and size (max 5MB)
            if (in_array($file_type, $allowed_types) && $file_size <= 5000000) {
                $upload_dir = 'images/products/';
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $target_file = $upload_dir . $new_item_id . '.jpg'; // Standardize to .jpg
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_uploaded = true;
                }
            }
        }
        
        // If no image was uploaded, use a default image
        if (!$image_uploaded) {
            $default_image = 'images/products/default.jpg';
            if (!file_exists($default_image)) {
                // Create a simple default image placeholder
                copy('images/Hero_image.jpg', $default_image);
            }
            copy($default_image, 'images/products/' . $new_item_id . '.jpg');
        }

        $stmt = $conn->prepare(
            "INSERT INTO Item (Item_ID, Seller_ID, Item_Name, Item_Description, Item_Price, Item_Tags, Item_Quantity, Added_On) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }

        // Bind the parameters (types: i = int, s = string, d = double)
        $stmt->bind_param(
            "iissdsi", // i, i, s, s, d, s, i
            $new_item_id,
            $user_id,
            $name,
            $description,
            $price,
            $tags,
            $quantity
        );


    // Execute the statement with error handling
    if (!$stmt->execute()) {
        // Provide a readable error message
        echo "Database error: " . $stmt->error;
        echo "<br>Debug info: Trying to insert Item_ID=$new_item_id, Seller_ID=$user_id";
        // Check if seller exists
        $check_stmt = $conn->prepare("SELECT Seller_ID FROM Seller WHERE Seller_ID = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows == 0) {
            echo "<br>Error: User ID $user_id is not registered as a seller in the Seller table.";
            echo "<br><a href='enroll_as_seller.php'>Please enroll as a seller first</a>";
        }
        $check_stmt->close();
    } else {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px auto; max-width: 600px; text-align: center;'>";
            echo "<strong>Success!</strong> Item '$name' has been uploaded successfully to the '$tags' category.";
            echo "<br><br><a href='product_page.php?category=" . urlencode($tags) . "' style='background: #00695c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>View in $tags Category</a>";
            echo " | <a href='product_page.php' style='background: #00695c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>View All Products</a>";
            echo "</div>";
        }

        // Close the statement
        $stmt->close();
    } // Close validation block

    $conn->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upload Item - Marketplace</title>
  <link rel="stylesheet" href="css/style.css?v=1">
  <style>
    table { width: 80%; margin: 40px auto; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: center; }
    th { background: #00695c; color: white; }
    button { background: #00695c; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; }
    .upload-form { 
      max-width: 600px; margin: 40px auto; padding: 30px; 
      background: #f9f9f9; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
    }
    .upload-form label { display: block; margin: 15px 0 5px 0; font-weight: bold; color: #333; }
    .upload-form input, .upload-form textarea, .upload-form select { 
      width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; 
      font-size: 14px; box-sizing: border-box; 
    }
    .upload-form input[type="submit"] { 
      background: #00695c; color: white; cursor: pointer; margin-top: 20px; 
      transition: background 0.3s ease; 
    }
    .upload-form input[type="submit"]:hover { background: #004d40; }
  </style>
</head>
<body>

    <header>
        <div class="logo">Marketplace</div>
        <nav>
            <a href="index.php">Go Back</a>
        </nav>
    </header>
    <h2 style="text-align:center; margin-top:30px;">Upload Your Item</h2>
    <div class="upload-form">
    <form action="upload_item.php" method="post" enctype="multipart/form-data">
        <label for="name">Product Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        
        <label for="description">Product Description:</label>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea><br><br>
        
        <label for="currencyInput">Price (USD):</label>
        <input type="text" id="currencyInput" name ="currencyInput" placeholder="$0.00"  required oninput="formatCurrencyInput(event)">
        <br>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" min="1" required><br><br>
        
        <label for="image">Product Image (Optional):</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif">
        <small style="color: #666; display: block; margin-top: 5px;">
          Supported formats: JPG, PNG, GIF. Max size: 5MB. If no image is uploaded, a default image will be used.
        </small><br><br>
        
        <label for="category">Category:</label>
        <select id="category" name="category" required>
            <option value="">Select a category</option>
            <option value="Electronics">Electronics</option>
            <option value="Furniture">Furniture</option>
            <option value="Food">Food</option>
            <option value="Beauty and Personal Care">Beauty and Personal Care</option>
             <option value="Fashion and Apparel">Fashion and Apparel</option>
            <option value="Toy and Hobbies">Toy and Hobbies</option>
            <!-- Add more options as needed -->
        </select><br><br>
        <input type="submit" value="Upload Item">
    </form>
    </div>

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

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const description = document.getElementById('description').value.trim();
        const price = document.getElementById('currencyInput').value.trim();
        const quantity = document.getElementById('quantity').value;
        const category = document.getElementById('category').value;
        const imageFile = document.getElementById('image').files[0];

        if (!name || !description || !price || !quantity || !category) {
            alert('Please fill in all required fields.');
            e.preventDefault();
            return false;
        }

        if (quantity < 1) {
            alert('Quantity must be at least 1.');
            e.preventDefault();
            return false;
        }

        if (imageFile && imageFile.size > 5000000) {
            alert('Image file must be less than 5MB.');
            e.preventDefault();
            return false;
        }

        return true;
    });
</script>
