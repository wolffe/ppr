<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Geolocation providers
 *
 * 
 */

// Set the database connection details
$servername = 'mysql.getbutterfly.com';
$username = 'ciprian';
$password = 'r3dlightSn0wbird';
$dbname = 'cms_ppr_db';

// Set the Nominatim endpoint URL
$nominatim_url = 'https://nominatim.openstreetmap.org/search.php';

// Set the user agent for the request
$user_agent = 'PPR Express (Browser)';


$addresses = [];

// Set the SQL query to retrieve the addresses
$sql = "SELECT id, address, county FROM properties WHERE coordinates IS NULL OR coordinates = ''";

try {
    // Connect to the database using PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL query
    $stmt = $pdo->prepare($sql);

    // Execute the SQL query
    $stmt->execute();

    // Loop through the results and geocode each address
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Get the address from the row
        $addresses[] = $row['address'] . ', ' . $row['county'] . ', Ireland';
    }
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

// Close the database connection
$pdo = null;
?>


<script>
const addresses = [
    <?php echo '"' . implode('","', $addresses) . '"'; ?>
];

async function fetchAddresses() {
    for (let i = 0; i < addresses.length; i++) {
        const address = addresses[i];

        try {
            const response = await fetch(`_get_coordinates.php?address=${encodeURIComponent(address)}`);
            const data = await response.json();

            console.log(`Latitude: ${data.lat}, Longitude: ${data.lon}`);

            // Make another Fetch request to update the database with the coordinates
            const updateResponse = await fetch(`_update_database.php?address=${encodeURIComponent(address)}&lat=${data.lat}&lon=${data.lon}`);
            const updateData = await updateResponse.json();

            console.log(updateData.message);
        } catch (error) {
            console.error(error);
        }

        // Wait for 2 seconds before making the next Fetch request
        await new Promise(resolve => setTimeout(resolve, 2000));
    }
}

fetchAddresses();
</script>


