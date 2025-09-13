<?php

function printAllEventsAsCards($conn) {
    // Get the selected state from the form submission (if any)
    $selectedState = isset($_GET['state']) ? $_GET['state'] : '';

    // Sanitize user input to prevent XSS attacks
    $selectedState = htmlspecialchars($selectedState, ENT_QUOTES, 'UTF-8');

    // SQL query to fetch events based on selected state and ensure isPublished is 1
    $sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, e.eventURL, e.eventSlug,
               f.paintballFieldName, f.paintballFieldWebsite, f.paintballFieldState, f.paintballFieldCity
        FROM events e
        JOIN fields f ON e.fieldID = f.fieldID
        WHERE e.eventEndDate >= ? AND e.isPublished = 1";

    // Add filtering condition for the selected state if a specific state is selected
    $params = [$currentDate = date('Y-m-d')];  // Current date parameter for filtering events
    $types = 's';  // Bind param types

    // Only filter by state if a specific state is selected (not "All States")
    if ($selectedState !== '') {
        $sql .= " AND f.paintballFieldState = ?";
        $params[] = $selectedState;
        $types .= "s";
    }

    // Add filter for Ares Alpha events if selected
    if (isset($_GET['ares_alpha'])) {
        $sql .= " AND e.ares_alpha = 1";
    }

    $sql .= " ORDER BY e.eventStartDate ASC";

    $stmt = $conn->prepare($sql);

    // Use bind_param to safely bind parameters
    $stmt->bind_param($types, ...$params);

    $stmt->execute();
    $result = $stmt->get_result();

    // Count the number of events found
    $numEvents = $result->num_rows;

    // Display the number of events found above the filter dropdown
    // echo "<p class='text-white text-center'>We found $numEvents event(s).</p>";

    // Get the states with upcoming events
    $statesWithEvents = getStatesWithUpcomingEvents($conn);

    // Bootstrap form for selecting state
    echo '<form method="GET" class="mb-4">';
    echo '  <div class="row">';

    // State filter dropdown that spans full width on small devices and 4 columns on medium and large devices
    echo '    <div class="col-sm-12 col-md-8 col-lg-6 col-xl-4 g-4 mx-auto">';
    echo '      <label class="form-label text-white" for="stateSelect">Filter by state</label>';
    echo '      <select class="form-select" id="stateSelect" name="state" aria-labelledby="stateSelect" onchange="this.form.submit()">';

    // Default "All States" option
    echo '        <option value=""' . ($selectedState === '' ? ' selected' : '') . '>All States</option>';

    // Call the function that prints the states with upcoming events
    printStatesAsDropdownOptions($statesWithEvents, $selectedState);

    echo '      </select>';
    echo '    </div>';
    
    echo '  </div>';
    echo '</form>';

    // Display event cards
    if ($numEvents > 0) {
        echo '<div class="col-sm-12 col-md-8 col-lg-6 col-xl-4 g-4 mx-auto">'; // Start of card grid

        while ($row = $result->fetch_assoc()) {
            // Escape output to prevent XSS attacks
            $eventName = htmlspecialchars($row['eventName'], ENT_QUOTES, 'UTF-8');
            $eventSlug = htmlspecialchars($row['eventSlug'], ENT_QUOTES, 'UTF-8');
            $eventStartDate = htmlspecialchars($row['eventStartDate'], ENT_QUOTES, 'UTF-8');
            $eventEndDate = htmlspecialchars($row['eventEndDate'], ENT_QUOTES, 'UTF-8');
            $eventURL = htmlspecialchars($row['eventURL'], ENT_QUOTES, 'UTF-8');
            $fieldName = htmlspecialchars($row['paintballFieldName'], ENT_QUOTES, 'UTF-8');
            $fieldWebsite = htmlspecialchars($row['paintballFieldWebsite'], ENT_QUOTES, 'UTF-8');
            $fieldCity = htmlspecialchars($row['paintballFieldCity'], ENT_QUOTES, 'UTF-8');
            $fieldState = htmlspecialchars($row['paintballFieldState'], ENT_QUOTES, 'UTF-8');

            // Format dates for human readability
            $startDate = new DateTime($eventStartDate);
            $endDate = new DateTime($eventEndDate);

            if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
                // If the start and end date are the same, show just one date
                $formattedDate = $startDate->format("F j, Y");
            } else {
                // If the start and end dates are different, format as "October 5-6, 2024"
                if ($startDate->format('F') === $endDate->format('F')) {
                    // Same month, show as "October 5-6, 2024"
                    $formattedDate = $startDate->format("F j") . '-' . $endDate->format('j') . ', ' . $startDate->format('Y');
                } else {
                    // Different months, show as "October 5 - November 6, 2024"
                    $formattedDate = $startDate->format("F j") . ' - ' . $endDate->format('F j, Y');
                }
            }

            // Bootstrap Card
            echo '<div class="col mb-3">';
            echo '  <div class="card h-100">';
            echo '    <div class="card-body">';
            echo '      <h5 class="card-title fw-bold text-primary mb-2"><a href="./event/' . $eventSlug . '.php" class="text-decoration-none">' . $eventName . '</a></h5>';


            // Display the formatted date with muted text
            echo '      <p class="card-text text-muted mb-1"><time datetime="' . $eventStartDate . '">' . $formattedDate . '</time></p>';

            // Print venue as "City, State" with medium weight
            echo '      <p class="card-text fw-medium mb-0"><a href="' . $fieldWebsite . '" target="_blank" class="text-decoration-none">' . $fieldName . '</a> in ' . $fieldCity . ', ' . $fieldState . '</p>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';

        }

        echo '</div>'; // End of card grid
    } else {
        echo "<p class='text-white text-center'>No upcoming events found.</p>";
    }

    $stmt->close();
}

// Function to get states that have upcoming events, ordered alphabetically
function getStatesWithUpcomingEvents($conn) {
    $sql = "SELECT DISTINCT f.paintballFieldState
            FROM events e
            JOIN fields f ON e.fieldID = f.fieldID
            WHERE e.eventEndDate >= ?
              AND f.country_code = 'USA'
            ORDER BY f.paintballFieldState ASC";  // Order states alphabetically
    $stmt = $conn->prepare($sql);

    $currentDate = date('Y-m-d');
    $stmt->bind_param('s', $currentDate);
    $stmt->execute();
    $result = $stmt->get_result();

    $statesWithEvents = [];
    while ($row = $result->fetch_assoc()) {
        $statesWithEvents[] = $row['paintballFieldState'];
    }

    $stmt->close();
    return $statesWithEvents;
}


// Function to print the states as dropdown options and ensure the selected state is retained
function printStatesAsDropdownOptions($statesWithEvents, $selectedState) {
    foreach ($statesWithEvents as $state) {
        // If the current state is the one selected, add the "selected" attribute
        $selected = ($state === $selectedState) ? 'selected' : '';
        echo "<option value=\"$state\" $selected>$state</option>\n";
    }
}
?>
