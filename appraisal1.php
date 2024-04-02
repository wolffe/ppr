<?php
setlocale(LC_MONETARY, 'en_IE');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '_header.php';




// Create a PDO connection
$servername = 'mysql.getbutterfly.com';
$username = 'ciprian';
$password = 'r3dlightSn0wbird';
$dbname = 'cms_ppr_db';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Disable auto-commit and begin a transaction
$conn->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
$conn->beginTransaction();




if(isset($_POST['ipv_ui_submit'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    $sql = "SELECT *, (6371 * acos(cos(radians($lat)) * cos(radians(lat)) * cos(radians(lon) - radians($lng)) + sin(radians($lat)) * sin(radians(lat)))) AS distance FROM properties HAVING distance <= .5 ORDER BY distance LIMIT 1000;";

    // Prepare the query
    $stmt = $conn->prepare($sql);

    // Execute the query
    $stmt->execute();

    // Fetch all the rows as an array of associative arrays
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$desiredDistance = 0.5; // in kilometers
$filteredProperties = array();

foreach ($results as $row) {
    if ($row['distance'] <= $desiredDistance) {
        $filteredProperties[] = $row;
    }
}

$prices = [];

// Now you can loop through $filteredProperties to display the relevant information
foreach ($filteredProperties as $property) {
    echo "Property address: " . $property['address'] . "<br>";
    echo "Latitude: " . $property['lat'] . "<br>";
    echo "Longitude: " . $property['lon'] . "<br>";
    echo "Distance: " . $property['distance'] . " km<br>";
    echo "Price: " . $property['price'] . "<br>";
    echo "<br>";

    $prices[] = $property['price'];
}

// Median
sort($prices);
$count = sizeof($prices);   // cache the count
$index = floor($count/2);  // cache the index
if (!$count) {
    echo "no values";
} elseif ($count & 1) {    // count is odd
    echo $prices[$index];
} else {                   // count is even
    echo ($prices[$index-1] + $prices[$index]) / 2;
}
//

$filteredPropertiesJSON = json_encode($filteredProperties);
echo '<div id="map" style="height: 500px;"></div>';

echo '<script>
document.addEventListener("DOMContentLoaded", (event) => {
    // Initialize the map
    var map = L.map("map").setView([53.34167156959458, -6.270996095845477], 10);

    // Add a tile layer from OpenStreetMap
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "© OpenStreetMap contributors"
    }).addTo(map);

    // Parse the PHP-generated JSON data
    var propertiesJSON = ' . $filteredPropertiesJSON . ';
    //var properties = JSON.parse(propertiesJSON);
    var properties = propertiesJSON;

    // Loop through properties and add markers to the map
    properties.forEach(function(property) {
        console.log(property.lat, property.lon);
        var marker = L.marker([property.lat, property.lon]).addTo(map);
        
        // Customize the marker popup content
        //marker.bindPopup(
        //    "<b>" + property.property_name + "</b><br>" +
        //    "Distance: " + property.distance + " km"
        //);
    });
});
</script>';
}
?>


<style>
.ipv-ui-map {
    width: 100%;
    height: 540px;
    border-radius: 3px;
    box-shadow: 0 0 48px rgba(0, 0, 0, 0.15);
}
</style>

