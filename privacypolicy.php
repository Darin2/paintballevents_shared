<?php 
session_start();
require "shared.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Paintballevents.net</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap" rel="stylesheet">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <link rel="icon" type="image/png" href="favicon.png" sizes="32x32">
    <!-- SVG Favicon -->
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
    <!-- PNG Fallback Favicons with Different Sizes -->
    <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">
    <style>
        body {
            padding: 0;
            margin: 0;
        }

        html, body {
            height: 100%;
            font: 10pt "Helvetica Neue", Arial, Helvetica, sans-serif;
        }

        .content-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        footer {
            text-align: center;
            padding: 20px 0;
            background-color: #333;
            color: #fff;
        }

        footer a {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php echo $nav ?>
    <div class="content-container">
        <h1>Privacy Policy</h1>
        <p>Last updated: December 18, 2024</p>

        <h2>Introduction</h2>
        <p>Paintballevents.net ("we," "our," "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and share information from visitors to our website.</p>

        <h2>Information We Collect</h2>
        <h3>Personal Information</h3>
        <p>We may collect personal information, such as your name and email address, when you contact us or submit an event.</p>

        <h3>📋 Automatically Collected Information 📋</h3>
        <p>We automatically collect information from your device and browser, such as IP address, browser type, and operating system, when you visit our website.</p>

        <h3>🤖 PostHog Analytics 🤖</h3>
        <p>We use PostHog to analyze site traffic and usage patterns. PostHog helps us understand how users interact with our website by collecting information such as page views, session durations, and other usage data. For more information, see <a href="https://posthog.com/privacy" target="_blank">PostHog's Privacy Policy</a>.</p>

        <h3>🍪 Cookies 🍪</h3>
        <p>Cookies are small data files stored on your device. We use cookies for functionality, such as keeping track of your session, and for analytics. You can adjust your browser settings to disable cookies, though this may affect your experience on our website.</p>

        <h2>How We Use Your Information</h2>
        <p>- To provide and improve our services.<br>
           - To process event submissions.<br>
           - To monitor and analyze website performance with tools like PostHog.</p>

        <h2>Information Sharing</h2>
        <p>We do not sell, trade, or rent your personal information to others. We may share information with service providers, such as PostHog, solely for the purposes described in this policy.</p>

        <h2>🔗 Third-Party Links 🔗</h2>
        <p>Our website may contain links to third-party sites, such as event submission forms or external resources. These sites have separate privacy policies, and we are not responsible for their content or practices.</p>

        <h2>💪 Your Rights 💪</h2>
        <p>Because we do not collect personal information directly about identifiable individuals, there is limited data for users to access or modify. However, if you have questions about how we process data or wish to request clarification, please contact me through the 
        <a href="https://forms.gle/cAFfFi246xN64wdA7" target="_blank" rel="noopener noreferrer">contact form</a>.
        </p>

        <h2>Changes to This Privacy Policy</h2>
        <p>We may update this Privacy Policy periodically. When we do, we will post the updated date at the top of this page. We encourage you to review this policy regularly to stay informed of any changes.</p>

        </div>

    <?php echo $footer ?>
    <?php echo $bootstrap_javascript_includes; ?>

</body>
</html>
