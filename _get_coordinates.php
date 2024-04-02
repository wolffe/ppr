<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

/**
 * geocode.maps.co
 * Powered by https://maps.co/
 *
 * @url https://geocode.maps.co/
 * @todo Use display_name, class and type to enhance listings
 * https://geocode.maps.co/search?q=90%20Suncroft%20Drive,%20Tallaght,%20Dublin%2024,%20Dublin,%20Ireland
 */

// Get the address query parameter from the request URL
$address = $_GET['address'];

// Construct the Maps.co API URL with the address parameter
$url = "https://geocode.maps.co/search?q=" . urlencode($address);

// Initialize a cURL handle
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the HTTP request and retrieve the response
$response = curl_exec($ch);

// Close the cURL handle
curl_close($ch);

// Parse the JSON response
$json = json_decode($response, true);

// Extract the latitude and longitude from the response
$lat = $json[0]['lat'];
$lon = $json[0]['lon'];



if (!$lat || !$lon) {
    $url_geokeo = 'https://geokeo.com/geocode/v1/search.php?q=' . urlencode($address) . '&api=8530b1d65307e3670cba8f508f96309c';

    $json_geokeo = file_get_contents($url_geokeo);
    $json_geokeo = json_decode($json_geokeo);

    if ($json_geokeo->status) {
        //echo 'status';
        if ($json_geokeo->status == 'ok') {
            $address_geokeo = $json_geokeo->results[0]->formatted_address;

            $lat = $json_geokeo->results[0]->geometry->location->lat;
            $lon = $json_geokeo->results[0]->geometry->location->lng;
        }
    }
}

// https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=70%20CHARLESTOWN%20PARK,%20ST%20MARGARETS%20RD,%20DUBLIN%2011,%20Co.%20Dublin,%20Ireland&f=pjson

if (!$lat || !$lon) {
    $url_arcgis = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/find?text=' . urlencode($address) . '&f=pjson';

    $json_arcgis = file_get_contents($url_arcgis);
    $json_arcgis = json_decode($json_arcgis);

    if ($json_arcgis->locations) {
        $address_arcgis = $json_arcgis->locations[0]->name;

        $lat = $json_arcgis->locations[0]->feature->geometry->y;
        $lon = $json_arcgis->locations[0]->feature->geometry->x;
    }
}

/*
if (!$lat || !$lon) {
    $url_geocode = 'https://geocode.xyz/' . urlencode($address) . '?json=1';

    $json_geocode = file_get_contents($url_geocode);
    $json_geocode = json_decode($json_geocode);

    if ($json_geocode->standard->addresst) {
        $address_geocode = $json_geocode->standard->addresst;

        $lat = $json_geocode->latt;
        $lon = $json_geocode->longt;
    }
}
/**/



// Return the latitude and longitude as a JSON-encoded string
echo json_encode(
    [
        'lat' => $lat,
        'lon' => $lon,
    ]
);
