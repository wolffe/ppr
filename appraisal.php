<?php
setlocale(LC_MONETARY, 'en_IE');

error_reporting(E_ALL);
ini_set('display_errors', 1);

include '_header.php';
?>


<style>
.ipv-ui-map {
    width: 100%;
    height: 540px;
    border-radius: 3px;
    box-shadow: 0 0 48px rgba(0, 0, 0, 0.15);
}
</style>

<div class="ipv-ui-form">
    <p><span class="ipv-ui-identity-dark">&#9632;</span><span class="ipv-ui-identity-light">&#9632;</span></p>

    <p class="has-small-font-size">Drag to move the map and scroll to zoom in or out. Click anywhere on the map below to select your property location.</p>

    <div id="ipv-ui-osm-map" class="ipv-ui-map" data-latitude="53.349797774002056" data-longitude="-6.260262966188748"></div>
    <input type="hidden" id="lat">
    <input type="hidden" id="lng">

    <form onsubmit="return false;">
        <div class="thin-ui-grid">
            <div class="thin-ui-col thin-ui-col-6">
                <p>
                    <label for="property-type">Property Type <span class="ipv-field-required">*</span></label><br>
                    <select id="property-type">
                        <option value="">Select property type...</option>
                        <option value="House">House</option>
                        <option value="Semi-Detached House">Semi-Detached House</option>
                        <option value="Detached House">Detached House</option>
                        <option value="Terraced House">Terraced House</option>
                        <option value="End of Terrace House">End of Terrace House</option>
                        <option value="Townhouse">Townhouse</option>
                        <option value="Bungalow">Bungalow</option>
                        <option value="Cottage">Cottage</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Duplex">Duplex</option>
                        <option value="Land">Land</option>
                        <option value="Site">Site</option>
                    </select>
                </p>
                <p>
                    <label for="bed">Beds <span class="ipv-field-required">*</span></label><br>
                    <input id="bed" name="bed" type="number" placeholder="0" min="1" max="50">
                </p>
                <p>
                    <label for="measurement">Size</label><br>
                    <input id="measurement" type="number" step="0.01" min="0" max="2000" name="SquareSizeInputField">
                    <select id="select">
                        <option id="meter" value="meter" name="SquareTypeInputField">sqm</option>
                        <option id="foot" value="foot" name="SquareTypeInputField">sqft</option>
                    </select>
                    <br><small>Optional</small>
                </p>
            </div>
            <div class="thin-ui-col thin-ui-col-6">
                <p>
                    <label>Are you</label><br>
                    <select id="ipv-reason">
                        <option selected>Ready to sell?</option>
                        <option>Looking to upsize/downsize?</option>
                        <option>Looking to release equity?</option>
                        <option>Probate family law?</option>
                        <option>Local Property Tax?</option>
                        <option>Family Transfer?</option>
                        <option>Capital Gains Tax?</option>
                        <option>HSE Fair Deal Scheme?</option>
                        <option>Planning to sell?</option>
                        <option>Planning to let?</option>
                        <option>Just curious?</option>
                    </select>
                    <br><small>Optional</small>
                </p>
                <p>
                    <label for="ipv_email">Email address <span class="ipv-field-required">*</span></label><br>
                    <input name="ipv_email" id="ipv-email" type="email" size="48" placeholder="Email address">
                    <br><small>We will send your valuation on this email address.</small>
                </p>

                <div class="ip-ui-child">
                    <button type="submit" id="ipv-ui-submit">Request Valuation</button>
                </div>
                <div class="ip-ui-child">
                    <div class="ipv-ui--hidden" id="ipv-ui-request"></div>
                </div>
            </div>
        </div>

        <p id="average" style="text-align:center;font-size:24px"></p>
        <p id="average-desc" style="text-align:center"></p>

        <p><span class="ipv-ui-identity-dark">&#9632;</span><span class="ipv-ui-identity-light">&#9632;</span></p>
    </form>
</div>


<script>
function median(numbers) {
    const sorted = numbers.slice().sort((a, b) => a - b);
    const middle = Math.floor(sorted.length / 2);

    if (sorted.length % 2 === 0) {
        return (sorted[middle - 1] + sorted[middle]) / 2;
    }

    return sorted[middle];
}


