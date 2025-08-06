<?php
// Enable exceptions for MySQLi errors
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli("localhost", "root", "", "warehousez");

// Set charset to utf8mb4 to support full Unicode
$conn->set_charset("utf8mb4");
?>
