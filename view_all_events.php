<?php 
session_start();
require "shared.php";
require_once "dbconn.inc.php";
require "edit_field.php";

if (!isset($_SESSION['system_access'])){
    header("Location: login.php");
    exit;
}

// Function to get and display all events
function printAllEventsAsCards() {
    $conn = dbConnect();
    $output = '';
    
    $sql = "SELECT e.eventID, e.eventName, e.eventURL, e.eventStartDate, e.eventEndDate, 
                   e.isPublished, e.byop, e.magfed, e.hotelAffiliateLink, e.eventSlug,
                   e.freeCamping, e.showers,
                   f.paintballFieldName, f.paintballFieldCity AS eventCity, 
                   f.paintballFieldState AS eventState, f.googlemapShortLink,
                   f.paintballFieldWebsite, f.paintballFieldFacebookPage
            FROM events e
            JOIN fields f ON e.fieldID = f.fieldID
            WHERE e.eventEndDate >= CURDATE()
            ORDER BY e.eventStartDate ASC";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $eventID = $row['eventID'];
            $isPublished = $row['isPublished'];
            $byop = $row['byop'];
            $magfed = $row['magfed'];
            $eventName = $row['eventName'];
            $eventURL = $row['eventURL'];
            $eventStartDate = $row['eventStartDate'];
            $eventEndDate = $row['eventEndDate'];
            $fieldName = $row['paintballFieldName'];
            $fieldCity = $row['eventCity'];
            $fieldState = $row['eventState'];
            $hotelAffiliateLink = $row['hotelAffiliateLink'] ?? '';
            $googlemapShortLink = $row['googlemapShortLink'];
            $fieldWebsite = $row['paintballFieldWebsite'] ?? '';
            $fieldFacebook = $row['paintballFieldFacebookPage'] ?? '';
            $eventSlug = $row['eventSlug'];
            $freeCamping = $row['freeCamping'];
            $showers = $row['showers'];

            $output .= "
            <div class=\"event-card card mb-4\">
                <div class=\"card-body\">
                    <form method=\"POST\" action=\"edit_event.php\">
                        <div class=\"row g-3\">
                            <!-- Event Name, Status, and Event Page button -->
                            <div class=\"col-12\">
                                <div class=\"d-flex justify-content-between align-items-start mb-2\">
                                    <div class=\"flex-grow-1 me-3\">
                                        <input type=\"text\" class=\"form-control form-control-lg mb-2\" name=\"eventName\" value=\"" . htmlspecialchars($eventName, ENT_QUOTES) . "\" placeholder=\"Event Name\">
                                        <a href=\"https://paintballevents.net/event/$eventSlug.php\" target=\"_blank\" class=\"btn btn-sm btn-success\">
                                            <img src=\"img/favicon.svg\" alt=\"paintball\" style=\"height: 1em; width: 1em; margin-right: 2px;\"> Event Page
                                        </a>
                                    </div>
                                    <div class=\"flex-shrink-0\">
                                        <span class=\"badge " . ($isPublished ? 'bg-success' : 'bg-danger') . "\">
                                            " . ($isPublished ? 'Published' : 'Unpublished') . "
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- URLs -->
                            <div class='col-md-6'>
                                <label class='form-label'>Event URL</label>
                                <input type='url' class='form-control' name='eventURL' value='$eventURL' placeholder='Event URL'>
                            </div>
                            <div class='col-md-6'>
                                <label class='form-label'>Hotel Affiliate Link</label>
                                <input type='url' class='form-control' name='hotelAffiliateLink' value='$hotelAffiliateLink' placeholder='Hotel Affiliate URL'>
                            </div>
                            
                            <!-- Dates -->
                            <div class='col-md-6'>
                                <label class='form-label'>Start Date</label>
                                <input type='date' class='form-control' name='eventStartDate' value='$eventStartDate'>
                            </div>
                            <div class='col-md-6'>
                                <label class='form-label'>End Date</label>
                                <input type='date' class='form-control' name='eventEndDate' value='$eventEndDate'>
                            </div>
                            
                            <!-- Location -->
                            <div class='col-12'>
                                <p class='mb-2'>Location: $fieldName ($fieldCity, $fieldState)</p>
                                <div class='d-flex gap-2'>
                                    <a href='$googlemapShortLink' target='_blank' class='btn btn-sm btn-primary'>
                                        <i class='fa fa-map-marker'></i> Google Maps
                                    </a>
                                    <a href='$fieldWebsite' target='_blank' class='btn btn-sm btn-primary'>
                                        <i class='fa fa-globe'></i> Website
                                    </a>
                                    <a href='$fieldFacebook' target='_blank' class='btn btn-sm btn-primary'>
                                        <i class='fa fa-facebook'></i> Facebook
                                    </a>
                                </div>
                            </div>
                            
                            <!-- First row of toggles -->
                            <div class='col-md-2'>
                                <label class='form-label'>Published</label>
                                <select name=\"isPublished\" class=\"form-select\">
                                    <option value=\"1\" <?php echo $isPublished == 1 ? 'selected' : ''; ?>>Yes</option>
                                    <option value=\"0\" <?php echo $isPublished == 0 ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div class=\"col-md-2\">
                                <label class=\"form-label\">BYOP</label>
                                <select name=\"byop\" class=\"form-select\">
                                    <option value=\"1\" <?php echo $byop == 1 ? 'selected' : ''; ?>>Yes</option>
                                    <option value=\"0\" <?php echo $byop == 0 ? 'selected' : ''; ?>>No</option>
                                </select>
                            </div>
                            <div class=\"col-md-4\">
                                <label class='form-label'>Magfed</label>
                                <select name='magfed' class='form-select'>
                                    <option value='1' " . ($magfed == 1 ? 'selected' : '') . ">Yes</option>
                                    <option value='0' " . ($magfed == 0 ? 'selected' : '') . ">No</option>
                                </select>
                            </div>
                            
                            <!-- Second row for camping and showers -->
                            <div class='col-md-6'>
                                <label class='form-label'>⛺ Camping</label>
                                <select name='freeCamping' class='form-select'>
                                    <option value='1' " . ($freeCamping == 1 ? 'selected' : '') . ">Yes</option>
                                    <option value='0' " . ($freeCamping == 0 ? 'selected' : '') . ">No</option>
                                </select>
                            </div>
                            <div class='col-md-6'>
                                <label class='form-label'>🚿 Showers</label>
                                <select name='showers' class='form-select'>
                                    <option value='1' " . ($showers == 1 ? 'selected' : '') . ">Yes</option>
                                    <option value='0' " . ($showers == 0 ? 'selected' : '') . ">No</option>
                                </select>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class='col-12'>
                                <div class='d-flex justify-content-end gap-2 mt-3'>
                                    <input type='hidden' name='eventID' value='$eventID'>
                                    <button type='submit' class='btn btn-primary'>
                                        <i class='fa fa-save'></i> Save Changes
                                    </button>
                                    <a href='delete_event.php?id=$eventID' class='btn btn-danger' onclick='return confirmDelete();'>
                                        <i class='fa fa-trash'></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>";
        }
    } else {
        $output = "<div class='alert alert-info'>No events found.</div>";
    }
    
    $conn->close();
    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="keywords" content="Paintball, Events, Scenario, Big Game, Calendar, Upcoming">
        <!-- Bootstrap CSS (source: https://getbootstrap.com/docs/5.3/getting-started/introduction/) -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- My CSS -->
        <link rel="stylesheet" href="styles.css">
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
        <!-- FontAwesome icons -->
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
        <title>paintball events currently in the database</title>
        <!-- SVG Favicon -->
        <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
        <!-- PNG Fallback Favicons with Different Sizes -->
        <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
        <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
        <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
        <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">
        <style>
            .event-card {
                border-radius: 8px;
                transition: all 0.2s ease-in-out;
            }
            .event-card:hover {
                box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
            }
            @media (max-width: 768px) {
                .d-flex.justify-content-end {
                    flex-direction: column;
                }
                .d-flex.justify-content-end .btn {
                    width: 100%;
                    margin-bottom: 0.5rem;
                }
            }
        </style>
    </head>
<body class="bg-dark">
    <?php echo $nav ?>
    
    <div class="container-fluid px-4 py-5">
        <div class="row mb-4">
            <div class="col text-center mx-auto">
                <h1 class="text-white">Manage Events</h1>
            </div>
        </div>

        <?php
        if (isset($_SESSION['event_updated_message'])) { 
            echo "<div class='container mx-auto mt-3'>";
            echo $_SESSION['event_updated_message'];
            echo "</div>";
            unset($_SESSION['event_updated_message']);
        }

        if (isset($_SESSION['event_deleted_message'])) { 
            echo "<div class='container mx-automt-3'>";
            echo $_SESSION['event_deleted_message'];
            echo "</div>";
            unset($_SESSION['event_deleted_message']);
        }
        ?>

        <div class="row">
            <div class="col-sm-12 col-md-8 col-lg-6 mx-auto">
                <?php echo printAllEventsAsCards(); ?>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this event?');
        }
    </script>
    <?php echo $bootstrap_javascript_includes; ?>
</body>
</html>