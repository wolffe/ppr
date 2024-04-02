<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);



// Get the address, latitude, and longitude from the query parameters
$address = $_GET['address'];
$lat = $_GET['lat'];
$lon = $_GET['lon'];

$address = preg_replace('/,\s\w+,\s\w+$/', '', $address);

if ((string) $lat === 'null' && (string) $lon === 'null') {
    // Send a JSON response indicating success
    $response = array('status' => 'error', 'message' => "No coordinates found for address: $address");
    header('Content-Type: application/json');
    echo json_encode($response);

    return;
}

// Set the database connection details
$servername = 'mysql.getbutterfly.com';
$username = 'ciprian';
$password = 'r3dlightSn0wbird';
$dbname = 'cms_ppr_db';

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Prepare and execute the UPDATE statement
$sqlUpdate = 'UPDATE properties SET coordinates = ?, lat = ?, lon = ? WHERE address = ?';
$stmt = $pdo->prepare($sqlUpdate);
$stmt->execute([$lat . ',' . $lon, $lat, $lon, $address]);

// Send a JSON response indicating success
$response = array('status' => 'success', 'message' => "Updated coordinates for address: $address");
header('Content-Type: application/json');
echo json_encode($response);
