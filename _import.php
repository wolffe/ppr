<?php
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

// Function to generate hash based on address
function generateAddressHash($address) {
    return md5(strtolower(trim($address)));
}

function extractPriceInt($price) {
    // Remove the euro sign and commas from the price
    $price = str_replace(',', '', substr($price, 1));

    // Round the float value to the nearest integer
    $price = round(floatval($price));

    // Extract the integer portion of the price
    $int = filter_var($price, FILTER_SANITIZE_NUMBER_INT);

    return intval($int);
}

function extractType($type) {
    if (strpos(strtolower($type), 'second-hand') !== false) {
        return 'Second Hand';
    } elseif (strpos(strtolower($type), 'new') !== false) {
        return 'New';
    } else {
        return 'N/A';
    }
}

function convertDate($date) {
    // Create a new DateTime object from the date string
    $dateTime = DateTime::createFromFormat('d/m/Y', $date);

    // Return the date in the yyyy-mm-dd format
    return $dateTime->format('Y-m-d');
}



// Open the CSV file
$file = fopen('PPR-ALL.csv', 'r');

// Read and discard the first line
fgets($file);

// Create a PDO connection
$servername = 'mysql.getbutterfly.com';
$username = 'ciprian';
$password = 'r3dlightSn0wbird';
$dbname = 'cms_ppr_db';

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Disable auto-commit and begin a transaction
$conn->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
$conn->beginTransaction();

// Loop through each line of the CSV file
$count = 0;
$addresses = [];
$hashes = 0;

while (($data = fgetcsv($file)) !== false) { // && $count < 1000
    // Create date|address|price hash
    $hash = generateAddressHash($data[0] . $data[1] . extractPriceInt($data[4]));
    //$hash = generateAddressHash($data[1]);

    // Generate columns
    $columns = [
        'hash' => $hash,
        'date' => convertDate($data[0]),
        'address' => $data[1],
        'county' => $data[2],
        'eircode' => $data[3],
        'price' => extractPriceInt($data[4]),
        'full_market_price' => $data[5],
        'vat_due' => $data[6],
        'description' => extractType($data[7]),
        'size' => $data[8],
    ];

    // Check if the hash already exists in the database
    $stmt_exists = $conn->prepare('SELECT COUNT(id) FROM properties WHERE hash = :hash');
    $stmt_exists->bindValue(':hash', $hash);
    $stmt_exists->execute();
    $result = $stmt_exists->fetchColumn();

    $addresses[] = $columns['address'];

    if ($result == 0) {
        echo 'Record does not exist: ' . $columns['address'] . ', creating...<br>';

        // Prepare the insert statement
        $stmt = $conn->prepare(
            "INSERT INTO properties (
                hash,
                address,
                county,
                price,
                full_market_price,
                vat_due,
                type,
                size,
                date,
                eircode
            ) VALUES (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )"
        );

        // Bind the parameters and execute the statement
        $stmt->bindParam(1, $hash);
        $stmt->bindParam(2, $columns['address']);
        $stmt->bindParam(3, $columns['county']);
        $stmt->bindParam(4, $columns['price']);
        $stmt->bindParam(5, $columns['full_market_price']);
        $stmt->bindParam(6, $columns['vat_due']);
        $stmt->bindParam(7, $columns['description']);
        $stmt->bindParam(8, $columns['size']);
        $stmt->bindParam(9, $columns['date']);
        $stmt->bindParam(10, $columns['eircode']);
        $stmt->execute();
    } else {
        //echo "Record already exists: " . $hash . ", skipping...<br>";

        $hashes++;

        //echo 'Record already exists or is newer ' . $columns['address'] . ', updating...<br>';
        echo 'Record already exists or is newer ' . $columns['address'] . ', skipping...<br>';

        /*
        // Update the record in the database
        $stmt = $conn->prepare(
            "UPDATE properties SET 
                address = ?,
                county = ?, 
                price = ?, 
                full_market_price = ?, 
                vat_due = ?, 
                type = ?, 
                size = ?, 
                date = ?, 
                eircode = ? 
            WHERE hash = ?"
        );
        $stmt->execute(
            [
                $columns['address'],
                $columns['county'],
                $columns['price'],
                $columns['full_market_price'],
                $columns['vat_due'],
                $columns['description'],
                $columns['size'],
                $columns['date'],
                $columns['eircode'],
                $hash,
            ]
        );
        /**/
    }

    // Commit the changes every 100 records
    if (++$count % 100 == 0) {
        $conn->commit();
        $conn->beginTransaction();
    }
}

echo '<p>Final count stopped at ' . $count . '.</p>';
echo '<p>All addresses: ' . count($addresses) . '.</p>';
echo '<p>All unique addresses: ' . count(array_unique(array_filter($addresses))) . '.</p>';
echo '<p>All hashes found: ' . $hashes . '.</p>';

// Commit any remaining changes and close the connection
$conn->commit();
fclose($file);
$conn = null;
