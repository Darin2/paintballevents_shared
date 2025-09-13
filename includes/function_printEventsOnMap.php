<?php

function printEventsOnMap($conn) {
    // Get the selected state from the form submission (if any)
    $selectedState = isset($_GET['state']) ? $_GET['state'] : '';

    // Sanitize user input to prevent XSS attacks
    $selectedState = htmlspecialchars($selectedState, ENT_QUOTES, 'UTF-8');

    // SQL query to fetch events based on selected state and ensure isPublished is 1
    $sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, e.eventSlug,
               f.paintballFieldName, f.paintballFieldCity, f.paintballFieldState,
               f.latitude, f.longitude
        FROM events e
        JOIN fields f ON e.fieldID = f.fieldID
        WHERE e.eventEndDate >= ? AND e.isPublished = 1";

    $params = [$currentDate = date('Y-m-d')];  // Current date parameter for filtering events
    $types = 's';  // Bind param types

    // Only filter by state if a specific state is selected
    if ($selectedState !== '') {
        $sql .= " AND f.paintballFieldState = ?";
        $params[] = $selectedState;
        $types .= "s";
    }

    $sql .= " ORDER BY e.eventStartDate ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count the number of events found
    $numEvents = $result->num_rows;

    // Prepare the JavaScript for the Leaflet map and markers
    echo '<script>
    var map = L.map("map", {
        center: [37.8283, -107.5795],  // Center map on the US
        zoom: 4,                      // Set the zoom level
        zoomControl: false             // Disable the zoom control
    });

    // Add OpenStreetMap tile layer
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 18,
            attribution: "Map data &copy; OpenStreetMap contributors"
    }).addTo(map);

    // Move zoom control to bottom right corner
    L.control.zoom({
        position: "bottomright"
    }).addTo(map);

    // Add the sidebar to the map
    var sidebar = L.control.sidebar("sidebar").addTo(map);

    // Open the sidebar on page load
    sidebar.open("home"); // Replace home with the appropriate tab ID
    
    var markers = []; // Array to store markers

    ';

    if ($numEvents > 0) {
        $index = 0;
        // Loop over the events and generate markers and sidebar items as a tidy list
        while ($row = $result->fetch_assoc()) {
            // Escape output to prevent XSS attacks
            $eventName = htmlspecialchars($row['eventName'], ENT_QUOTES, 'UTF-8');
            $eventSlug = htmlspecialchars($row['eventSlug'], ENT_QUOTES, 'UTF-8');
            $eventStartDate = htmlspecialchars($row['eventStartDate'], ENT_QUOTES, 'UTF-8');
            $eventEndDate = htmlspecialchars($row['eventEndDate'], ENT_QUOTES, 'UTF-8');
            $fieldName = htmlspecialchars($row['paintballFieldName'], ENT_QUOTES, 'UTF-8');
            $fieldCity = htmlspecialchars($row['paintballFieldCity'], ENT_QUOTES, 'UTF-8');
            $fieldState = htmlspecialchars($row['paintballFieldState'], ENT_QUOTES, 'UTF-8');
            $latitude = htmlspecialchars($row['latitude'], ENT_QUOTES, 'UTF-8');
            $longitude = htmlspecialchars($row['longitude'], ENT_QUOTES, 'UTF-8');

            // Format dates for human readability
            $startDate = new DateTime($eventStartDate);
            $endDate = new DateTime($eventEndDate);
            $formattedDate = ($startDate->format('Y-m-d') === $endDate->format('Y-m-d'))
                ? $startDate->format("F j, Y")
                : $startDate->format("F j") . '-' . $endDate->format('j') . ', ' . $startDate->format('Y');

            // Add the marker to the map and store it in the markers array
            echo "var marker = L.marker([$latitude, $longitude])
                .addTo(map)
                .bindPopup('<strong><a href=\"https://darin.tech/paintballevents_testing/event/$eventSlug.php\">$eventName</a></strong><br>$formattedDate<br>$fieldCity, $fieldState');
            markers.push(marker);";

            // Generate the event list item in the sidebar
            echo "document.getElementById('event-list').innerHTML += `
                <li class='list-group-item' data-marker-index='$index'>
                    <strong class='h5 fw-bold'><a href='./event/$eventSlug.php' target='_blank' class='event-link'>$eventName</a></strong>
                    <br><span class='fs-6'>$formattedDate</span>
                    <br><span class='fs-6'>$fieldCity, $fieldState</span>
                </li>`;";

            $index++;
        }

        // Use event delegation to listen for clicks on the list-group-items
        echo "document.getElementById('event-list').addEventListener('click', function(event) {
            var target = event.target.closest('.list-group-item');
            var isLink = event.target.closest('.event-link');

            // If the click is on an anchor link (.event-link), we let the link work normally
            if (isLink) {
                return;  // Let the browser follow the link, no need to interact with the map
            }

            // If the click is not on a link but on a list item, we open the corresponding marker popup
            if (target) {
                var index = target.getAttribute('data-marker-index');
                if (index !== null && markers[index]) {
                    markers[index].openPopup();  // Open the marker popup without zooming in
                    map.panTo(markers[index].getLatLng());  // Center map on the marker
                }
            }
        });";
        
    } else {
        echo 'L.marker([39.8283, -98.5795]).addTo(map).bindPopup("No upcoming events found.").openPopup();';
    }

    echo '</script>';

    $stmt->close();
}

?>
