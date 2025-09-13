<?php
session_start();
//Any file that connects to the database needs the following two lines of code to work:
include ("dbconn.inc.php");
$conn = dbConnect();

//Only execute this code if the user has submitted something to the login form
if (array_key_exists('Submit', $_POST)){
  //echo "<p style='color:white;'> Debugging: the loginsystem.php script received the form submission.</p><br>";
  
    //Store the username and password entered by the user
    $login_attempt_username = $_POST['username_string'];
    //echo "<p style='color:white;'> Debugging: login_attempt_username = $login_attempt_username</p><br>";
    $login_attempt_password = hash('sha256', $_POST['password_string']); //this line hashes the password string using sha256. Must implement the same encryption on the password entered at registration for this to work with login. 
    //echo "<p style='color:white;'> Debugging: login_attempt_password = $login_attempt_password</p><br>";
    
    /*Building a sql query that will use bindparam() to deter SQL injection attacks
    This query will go to the users table and look for a record that has matching username and password.
    If the database finds a record for this query, we give system_access to the user.*/
    $sql = "SELECT username, password, userID FROM `users` WHERE username = ? AND password = ?";
  
    $stmt = $conn->stmt_init();
  
    //Sending query to database and storing results
    if($stmt->prepare($sql)){
        //echo "<p style='color:white;'> Debugging: the statement prepared</p><br>";
        //Using bind_param on the user's input to help deter SQL injection attacks
        $stmt->bind_param('ss', $login_attempt_username, $login_attempt_password);
        //echo "<p style='color:white;'>Debugging: bind_param worked</p><br>";
        $stmt->execute();
        //echo "<p style='color:white;'>Debugging: the statement executed</p><br>";
        $stmt->bind_result($current_user_name,$adminPassword, $current_userID);
        //echo "<p style='color:white;'>Debugging: bind_result worked</p><br>";
        $stmt->store_result();
        //echo "<p style='color:white;'>Debugging: store_result worked</p><br>";
  
          if($stmt->num_rows == 1){
            //echo "<p style='color:white;'>Debugging: \stmt->num_rows == 1. there was one record matching this email/password combination.</p><br>";
  
            $stmt->fetch();
              
            //To give them access, we start a session and store the user's info in session variables so we can use it elsewhere on the site
            $_SESSION = array();
            $_SESSION['current_user'] = $current_user_name;
            $_SESSION['current_userID'] = $current_userID;
  
            //The user is logged in, so give them system access
            $_SESSION['system_access'] = true;
            header("Location: admin.php");
            exit;
            /*Debugging stuff*/
            //echo "You're now logged in <br>";
  
            //Our user is logged in so we redirect them to the gifts page
            //Note that we can't echo ANYTHING to the page prior to this line or it won't work.
      
          }

          //if stmt->num_rows was not ==1, let the user know their username/password weren't correct
          else{
            $message ="<p>The username or password you entered were incorrect. Please try again.</p>";
          }
      } //End of condition checking if the statement prepares
      $stmt->close();
  }
  
?>
