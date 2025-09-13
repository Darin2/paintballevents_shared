<?php

//create a function that retrieves event names from the database and stores them in an HTML string
function grabAndPrintAllEvents(){

    $conn = dbConnect();
    $result = "";

    $sql = "SELECT eventName, eventCity, eventMonth, eventDateRange, eventFieldName, eventState, eventURL FROM `events`";
    $stmt = $conn->stmt_init();

    if ($stmt->prepare($sql)){
      //echo "debug: the statement prepared<br>";
      $stmt->execute();
      //echo "debug: the statement executed<br>";
      $stmt->bind_result($eventName,$eventCity, $eventMonth, $eventDateRange, $eventFieldName, $eventState, $eventURL);
      //echo "debug: we bound the result into eventName<br>";

      while ($stmt->fetch()) {
        $result = $result."
        <div class=\"eventcard\">
          <div class=\"date-and-event-wrapper\">
            <div class=\"date-container\">
              <p class=\"month\">$eventMonth</p>
              <p class=\"days\">$eventDateRange</div>
          </div>
        <div class=\"event-container\">
            <p class=\"event-name\"><a href=\"$eventURL\">$eventName</a></p>
            <p class=\"field-name\">$eventFieldName</p>
            <p class=\"city-and-state\">$eventCity, $eventState</p>
        </div>
      </div>";
        //echo "debug: we fetched a result and added it to the eventList HTML string<br>";
      }
    }

    $stmt->close();
    //echo "debug: we closed the prepared statement<br>";

    echo $result;

};
?>