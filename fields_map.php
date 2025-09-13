<?php 
session_start();
require "shared.php";
require_once "dbconn.inc.php";
$conn = dbConnect();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://paintballevents.net/fields_map.php">
    <meta name="keywords" content="Paintball Fields, Paintball Fields Near Me, Where to Play Paintball, Paintball Locations, Paintball Fields Map, Paintball Field Finder, Local Paintball Fields, Paintball Field Directory, Paintball Field Locator, Paintball Field Search, Paintball Field Locations, Paintball Field Map, Paintball Field Finder Near Me, Paintball Field Directory Near Me, Paintball Field Locator Near Me">
    <meta name="description" content="Find paintball fields near you with our interactive map. Search for local paintball fields, browse by location, and discover where to play paintball in your area. Updated directory of paintball fields across the USA, Canada, and UK." />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <title>Paintball Fields Map | Find Local Paintball Fields Near You | PaintballEvents.net</title>
    <!-- SVG Favicon -->
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
    <!-- PNG Fallback Favicons with Different Sizes -->
    <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />


    <style>
        /* Add IBM Plex Sans font */
        @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500&display=swap');

        /* Loading spinner styles */
        #map-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(22, 22, 22, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            transition: opacity 0.3s ease;
        }

        /*Hide desktop view on screens less than 768px wide*/
        @media only screen and (max-width: 768px) {
            #desktop-view {
                display: none;
            }
            #mobile-fields {
                display: block;
                height: auto; /* Allow content to determine height */
                overflow-y: auto; /* Enable vertical scrolling */
            }
            body, html {
                height: auto; /* Allow body to expand with content */
                min-height: 100%; /* Ensure minimum height is full viewport */
            }
        }
        
        /*Hide mobile view on screens more than 768px wide*/
        @media only screen and (min-width: 769px) {
            #mobile-fields {
                display: none;
            }
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #0f62fe;
            border-top: 3px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 16px;
        }

        .loading-text {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 16px;
            letter-spacing: 0.16px;
            font-weight: 500;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Leaflet popup styling */
        .leaflet-popup-content-wrapper {
            background-color: rgb(45, 45, 45) !important;
            color: #e0e0e0 !important;
            border-radius: 0 !important;
        }

        .leaflet-popup-content {
            margin: 12px !important;
        }

        .leaflet-popup-content a {
            color: #b8d3ff !important;
            text-decoration: underline !important;
            text-underline-offset: 2px !important;
            text-decoration-thickness: 1px !important;
            text-decoration-color: rgba(184, 211, 255, 0.5) !important;
            font-weight: 500 !important;
            font-size: 16px !important;
            letter-spacing: 0.16px !important;
        }

        .leaflet-popup-content a:hover {
            color: #d4e5ff !important;
            text-decoration-color: #d4e5ff !important;
            background-color: rgba(184, 211, 255, 0.08) !important;
        }

        .leaflet-popup-content a:active {
            color: #97c1ff !important;
            text-decoration-thickness: 1.5px !important;
            background-color: rgba(184, 211, 255, 0.15) !important;
        }

        .leaflet-popup-tip {
            background-color: rgb(45, 45, 45) !important;
        }

        /* Dark mode styling for field list items */
        #field-list-container .list-group-item {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #e0e0e0;
            transition: background-color 0.2s ease;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.16px;
        }

        #field-list-container .list-group-item:hover {
            background-color: #363636;
        }

        #field-list-container .list-group-item a {
            color: #b8d3ff;
            text-decoration: underline;
            text-underline-offset: 2px;
            text-decoration-thickness: 1px;
            text-decoration-color: rgba(184, 211, 255, 0.5);
            font-weight: 500;
            font-size: 20px;
            letter-spacing: 0.16px;
            transition: color 0.15s ease, text-decoration-color 0.15s ease, text-decoration-thickness 0.15s ease;
        }

        #field-list-container .list-group-item a:hover {
            color: #d4e5ff;
            text-decoration-thickness: 1.5px;
            text-decoration-color: #d4e5ff;
            background-color: rgba(184, 211, 255, 0.08);
            padding: 1px 2px;
            border-radius: 2px;
        }

        #field-list-container .list-group-item a:active {
            color: #97c1ff;
            text-decoration-thickness: 1.5px;
            background-color: rgba(184, 211, 255, 0.15);
        }

        #field-list-container .list-group-item .fs-6 {
            color: #e0e0e0;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.16px;
        }

        /* Dark mode styling for field list container */
        #field-list {
            background-color: #161616;
            border-right: 1px solid #404040;
        }

        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Allow vertical scrolling */
        }

        #container {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: calc(100% - 60px);
            overflow: hidden;
        }

        #field-list {
            width: 30%;
            overflow-y: auto;
            padding: 1rem;
            max-height: 100vh;
            border-radius: 0;
        }

        #field-list .list-group-item {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #e0e0e0;
            transition: background-color 0.2s ease;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.16px;
            border-radius: 0;
        }

        #map {
            flex: 1;
            height: 100vh;
            overflow: hidden; /* Prevent scrolling on the map */
        }

        /* Add styles for map attribution */
        .leaflet-control-attribution {
            bottom: 70px !important;
        }

        #navbar {
            background-color: #343a40;
            color: #ffffff;
            padding: 1rem;
            text-align: center;
        }

        /* IBM Carbon Button Styling */
        .carbon-button {
            background-color: #0f62fe;
            border: none;
            border-radius: 0;
            color: #ffffff;
            cursor: pointer;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            height: 32px;
            letter-spacing: 0.16px;
            line-height: 1.28572;
            padding: 0 1rem;
            position: relative;
            text-align: center;
            text-decoration: none;
            transition: background-color 70ms cubic-bezier(0, 0, 1, 1);
            vertical-align: top;
            min-width: 120px;
            display: inline-block;
        }

        .carbon-button:hover {
            background-color: #0353e9;
        }

        .carbon-button:active {
            background-color: #002d9c;
        }

        .carbon-button:focus {
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f62fe;
            outline: none;
        }

        /* Form styling */
        #field-list .form-select {
            background-color: #262626;
            border-color: #404040;
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #field-list .form-select:focus {
            background-color: #262626;
            border-color: #0f62fe;
            color: #e0e0e0;
            box-shadow: 0 0 0 2px #0f62fe;
        }

        #field-list .form-select option {
            background-color: #262626;
            color: #e0e0e0;
        }

        #field-list label {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #field-list h5 {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 0.16px;
        }

        /* Dark mode styling for mobile container */
        #mobile-fields {
            background-color: #161616;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.16px;
            min-height: 100vh;
            height: auto; /* Allow content to determine height */
            overflow-y: auto; /* Enable vertical scrolling */
        }

        #mobile-fields #list-container {
            background-color: #161616;
        }

        #mobile-fields h5 {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 0.16px;
        }

        #mobile-fields .form-select {
            background-color: #262626;
            border-color: #404040;
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #mobile-fields .form-select:focus {
            background-color: #262626;
            border-color: #0f62fe;
            color: #e0e0e0;
            box-shadow: 0 0 0 2px #0f62fe;
        }

        #mobile-fields .form-select option {
            background-color: #262626;
            color: #e0e0e0;
        }

        #mobile-fields label {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #mobile-fields .btn-link {
            color: #b8d3ff;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #mobile-fields .btn-link:hover {
            color: #d4e5ff;
        }

        #mobile-fields .btn-link:active {
            color: #97c1ff;
        }

        #mobile-fields .carbon-button {
            margin-bottom: 1.5rem;
        }

        #mobile-fields .list-group-item {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #e0e0e0;
            transition: background-color 0.2s ease;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.16px;
        }

        #mobile-fields .list-group-item:hover {
            background-color: #363636;
        }

        #mobile-fields .list-group-item a {
            color: #b8d3ff;
            text-decoration: underline;
            text-underline-offset: 2px;
            text-decoration-thickness: 1px;
            text-decoration-color: rgba(184, 211, 255, 0.5);
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 0.16px;
            transition: color 0.15s ease, text-decoration-color 0.15s ease, text-decoration-thickness 0.15s ease;
        }

        #mobile-fields .list-group-item a:hover {
            color: #d4e5ff;
            text-decoration-thickness: 1.5px;
            text-decoration-color: #d4e5ff;
            background-color: rgba(184, 211, 255, 0.08);
            padding: 1px 2px;
            border-radius: 2px;
        }

        #mobile-fields .list-group-item a:active {
            color: #97c1ff;
            text-decoration-thickness: 1.5px;
            background-color: rgba(184, 211, 255, 0.15);
        }

        #mobile-fields .list-group-item .fs-6 {
            color: #e0e0e0;
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.16px;
        }

        /* Dark mode map styling */
        .leaflet-container {
            background-color: #161616 !important;
        }
        
        .leaflet-tile {
            filter: brightness(0.6) invert(1) contrast(3) hue-rotate(200deg) saturate(0.3) brightness(0.7);
        }
        
        .leaflet-control-zoom a {
            background-color: #2d2d2d !important;
            color: #e0e0e0 !important;
            border-color: #404040 !important;
        }
        
        .leaflet-control-zoom a:hover {
            background-color: #363636 !important;
        }
    </style>
