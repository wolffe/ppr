$(document).ready(function() {
    $('#addressForm').submit(function(event) {
        event.preventDefault();
        var address = $('#addressInput').val();
        getAddressRedirectedURL(address);
    });
});

function getAddressRedirectedURL(address) {
    setTimeout(function() {
        $.ajax({
            url: 'get_coordinates.php',
            type: 'POST',
            data: { address: address },
            success: function(data) {
                // Parse the redirected URL
                var redirectedURL = data.trim();
                // Wait for the page to fully load before extracting coordinates
                waitForPageLoad(redirectedURL);
            },
            error: function() {
                $('#coordinatesOutput').html('Failed to fetch coordinates');
            }
        });
    }, 10000); // 3 seconds delay
}


function waitForPageLoad(redirectedURL) {
    // You can implement the logic for waiting and extracting coordinates here
    console.log("Redirected URL:", redirectedURL);
    // Example: You can now proceed with extracting coordinates from the redirected URL
}
