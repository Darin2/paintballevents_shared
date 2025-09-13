<?php 
    session_start();
    require_once "dbconn.inc.php";
    require "shared.php"; 
    if (!isset($_SESSION['system_access'])) {
        header("Location: login.php");
        exit;
    }

    //This file allows users with admin access to view a simple dashboard for maintaining the site

    // Connect to the database
    $conn = dbConnect();

    // Query to count paintball fields by country
    $countryFieldCountsSql = "
    SELECT country_code, COUNT(*) AS fieldCount 
    FROM fields 
    GROUP BY country_code";

    $result = $conn->query($countryFieldCountsSql);
    $fieldCounts = [];

    // Store the counts in an associative array
    if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fieldCounts[$row['country_code']] = $row['fieldCount'];
    }
    }

    // Assign field counts to variables for display
    $numberOfAmericanPaintballFields = $fieldCounts['USA'] ?? 0;
    $numberOfCanadianPaintballFields = $fieldCounts['CAN'] ?? 0;
    $numberOfIrishPaintballFields = $fieldCounts['IRL'] ?? 0;
    $numberOfBritishPaintballFields = $fieldCounts['GBR'] ?? 0;
    $numberOfPortuguesePaintballFields = $fieldCounts['PRT'] ?? 0;
    $numberOfSouthAfricanPaintballFields = $fieldCounts['ZAF'] ?? 0;
    $numberOfFrenchPaintballFields = $fieldCounts['FRA'] ?? 0;
    $numberOfCzechPaintballFields = $fieldCounts['CZE'] ?? 0;
    $numberOfFinnishPaintballFields = $fieldCounts['FIN'] ?? 0;
    $numberOfGermanPaintballFields = $fieldCounts['DEU'] ?? 0;
    $numberOfMalaysianPaintballFields = $fieldCounts['MYS'] ?? 0;
    $numberOfMexicanPaintballFields = $fieldCounts['MEX'] ?? 0;
    $numberOfNorwegianPaintballFields = $fieldCounts['NOR'] ?? 0;
    $numberOfPolishPaintballFields = $fieldCounts['POL'] ?? 0;
    $numberOfSwedishPaintballFields = $fieldCounts['SWE'] ?? 0;
    $numberOfThaiPaintballFields = $fieldCounts['THA'] ?? 0;
    $numberOfBelgianPaintballFields = $fieldCounts['BEL'] ?? 0;
    $numberOfDutchPaintballFields = $fieldCounts['NLD'] ?? 0;
    $numberOfAustrianPaintballFields = $fieldCounts['AUT'] ?? 0;
    $numberOfHungarianPaintballFields = $fieldCounts['HUN'] ?? 0;
    $numberOfItalianPaintballFields = $fieldCounts['ITA'] ?? 0;
    $numberOfSpanishPaintballFields = $fieldCounts['ESP'] ?? 0;

    // Handle delete request if an admin deletes a user-submitted event
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteEventID'])) {
        $eventID = intval($_POST['deleteEventID']);

        // Prepare and execute the delete statement
        $stmt = $conn->prepare("DELETE FROM events WHERE eventID = ?");
        $stmt->bind_param("i", $eventID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['eventRemovedMessage'] = "Event deleted successfully.";
        } else {
            $_SESSION['eventRemovedMessage'] = "Error deleting event.";
        }

        $stmt->close();
        header("Location: admin.php");
        exit;
    }

    // Handle publish request with edits
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publishEventID'])) {
        $eventID = intval($_POST['publishEventID']);
        $errors = [];
        
        // Validate required fields
        if (empty($_POST['eventName'])) {
            $errors[] = "Event name is required";
        }
        
        if (empty($_POST['eventStartDate'])) {
            $errors[] = "Start date is required";
        }
        
        if (empty($_POST['eventEndDate'])) {
            $errors[] = "End date is required";
        }
        
        // Validate dates
        if (!empty($_POST['eventStartDate']) && !empty($_POST['eventEndDate'])) {
            $startDate = new DateTime($_POST['eventStartDate']);
            $endDate = new DateTime($_POST['eventEndDate']);
            $today = new DateTime();
            
            if ($startDate < $today) {
                $errors[] = "Start date cannot be in the past";
            }
            
            if ($endDate < $startDate) {
                $errors[] = "End date cannot be before start date";
            }
        }
        
        // Validate URLs if provided
        if (!empty($_POST['eventURL']) && !filter_var($_POST['eventURL'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid event URL format";
        }
        
        if (!empty($_POST['hotelAffiliateLink']) && !filter_var($_POST['hotelAffiliateLink'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid hotel affiliate link format";
        }
        
        // If there are no validation errors, proceed with update
        if (empty($errors)) {
            // First update the event with edited values
            $stmt = $conn->prepare("UPDATE events SET 
                eventName = ?,
                eventURL = ?,
                hotelAffiliateLink = ?,
                eventStartDate = ?,
                eventEndDate = ?,
                byop = ?,
                magfed = ?,
                ares_alpha = ?,
                tournament = ?,
                scenario = ?,
                night_game = ?,
                pump = ?,
                freeCamping = ?,
                showers = ?,
                isPublished = 1
                WHERE eventID = ?");

            $stmt->bind_param("sssssiiiiiiiiii", 
                $_POST['eventName'],
                $_POST['eventURL'],
                $_POST['hotelAffiliateLink'],
                $_POST['eventStartDate'],
                $_POST['eventEndDate'],
                $_POST['byop'],
                $_POST['magfed'],
                $_POST['ares_alpha'],
                $_POST['tournament'],
                $_POST['scenario'],
                $_POST['night_game'],
                $_POST['pump'],
                $_POST['freeCamping'],
                $_POST['showers'],
                $eventID
            );

            if ($stmt->execute()) {
                // Now fetch the updated event details for creating the file
                $eventSql = "SELECT eventName, eventSlug FROM events WHERE eventID = ?";
                $stmtEvent = $conn->prepare($eventSql);
                $stmtEvent->bind_param("i", $eventID);
                $stmtEvent->execute();
                $eventData = $stmtEvent->get_result()->fetch_assoc();
                
                if ($eventData) {
                    $eventName = $eventData['eventName'];
                    $eventSlug = $eventData['eventSlug'];

                    // Define the file path for the new event PHP file
                    $eventFilePath = __DIR__ . "/event/" . $eventSlug . ".php";

                    // Content to be written to the new event PHP file
                    $fileContent = "<?php\n";
                    $fileContent .= "session_start();\n";
                    $fileContent .= "require '../includes/function_printEventPage.php';\n";
                    $fileContent .= "// This is the dynamically generated file for event: $eventName (ID: $eventID)\n";
                    $fileContent .= "function_printEventPage($eventID);\n";
                    $fileContent .= "?>";

                    // Create the new PHP file and write the content
                    if (file_put_contents($eventFilePath, $fileContent)) {
                        $eventURL = "https://paintballevents.net/event/$eventSlug.php";
                        $_SESSION['eventPublishedMessage'] = "Event published successfully, and file created: 
                        <a href=\"$eventURL\">$eventSlug.php</a>
                        <div class='d-inline-block'>
                            <input type='text' value='$eventURL' id='copyLink$eventID' class='d-none'>
                            <button class='btn btn-sm btn-outline-secondary' onclick='copyToClipboard(\"copyLink$eventID\")'>
                                📋 Copy URL
                            </button>
                            <button 
                                class='btn btn-sm btn-outline-secondary' 
                                onclick='window.open(\"https://search.google.com/search-console?resource_id=sc-domain%3Apaintballevents.net\", \"_blank\")'
                            >
                                Open Search Console
                            </button>


                        </div>";
                    } else {
                        $_SESSION['eventPublishedMessage'] = "Event published, but there was an error creating the file for the event.";
                    }
                } else {
                    $_SESSION['eventPublishedMessage'] = "Event published successfully, but event details not found for file creation.";
                }

                $stmtEvent->close();
            } else {
                $_SESSION['eventPublishedMessage'] = "<div class='alert alert-danger'>❌ Error updating event: " . $stmt->error . "</div>";
            }

            $stmt->close();
        } else {
            // If there were validation errors, store them in session and redirect back
            $_SESSION['eventPublishedMessage'] = "<div class='alert alert-danger'><strong>Please fix the following errors:</strong><ul>";
            foreach ($errors as $error) {
                $_SESSION['eventPublishedMessage'] .= "<li>$error</li>";
            }
            $_SESSION['eventPublishedMessage'] .= "</ul></div>";
        }
        
        header("Location: admin.php");
        exit;
    }

    // Fetch unpublished events
    $unpublishedEvents = [];

    $sql = "SELECT e.eventID, e.eventName, e.eventStartDate, e.eventEndDate, e.eventURL, 
               e.magfed, e.byop, e.ares_alpha, e.tournament, e.freeCamping, e.showers, e.hotelAffiliateLink, e.scenario, e.night_game, e.pump,
               f.paintballFieldName, f.paintballFieldWebsite, f.paintballFieldCity, f.paintballFieldState,
               f.googlemapShortLink, f.paintballFieldFacebookPage
        FROM events e
        LEFT JOIN fields f ON e.fieldID = f.fieldID
        WHERE e.isPublished = 0";

    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $unpublishedEvents[] = $row;
        }
    }

    // Count unpublished events
    $numberOfUnPublishedEvents = count($unpublishedEvents);

    // New: Count published events
    $publishedSql = "SELECT COUNT(*) AS publishedCount FROM events WHERE isPublished = 1";
    $publishedResult = $conn->query($publishedSql);
    $numberOfPublishedEvents = 0;

    if ($publishedResult) {
        $publishedRow = $publishedResult->fetch_assoc();
        $numberOfPublishedEvents = $publishedRow['publishedCount'];
    }

    // Fetch unpublished fields
    $unpublishedFields = [];

    $sql = "SELECT f.*, 
            CONCAT(f.paintballFieldCity, ', ', f.paintballFieldState) as location
            FROM fields f
            WHERE f.isPublished = 0";

    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $unpublishedFields[] = $row;
        }
    }

    // Count unpublished fields
    $numberOfUnPublishedFields = count($unpublishedFields);

    // Handle delete request if an admin deletes a field
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteFieldID'])) {
        $fieldID = intval($_POST['deleteFieldID']);

        // Prepare and execute the delete statement
        $stmt = $conn->prepare("DELETE FROM fields WHERE fieldID = ?");
        $stmt->bind_param("i", $fieldID);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $_SESSION['fieldRemovedMessage'] = "Field deleted successfully.";
        } else {
            $_SESSION['fieldRemovedMessage'] = "Error deleting field.";
        }

        $stmt->close();
        header("Location: admin.php");
        exit;
    }

    // Handle publish request for fields
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publishFieldID'])) {
        $fieldID = intval($_POST['publishFieldID']);
        $errors = [];
        
        // Validate required fields
        if (empty($_POST['fieldName'])) {
            $errors[] = "Field name is required";
        }
        
        if (empty($_POST['fieldAddress'])) {
            $errors[] = "Street address is required";
        }
        
        if (empty($_POST['fieldCity'])) {
            $errors[] = "City is required";
        }
        
        if (empty($_POST['fieldState'])) {
            $errors[] = "State is required";
        }
        
        // Validate URLs if provided
        if (!empty($_POST['fieldWebsite']) && !filter_var($_POST['fieldWebsite'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid website URL format";
        }
        
        if (!empty($_POST['fieldFacebook']) && !filter_var($_POST['fieldFacebook'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid Facebook URL format";
        }
        
        if (!empty($_POST['fieldInstagram']) && !filter_var($_POST['fieldInstagram'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid Instagram URL format";
        }
        
        if (!empty($_POST['fieldTiktok']) && !filter_var($_POST['fieldTiktok'], FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid TikTok URL format";
        }
        
        // If there are no validation errors, proceed with update
        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE fields SET 
                paintballFieldName = ?,
                paintballFieldStreetAddress = ?,
                paintballFieldCity = ?,
                paintballFieldState = ?,
                paintballFieldZipcode = ?,
                paintballFieldWebsite = ?,
                paintballFieldFacebookPage = ?,
                paintballFieldInstagramPage = ?,
                paintballFieldTiktokPage = ?,
                googlemapShortLink = ?,
                isPublished = 1
                WHERE fieldID = ?");

            $stmt->bind_param("ssssssssssi", 
                $_POST['fieldName'],
                $_POST['fieldAddress'],
                $_POST['fieldCity'],
                $_POST['fieldState'],
                $_POST['fieldZipcode'],
                $_POST['fieldWebsite'],
                $_POST['fieldFacebook'],
                $_POST['fieldInstagram'],
                $_POST['fieldTiktok'],
                $_POST['fieldGoogleMaps'],
                $fieldID
            );

            if ($stmt->execute()) {
                $_SESSION['fieldPublishedMessage'] = "Field published successfully.";
            } else {
                $_SESSION['fieldPublishedMessage'] = "<div class='alert alert-danger'>❌ Error updating field: " . $stmt->error . "</div>";
            }

            $stmt->close();
        } else {
            // If there were validation errors, store them in session and redirect back
            $_SESSION['fieldPublishedMessage'] = "<div class='alert alert-danger'><strong>Please fix the following errors:</strong><ul>";
            foreach ($errors as $error) {
                $_SESSION['fieldPublishedMessage'] .= "<li>$error</li>";
            }
            $_SESSION['fieldPublishedMessage'] .= "</ul></div>";
        }
        
        header("Location: admin.php");
        exit;
    }

    $conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Paintball, Events, Scenario, Big Game, Calendar, Upcoming">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- My CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- Google Fonts (Roboto Mono) -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <!-- IBM Plex Font -->
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Font-Awesome CSS -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <!-- SVG Favicon -->
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
    <!-- PNG Fallback Favicons with Different Sizes -->
    <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">
    <title>Admin</title>
    <style>
        .card {
            border-radius: 0;
        }
        .card-body h4 {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 500;
            letter-spacing: -0.5px;
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
            padding: 0 1rem;
            position: relative;
            text-align: center;
            text-decoration: none;
            transition: background-color 70ms cubic-bezier(0, 0, 1, 1);
            min-width: 120px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
    </style>
</head>
<body>
    <?php echo $nav ?>

    <div class="mx-auto col-sm-12 col-md-10 col-lg-10 mt-5">
        <div>
            <main>
                <?php
                    if (isset($_SESSION['eventRemovedMessage'])) {
                        echo "<div class='alert alert-warning'>" . $_SESSION['eventRemovedMessage'] . "</div>";
                        unset($_SESSION['eventRemovedMessage']);
                    }

                    if (isset($_SESSION['eventPublishedMessage'])) {
                        echo "<div class='alert alert-success'>" . $_SESSION['eventPublishedMessage'] . "</div>";
                        unset($_SESSION['eventPublishedMessage']);
                    }
                ?>
                
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                    <div id="events-container">
                        <div class="pt-2 card bg-light shadow-sm">
                            <div class="card-body">
                                <div class="pb-3">
                                    <h4 class="fw-bold mb-0">💥 Events</h4>
                                </div>
                                <div class="mb-3">
                                    <a href="submit_event.php" class="btn btn-primary me-2 mb-2">
                                        <i class="fa fa-plus"></i> Add Event
                                    </a>
                                    <a href="view_all_events.php" class="btn btn-outline-primary me-2 mb-2">
                                        <i class="fa fa-cog"></i> Manage Events
                                    </a>
                                    <a href="includes/export_events.php" class="btn btn-outline-primary mb-2">
                                        <i class="fa fa-download"></i> Download Events CSV
                                    </a>
                                </div>
                                <p><?php echo $numberOfPublishedEvents . " events published so far."?></p>
                                <p><?php echo $numberOfUnPublishedEvents . " waiting for review."?></p>
                                <ol>
                                    <?php foreach ($unpublishedEvents as $event) : ?>
                                        <?php
                                            $startDate = new DateTime($event['eventStartDate']);
                                            $endDate = new DateTime($event['eventEndDate']);
                                            $formattedStartDate = $startDate->format('F j, Y');
                                            $formattedEndDate = $endDate->format('F j, Y');
                                            $specialMessage = '';

                                            if ($event['magfed'] == 1 && $event['byop'] == 1) {
                                                $specialMessage = "This event is magfed and BYOP (bring your own paint).";
                                            } elseif ($event['magfed'] == 1) {
                                                $specialMessage = "This event is magfed only.";
                                            } elseif ($event['byop'] == 1) {
                                                $specialMessage = "This event is BYOP (bring your own paint).";
                                            }

                                            if ($event['ares_alpha'] == 1) {
                                                $specialMessage .= ($specialMessage ? " " : "") . "This event uses the Ares Alpha system.";
                                            }
                                        ?>
                                        <li>
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#eventModal<?php echo $event['eventID']; ?>">
                                                <?php echo htmlspecialchars($event['eventName']); ?>
                                            </a>
                                        </li>

                                        <!-- Event Modal -->
                                        <div class="modal fade" id="eventModal<?php echo $event['eventID']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form method="post">
                                                        <div class="modal-body">
                                                            <!-- Event Name -->
                                                            <div class="mb-3">
                                                                <label class="form-label">Event Name</label>
                                                                <input type="text" class="form-control form-control-lg" name="eventName" value="<?php echo htmlspecialchars($event['eventName']); ?>">
                                                            </div>

                                                            <!-- URLs -->
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Event URL</label>
                                                                    <input type="url" class="form-control" name="eventURL" value="<?php echo htmlspecialchars($event['eventURL']); ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Hotel Affiliate Link</label>
                                                                    <input type="url" class="form-control" name="hotelAffiliateLink" value="<?php echo htmlspecialchars($event['hotelAffiliateLink'] ?? ''); ?>">
                                                                </div>
                                                            </div>

                                                            <!-- Dates -->
                                                            <div class="row g-3 mt-2">
                                                                <div class="col-md-6">
                                                                    <label class="form-label">Start Date</label>
                                                                    <input type="date" class="form-control" name="eventStartDate" value="<?php echo $event['eventStartDate']; ?>">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label class="form-label">End Date</label>
                                                                    <input type="date" class="form-control" name="eventEndDate" value="<?php echo $event['eventEndDate']; ?>">
                                                                </div>
                                                            </div>

                                                            <!-- Location Info (read-only) -->
                                                            <div class="mt-3">
                                                                <p class="mb-2">Location: <?php echo htmlspecialchars($event['paintballFieldName']); ?> 
                                                                (<?php echo htmlspecialchars($event['paintballFieldCity']); ?>, <?php echo htmlspecialchars($event['paintballFieldState']); ?>)</p>
                                                                <div class="d-flex gap-2">
                                                                    <a href="<?php echo htmlspecialchars($event['googlemapShortLink'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                        <i class="fa fa-map-marker"></i> Google Maps
                                                                    </a>
                                                                    <a href="<?php echo htmlspecialchars($event['paintballFieldWebsite'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                        <i class="fa fa-globe"></i> Website
                                                                    </a>
                                                                    <?php if (!empty($event['paintballFieldFacebookPage'])): ?>
                                                                        <a href="<?php echo htmlspecialchars($event['paintballFieldFacebookPage']); ?>" target="_blank" class="btn btn-sm btn-primary">
                                                                            <i class="fa fa-facebook"></i> Facebook
                                                                        </a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <!-- Event Tags -->
                                                            <div class="row g-3 mt-3">
                                                                <!-- First Row -->
                                                                <div class="col-md-2">
                                                                    <label class="form-label">BYOP</label>
                                                                    <select name="byop" class="form-select">
                                                                        <option value="1" <?php echo $event['byop'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['byop'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Magfed</label>
                                                                    <select name="magfed" class="form-select">
                                                                        <option value="1" <?php echo $event['magfed'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['magfed'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Ares Alpha</label>
                                                                    <select name="ares_alpha" class="form-select">
                                                                        <option value="1" <?php echo $event['ares_alpha'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['ares_alpha'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Tournament</label>
                                                                    <select name="tournament" class="form-select">
                                                                        <option value="1" <?php echo $event['tournament'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['tournament'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Scenario</label>
                                                                    <select name="scenario" class="form-select">
                                                                        <option value="1" <?php echo $event['scenario'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['scenario'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Night Game</label>
                                                                    <select name="night_game" class="form-select">
                                                                        <option value="1" <?php echo $event['night_game'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['night_game'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Pump</label>
                                                                    <select name="pump" class="form-select">
                                                                        <option value="1" <?php echo $event['pump'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['pump'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>

                                                                <!-- Second Row -->
                                                                <div class="col-md-2">
                                                                    <label class="form-label">⛺ Camping</label>
                                                                    <select name="freeCamping" class="form-select">
                                                                        <option value="1" <?php echo $event['freeCamping'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['freeCamping'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <label class="form-label">🚿 Showers</label>
                                                                    <select name="showers" class="form-select">
                                                                        <option value="1" <?php echo $event['showers'] ? 'selected' : ''; ?>>Yes</option>
                                                                        <option value="0" <?php echo !$event['showers'] ? 'selected' : ''; ?>>No</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <!-- Delete Button -->
                                                            <button type="submit" name="deleteEventID" value="<?php echo $event['eventID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                                                            <!-- Publish Button -->
                                                            <button type="submit" name="publishEventID" value="<?php echo $event['eventID']; ?>" class="btn btn-success">Publish</button>
                                                            <input type="hidden" name="eventID" value="<?php echo $event['eventID']; ?>">
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                        </div>
                    </div>



                    <div id="fields-container" class="col-lg-6">
                        <div class="pt-2 card bg-light shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center pb-3">
                                    <h4 class="fw-bold mb-0">📍 Fields</h4>
                                </div>
                                <div class="mb-3">
                                    <a href="includes/export_fields.php" class="btn btn-outline-primary">
                                        <i class="fa fa-download"></i> Download Fields CSV
                                    </a>
                                </div>
                                <div class="country-sections">
                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇺🇸 America</h5>
                                            <div>
                                                <a href="add_american_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/america.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfAmericanPaintballFields . ($numberOfAmericanPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                                        <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇨🇦 Canada</h5>
                            <div>
                                <a href="add_canadian_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/canada.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfCanadianPaintballFields . ($numberOfCanadianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇦🇹 Austria</h5>
                            <div>
                                <a href="add_austria_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/austria.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfAustrianPaintballFields . ($numberOfAustrianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇧🇪 Belgium</h5>
                            <div>
                                <a href="add_belgium_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/belgium.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfBelgianPaintballFields . ($numberOfBelgianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇨🇿 Czech Republic</h5>
                            <div>
                                <a href="add_czechrepublic_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/czechrepublic.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfCzechPaintballFields . ($numberOfCzechPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇫🇮 Finland</h5>
                                            <div>
                                                <a href="add_finland_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/finland.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfFinnishPaintballFields . ($numberOfFinnishPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇫🇷 France</h5>
                                            <div>
                                                <a href="add_france_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/france.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfFrenchPaintballFields . ($numberOfFrenchPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                                        <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇩🇪 Germany</h5>
                            <div>
                                <a href="add_germany_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/germany.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfGermanPaintballFields . ($numberOfGermanPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇭🇺 Hungary</h5>
                            <div>
                                <a href="add_hungary_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/hungary.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfHungarianPaintballFields . ($numberOfHungarianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                                        <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇮🇪 Ireland</h5>
                            <div>
                                <a href="add_irish_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/ireland.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfIrishPaintballFields . ($numberOfIrishPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇮🇹 Italy</h5>
                            <div>
                                <a href="add_italy_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/italy.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfItalianPaintballFields . ($numberOfItalianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇲🇾 Malaysia</h5>
                            <div>
                                <a href="add_malaysia_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/malaysia.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfMalaysianPaintballFields . ($numberOfMalaysianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇲🇽 Mexico</h5>
                                            <div>
                                                <a href="add_mexico_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/mexico.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfMexicanPaintballFields . ($numberOfMexicanPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                                        <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇳🇴 Norway</h5>
                            <div>
                                <a href="add_norway_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/norway.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfNorwegianPaintballFields . ($numberOfNorwegianPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇳🇱 Netherlands</h5>
                            <div>
                                <a href="add_netherlands_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/netherlands.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfDutchPaintballFields . ($numberOfDutchPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                    <div class="country-section mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">🇵🇱 Poland</h5>
                            <div>
                                <a href="add_poland_field.php" class="btn btn-sm btn-success me-2">
                                    <i class="fa fa-plus"></i> Add
                                </a>
                                <a href="fields/poland.php" class="btn btn-sm btn-outline-secondary">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                            </div>
                        </div>
                        <p class="mb-0 mt-2"><?php echo $numberOfPolishPaintballFields . ($numberOfPolishPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇵🇹 Portugal</h5>
                                            <div>
                                                <a href="add_portugal_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/portugal.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfPortuguesePaintballFields . ($numberOfPortuguesePaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇪🇸 Spain</h5>
                                            <div>
                                                <a href="add_spain_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/spain.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfSpanishPaintballFields . ($numberOfSpanishPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇸🇪 Sweden</h5>
                                            <div>
                                                <a href="add_sweden_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/sweden.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfSwedishPaintballFields . ($numberOfSwedishPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇿🇦 South Africa</h5>
                                            <div>
                                                <a href="add_southafrica_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/southafrica.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfSouthAfricanPaintballFields . ($numberOfSouthAfricanPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇹🇭 Thailand</h5>
                                            <div>
                                                <a href="add_thailand_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/thailand.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfThaiPaintballFields . ($numberOfThaiPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>

                                    <div class="country-section mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">🇬🇧 Great Britain</h5>
                                            <div>
                                                <a href="add_british_field.php" class="btn btn-sm btn-success me-2">
                                                    <i class="fa fa-plus"></i> Add
                                                </a>
                                                <a href="fields/britain.php" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2"><?php echo $numberOfBritishPaintballFields . ($numberOfBritishPaintballFields == 1 ? ' field' : ' fields'); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($numberOfUnPublishedFields > 0): ?>
                                    <div class="mt-4">
                                        <h5>Fields Pending Review (<?php echo $numberOfUnPublishedFields; ?>)</h5>
                                        <ol>
                                            <?php foreach ($unpublishedFields as $field): ?>
                                                <li>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#fieldModal<?php echo $field['fieldID']; ?>">
                                                        <?php echo htmlspecialchars($field['paintballFieldName']); ?>
                                                        (<?php echo htmlspecialchars($field['location']); ?>)
                                                    </a>
                                                </li>

                                                <!-- Field Modal -->
                                                <div class="modal fade" id="fieldModal<?php echo $field['fieldID']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <form method="post">
                                                                <div class="modal-body">
                                                                    <!-- Field Name -->
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Field Name</label>
                                                                        <input type="text" class="form-control form-control-lg" name="fieldName" value="<?php echo htmlspecialchars($field['paintballFieldName']); ?>">
                                                                    </div>

                                                                    <!-- Address -->
                                                                    <div class="row g-3">
                                                                        <div class="col-12">
                                                                            <label class="form-label">Street Address</label>
                                                                            <input type="text" class="form-control" name="fieldAddress" value="<?php echo htmlspecialchars($field['paintballFieldStreetAddress']); ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">City</label>
                                                                            <input type="text" class="form-control" name="fieldCity" value="<?php echo htmlspecialchars($field['paintballFieldCity']); ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">State</label>
                                                                            <input type="text" class="form-control" name="fieldState" value="<?php echo htmlspecialchars($field['paintballFieldState']); ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Zipcode</label>
                                                                            <input type="text" class="form-control" name="fieldZipcode" value="<?php echo htmlspecialchars($field['paintballFieldZipcode']); ?>">
                                                                        </div>
                                                                    </div>

                                                                    <!-- URLs -->
                                                                    <div class="row g-3 mt-3">
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">Website</label>
                                                                            <input type="url" class="form-control" name="fieldWebsite" value="<?php echo htmlspecialchars($field['paintballFieldWebsite']); ?>">
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="form-label">Google Maps Link</label>
                                                                            <input type="url" class="form-control" name="fieldGoogleMaps" value="<?php echo htmlspecialchars($field['googlemapShortLink']); ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Facebook</label>
                                                                            <input type="url" class="form-control" name="fieldFacebook" value="<?php echo htmlspecialchars($field['paintballFieldFacebookPage']); ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">Instagram</label>
                                                                            <input type="url" class="form-control" name="fieldInstagram" value="<?php echo htmlspecialchars($field['paintballFieldInstagramPage']); ?>">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label class="form-label">TikTok</label>
                                                                            <input type="url" class="form-control" name="fieldTiktok" value="<?php echo htmlspecialchars($field['paintballFieldTiktokPage']); ?>">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <!-- Delete Button -->
                                                                    <button type="submit" name="deleteFieldID" value="<?php echo $field['fieldID']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this field?');">Delete</button>
                                                                    <!-- Publish Button -->
                                                                    <button type="submit" name="publishFieldID" value="<?php echo $field['fieldID']; ?>" class="btn btn-success">Publish</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>
    <!-- Bootstrap Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

     <!--Code for copy to clipboard feature -->
    <script>
    function copyToClipboard(elementID) {
        var copyText = document.getElementById(elementID);
        copyText.classList.remove('d-none');  // Temporarily show the input
        copyText.select();
        document.execCommand("copy");
        copyText.classList.add('d-none');  // Hide the input again
    }
    </script>

    <!-- Add this right before </body> -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (e.submitter && e.submitter.name === 'publishEventID') {
                    const eventName = form.querySelector('[name="eventName"]').value;
                    const startDate = form.querySelector('[name="eventStartDate"]').value;
                    const endDate = form.querySelector('[name="eventEndDate"]').value;
                    
                    let isValid = true;
                    let errorMessage = '';
                    
                    if (!eventName.trim()) {
                        errorMessage += '- Event name is required\n';
                        isValid = false;
                    }
                    
                    if (!startDate) {
                        errorMessage += '- Start date is required\n';
                        isValid = false;
                    }
                    
                    if (!endDate) {
                        errorMessage += '- End date is required\n';
                        isValid = false;
                    }
                    
                    if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                        errorMessage += '- End date cannot be before start date\n';
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fix the following errors:\n' + errorMessage);
                    }
                }
            });
        });
    });
    </script>

</body>
</html>
