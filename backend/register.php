<?php
// register.php - handle account creation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: createacc.php');
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

$username = isset($_POST['user']) ? trim($_POST['user']) : '';
$password = isset($_POST['psw']) ? $_POST['psw'] : '';
$password_repeat = isset($_POST['psw-repeat']) ? $_POST['psw-repeat'] : '';

// Basic validation
if ($username === '' || $password === '') {
    // missing fields
    header('Location: createacc.php?error=missing');
    exit;
}
if ($password !== $password_repeat) {
    header('Location: createacc.php?error=nomatch');
    exit;
}

// Password policy: at least 6 characters
if (strlen($password) < 6) {
    header('Location: createacc.php?error=weak');
    exit;
}

$conn = get_db_connection();
// Ensure Users table exists (helps when DB migration hasn't been run yet)
$create_users_sql = "CREATE TABLE IF NOT EXISTS Users (
    User_ID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    Role VARCHAR(20) NOT NULL DEFAULT 'buyer',
    Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);";

if (!$conn->query($create_users_sql)) {
    error_log('Failed to create Users table: ' . $conn->error);
    $conn->close();
    header('Location: createacc.php?error=server');
    exit;
}

// Prepared statement to avoid SQL injection
$stmt = $conn->prepare('INSERT INTO Users (Username, PasswordHash) VALUES (?, ?)');
if (!$stmt) {
    // If prepare failed, log error and try to surface a helpful message
    error_log('Prepare failed: ' . $conn->error);
    $conn->close();
    header('Location: createacc.php?error=server');
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);
$stmt->bind_param('ss', $username, $password_hash);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: index.php?registered=1');
    exit;
} else {
    // Duplicate username or other error
    error_log('Execute failed: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    header('Location: createacc.php?error=exists');
    exit;
}

?>
