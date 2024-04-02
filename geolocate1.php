<script>
const addresses = [
    ["02/02/2023","25 Ashville, Kiltullagh, Co. Galway","Galway"],
    ["02/02/2023","26 MONTELADO WAY, FARMLEIGH, DUNMORE RD","Waterford"],
    ["02/02/2023","26 SEAFIELD DOWNS, CLONTARF, DUBLIN 3","Dublin"],
];

const fetchData = async (address) => {
    const response = await fetch(`https://geocode.maps.co/search?q=${encodeURIComponent(address)}`);
    const data = await response.json();
    if (data && data[0] && data[0].lat && data[0].lon) {
        return { lat: data[0].lat, lon: data[0].lon };
    }
    return null;
};

const fetchAllData = async () => {
    const results = [];
    for (const address of addresses) {
        const result = await fetchData(address[1]);
        if (result) {
            results.push({ date: address[0], address: address[1], county: address[2], ...result });
        }
        await new Promise((resolve) => setTimeout(resolve, 500)); // wait 500ms before next request
    }
    return results;
};

fetchAllData().then((results) => {
    console.log(results[0].lat);
    console.log(results[0].lon);
}).catch((error) => {
    console.error(error);
});
</script>
