<?php
if(isset($_POST['url'])) {
    $redirectedURL = $_POST['url'];
    preg_match('/@([-0-9.]+),([-0-9.]+)/', $redirectedURL, $matches);
    $latitude = $matches[1];
    $longitude = $matches[2];
    echo "Latitude: {$latitude}, Longitude: {$longitude}";
}
?>
