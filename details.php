<?php
setlocale(LC_MONETARY, "en_IE");

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


$property_id = (int) $_GET['id']; // Replace with the ID of the property you want to retrieve
$search_query = "SELECT * FROM properties WHERE id = $property_id LIMIT 1";

$property = $conn->query($search_query)->fetch();

if ($property) {
  // Property found, do something with it
  //print_r($property);
} else {
  // Property not found
  echo "Property not found";
}

$coordinates = explode(',', $property['coordinates']);
$lat = $coordinates[0];
$lon = $coordinates[1];

$id = $property['id'];

$price = (int) $property['price'];
$formatter = new NumberFormatter('ga_IE', NumberFormatter::DECIMAL);
$formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
$price_formatted = $formatter->format($price);
?>

<div class="thin-ui-grid">
    <div class="thin-ui-col thin-ui-col-6">
        <h3>Original Property Register details</h3>

        <p>
            Address:<br>
            <?php echo $property['address']; ?>
        </p>
        <p>
            PPR Price:<br>
            <?php echo $price_formatted; ?>
        </p>

        <?php
        if ($property['type'] === 'New') {
            $value = $property['price'];
            $newValue = $value * (1 + 0.135);
            ?>

            <p>
                Sold For:<br>
                <?php echo $formatter->format($newValue); ?>
            </p>

            <?php
        }
        ?>

        <p>
            VAT Due:<br>
            <?php echo $property['vat_due']; ?>
        </p>
        <p>
            Full Market Price:<br>
            <?php echo $property['full_market_price']; ?>
        </p>
        <p>
            Property Type:<br>
            <?php echo $property['type']; ?>
        </p>

        <h3>Other Properties in the Area</h3>

        <?php
        $lat = $property['lat'];
        $lon = $property['lon'];

        if ($lat !== '' && $lon !== '') {
            $sql = "SELECT *, (
                (
                    (
                        acos(
                            sin(
                                (
                                    $lat * pi() / 180
                                )
                            ) * sin(
                                (
                                    `lat` * pi() / 180
                                )
                            ) + cos(
                                (
                                    $lat * pi() / 180
                                )
                            ) * cos(
                                (
                                    `lat` * pi() / 180
                                )
                            ) * cos(
                                (
                                    (
                                        $lon - `lon`
                                    ) * pi() / 180
                                )
                            )
                        )
                    ) * 180 / pi()
                ) * 60 * 1.1515 * 1.609344
            ) AS distance FROM `properties` WHERE id != $id HAVING (distance <= 1);";

            // Prepare the query
            $stmt = $conn->prepare($sql);

            // Execute the query
            $stmt->execute();

            // Fetch all the rows as an array of associative arrays
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo '<ul class="additional-properties">';
            // Loop through the results and output each row
            foreach ($results as $row) {
                // Do something with each row
                // For example, print the ID and address
                echo '<li data-address="' . $row['address'] . '" data-lat="' . $row['lat'] . '" data-lon="' . $row['lon'] . '">
                    <a href="https://getbutterfly.com/web/_ppr/details.php?id=' . $row['id'] . '">' . $row['address'] . '</a>
                    <br><small>Distance: ' . number_format($row['distance'], 2) . ' km</small>
                </li>';
            }
            echo '</ul>';
        }
        ?>
    </div>
    <div class="thin-ui-col thin-ui-col-6">
        <div id="map" style="height: 400px;"></div>
    </div>
</div>


<script>
var additional_locations = [];

var lis = document.querySelectorAll(".additional-properties li");
for (var i = 0; i < lis.length; i++) {
  var li = lis[i];
  var address = li.getAttribute("data-address");
  var lat = parseFloat(li.getAttribute("data-lat"));
  var lon = parseFloat(li.getAttribute("data-lon"));
  var additional_location = [address, lat, lon];
  additional_locations.push(additional_location);
}

/*
var locations = [
  ["LOCATION_1", 11.8166, 122.0942],
  ["LOCATION_2", 11.9804, 121.9189],
  ["LOCATION_3", 10.7202, 122.5621],
  ["LOCATION_4", 11.3889, 122.6277],
  ["LOCATION_5", 10.5929, 122.6325]
];
/**/

/*
var map = L.map('map').setView([11.206051, 122.447886], 8);
mapLink =
  '<a href="http://openstreetmap.org">OpenStreetMap</a>';
L.tileLayer(
  'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; ' + mapLink + ' Contributors',
    maxZoom: 18,
  }).addTo(map);
/**/


window.addEventListener("DOMContentLoaded", (event) => {
var map = L.map('map').setView([<?php echo $property['coordinates']; ?>], 13);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(map);

var marker = L.marker([<?php echo $property['coordinates']; ?>]).addTo(map);
marker._icon.classList.add("huechange");




for (var i = 0; i < additional_locations.length; i++) {
  marker = new L.marker([additional_locations[i][1], additional_locations[i][2]])
    .bindPopup(additional_locations[i][0])
    .addTo(map);
}

});
</script>

<?php
include '_footer.php';
