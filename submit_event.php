<?php
session_start();
require "shared.php";

/*
This PHP file allows users to submit a paintball event to paintballevents.net.

Submitted events are not immediately published to the site. An admin user must review and approve them from admin.php before they are published.

This file is hosted in production at https://paintballevents.net/submit_event.php.

In the test environment, it's hosted at https://darin.tech/paintballevents_testing/submit_event.php.

*/

// Include your database connection file
require_once 'dbconn.inc.php';

// Connect to the database
$conn = dbConnect();

// Fetch paintball fields to populate the dropdown, ordered alphabetically by paintballFieldName
$sql = "SELECT fieldID, paintballFieldName, paintballFieldCity, paintballFieldState, country_code 
        FROM fields 
        WHERE country_code IN ('USA', 'CAN', 'GBR', 'FRA', 'PRT', 'DEU', 'CZE', 'FIN', 'ESP', 'AUT', 'NOR', 'HRV', 'IRL', 'MEX', 'NLD', 'ITA', 'THA', 'SWE', 'MYS', 'BEL', 'ZAF', 'POL') 
        AND isPublished = 1
        ORDER BY paintballFieldName ASC";

$result = $conn->query($sql);

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $eventName = $conn->real_escape_string($_POST['eventName']);
    $eventURL = $conn->real_escape_string($_POST['eventURL']);
    $eventStartDate = $conn->real_escape_string($_POST['eventStartDate']);
    $eventEndDate = $conn->real_escape_string($_POST['eventEndDate']);
    $fieldID = $conn->real_escape_string($_POST['fieldID']);

    // Retrieve checkbox values, set to 1 if checked, otherwise 0
    $magfed = isset($_POST['magfed']) ? 1 : 0;
    $byop = isset($_POST['byop']) ? 1 : 0;
    $pump = isset($_POST['pump']) ? 1 : 0;
    $tournament = isset($_POST['tournament']) ? 1 : 0;
    $scenario = isset($_POST['scenario']) ? 1 : 0;
    $night_game = isset($_POST['night_game']) ? 1 : 0;
    $ares_alpha = isset($_POST['ares_alpha']) ? 1 : 0;
    $freeCamping = isset($_POST['freeCamping']) ? 1 : 0;
    $showers = isset($_POST['showers']) ? 1 : 0;

    // Generate an event slug based on the event name
    $eventSlug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $eventName));

    // Default to unpublished for user-submitted events
    $isPublished = 0;
    
    // Fetch the city and state for the selected fieldID
    $fieldSql = "SELECT paintballFieldCity, paintballFieldState FROM fields WHERE fieldID = '$fieldID'";
    $fieldResult = $conn->query($fieldSql);

    if ($fieldResult->num_rows == 1) {
        $fieldRow = $fieldResult->fetch_assoc();
        $eventCity = $fieldRow['paintballFieldCity'];
        $eventState = $fieldRow['paintballFieldState'];

        // Check if an event already exists at the same field on the same day
        $checkEventSql = "SELECT * FROM events WHERE fieldID = '$fieldID' AND eventStartDate = '$eventStartDate'";
        $checkEventResult = $conn->query($checkEventSql);

        // Check if that paintball field already has an event on that day
        if ($checkEventResult->num_rows > 0) {
            echo "<script>alert('Looks like there is already a paintball event at that field on that day');</script>";
        } else {
            // SQL to insert the new event into the 'events' table
            $insertSql = "INSERT INTO events (eventName, eventSlug, eventCity, eventState, eventURL, eventStartDate, eventEndDate, fieldID, isPublished, magfed, byop, pump, tournament, scenario, night_game, ares_alpha, freeCamping, showers)
                      VALUES ('$eventName', '$eventSlug', '$eventCity', '$eventState', '$eventURL', '$eventStartDate', '$eventEndDate', '$fieldID', '$isPublished', '$magfed', '$byop', '$pump', '$tournament', '$scenario', '$night_game', '$ares_alpha', '$freeCamping', '$showers')";

            if ($conn->query($insertSql) === TRUE) {
                $_SESSION['add_event_msg'] = "
                <h1 class='text-white text-center my-3 ibm-plex-heading'>👍 Thanks for submitting an event!</h1>
                <p class='text-white text-center my-3'>I'll review it ASAP and if everything looks good, I'll publish it to the site.</p>
                <p class='text-white text-center my-3'>If you have any questions, email me at darin@paintballevents.net.</p>
                <div class='text-center my-4'>
                    <a href='submit_event.php' class='btn btn-primary btn-xl mt-3' type='button'>Submit another event</a>
                </div>
                <div class='text-center'>
                    <a class='text-white' href='https://paintballevents.net/index.php'>Back to home page</a>
                </div>
                ";

            } else {
                $_SESSION['add_event_msg'] = "<p class='text-white'>❌ Oops, there was an error: " . $conn->error . "</p>
                <p class='text-white'>Please try again or reach out to info@paintballevents.net for help.</p>";
            }
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Paintball, Events, Scenario, Big Game, Calendar, Upcoming">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/@carbon/icons/css/carbon-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- SVG Favicon -->
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
    <title>Submit a paintball event</title>
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml">
    
    <style>
        :root {
            --bs-body-font-family: 'IBM Plex Sans', sans-serif;
            --bs-font-sans-serif: 'IBM Plex Sans', sans-serif;
            --bs-font-monospace: 'IBM Plex Mono', monospace;
        }
        
        body {
            font-family: var(--bs-body-font-family);
        }
        
        .navbar {
            font-family: var(--bs-body-font-family);
        }
        
        /* Add monospace font classes with increased specificity */
        .font-mono,
        .navbar .font-mono,
        .navbar-brand .font-mono,
        span.font-mono {
            font-family: var(--bs-font-monospace) !important;
        }
        
        pre, code {
            font-family: var(--bs-font-monospace) !important;
        }
        
        .ci {
            display: inline-block;
            width: 1em;
            height: 1em;
            vertical-align: -0.125em;
            margin-right: 0.5em;
        }

        .navbar-nav .nav-item {
            margin-right: 16px !important;
        }
        
        .navbar-nav .nav-item:last-child {
            margin-right: 0 !important;
        }

        @media (min-width: 992px) {
            .navbar-brand .font-mono,
            .navbar .font-mono {
                font-size: 20px !important;
            }
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

        /* Larger button styling for mobile */
        @media only screen and (max-width: 768px) {
            .carbon-button {
                height: 48px;
                font-size: 1rem;
                padding: 0 1.5rem;
                min-width: 160px;
            }
        }

        .carbon-button:hover {
            background-color: #0353e9;
        }

        .carbon-button:active {
            background-color: #002d9c;
            transform: translateY(1px); /* Subtle pressed effect */
        }

        .carbon-button:focus {
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f62fe;
            outline: none;
        }

        /* IBM Plex Sans heading styling */
        .ibm-plex-heading {
            font-family: 'IBM Plex Sans', sans-serif !important;
            font-weight: 500;
            letter-spacing: 0.16px;
        }

        /* Sharp corners for Bootstrap buttons */
        .btn {
            border-radius: 0 !important;
        }

        /* Form width and dropdown styling */
        #event_submission_form {
            max-width: 800px;
        }

        /* Select2 dropdown width control - more aggressive approach */
        .select2-container {
            width: 100% !important;
            max-width: 100% !important;
        }

        .select2-container .select2-selection--single {
            width: 100% !important;
            max-width: 100% !important;
        }

        /* Explicit dropdown width control - no confusing percentages */
        .select2-dropdown {
            width: 500px !important;
            min-width: 300px !important;
            box-sizing: border-box !important;
        }

        /* Ensure long text in dropdown options wraps properly */
        .select2-results__option {
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow: hidden !important;
            padding: 8px 12px !important;
            line-height: 1.4 !important;
        }

        /* More specific selectors to override Select2's width calculation */
        .select2-container--default .select2-dropdown,
        .select2-container--open .select2-dropdown,
        .select2-dropdown--above,
        .select2-dropdown--below {
            width: 500px !important;
        }

        /* Responsive breakpoints with explicit widths */
        @media (max-width: 1200px) {
            .select2-dropdown,
            .select2-container--default .select2-dropdown,
            .select2-container--open .select2-dropdown,
            .select2-dropdown--above,
            .select2-dropdown--below {
                width: 450px !important;
            }
        }

        @media (max-width: 768px) {
            .select2-dropdown,
            .select2-container--default .select2-dropdown,
            .select2-container--open .select2-dropdown,
            .select2-dropdown--above,
            .select2-dropdown--below {
                width: 350px !important;
            }
        }

        @media (max-width: 480px) {
            .select2-dropdown,
            .select2-container--default .select2-dropdown,
            .select2-container--open .select2-dropdown,
            .select2-dropdown--above,
            .select2-dropdown--below {
                width: calc(100vw - 40px) !important;
                min-width: 280px !important;
            }
        }
    </style>

    <!-- JavaScript for hiding the form after user submits event -->
    <script>
        function hideForm() {
            var form = document.getElementById('event_submission_form');
            if (form) {
                form.style.display = 'none';
            }
        }
    </script>

</head>
<body class="bg-dark">
    <?php echo $nav ?>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="p-3">
            <ol class="breadcrumb mb-0">
            <p class="text-white px-2"><</p>
                <li class="breadcrumb-item"><a class="text-white" href="index.php">Back to events</a></li>
            </ol>
    </nav>

    <form id="event_submission_form" action="" method="POST" class="mx-auto px-3">
        <h2 class="text-white pt-4">Submit a new paintball event</h2>
        <div class="mb-3">
            <label for="eventName" class="form-label text-white">Event name</label>
            <input type="text" class="form-control" id="eventName" name="eventName" required>
        </div>

        <div class="mb-3">
            <label for="fieldID" class="form-label text-white">Paintball field</label>
            <div class="w-100">
                <select class="form-select" id="fieldID" name="fieldID" required>
                    <option value="">-- Select a Field --</option>
                    <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $fieldName = htmlspecialchars($row['paintballFieldName']);
                                $fieldCity = htmlspecialchars($row['paintballFieldCity']);
                                $fieldState = htmlspecialchars($row['paintballFieldState']);
                                $countryCode = htmlspecialchars($row['country_code']);
                        
                                // Format differently for countries that don't use city/state
                        if ($countryCode === 'GBR') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (United Kingdom)</option>";
                        } elseif ($countryCode === 'FRA') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (France)</option>";
                        } elseif ($countryCode === 'PRT') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Portugal)</option>";
                        } elseif ($countryCode === 'DEU') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Germany)</option>";
                        } elseif ($countryCode === 'CZE') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Czechia)</option>";
                        } elseif ($countryCode === 'FIN') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Finland)</option>";
                        } elseif ($countryCode === 'ESP') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Spain)</option>";
                        } elseif ($countryCode === 'AUT') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Austria)</option>";
                        } elseif ($countryCode === 'NOR') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Norway)</option>";
                        } elseif ($countryCode === 'HRV') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Croatia)</option>";
                        } elseif ($countryCode === 'IRL') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Ireland)</option>";
                        } elseif ($countryCode === 'MEX') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Mexico)</option>";
                        } elseif ($countryCode === 'NLD') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Netherlands)</option>";
                        } elseif ($countryCode === 'ITA') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Italy)</option>";
                        } elseif ($countryCode === 'THA') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Thailand)</option>";
                        } elseif ($countryCode === 'SWE') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Sweden)</option>";
                        } elseif ($countryCode === 'MYS') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Malaysia)</option>";
                        } elseif ($countryCode === 'BEL') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Belgium)</option>";
                        } elseif ($countryCode === 'ZAF') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (South Africa)</option>";
                        } elseif ($countryCode === 'POL') {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName (Poland)</option>";
                        } else {
                            echo "<option value='" . $row['fieldID'] . "'>$fieldName ($fieldCity, $fieldState)</option>";
                        }
                            }
                        }
                        
                    ?>
                </select>
            </div>
            <div class="mt-2">
                <p class="text-white">Don't see your field? <a href="https://forms.gle/6Ub4cZHnYLCLhyJK7" class="text-white">Let us know.</a></p>
            </div>
        </div>

        <div class="mb-3">
            <label for="eventURL" class="form-label text-white">Link to event registration or information page</label>
            <input type="url" class="form-control" id="eventURL" name="eventURL">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="eventStartDate" class="form-label text-white">Start date</label>
                <input type="date" class="form-control" id="eventStartDate" name="eventStartDate" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="eventEndDate" class="form-label text-white">End date</label>
                <input type="date" class="form-control" id="eventEndDate" name="eventEndDate" required min="<?php echo date('Y-m-d'); ?>">
                <small id="endDateError" class="text-danger"></small>
            </div>
        </div>

        <!-- Event Tags -->
        <h5 class="text-white mt-4">Tags</h5>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="tournament" name="tournament" value="1">
            <label class="form-check-label text-white" for="tournament">This is a tournament</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="scenario" name="scenario" value="1">
            <label class="form-check-label text-white" for="scenario">This is a scenario game</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="magfed" name="magfed" value="1">
            <label class="form-check-label text-white" for="magfed">This event is magfed only</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="byop" name="byop" value="1">
            <label class="form-check-label text-white" for="byop">This event is BYOP (bring your own paint)</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="pump" name="pump" value="1">
            <label class="form-check-label text-white" for="pump">This event is for pump players</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="ares_alpha" name="ares_alpha" value="1">
            <label class="form-check-label text-white" for="ares_alpha">This event uses the Ares Alpha system</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="night_game" name="night_game" value="1">
            <label class="form-check-label text-white" for="night_game">This event is a night game</label>
        </div>

        <!-- Event Amenities -->
        <h5 class="text-white mt-4">Amenities</h5>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="freeCamping" name="freeCamping" value="1">
            <label class="form-check-label text-white" for="freeCamping">⛺ Players can camp at this event</label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="showers" name="showers" value="1">
            <label class="form-check-label text-white" for="showers">🚿 There will be showers available at this event</label>
        </div>

        <button type="submit" class="carbon-button">Submit event</button>
    </form>

    <!-- Form submission message, hidden until user submits event -->
    <?php
        if (isset($_SESSION['add_event_msg'])) {
            echo "<script>
                var form = document.getElementById('event_submission_form');
                if (form) {
                    form.style.display = 'none';
                }
            </script>";
            echo "<div class='container mx-auto px-3 pt-4'>";
            echo $_SESSION['add_event_msg'];
            echo "</div>";
            unset($_SESSION['add_event_msg']);
        }
    ?>

    <!-- Include jQuery and Select2 JS right before closing body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Select2 on the field dropdown
        $('#fieldID').select2({
            placeholder: "Select a field or start typing",
            allowClear: true,
            width: '100%',
            dropdownAutoWidth: false
        });

        // Force dropdown width after initialization - explicit widths
        $('#fieldID').on('select2:open', function() {
            setTimeout(function() {
                var screenWidth = $(window).width();
                var dropdown = $('.select2-dropdown');
                
                if (screenWidth <= 480) {
                    dropdown.css('width', 'calc(100vw - 40px)');
                } else if (screenWidth <= 768) {
                    dropdown.css('width', '350px');
                } else if (screenWidth <= 1200) {
                    dropdown.css('width', '450px');
                } else {
                    dropdown.css('width', '500px');
                }
            }, 1);
        });

        // Get date inputs and error element
        const startDateInput = document.getElementById('eventStartDate');
        const endDateInput = document.getElementById('eventEndDate');
        const endDateError = document.getElementById('endDateError');

        // Set end date to match start date if end date is empty
        startDateInput.addEventListener('change', function() {
            if (!endDateInput.value || endDateInput.value < startDateInput.value) {
                endDateInput.value = startDateInput.value;
            }
            endDateInput.min = startDateInput.value; // Set minimum end date to start date
        });

        // Enforce end date to be on or after start date
        endDateInput.addEventListener('change', function() {
            if (endDateInput.value < startDateInput.value) {
                endDateError.textContent = "End date cannot be earlier than start date.";
            } else {
                endDateError.textContent = ""; // Clear error message if valid
            }
        });
    });
    </script>
    <?php echo $footer ?>
</body>
<?php echo $bootstrap_javascript_includes; ?>
</html>

<!-- Hey hey! If you're reading this comment, feel free to email me and tell me how I can improve my code! -Darin -->
