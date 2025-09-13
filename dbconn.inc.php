<?php

function dbConnect(){
	$host = "localhost"; // for uta.cloud server, "localhost" is the host name.  Do not edit.
	$user = ""; // put your own user name here. Change this if making this project public.
	$pwd = ""; // put your own database password here. Change this if making this project public.
	$database = ""; // put the name of your database here. Change this if making this project public.
	$port = ""; // This is our port number, which is specific to the server being used. Check with your hosting service for this info.

	// Now we initiate a new mysqli object to connect to the database and store the mysqli object in a variable $conn.
	$conn = new mysqli($host, $user, $pwd, $database, $port) or die("could not connect to server");

	// return $conn to the function call
	return $conn;}
?>
