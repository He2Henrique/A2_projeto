<?php
session_start(); // Start the session to access session variables
require_once ('core_func.php'); // Include the database connection file
require_once ('config_serv.php'); // Include the database connection file

$conn = DatabaseManager::getInstance(); // Get the database connection instance
    
$result = $aulas = $conn->select('aulas', [], 'id_aulas,id_modalidade'); // Get the user's name and ID
print_r($result); // Print the result for debugging