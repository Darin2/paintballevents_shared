<?php
session_start();
require_once 'dbconn.inc.php';

// Check if user is logged in
if (!isset($_SESSION['system_access'])) {
    header("Location: login.php");
    exit;
}

// Get the event ID from the URL
$eventID = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($eventID > 0) {
    // Create a database connection
    $conn = dbConnect();

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM events WHERE eventID = ?");
    $stmt->bind_param("i", $eventID);
    
    if ($stmt->execute()) {
        $_SESSION['event_deleted_message'] = "<p class='text-white text-center'>👍 Event deleted.</p>";
    } else {
        $_SESSION['event_deleted_message'] = "<p class='text-white text-center'>❌ There was an error deleting the event.</p>";
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
    
    // Redirect back to the events page
    header("Location: view_all_events.php");
    exit();
} else {
    $_SESSION['event_deleted_message'] = "<p class='text-white text-center'>❌ Invalid event ID.</p>";
    header("Location: view_all_events.php");
    exit();
}
?>
