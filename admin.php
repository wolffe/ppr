<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


// URL of the ZIP file to download
$url = 'https://www.propertypriceregister.ie/website/npsra/ppr/npsra-ppr.nsf/Downloads/PPR-ALL.zip/$FILE/PPR-ALL.zip';

// Location where the ZIP file will be saved
$zipfile = 'PPR-ALL.zip';

// Location where the CSV file will be extracted
$csvfile = 'PPR-ALL-'.time().'.csv';

// Create a stream context to disable SSL verification
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]);

// Download the ZIP file with SSL verification disabled
$zipdata = file_get_contents($url, false, $context);

// Check for errors
if ($zipdata === false) {
    echo 'Failed to download ZIP file';
    exit(1);
}

// Save the ZIP file to disk
if (file_put_contents($zipfile, $zipdata) === false) {
    echo 'Failed to save ZIP file';
    exit(1);
}

// Extract the CSV file from the ZIP file
$zip = new ZipArchive();
if ($zip->open($zipfile) === true) {
    if ($zip->extractTo('.') === false) {
        echo 'Failed to extract CSV file from ZIP';
        exit(1);
    }
    $zip->close();
    // Rename the extracted CSV file to its final name
    if (rename('PPR-ALL.csv', $csvfile) === false) {
        echo 'Failed to rename CSV file';
        exit(1);
    }
} else {
    echo 'Failed to open ZIP file';
    exit(1);
}
