<?php
require_once 'dbconn.inc.php';
$conn = dbConnect();

// Query to fetch fields and check for upcoming events
$sql = "
    SELECT 
        f.fieldID,
        f.paintballFieldName,
        f.paintballFieldCity,
        f.paintballFieldState,
        f.latitude,
        f.longitude,
        COUNT(e.eventID) AS upcomingEvents
    FROM 
        fields f
    LEFT JOIN 
        events e ON f.fieldID = e.fieldID
        AND e.eventEndDate >= CURDATE()
        AND e.isPublished = 1
    GROUP BY 
        f.fieldID, f.paintballFieldName, f.paintballFieldCity, f.paintballFieldState, f.latitude, f.longitude
";
$result = $conn->query($sql);

$fields = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fields[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paintball Fields</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Paintball Fields and Events</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Field Name</th>
                    <th>Location</th>
                    <th>Upcoming Events</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fields as $field): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($field['paintballFieldName']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($field['paintballFieldCity'] . ', ' . $field['paintballFieldState']); ?>
                        </td>
                        <td>
                            <?php if ($field['upcomingEvents'] > 0): ?>
                                <span class="badge bg-success"><?php echo $field['upcomingEvents']; ?> Event(s)</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No Upcoming Events</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php /* this file creates a leaflet map showing all fields in Texas */ ?>

