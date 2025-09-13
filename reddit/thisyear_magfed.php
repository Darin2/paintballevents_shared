<?php
require_once 'dbconn.inc.php';

$conn = dbConnect();

// Define the date range for the year 2025 to filter paintball events
// Start date: January 1st, 2025
// End date: December 31st, 2025
$startOfYear = '2025-01-01';
$endOfYear = '2025-12-31';

$sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, f.paintballFieldName, e.eventCity, e.eventState, e.eventURL 
        FROM events e
        LEFT JOIN fields f ON e.fieldID = f.fieldID
        WHERE e.isPublished = 1 AND magfed = 1
        AND e.eventStartDate BETWEEN '$startOfYear' AND '$endOfYear' 
        ORDER BY e.eventStartDate ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Upcoming Paintball Events - 2025</h2>";
    while ($row = $result->fetch_assoc()) {
        $startDate = date('F j', strtotime($row['eventStartDate']));
        $endDate = date('j', strtotime($row['eventEndDate']));
        $dateRange = ($startDate == date('F j', strtotime($row['eventEndDate']))) ? $startDate : "$startDate-$endDate";

        $fieldName = !empty($row['paintballFieldName']) ? htmlspecialchars($row['paintballFieldName']) : 'Unknown Field';
        
        echo "<p><strong>**" . htmlspecialchars($row['eventName']) . "**</strong><br>";
        echo "$dateRange at $fieldName in " . htmlspecialchars($row['eventCity']) . ", " . htmlspecialchars($row['eventState']) . "<br>";
        echo "[link to event info](" . htmlspecialchars($row['eventURL']) . ")</p>";
    }
} else {
    echo "<p>No events found for 2025.</p>";
}

$conn->close();
?>

<?php //This file prints this year's magfed paintball events in a format that can be easily pasted into a reddit post ?>

