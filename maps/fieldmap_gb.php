<?php
require_once 'dbconn.inc.php';
$conn = dbConnect();

// Fetch paintball fields in Great Britain
$sql = "SELECT paintballFieldName, latitude, longitude, paintballFieldWebsite, fullAddress FROM fields WHERE country_code = 'GBR'";
$result = $conn->query($sql);

$fields = [];
$fieldCount = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Validate latitude and longitude (non-null and numeric)
        if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
            $fields[] = $row;
            $fieldCount++;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paintball Fields in Great Britain</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 600px;
            width: 100%;
        }
    </style>
</head>
<body>

<h1>Paintball Fields in Great Britain</h1>
<p>Total Fields Found: <strong><?php echo $fieldCount; ?></strong></p>
<div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Initialize Leaflet map
    var map = L.map('map').setView([54.5, -3.5], 6); // Center map on the UK

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // PHP to JavaScript data transfer
    var fields = <?php echo json_encode($fields, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    // Loop through the fields and add markers
    var bounds = L.latLngBounds();
    fields.forEach(function(field) {
        var websiteLink = field.paintballFieldWebsite 
            ? `<a href="${field.paintballFieldWebsite}" target="_blank">${field.paintballFieldName}</a>` 
            : field.paintballFieldName; // Fallback if no website is provided

        var marker = L.marker([field.latitude, field.longitude])
            .addTo(map)
            .bindPopup(websiteLink);
        bounds.extend(marker.getLatLng());
    });

    // Auto fit map to marker bounds
    if (bounds.isValid()) {
        map.fitBounds(bounds);
    }

</script>

</body>
</html>

<?php /* this file creates a leaflet map showing all fields in Great Britain. */ ?>