</head>

<body>
    <?php echo $nav; ?>
    
    <!-- Desktop view container with map and filters -->
    <div id="desktop-view">
        <div id="container">
            <div id="field-list" class="list-group">
                <h1 class="mt-2" style="color: #e0e0e0; font-family: 'IBM Plex Sans', sans-serif; font-weight: 500; font-size: 24px; letter-spacing: 0.16px;">Find a Paintball Field</h1>
                <?php
                // Build the same filter logic for counting as we use for the main query
                $countSql = "SELECT COUNT(*) as total FROM fields WHERE isPublished = 1";
                $countParams = [];
                $countTypes = '';

                // Add country filter if selected
                if (isset($_GET['country']) && $_GET['country'] !== '') {
                    $countSql .= " AND country_code = ?";
                    $countParams[] = $_GET['country'];
                    $countTypes .= 's';
                }

                // Add state filter if selected
                if (isset($_GET['state']) && $_GET['state'] !== '') {
                    $countSql .= " AND paintballFieldState = ?";
                    $countParams[] = $_GET['state'];
                    $countTypes .= 's';
                }

                $countStmt = $conn->prepare($countSql);
                
                if (!empty($countParams)) {
                    $countStmt->bind_param($countTypes, ...$countParams);
                }
                
                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $totalFields = $countResult->fetch_assoc()['total'];
                $countStmt->close();

                // Display the count message
                echo '<div class="mt-3 mb-3 text-white" style="font-family: \'IBM Plex Sans\', sans-serif; font-size: 14px; letter-spacing: 0.16px;">';
                echo $totalFields . ' results';
                echo '</div>';
                ?>

                <!-- Filters section -->
                <form method="GET" action="" class="my-2">
                    <h5>🔍 Filters</h5>
                    <!-- Country picker -->
                    <select class="form-select mb-2" name="country" id="desktopCountrySelect" aria-labelledby="countrySelect">
                        <option value="" <?php if (!isset($_GET['country']) || $_GET['country'] === '') echo 'selected'; ?>>🌍 Anywhere</option>
                        <option value="USA" <?php if (isset($_GET['country']) && $_GET['country'] === 'USA') echo 'selected'; ?>>🇺🇸 United States</option>
                        <option value="CAN" <?php if (isset($_GET['country']) && $_GET['country'] === 'CAN') echo 'selected'; ?>>🇨🇦 Canada</option>
                        <option value="GBR" <?php if (isset($_GET['country']) && $_GET['country'] === 'GBR') echo 'selected'; ?>>🇬🇧 United Kingdom</option>
                    </select>

                    <!-- State picker -->
                    <select class="form-select mb-2" name="state" aria-labelledby="stateSelect" id="desktopStateSelect" style="display:none;">
                        <option value="" <?php if (!isset($_GET['state']) || $_GET['state'] === '') echo 'selected'; ?>>Any state</option>
                        <?php
                        // Get states with fields
                        $statesSql = "SELECT DISTINCT paintballFieldState FROM fields WHERE isPublished = 1 AND country_code = 'USA' ORDER BY paintballFieldState ASC";
                        $statesResult = $conn->query($statesSql);
                        while ($state = $statesResult->fetch_assoc()) {
                            $selected = (isset($_GET['state']) && $_GET['state'] === $state['paintballFieldState']) ? 'selected' : '';
                            echo "<option value=\"{$state['paintballFieldState']}\" $selected>{$state['paintballFieldState']}</option>";
                        }
                        ?>
                    </select>

                    <button type="submit" class="carbon-button mt-2">Apply filters</button>
                    <?php if (isset($_GET['country']) || isset($_GET['state'])): ?>
                        <a href="fields_map.php" class="btn btn-link mt-2 text-decoration-none">Reset filters</a>
                    <?php endif; ?>
                </form>

                <!-- Field list container -->
                <ul id="field-list-container" class="list-group mt-3"></ul>
            </div>

            <!-- Map container -->
            <div id="map">
                <div id="map-loading">
                    <div class="spinner"></div>
                    <div class="loading-text">Loading paintball fields...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile view container with field list and filters -->
    <div id="mobile-fields">
        <div id="list-container" class="px-2">
            <h1 class="mt-2" style="color: #e0e0e0; font-family: 'IBM Plex Sans', sans-serif; font-weight: 500; font-size: 24px; letter-spacing: 0.16px;">Find a Paintball Field</h1>
            <?php
            // Display the count message for mobile
            echo '<div class="mt-3 mb-3 text-white" style="font-family: \'IBM Plex Sans\', sans-serif; font-size: 14px; letter-spacing: 0.16px;">';
            echo $totalFields . ' results';
            echo '</div>';
            ?>

            <!-- Filters section for mobile view -->
            <form method="GET" action="">
                <h5>🔍 Filters</h5>
                <!-- Country picker -->
                <select class="form-select mb-2" name="country" id="mobileCountrySelect" aria-labelledby="countrySelect">
                    <option value="" <?php if (!isset($_GET['country']) || $_GET['country'] === '') echo 'selected'; ?>>🌍 Anywhere</option>
                    <option value="USA" <?php if (isset($_GET['country']) && $_GET['country'] === 'USA') echo 'selected'; ?>>🇺🇸 United States</option>
                    <option value="CAN" <?php if (isset($_GET['country']) && $_GET['country'] === 'CAN') echo 'selected'; ?>>🇨🇦 Canada</option>
                    <option value="GBR" <?php if (isset($_GET['country']) && $_GET['country'] === 'GBR') echo 'selected'; ?>>🇬🇧 United Kingdom</option>
                </select>

                <!-- State picker -->
                <select class="form-select mb-2" name="state" aria-labelledby="stateSelect" id="mobileStateSelect" style="display:none;">
                    <option value="" <?php if (!isset($_GET['state']) || $_GET['state'] === '') echo 'selected'; ?>>Any state</option>
                    <?php
                    // Reset the result pointer
                    $statesResult->data_seek(0);
                    while ($state = $statesResult->fetch_assoc()) {
                        $selected = (isset($_GET['state']) && $_GET['state'] === $state['paintballFieldState']) ? 'selected' : '';
                        echo "<option value=\"{$state['paintballFieldState']}\" $selected>{$state['paintballFieldState']}</option>";
                    }
                    ?>
                </select>

                <button type="submit" class="carbon-button mt-2">Apply filters</button>
                <?php if (isset($_GET['country']) || isset($_GET['state'])): ?>
                    <a href="fields_map.php" class="btn btn-link mt-2 text-decoration-none">Reset filters</a>
                <?php endif; ?>
            </form>

            <!-- Mobile field list container -->
            <ul id="mobile-field-list-container" class="list-group mt-3"></ul>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map
        var map = L.map('map', {
            center: [39.8283, -98.5795],  // Center of USA
            zoom: 4,  // Zoom level to show most of USA
            zoomControl: true,
            worldCopyJump: false,  // Prevent endless horizontal scrolling
            maxBounds: [[-85, -180], [85, 180]],  // Restrict map bounds to prevent endless scrolling
            maxBoundsViscosity: 1.0  // Make bounds completely solid
        });

        // Function to center map based on country selection
        function centerMapOnCountry(country) {
            if (country === 'GBR') {
                map.setView([54.5, -2], 5); // Center on Britain with appropriate zoom
            } else if (country === 'USA') {
                map.setView([39.8283, -98.5795], 4); // Center on USA
            } else if (country === 'CAN') {
                map.setView([52.1304, -95.3468], 4); // Center on Canada
            } else {
                map.setView([39.8283, -98.5795], 4); // Default to USA view
            }
        }

        // Hide loading spinner when map is ready
        map.whenReady(function() {
            document.getElementById('map-loading').style.opacity = '0';
            setTimeout(function() {
                document.getElementById('map-loading').style.display = 'none';
            }, 300);
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Create a single marker icon instance to be reused
        var markerIcon = L.divIcon({
            className: 'custom-marker',
            html: '<div class="marker-circle"></div>',
            iconSize: [8, 8],
            iconAnchor: [4, 4]
        });

        // Array to store all markers
        var markers = [];

        <?php
        // Query to get all published fields
        $sql = "SELECT * FROM fields WHERE isPublished = 1";
        $params = [];
        $types = '';

        // Add country filter if selected
        if (isset($_GET['country']) && $_GET['country'] !== '') {
            $sql .= " AND country_code = ?";
            $params[] = $_GET['country'];
            $types .= 's';
        }

        // Add state filter if selected
        if (isset($_GET['state']) && $_GET['state'] !== '') {
            $sql .= " AND paintballFieldState = ?";
            $params[] = $_GET['state'];
            $types .= 's';
        }

        $sql .= " ORDER BY paintballFieldName ASC";
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();

        // Add markers for each field
        $index = 0;
        $result->data_seek(0); // Reset the result pointer
        while ($row = $result->fetch_assoc()) {
            $fieldName = htmlspecialchars($row['paintballFieldName'], ENT_QUOTES, 'UTF-8');
            $fieldCity = htmlspecialchars($row['paintballFieldCity'], ENT_QUOTES, 'UTF-8');
            $fieldState = htmlspecialchars($row['paintballFieldState'], ENT_QUOTES, 'UTF-8');
            $fieldWebsite = htmlspecialchars($row['paintballFieldWebsite'], ENT_QUOTES, 'UTF-8');
            $googlemapShortLink = htmlspecialchars($row['googlemapShortLink'], ENT_QUOTES, 'UTF-8');
            $latitude = $row['latitude'];
            $longitude = $row['longitude'];
            $fieldID = $row['fieldID'];
            $countryCode = htmlspecialchars($row['country_code'], ENT_QUOTES, 'UTF-8');

            // Build popup content with country information
            $popupContent = "<div class='py-2' data-field-id='$fieldID'>";
            $popupContent .= "<strong><a href='$fieldWebsite' target='_blank'>$fieldName</a></strong>";
            $popupContent .= "<div style='margin: 8px 0;'>";
            if ($countryCode === 'GBR') {
                $popupContent .= "<span style='font-size:1rem'>🇬🇧 United Kingdom</span>";
            } else if ($countryCode === 'CAN') {
                $popupContent .= "<span style='font-size:1rem'>🇨🇦 $fieldCity, $fieldState, Canada</span>";
            } else if ($countryCode === 'USA') {
                $popupContent .= "<span style='font-size:1rem'>🇺🇸 $fieldCity, $fieldState</span>";
            } else if ($countryCode === 'IRL') {
                $popupContent .= "<span style='font-size:1rem'>🇮🇪 $fieldCity, $fieldState, Ireland</span>";
            } else {
                $popupContent .= "<span style='font-size:1rem'>$fieldCity, $fieldState, $countryCode</span>";
            }
            $popupContent .= "</div>";

            $popupContent .= "</div>";

            // Add to desktop list
            echo "document.getElementById('field-list-container').innerHTML += `
                <li class='list-group-item py-4' data-marker-index='$index'>
                    <strong><a href='$fieldWebsite' target='_blank'>$fieldName</a></strong>
                    <br><span class='fs-6'>";
            if ($countryCode === 'GBR') {
                echo "🇬🇧 United Kingdom";
            } else if ($countryCode === 'CAN') {
                echo "🇨🇦 $fieldCity, $fieldState, Canada";
            } else if ($countryCode === 'USA') {
                echo "🇺🇸 $fieldCity, $fieldState";
            } else if ($countryCode === 'IRL') {
                echo "🇮🇪 $fieldCity, $fieldState, Ireland";
            } else {
                echo "$fieldCity, $fieldState, $countryCode";
            }
            echo "</span>
                </li>`;";

            // Add to mobile list
            echo "document.getElementById('mobile-field-list-container').innerHTML += `
                <li class='list-group-item py-4'>
                    <strong><a href='$fieldWebsite' target='_blank'>$fieldName</a></strong>
                    <br><span class='fs-6'>";
            if ($countryCode === 'GBR') {
                echo "🇬🇧 United Kingdom";
            } else if ($countryCode === 'CAN') {
                echo "🇨🇦 $fieldCity, $fieldState, Canada";
            } else if ($countryCode === 'USA') {
                echo "🇺🇸 $fieldCity, $fieldState";
            } else if ($countryCode === 'IRL') {
                echo "🇮🇪 $fieldCity, $fieldState, Ireland";
            } else {
                echo "$fieldCity, $fieldState, $countryCode";
            }
            echo "</span>
                </li>`;";

            // Add marker to the map and store it in the markers array
            echo "var marker = L.marker([$latitude, $longitude], {icon: markerIcon})
                .bindPopup(`$popupContent`)
                .addTo(map);
            markers[$index] = marker;";
            
            $index++;
        }
        ?>

        // Add click handler for list items
        document.getElementById('field-list').addEventListener('click', function(event) {
            var target = event.target.closest('.list-group-item');
            var isLink = event.target.closest('a');

            if (isLink) {
                return;
            }

            if (target) {
                var index = target.getAttribute('data-marker-index');
                if (index !== null && markers[index]) {
                    markers[index].openPopup();
                    map.panTo(markers[index].getLatLng());
                }
            }
        });

        // Center map based on applied filters (only when filters are actually applied via URL parameters)
        <?php if (isset($_GET['country']) && $_GET['country'] !== ''): ?>
            centerMapOnCountry('<?php echo htmlspecialchars($_GET['country'], ENT_QUOTES, 'UTF-8'); ?>');
        <?php endif; ?>
    </script>

    <style>
        .marker-circle {
            width: 12px;
            height: 12px;
            background-color: #15DC15;  /* Specific lime green color */
            border: 1px solid #0A840A;  /* Darker green border */
            border-radius: 50%;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3);
        }
        .marker-circle:hover {
            background-color: #1AFF1A;  /* Slightly brighter version of #15DC15 */
            transform: scale(1.2);
            transition: all 0.2s ease;
        }
    </style>

    <?php echo $bootstrap_javascript_includes; ?>

    <!-- Hiding US state dropdown if country_code !='USA' -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const desktopCountrySelect = document.getElementById('desktopCountrySelect');
            const mobileCountrySelect = document.getElementById('mobileCountrySelect');

            // Show/Hide state dropdown based on country selection
            function toggleStateDropdown(selectId, countrySelect) {
                const stateSelect = document.getElementById(selectId);
                if (stateSelect) {
                    if (countrySelect.value === 'USA') {
                        stateSelect.style.display = '';
                    } else {
                        stateSelect.style.display = 'none';
                        stateSelect.value = '';  // Clear state selection when hidden
                    }
                }
            }

            // Initial toggle for both desktop and mobile
            toggleStateDropdown('desktopStateSelect', desktopCountrySelect);
            toggleStateDropdown('mobileStateSelect', mobileCountrySelect);

            // Add listeners for both desktop and mobile country select dropdowns
            desktopCountrySelect.addEventListener('change', function() {
                toggleStateDropdown('desktopStateSelect', desktopCountrySelect);
            });

            mobileCountrySelect.addEventListener('change', function() {
                toggleStateDropdown('mobileStateSelect', mobileCountrySelect);
            });
        });
    </script>
</body>
</html> 