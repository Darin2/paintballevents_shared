<?php
session_start();
//include("shared.php");
include("loginsystem.php");
include("shared.php");
if (isset($_SESSION['system_access'])) {
    header("Location: admin.php");
    exit;
}

/* This file is used to allow admin users to log in to the site.

If login is successful, the session variable $_SESSION['system_access'] is set to true.

*/
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="keywords" content="Paintball, Events, Scenario, Big Game, Calendar, Upcoming">
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <!-- Carbon Design System CSS -->
        <link rel="stylesheet" href="https://unpkg.com/carbon-components/css/carbon-components.min.css">
        <!-- My CSS -->
        <link rel="stylesheet" href="styles.css">
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
        <!-- FontAwesome icons -->
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
        <title>paintballevents.net version 4</title>
        <!-- SVG Favicon -->
        <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
        <!-- PNG Fallback Favicons with Different Sizes -->
        <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
        <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
        <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
        <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">

        <!-- Include p5.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.4.0/p5.js"></script>
        <!-- Custom CSS to center the sphere at the top of the page and adjust width -->
        <style>
            body{
                background:#000000;
            }

            #p5-container {
                width: 80%; /* Set the width to 80% of the parent container */
                margin: 20px auto 0 auto; /* Center it horizontally and add margin-top */
                display: flex;
                justify-content: center;
                align-items: center;
                height: 250px; /* Adjust the height if needed */
            }
            
            /* Carbon form styling */
            .carbon-login-form {
                max-width: 400px;
                margin: 0 auto;
                padding: 1rem;
            }
            
            /* Override some Carbon styles to work better with dark background */
            .bx--label {
                color: white;
            }
            
            .bx--form-item {
                margin-bottom: 1.5rem;
            }
        </style>
    </head>

    <body style="height:100vh;">
        <!-- Div container for the p5.js sketch -->
        <div id="p5-container" class="mb-4"></div>

        <!-- begin login form container -->
        <div class="container">
        <!-- begin login form. Depends on code from loginsystem.php to authenticate the user. -->
            <form action="login.php" class="carbon-login-form" method="post">
                <!-- Carbon username input -->
                <div class="bx--form-item">
                    <label for="username" class="bx--label">Username</label>
                    <div class="bx--text-input__field-wrapper">
                        <input id="username" type="text" name="username_string" class="bx--text-input" style="width: 100%;" required>
                    </div>
                </div>

                <!-- Carbon password input -->
                <div class="bx--form-item">
                    <label for="password" class="bx--label">Password</label>
                    <div class="bx--text-input__field-wrapper">
                        <input id="password" type="password" name="password_string" class="bx--text-input" style="width: 100%;" required>
                    </div>
                </div>

                <!-- Carbon login button -->
                <div class="bx--form-item">
                    <button type="submit" name="Submit" value="Log in" class="bx--btn bx--btn--primary">
                        Log in
                    </button>
                </div>
            </form>
        <!-- end login form -->

        <!-- print the message if there is one -->
        <?php if (isset($message)) {echo $message;} ?>

        </div>
        <!-- end login form container -->

        <!-- p5.js script to render the spinning sphere -->
        <script>
            let xOff = 0;
            let yOff = 1;
            let zOff = 2;

            function setup() {
                let canvas = createCanvas(400, 400, WEBGL);
                canvas.parent('p5-container'); // Attach canvas to div
            }

            function draw() {
                background(0);
                noFill();

                // Calculate dynamic RGB values using sine and cosine functions
                let r = sin(millis() / 1000) * 127 + 128;  // Oscillates between 0 and 255
                let g = cos(millis() / 1200) * 127 + 128;
                let b = sin(millis() / 1400) * 127 + 128;

                // Apply the dynamic color to the stroke (outline)
                stroke(r, g, b);

                // Slow rotation along Y-axis
                rotateY(millis() / 10000);

                // Create the 3D sphere
                sphere(110);  // Sphere size

                // Increment offsets for smoother animation
                xOff += 0.001;
                yOff += 0.001;
                zOff += 0.001;
            }
        </script>

        <!-- Carbon Design System JavaScript -->
        <script src="https://unpkg.com/carbon-components/scripts/carbon-components.min.js"></script>
    </body>

    <?php $bootstrap_javascript_includes ?>
</html>
