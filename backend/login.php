<?php
// login.php - authenticate user and start session
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signin.php');
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

$username = isset($_POST['user']) ? trim($_POST['user']) : '';
$password = isset($_POST['psw']) ? $_POST['psw'] : '';

if ($username === '' || $password === '') {
    header('Location: signin.php?error=missing');
    exit;
}

$conn = get_db_connection();

$stmt = $conn->prepare('SELECT User_ID, Username, PasswordHash, Role FROM Users WHERE Username = ? LIMIT 1');
if (!$stmt) {
    error_log('Prepare failed (login): ' . $conn->error);
    $conn->close();
    header('Location: signin.php?error=server');
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['PasswordHash'])) {
        session_start();
        // prevent session fixation
        session_regenerate_id(true);
        // store minimal user info in session
        $_SESSION['user_id'] = $row['User_ID'];
        $_SESSION['username'] = $row['Username'];
        $roles = explode(' ', $row['Role']);
        if (count($roles) > 0 && in_array('seller', $roles)) {
            $_SESSION['seller_registered'] = true;
        }
        $stmt->close();
        $conn->close();
        header('Location: ../frontend/index.php');
        exit;

    }
}

// invalid credentials
if (isset($stmt) && !empty($stmt)) $stmt->close();
$conn->close();
header('Location: signin.php?error=invalid');
exit;

?>
