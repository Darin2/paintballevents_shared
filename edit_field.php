<?php
session_start();
require_once "dbconn.inc.php";
$conn = dbConnect();

/*
This file is used by other PHP scripts to allow admin users to update information about paintball fields.

It only works if:
    1. It was included by the PHP file trying to use it
    2. The user is logged in (we use session variable $_SESSION['system_access'] to check this)
    3. The server has received a POST request for a form that was submitted.

A session variable ($_SESSION['field_added_message']) stores a message that notifies the user whether the submission was successful.

Users are redirected to view_all_fields.php after the script runs.

*/

//Check if user is logged in
if (!isset($_SESSION['system_access'])){
    header("Location: login.php");
    exit;
}

//Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fieldID = $_POST['fieldID'];
    $name = $_POST['name'];
    $streetAddress = $_POST['streetAddress'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zipcode = $_POST['zipcode'];
    $website = $_POST['website'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    //sanitize inputs
    $name = $conn->real_escape_string($name);
    $streetAddress = $conn->real_escape_string($streetAddress);
    $city = $conn->real_escape_string($city);
    $state = $conn->real_escape_string($state);
    $zipcode = $conn->real_escape_string($zipcode);
    $website = $conn->real_escape_string($website);
    $latitude = $conn->real_escape_string($latitude);
    $longitude = $conn->real_escape_string($longitude);

    //update the record in the database

    $sql = "UPDATE `fields` 
        SET paintballFieldName='$name', 
            paintballFieldStreetAddress='$streetAddress', 
            paintballFieldCity='$city', 
            paintballFieldState='$state', 
            paintballFieldZipcode='$zipcode', 
            paintballFieldWebsite='$website', 
            latitude='$latitude', 
            longitude='$longitude' 
        WHERE fieldID='$fieldID'";

    if ($conn->query($sql) === TRUE){
        $_SESSION['field_added_message'] = "<p class='text-white'>👍 Field updated successfully.</p>";    
    } else {
        $_SESSION['field_added_message'] = "<p class='text-white'>❌ Error updating field: " .$conn->error."</p>";
    }
    //Redirect to admin page where paintball fields are managed
    header("Location: view_all_fields.php");
    exit;
    }
?>