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

$counties = [];

foreach ($conn->query("SELECT DISTINCT county FROM properties") as $results) {
   $counties[] = $results['county'];
}

$counties = array_filter( array_unique( $counties ) );
sort( $counties );



if (isset($_GET['search'])) {
    // Set the number of results per page
    $results_per_page = 10;

    // Get the current page number from the query string
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Calculate the offset
    $offset = ($current_page - 1) * $results_per_page;

    // Initialize WHERE clauses
    $where_address = '';
    $where_county = '';
    $where_price = '';
    $where_date = '';
    $where_type = '';

    // Get address
    if (isset($_GET['address']) && trim($_GET['address']) !== '') {
        $address = trim($_GET['address']);

        $where_address = " AND address LIKE '%$address%' ";
    }

    // Get county
    if (isset($_GET['county']) && trim($_GET['county']) !== '') {
        $county = trim($_GET['county']);

        $where_county = " AND county = '$county' ";
    }

    // Get price
    $price_from = trim($_GET['price_from']);
    $price_to = trim($_GET['price_to']);

    $where_price = " AND (price BETWEEN '$price_from' AND '$price_to') ";

    // Get date
    $date_from = trim($_GET['date_from']);
    $date_to = trim($_GET['date_to']);

    $where_date = " AND (date BETWEEN '$date_from' AND '$date_to') ";

    // Get type
    if (isset($_GET['type']) && trim($_GET['type']) !== '') {
        $type = trim($_GET['type']);

        $where_type = " AND type = '$type' ";
    }

    // Modify the query to include the WHERE clauses and the LIMIT and OFFSET clauses
    $search_query = "SELECT * FROM properties WHERE 1=1
        $where_address
        $where_county
        $where_price
        $where_date
        $where_type
        ORDER BY date DESC
        LIMIT :limit OFFSET :offset
    ";

    // Prepare the statement
    $stmt = $conn->prepare($search_query);

    // Bind the parameters
    $stmt->bindParam(':limit', $results_per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    // Execute the query
    $stmt->execute();

    // Fetch the results
    $results = $stmt->fetchAll();

    // Loop through the results
    foreach ($results as $search_results) {
        $response[] = $search_results;
    }

    // Modify the query to get the total number of rows
    $count_query = "SELECT COUNT(*) as count FROM properties WHERE 1=1
        $where_address
        $where_county
        $where_price
        $where_date
        $where_type
    ";

    // Execute the query
    $stmt = $conn->query($count_query);

    // Get the total number of rows
    $total_rows = $stmt->fetch()['count'];

    // Calculate the total number of pages
    $total_pages = ceil($total_rows / $results_per_page);



    /**
     * Build pagination based on form fields and current page
     */

    // Get the current search query parameters
    $search_params = $_GET;

    // Remove the "page" parameter from the search query parameters, if it exists
    unset($search_params['page']);

    // Calculate the maximum number of links to show between the Previous and Next links
    $max_links = 10;

    // Calculate the first and last page numbers to show
    $first_link = max(1, $current_page - floor($max_links / 2));
    $last_link = min($total_pages, $first_link + $max_links - 1);

    // Adjust the first and last page numbers to show exactly $max_links links
    if ($last_link - $first_link + 1 < $max_links) {
        if ($first_link == 1) {
            $last_link = min($total_pages, $first_link + $max_links - 1);
        } else {
            $first_link = max(1, $last_link - $max_links + 1);
        }
    }

    // Build the pagination links
    $pagination_links = '';



    // Build the "Previous" link
    if ($current_page > 1) {
        $prev_page = $current_page - 1;

        // Add the "page" parameter to the search query parameters
        $search_params['page'] = $prev_page;

        // Build the pagination link with the current search query parameters
        $pagination_link = 'results.php?' . http_build_query($search_params);

        $pagination_links .= "<a href=\"$pagination_link\" class='thin-ui-button thin-ui-button-neutral thin-ui-button-small'>&laquo; Previous</a> ";
    }

    // Build the numbered links
    for ($i = $first_link; $i <= $last_link; $i++) {
        // Build the pagination link with the current search query parameters
        $search_params['page'] = $i;
        $pagination_link = 'results.php?' . http_build_query($search_params);

        // Add the page number to the pagination links string
        if ($i == $current_page) {
            $pagination_links .= "<strong class='thin-ui-button thin-ui-button-primary thin-ui-button-small'>$i</strong> ";
        } else {
            $pagination_links .= '<a href="' . $pagination_link . '" class="thin-ui-button thin-ui-button-neutral thin-ui-button-small">' . $i . '</a> ';
        }
    }

    // Build the "Next" link
    if ($current_page < $total_pages) {
        $next_page = $current_page + 1;

        // Add the "page" parameter to the search query parameters
        $search_params['page'] = $next_page;

        // Build the pagination link with the current search query parameters
        $pagination_link = 'results.php?' . http_build_query($search_params);

        $pagination_links .= "<a href=\"$pagination_link\" class='thin-ui-button thin-ui-button-neutral thin-ui-button-small'>Next &raquo;</a>";
    }

    echo '<div class="pagination">';
        echo $pagination_links;
    echo '</div>';
    // End pagination



    echo '<table width="100%">
        <thead>
            <tr>
                <th>Date Added</th>
                <th>Address</th>
                <th>Coordinates</th>
                <th>County</th>
                <th>Price</th>
                <th>Type</th>
            <tr>
        </thead>
        <tbody>';

            foreach ($response as $row) {
                $price = (int) $row['price'];
                $formatter = new NumberFormatter('ga_IE', NumberFormatter::DECIMAL);
                $price_formatted = $formatter->format($price);

                echo '<tr>
                    <td>' . $row['date'] . '</td>
                    <td><a href="https://getbutterfly.com/web/_ppr/details.php?id=' . $row['id'] . '">' . $row['address'] . '</a></td>
                    <td>' . $row['coordinates'] . '</td>
                    <td>' . $row['county'] . '</td>
                    <td>&euro;' . $price_formatted . '</td>
                    <td>' . $row['type'] . '</td>
                </tr>';
            }

        echo '</tbody>
    </table>';


    echo '<div class="pagination">';
        echo $pagination_links;
    echo '</div>';


    // Output the search results
    /*
    echo '<pre>';
    print_r($response);
    echo '</pre>';
    /**/
}

include '_footer.php';