document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('ipv-ui-osm-map')) {
        // Initialize coordinates
        // Dublin is 53.349803617967474, -6.260251700878144
        let lat = document.getElementById('ipv-ui-osm-map').dataset.latitude,
            lon = document.getElementById('ipv-ui-osm-map').dataset.longitude;

        // Initialize map
        let osmMap = L.map('ipv-ui-osm-map').setView([lat, lon], 12);
        let markerGroup = L.layerGroup().addTo(osmMap);

        L.marker([lat, lon]).addTo(osmMap).bindPopup('');

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(osmMap);
        L.control.scale().addTo(osmMap);

        // Get marker position onclick
        osmMap.on('click', e => {
            osmMap.eachLayer(layer => {
                if (layer._latlng) {
                    osmMap.removeLayer(layer);
                }
            });

            console.log("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng);


            L.marker([e.latlng.lat, e.latlng.lng]).addTo(markerGroup);
            L.marker([e.latlng.lat, e.latlng.lng])
            .addTo(osmMap)
            .bindPopup("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng);

            document.getElementById('lat').value = e.latlng.lat;
            document.getElementById('lng').value = e.latlng.lng;
        });



        /**
         * 4Val: Valuation Submission
         */
        if (document.getElementById('ipv-ui-submit')) {
            document.getElementById('ipv-ui-submit').addEventListener('click', event => {
                event.preventDefault();

                document.getElementById('ipv-ui-request').innerHTML = 'Getting your valuation...';
                document.getElementById('ipv-ui-submit').disabled = true;

                let lat = document.getElementById('lat').value;
                let lng = document.getElementById('lng').value;
                let property_type = document.getElementById('property-type').value;
                let bed = document.getElementById('bed').value;
                let measurement = document.getElementById('measurement').value;
                let select = document.getElementById('select').value;
                let reason = document.getElementById('ipv-reason').value;
                let email = document.getElementById('ipv-email').value;

                if (email !== '' && lat !== '' && lng !== '' && property_type !== '' && bed !== '') {
                    let request = new XMLHttpRequest(),
                        requestString = '';

                    requestString += '&lat=' + lat;
                    requestString += '&lng=' + lng;
                    requestString += '&property_type=' + property_type;
                    requestString += '&bed=' + bed;
                    requestString += '&measurement=' + measurement;
                    requestString += '&select=' + select;
                    requestString += '&reason=' + reason;
                    requestString += '&email=' + email;

                    request.open('POST', '_appraisal.php', true);
                    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    request.onload = () => {
                        response = JSON.parse(request.response);
                        console.log(response.score);

                        if (response.success === 'false') {
                            document.getElementById('ipv-ui-request').innerHTML = 'Fail: No data.';
                        } else {
                            response = response.score;

                            /*
                            let topComparableAsArray = [];
                            let valuation = response;
                            let topComparables = valuation.topComparables;

                            topComparables.forEach(comparable => {
                                topComparableAsArray.push(comparable.price);
                            });

                            let finalValuation = median(topComparableAsArray);
                            /**/

                            // console.log('Valuation is ' + response);
                            let valMinus10 = response * (1 - 0.10);
                            let valPlus10 = response * (1 + 0.10);

                            valMinus10 = new Intl.NumberFormat('en-IE', { style: 'currency', currency: 'EUR' }).format(valMinus10);
                            valPlus10 = new Intl.NumberFormat('en-IE', { style: 'currency', currency: 'EUR' }).format(valPlus10);

                            document.getElementById('average').innerHTML = `<b>${valMinus10}</b> &mdash; <b>${valPlus10}</b>`;
                            document.getElementById('average-desc').innerHTML = `Based on recent property sales in your area, your property is valued approximately between <b>${valMinus10}</b> and <b>${valPlus10}</b>. However, various factors will affect the value of your property, and an agent will be in contact to see if it would be appropriate to arrange a professional valuation or sale appraisal.`;

                            document.getElementById('ipv-ui-request').innerHTML = 'Success: Valuation received!';
                            document.getElementById('ipv-ui-submit').disabled = false;
                        }
                    };
                    request.send('action=wppd_pro_action_val_form' + requestString);
                } else {
                    document.getElementById('ipv-ui-request').innerHTML = 'Error: All fields are required!';
                    document.getElementById('ipv-ui-submit').disabled = false;
                }
                //

                /*


                if (email !== '') {
                    const requestOptions = {
                        method: 'GET',
                        redirect: 'follow'
                    };
                    let topComparableAsArray = [];

                    fetch(`https://ippiapi.4property.com/instantpropertyvaluation?${requestString}`, requestOptions)
                        .then(response => response.text())
                        .then(result => {
                            //console.log(JSON.parse(result));
                            let valuation = JSON.parse(result);
                            let topComparables = valuation.topComparables;
                            //console.log(topComparables);

                            topComparables.forEach(comparable => {
                                topComparableAsArray.push(comparable.price);
                                //console.log(comparable.price);
                            });

                            //console.log('Valuation is ' + median(topComparableAsArray));
                            document.getElementById('average').innerHTML = 'Valuation is ' + median(topComparableAsArray);

                            document.getElementById('ipv-ui-request').innerHTML = 'Email sent successfully!';
                            document.getElementById('ipv-ui-submit').disabled = false;



                            // Create WordPress Valuation CPT
                            requestStringWP += '&amount=' + median(topComparableAsArray);

                            request.open('POST', wp4pmAjaxVar.ajaxurl, true);
                            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                            request.onload = () => {
                                response = JSON.parse(request.response);

                                if (response.success === 'true') {
                                    document.getElementById('ipv-ui-request').innerHTML = 'Email sent successfully!';
                                    document.getElementById('ipv-ui-submit').disabled = false;
                                } else {
                                    document.getElementById('ipv-ui-request').innerHTML = 'An error has occured!';
                                    document.getElementById('ipv-ui-submit').disabled = false;
                                }
                            };
                            request.send('action=wppd_pro_action_val_form' + requestStringWP);
                        })
                        .catch(error => console.log('error', error));
                } else {
                    document.getElementById('ipv-ui-request').innerHTML = 'An error has occured!';
                    document.getElementById('ipv-ui-submit').disabled = false;
                }
                /**/
            });
        }
    }
});
</script>

<?php
include '_footer.php';
