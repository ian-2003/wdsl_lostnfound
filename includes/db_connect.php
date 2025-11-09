<?php
// IMPORTANT: Replace with your NEW, secure credentials.
define('DB_SERVER', 'MYSQL9001.site4now.net');
define('DB_USERNAME', 'abfaa3_tpaa23'); // Your DB User ID
define('DB_PASSWORD', 'Pjant9Ys'); // Your NEW Password
define('DB_NAME', 'db_abfaa3_tpaa23'); // Your DB Name

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Start the session on every page that needs it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>