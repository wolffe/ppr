<?php
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



$email         = filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL );
$lat           = filter_input( INPUT_POST, 'lat', FILTER_VALIDATE_FLOAT );
$lon           = filter_input( INPUT_POST, 'lng', FILTER_VALIDATE_FLOAT );
$property_type = urlencode( filter_input( INPUT_POST, 'property_type', FILTER_SANITIZE_SPECIAL_CHARS ) );
$bed           = (int) $_POST['bed'];
$measurement   = (int) $_POST['measurement'];
$select        = filter_input( INPUT_POST, 'select', FILTER_SANITIZE_SPECIAL_CHARS );
$reason        = filter_input( INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS );



function wppd_pro_get_median( $arr ) {
    sort( $arr );

    $count     = count( $arr );
    $middleval = floor( ( $count - 1 ) / 2 );

    if ( $count % 2 ) {
        $median = $arr[ $middleval ];
    } else {
        $low    = $arr[ $middleval ];
        $high   = $arr[ $middleval + 1 ];
        $median = ( ( $low + $high ) / 2 );
    }

    return $median;
}




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
    ) AS distance FROM `properties` HAVING (distance <= 1);";

    // Prepare the query
    $stmt = $conn->prepare($sql);

    // Execute the query
    $stmt->execute();

    // Fetch all the rows as an array of associative arrays
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $prices = [];

    // Loop through the results and output each row
    foreach ($results as $row) {
        $prices[] = $row['price'];
    }

    echo '<pre>';
    print_r($prices);
    echo '</pre>';


    $success = 'false';
    $amount = 0;

    if (count($prices) > 0) {
        $success = 'true';
        $amount = wppd_pro_get_median( $prices );
    }

}


echo json_encode([
    'success' => $success,
    'score' => $amount
]);

