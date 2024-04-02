<?php
setlocale(LC_MONETARY, 'en_IE');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '_header.php';

/*
https://github.com/virtualarchitectures/Open-Data-in-Ireland/blob/6c2ad25070111d67150031acc7fbad794dd97837/README.md?plain=1#L21
https://github.com/BuildingCityDashboards/bcd-dd-v2.1/blob/b39abce5c310e7702335838926abe85fc210ffc8/public/javascripts/queries/homes_housing.js#L4

https://github.com/BuildingCityDashboards/bcd-dd-v2.1/blob/b39abce5c310e7702335838926abe85fc210ffc8/controllers/res_property_price.js#L37

https://github.com/A2dez/property_prices_ireland
https://a2dez.net/2022/09/28/is-there-value-left-anywhere-in-buying-a-new-home/

NEW HOUSE VAT - https://thepropertypin.com/t/propertypriceregisterireland-com-vs-propertypriceregister-ie/49057

In a small number of transactions included in the Register the price shown does not represent the full market price of the property concerned for a variety of reasons. All such properties are marked **.
If the property is a new property, the price shown is exclusive of VAT at 13.5%.

https://www.amazon.co.uk/Fireside-Apps-Property-Register-Ireland/dp/B00X3YNM5A

https://www.kaggle.com/datasets/erinkhoo/property-price-register-ireland
/** */


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create a PDO connection
$servername = 'mysql.getbutterfly.com';
$username = 'ciprian';
$password = 'r3dlightSn0wbird';
$dbname = 'cms_ppr_db';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Disable auto-commit and begin a transaction
$conn->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
$conn->beginTransaction();

// Get all properties
$sql = "SELECT COUNT(id) AS count FROM properties";
$result = $conn->query($sql);

$count_total = $result->fetchColumn();

// Get all properties with coordinates
$sql = "SELECT COUNT(id) AS count FROM properties WHERE coordinates IS NOT NULL OR coordinates != ''";
$result = $conn->query($sql);

$count_coordinates = $result->fetchColumn();
?>

<div class="thin-ui-grid">
    <div class="thin-ui-col thin-ui-col-6">
        <div style="font-size:32px;font-weight:700"><?php echo number_format($count_total); ?></div>
        total properties
    </div>
    <div class="thin-ui-col thin-ui-col-6">
        <div style="font-size:32px;font-weight:700"><?php echo number_format($count_coordinates); ?></div>
        total properties with coordinates
    </div>
</div>

<div class="thin-ui-grid">
    <div class="thin-ui-col">
        <div id="map" style="height: 400px;"></div>
    </div>
</div>

<?php
$sql = "SELECT address, lat, lon FROM `properties` WHERE coordinates IS NOT NULL OR coordinates != ''";

// Prepare the query
$stmt = $conn->prepare($sql);

// Execute the query
$stmt->execute();

// Fetch all the rows as an array of associative arrays
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$map_properties = [];

// Loop through the results and output each row
foreach ($results as $row) {
    $map_properties[] = [
        $row['address'],
        $row['lat'],
        $row['lon'],
    ];
}

// Encode the array in JSON format
$json = json_encode($map_properties);
?>

<script>
var locations = <?php echo $json; ?>;

window.addEventListener("DOMContentLoaded", (event) => {
    var map = L.map('map').setView([53.349797774002056, -6.260262966188748], 6);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    var markers = L.markerClusterGroup({ chunkedLoading: true });

    for (var i = 0; i < locations.length; i++) {
        markers.addLayer(L.marker([locations[i][1], locations[i][2]])).bindPopup(locations[i][0]);
        /*
        marker = new L.marker([locations[i][1], locations[i][2]])
            .bindPopup(locations[i][0])
            .addTo(map);
        /**/
    }

    map.addLayer(markers);
});
</script>


<?php
include '_footer.php';
