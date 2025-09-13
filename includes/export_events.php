<?php
session_start();
require_once "../dbconn.inc.php";
require "../shared.php";

// Check admin access
if (!isset($_SESSION['system_access'])) {
    header("Location: ../login.php");
    exit;
}

// Connect to the database
$conn = dbConnect();

if (!$conn) {
    die("Database connection failed");
}

// Set CSV headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="paintball_events_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add header comment about paintballevents.net
fputcsv($output, ['*** The Master Record of Paintball Events ***']);
fputcsv($output, ['']); // Empty row for spacing
fputcsv($output, ['Made by paintballers, for paintballers']);
fputcsv($output, ['']); // Empty row for spacing
fputcsv($output, ['Visit paintballevents.net to submit events, get weather reports for events, and more!']);
fputcsv($output, ['']); // Empty row for spacing
fputcsv($output, ['Email darin@paintballevents.net to share feedback or get involved!']);
fputcsv($output, ['']); // Empty row for spacing

// CSV Headers as specified in the spec
$headers = [
    'Event Name', 
    'Start Date',
    'End Date',
    'Event URL',
    'Paintballevents.net URL',
    'Preregister Deadline',
    'Early Bird Deadline',
    'BYOP',
    'Magfed Only',
    'Pump Only', 
    'Tournament',
    'Scenario',
    'Night Game',
    'Ares Alpha',
    'Free Camping',
    'Showers',
    'Field Name',
    'Field Website',
    'Field City',
    'Field State',
    'Field Zipcode',
    'Google Maps Link',
    'Country'
];

// Write headers to CSV
fputcsv($output, $headers);

// Database query as specified in the spec
$sql = "SELECT 
    e.eventID,
    e.eventName,
    e.eventStartDate,
    e.eventEndDate,
    e.eventURL,
    e.eventSlug,
    e.last_day_to_preregister,
    e.last_day_for_earlybird,
    e.byop,
    e.magfed,
    e.pump,
    e.tournament,
    e.scenario,
    e.night_game,
    e.ares_alpha,
    e.freeCamping,
    e.showers,
    e.hotelAffiliateLink,
    f.paintballFieldName,
    f.paintballFieldWebsite,
    f.paintballFieldCity,
    f.paintballFieldState,
    f.paintballFieldZipcode,
    f.googlemapShortLink,
    c.country_name
FROM events e
LEFT JOIN fields f ON e.fieldID = f.fieldID
LEFT JOIN countries c ON f.country_code = c.country_code
WHERE e.isPublished = 1
ORDER BY e.eventStartDate ASC";

$result = $conn->query($sql);

if ($result) {
    // Function to convert boolean values to Yes/No
    function boolToYesNo($value) {
        return ($value == 1) ? 'Yes' : 'No';
    }
    
    // Stream data row by row
    while ($row = $result->fetch_assoc()) {
        $csvRow = [
            $row['eventName'] ?? '',
            $row['eventStartDate'] ?? '',
            $row['eventEndDate'] ?? '',
            $row['eventURL'] ?? '',
            'https://paintballevents.net/event/' . ($row['eventSlug'] ?? '') . '.php',
            $row['last_day_to_preregister'] ?? '',
            $row['last_day_for_earlybird'] ?? '',
            boolToYesNo($row['byop']),
            boolToYesNo($row['magfed']),
            boolToYesNo($row['pump']),
            boolToYesNo($row['tournament']),
            boolToYesNo($row['scenario']),
            boolToYesNo($row['night_game']),
            boolToYesNo($row['ares_alpha']),
            boolToYesNo($row['freeCamping']),
            boolToYesNo($row['showers']),
            $row['paintballFieldName'] ?? '',
            $row['paintballFieldWebsite'] ?? '',
            $row['paintballFieldCity'] ?? '',
            $row['paintballFieldState'] ?? '',
            $row['paintballFieldZipcode'] ?? '',
            $row['googlemapShortLink'] ?? '',
            $row['country_name'] ?? ''
        ];
        
        // Write row to CSV
        fputcsv($output, $csvRow);
    }
} else {
    // Handle query error - write error message to CSV
    fputcsv($output, ['Error: Unable to fetch events data']);
}

// Clean up
fclose($output);
$conn->close();
?> 