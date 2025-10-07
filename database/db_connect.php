<?php
// Simple mysqli connection helper for the Marketplace database
$DB_SERVER = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'Marketplace';

function get_db_connection() {
    global $DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME;
    $conn = new mysqli($DB_SERVER, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }
    // set charset
    $conn->set_charset('utf8mb4');
    return $conn;
}

?>