<div class="ipv-ui-form">
    <p><span class="ipv-ui-identity-dark">&#9632;</span><span class="ipv-ui-identity-light">&#9632;</span></p>

    <p class="has-small-font-size">Drag to move the map and scroll to zoom in or out. Click anywhere on the map below to select your property location.</p>

    <div id="ipv-ui-osm-map" class="ipv-ui-map" data-latitude="53.349797774002056" data-longitude="-6.260262966188748"></div>

    <form method="post">
        <input type="hidden" name="lat" id="lat">
        <input type="hidden" name="lng" id="lng">

        <div class="thin-ui-grid">
            <div class="thin-ui-col thin-ui-col-6">
                <p>
                    <label for="property-type">Property Type <span class="ipv-field-required">*</span></label><br>
                    <select id="property-type">
                        <option value="">Select property type...</option>
                        <option value="House">House</option>
                        <option value="Semi-Detached House">Semi-Detached House</option>
                        <option value="Detached House">Detached House</option>
                        <option value="Terraced House">Terraced House</option>
                        <option value="End of Terrace House">End of Terrace House</option>
                        <option value="Townhouse">Townhouse</option>
                        <option value="Bungalow">Bungalow</option>
                        <option value="Cottage">Cottage</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Duplex">Duplex</option>
                        <option value="Land">Land</option>
                        <option value="Site">Site</option>
                    </select>
                </p>
                <p>
                    <label for="bed">Beds <span class="ipv-field-required">*</span></label><br>
                    <input id="bed" name="bed" type="number" placeholder="0" min="1" max="50">
                </p>
                <p>
                    <label for="measurement">Size</label><br>
                    <input id="measurement" type="number" step="0.01" min="0" max="2000" name="SquareSizeInputField">
                    <select id="select">
                        <option id="meter" value="meter" name="SquareTypeInputField">sqm</option>
                        <option id="foot" value="foot" name="SquareTypeInputField">sqft</option>
                    </select>
                    <br><small>Optional</small>
                </p>
            </div>
            <div class="thin-ui-col thin-ui-col-6">
                <p>
                    <label>Are you</label><br>
                    <select id="ipv-reason">
                        <option selected>Ready to sell?</option>
                        <option>Looking to upsize/downsize?</option>
                        <option>Looking to release equity?</option>
                        <option>Probate family law?</option>
                        <option>Local Property Tax?</option>
                        <option>Family Transfer?</option>
                        <option>Capital Gains Tax?</option>
                        <option>HSE Fair Deal Scheme?</option>
                        <option>Planning to sell?</option>
                        <option>Planning to let?</option>
                        <option>Just curious?</option>
                    </select>
                    <br><small>Optional</small>
                </p>
                <p>
                    <label for="ipv_email">Email address <span class="ipv-field-required">*</span></label><br>
                    <input name="ipv_email" id="ipv-email" type="email" size="48" placeholder="Email address">
                    <br><small>We will send your valuation on this email address.</small>
                </p>

                <div class="ip-ui-child">
                    <button type="submit" name="ipv_ui_submit" id="ipv-ui-submit">Request Valuation</button>
                </div>
                <div class="ip-ui-child">
                    <div class="ipv-ui--hidden" id="ipv-ui-request"></div>
                </div>
            </div>
        </div>

        <p id="average" style="text-align:center;font-size:24px"></p>
        <p id="average-desc" style="text-align:center"></p>

        <p><span class="ipv-ui-identity-dark">&#9632;</span><span class="ipv-ui-identity-light">&#9632;</span></p>
    </form>
</div>


<script>
function median(numbers) {
    const sorted = numbers.slice().sort((a, b) => a - b);
    const middle = Math.floor(sorted.length / 2);

    if (sorted.length % 2 === 0) {
        return (sorted[middle - 1] + sorted[middle]) / 2;
    }

    return sorted[middle];
}


document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ipv-ui-osm-map')) {
        // Initialize coordinates
        // Dublin is 53.349803617967474, -6.260251700878144
        let lat = document.getElementById('ipv-ui-osm-map').dataset.latitude,
            lon = document.getElementById('ipv-ui-osm-map').dataset.longitude;

        // Initialize map
        let osmMap = L.map('ipv-ui-osm-map').setView([lat, lon], 12);
        let markerGroup = L.layerGroup().addTo(osmMap);

        L.marker([lat, lon]).addTo(osmMap).bindPopup('');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(osmMap);
        L.control.scale().addTo(osmMap);

        // Get marker position onclick
        osmMap.on('click', e => {
            osmMap.eachLayer(layer => {
                if (layer._latlng) {
                    osmMap.removeLayer(layer);
                }
            });

            console.log("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng);


            L.marker([e.latlng.lat, e.latlng.lng]).addTo(markerGroup);
            L.marker([e.latlng.lat, e.latlng.lng])
            .addTo(osmMap)
            .bindPopup("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng);

            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });
    }
});
</script>

<?php
include '_footer.php';
