<?php
session_start();
session_unset();
session_destroy();
// Redirect to the frontend homepage after logging out
header("Location: ../frontend/index.php");
exit();
?>
