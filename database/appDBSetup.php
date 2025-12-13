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
$dbname = "Marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "\nConnected successfully";

// views created...

// New Products ...
$sql = "CREATE VIEW New_Products AS
SELECT *
FROM Marketplace.Item
WHERE Added_On >= DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 DAY);"; 

// Featured products ...
// $sql =  


// Recomendations ...
// $sql = 



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Marketplace Products</title>
  <link rel="stylesheet" href="css/style.css?v=1" />
</head>
<body>
  <header>
    <?php
        if ($conn->query($sql) === TRUE) {
            echo "New Products View created successfully\n";
        } else {
            echo "Error creating view: " . $conn->error;
        }
    ?>
  </header>
  <button onclick="window.location.href = '../frontend/';">Go to Marketplace</button>
</body>
</html>

<?php
mysqli_close($conn);
?>

