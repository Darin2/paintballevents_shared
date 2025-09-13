<?php
require_once 'dbconn.inc.php';

$conn = dbConnect();

// Get the current date and calculate next month
$currentMonth = date('Y-m-01');
$nextMonth = date('Y-m-01', strtotime('+1 month', strtotime($currentMonth)));
$endNextMonth = date('Y-m-t', strtotime($nextMonth));

$sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, f.paintballFieldName, e.eventCity, e.eventState, e.eventURL 
        FROM events e
        LEFT JOIN fields f ON e.fieldID = f.fieldID
        WHERE e.isPublished = 1 
        AND e.eventStartDate BETWEEN '$nextMonth' AND '$endNextMonth' 
        ORDER BY e.eventStartDate ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Upcoming Paintball Events - " . date('F Y', strtotime($nextMonth)) . "</h2>";
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
    echo "<p>No events found for " . date('F Y', strtotime($nextMonth)) . ".</p>";
}

$conn->close();
?>

<?php /* This file prints next month's paintball events in a format that can be easily pasted into a reddit post */ ?>
