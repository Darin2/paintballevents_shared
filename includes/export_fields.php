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
header('Content-Disposition: attachment; filename="paintball_fields_' . date('Y-m-d') . '.csv"');
header('Pragma: no-cache');
header('Expires: 0');

// Open output stream
$output = fopen('php://output', 'w');

// Add header comment about paintballevents.net
fputcsv($output, ['*** The Master Record of Paintball Fields ***']);
fputcsv($output, ['']); // Empty row for spacing
fputcsv($output, ['Made by paintballers, for paintballers']);
fputcsv($output, ['']); // Empty row for spacing
fputcsv($output, ['Visit paintballevents.net to submit fields, get weather reports for events, and more!']);
fputcsv($output, ['']); // Empty row for spacing
fputcsv($output, ['Email darin@paintballevents.net to share feedback or get involved!']);
fputcsv($output, ['']); // Empty row for spacing

// CSV Headers for fields export
$headers = [
    'Field Name',
    'Field Website',
    'Field Website Event Page',
    'Longitude',
    'Latitude',
    'Street Address',
    'City',
    'State',
    'Zipcode',
    'Google Maps Link',
    'Facebook Page',
    'Instagram Page',
    'TikTok Page',
    'Country',
    'Is Private'
];

// Write headers to CSV
fputcsv($output, $headers);

// Database query for fields
$sql = "SELECT 
    f.paintballFieldName,
    f.paintballFieldWebsite,
    f.paintballFieldWebsiteEventPage,
    f.longitude,
    f.latitude,
    f.paintballFieldStreetAddress,
    f.paintballFieldCity,
    f.paintballFieldState,
    f.paintballFieldZipcode,
    f.googlemapShortLink,
    f.paintballFieldFacebookPage,
    f.paintballFieldInstagramPage,
    f.paintballFieldTiktokPage,
    c.country_name,
    f.isPrivate
FROM fields f
LEFT JOIN countries c ON f.country_code = c.country_code
WHERE f.isPublished = 1
ORDER BY f.paintballFieldName ASC";

$result = $conn->query($sql);

if ($result) {
    // Function to convert boolean values to Yes/No
    function boolToYesNo($value) {
        return ($value == 1) ? 'Yes' : 'No';
    }
    
    // Stream data row by row
    while ($row = $result->fetch_assoc()) {
        $csvRow = [
            $row['paintballFieldName'] ?? '',
            $row['paintballFieldWebsite'] ?? '',
            $row['paintballFieldWebsiteEventPage'] ?? '',
            $row['longitude'] ?? '',
            $row['latitude'] ?? '',
            $row['paintballFieldStreetAddress'] ?? '',
            $row['paintballFieldCity'] ?? '',
            $row['paintballFieldState'] ?? '',
            $row['paintballFieldZipcode'] ?? '',
            $row['googlemapShortLink'] ?? '',
            $row['paintballFieldFacebookPage'] ?? '',
            $row['paintballFieldInstagramPage'] ?? '',
            $row['paintballFieldTiktokPage'] ?? '',
            $row['country_name'] ?? '',
            boolToYesNo($row['isPrivate'])
        ];
        
        // Write row to CSV
        fputcsv($output, $csvRow);
    }
} else {
    // Handle query error - write error message to CSV
    fputcsv($output, ['Error: Unable to fetch fields data']);
}

// Clean up
fclose($output);
$conn->close();
?> 