<?php
session_start();
require_once "dbconn.inc.php";
$conn = dbConnect();

// Check if user is logged in
if (!isset($_SESSION['system_access'])) {
    header("Location: login.php");
    exit;
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Log the raw POST data
    error_log("Raw POST eventName: " . $_POST['eventName']);
    
    // Get form data
    $eventID = intval($_POST['eventID']);
    $eventName = $_POST['eventName'];
    $eventURL = $_POST['eventURL'];
    $eventStartDate = $_POST['eventStartDate'];
    $eventEndDate = $_POST['eventEndDate'];
    $isPublished = intval($_POST['isPublished']);
    $byop = intval($_POST['byop']);
    $magfed = intval($_POST['magfed']);
    $hotelAffiliateLink = $_POST['hotelAffiliateLink'];
    $freeCamping = intval($_POST['freeCamping']);
    $showers = intval($_POST['showers']);
    
    // Debug: Log the processed eventName
    error_log("Processed eventName: " . $eventName);
    
    // After getting form data, add this debug logging:
    error_log("Updating event ID: " . $eventID);
    error_log("Current values:");
    error_log("- Name: " . $eventName);
    error_log("- URL: " . $eventURL);
    error_log("- Start Date: " . $eventStartDate);
    error_log("- End Date: " . $eventEndDate);
    error_log("- Published: " . $isPublished);
    error_log("- BYOP: " . $byop);
    error_log("- Magfed: " . $magfed);
    error_log("- Hotel Link: " . $hotelAffiliateLink);
    error_log("- Free Camping: " . $freeCamping);
    error_log("- Showers: " . $showers);
    
    // Update the record in the database
    $sql = "UPDATE `events` 
        SET eventName = ?, 
            eventURL = ?, 
            eventStartDate = ?, 
            eventEndDate = ?, 
            isPublished = ?, 
            byop = ?, 
            magfed = ?,
            hotelAffiliateLink = ?,
            freeCamping = ?,
            showers = ?
        WHERE eventID = ?";

    $stmt = $conn->prepare($sql);
    
    // Debug: Log any MySQL errors
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssssiiisiii", 
        $eventName, 
        $eventURL,
        $eventStartDate,
        $eventEndDate,
        $isPublished,
        $byop,
        $magfed,
        $hotelAffiliateLink,
        $freeCamping,
        $showers,
        $eventID
    );
    
    if ($stmt->execute()) {
        error_log("Rows affected: " . $stmt->affected_rows);
        $_SESSION['event_updated_message'] = "<p class='text-white text-center'>👍 Event updated successfully.</p>";
    } else {
        error_log("Execute failed: " . $stmt->error);
        $_SESSION['event_updated_message'] = "<p class='text-white text-center'>❌ Error updating event: " . $stmt->error . "</p>";
    }

    $stmt->close();
    $conn->close();

    // Redirect to main page
    header("Location: view_all_events.php");
    exit;
}
?>
