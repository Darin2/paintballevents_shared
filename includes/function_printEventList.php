<?php

function printEventList($conn) {
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

    $sql .= " ORDER BY e.eventStartDate ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    // Count the number of events found
    $numEvents = $result->num_rows;

    // Get the states with upcoming events
    $statesWithEvents = getStatesWithUpcomingEvents($conn);

    // Bootstrap form for selecting state
    echo '<form method="GET" class="mb-4">';
    echo '  <div class="row">';
    echo '    <div class="col-sm-12 col-md-8 col-lg-6 col-xl-4 g-4 mx-auto">';
    echo '      <label class="form-label" for="stateSelect">Filter by state</label>';
    echo '      <select class="form-select" id="stateSelect" name="state" aria-labelledby="stateSelect" onchange="this.form.submit()">';
    echo '        <option value=""' . ($selectedState === '' ? ' selected' : '') . '>All States</option>';
    printStatesAsDropdownOptions($statesWithEvents, $selectedState);
    echo '      </select>';
    echo '    </div>';
    echo '  </div>';
    echo '</form>';

    // Display event list
    if ($numEvents > 0) {
        echo '<ul class="list-group mb-4">'; // Start of the list

        while ($row = $result->fetch_assoc()) {
            // Escape output to prevent XSS attacks
            $eventName = htmlspecialchars($row['eventName'], ENT_QUOTES, 'UTF-8');
            $eventSlug = htmlspecialchars($row['eventSlug'], ENT_QUOTES, 'UTF-8');
            $eventStartDate = htmlspecialchars($row['eventStartDate'], ENT_QUOTES, 'UTF-8');
            $eventEndDate = htmlspecialchars($row['eventEndDate'], ENT_QUOTES, 'UTF-8');
            $fieldName = htmlspecialchars($row['paintballFieldName'], ENT_QUOTES, 'UTF-8');
            $fieldCity = htmlspecialchars($row['paintballFieldCity'], ENT_QUOTES, 'UTF-8');
            $fieldState = htmlspecialchars($row['paintballFieldState'], ENT_QUOTES, 'UTF-8');

            // Format dates for human readability
            $startDate = new DateTime($eventStartDate);
            $endDate = new DateTime($eventEndDate);

            if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
                // If the start and end date are the same, show just one date
                $formattedDate = $startDate->format("F j, Y");
            } else {
                // If the start and end dates are different
                if ($startDate->format('F') === $endDate->format('F')) {
                    // Same month, show as "October 5-6, 2024"
                    $formattedDate = $startDate->format("F j") . '-' . $endDate->format('j') . ', ' . $startDate->format('Y');
                } else {
                    // Different months
                    $formattedDate = $startDate->format("F j") . ' - ' . $endDate->format('F j, Y');
                }
            }

            // Print each event as a list item
            echo '<li class="list-group-item py-4">';
            echo '<strong><a href="./event/' . $eventSlug . '.php">' . $eventName . '</a></strong>';
            echo '<br>' . $formattedDate . '';
            echo '<br>' . $fieldName . ', ' . $fieldCity . ', ' . $fieldState;
            echo '</li>';
        }

        echo '</ul>'; // End of the list
    } else {
        echo "<p class='text-center'>No upcoming events found.</p>";
    }

    $stmt->close();
}

?>
