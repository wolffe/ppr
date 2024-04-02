<?php
setlocale(LC_MONETARY, "en_IE");

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

$counties = [];

foreach ($conn->query("SELECT DISTINCT county FROM properties") as $results) {
   $counties[] = $results['county'];
}

$counties = array_filter( array_unique( $counties ) );
sort( $counties );

include '_header.php';
?>

<form method="get" action="results.php">
    <div class="thin-ui-grid">
        <div class="thin-ui-col thin-ui-col-6">
            <p>
                <select name="county" class="large-text">
                    <option value="">Any county...</option>

                    <?php
                    foreach ( $counties as $county ) {
                        echo '<option value="' . $county . '">' . $county . '</option>';
                    }
                    ?>

                </select>
            </p>
        </div>
        <div class="thin-ui-col thin-ui-col-6">
            <p>
                <select name="type" class="large-text">
                    <option value="">Any property type...</option>
                    <option value="New">New Build</option>
                    <option value="Second Hand">Second Hand</option>
                </select>
            </p>
        </div>
        <div class="thin-ui-col thin-ui-col-6">
            <p>
                Date from <input type="text" name="date_from" value="2010-01-01" size="12"> to <input type="text" name="date_to" value="<?php echo date('Y-m-d'); ?>" size="12">
            </p>
        </div>
        <div class="thin-ui-col thin-ui-col-6">
            <p>
                Price from <input type="text" name="price_from" value="50000" size="12"> to <input type="text" name="price_to" value="10000000" size="12">
            </p>
        </div>
        <div class="thin-ui-col thin-ui-col-12">
            <p>
                Address is <input type="text" name="address" class="large-text">
            </p>
        </div>
        <div class="thin-ui-col thin-ui-col-12">
            <p>
                <input type="submit" name="search" value="Search" class="thin-ui-button thin-ui-button-primary thin-ui-button-regular">
            </p>
        </div>
    </div>
</form>

<?php
include '_footer.php';
