<?php
if(isset($_POST['address'])) {
    $address = urlencode($_POST['address']);
    $url = "https://www.bing.com/maps?q={$address}";

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // Execute cURL session
    $response = curl_exec($ch);

    // Check for errors
    if($response === false) {
        echo "Failed to fetch coordinates";
    } else {
        // Get the final URL after redirection
        $redirectedURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        // Close cURL session
        curl_close($ch);

        // Send the redirected URL as the response
        echo $redirectedURL;
    }
}
?>
