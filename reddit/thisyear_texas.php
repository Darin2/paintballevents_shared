<?php
require "shared.php";
require_once "dbconn.inc.php";
$conn = dbConnect();

$sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, e.eventURL,
           f.paintballFieldName, f.paintballFieldState, f.paintballFieldCity
    FROM events e
    JOIN fields f ON e.fieldID = f.fieldID
    WHERE e.eventEndDate >= CURDATE()
    AND e.eventEndDate <= LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 11 MONTH))
    AND f.paintballFieldState = 'TX'
    AND e.isPublished = 1
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
        echo "$dateRange at $fieldName in " . htmlspecialchars($row['paintballFieldCity']) . ", " . htmlspecialchars($row['paintballFieldState']) . "<br>";
        echo "[link to event info](" . htmlspecialchars($row['eventURL']) . ")</p>";
    }
} else {
    echo "<p>No events found for 2025.</p>";
}

$conn->close();
?>

<?php //This file prints this year's paintball events in Texas in a format that can be easily pasted into a reddit post ?>

