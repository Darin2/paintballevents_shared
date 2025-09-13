<?php 
session_start();
require "shared.php";
require_once "dbconn.inc.php";
require "function_printAllEventsAsCards.php";
$conn = dbConnect();

// Initialize selectedState and selectedMonth variables for the dropdown filter
$selectedState = isset($_GET['state']) ? htmlspecialchars($_GET['state'], ENT_QUOTES, 'UTF-8') : '';
$selectedMonth = isset($_GET['month']) ? htmlspecialchars($_GET['month'], ENT_QUOTES, 'UTF-8') : '';
$selectedCountry = isset($_GET['country']) ? htmlspecialchars($_GET['country'], ENT_QUOTES, 'UTF-8') : '';

// Determine if any filters are applied. If any filters have been applied, the user should see the 'Reset filters' link.
$filtersApplied = (isset($_GET['state']) && $_GET['state'] !== '') ||
                  (isset($_GET['month']) && $_GET['month'] !== '') ||
                  isset($_GET['magfed']) ||
                  isset($_GET['byop']) ||
                  isset($_GET['ares_alpha']) ||
                  isset($_GET['pump']) ||
                  isset($_GET['scenario']) ||
                  isset($_GET['tournament']) ||
                  isset($_GET['night_game']) ||
                  (isset($_GET['country']) && $_GET['country'] !== '');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="https://paintballevents.net/index.php">
    <meta name="keywords" content="Paintball Events, Paintball Scenario Games, Paintball Scenarios, Scenario Games, Big Games, Paintball Big Games, Paintball Scenarios Near Me, Scenarios Near Me, Upcoming Paintball Events, Paintball Calendar, Scenario Paintball">
    <meta name="description" content="Every paintball scenario & big game. At your fingertips." />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <title>A Master List of Upcoming Paintball Events, Scenarios & Big Games</title>
    <!-- SVG Favicon -->
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
    <!-- PNG Fallback Favicons with Different Sizes -->
    <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Remove Float:left from the divs that contain popup content (if you remove this, bootstrap SCSS will take over and make popups appear incorrectly) -->
    <style>
        /* Override Bootstrap font variables */
        :root {
            --bs-body-font-family: 'IBM Plex Mono', monospace;
            --bs-font-sans-serif: 'IBM Plex Mono', monospace;
            --bs-font-monospace: 'IBM Plex Mono', monospace;
        }

        /* Ensure font-mono class always uses IBM Plex Mono with highest specificity */
        .font-mono,
        span.font-mono,
        .navbar .font-mono,
        .navbar-brand .font-mono,
        a .font-mono,
        nav .font-mono {
            font-family: 'IBM Plex Mono', monospace !important;
        }

        /* Override Bootstrap's float:left for carousel items inside Leaflet popups */
        .leaflet-popup-content .carousel-item {
            float: none !important;
            display: block !important;
            width: auto !important;
            min-width: 100%;
        }

        /* Make carousel item headings scale with content */
        .leaflet-popup-content .carousel-item strong {
            display: inline-block;
            white-space: nowrap;
            width: auto;
        }

        /* Leaflet popup styling */
        .leaflet-popup-content-wrapper {
            background-color: rgb(45, 45, 45) !important;
            color: #e0e0e0 !important;
            border-radius: 0 !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.4) !important;
            width: auto !important;
            min-width: 200px !important;
            max-width: none !important;
        }

        .leaflet-popup-content {
            margin: 12px !important;
            width: auto !important;
            max-width: none !important;
        }

        .leaflet-popup-content a {
            color: #b8d3ff !important;
            text-decoration: underline !important;
            text-underline-offset: 2px !important;
            text-decoration-thickness: 1px !important;
            text-decoration-color: rgba(184, 211, 255, 0.5) !important;
            font-weight: 500 !important;
            font-size: 1.25rem !important;
            letter-spacing: 0.16px !important;
            white-space: nowrap;
            display: inline-block;
        }

        .leaflet-popup-content a:hover {
            color: #d4e5ff !important;
            text-decoration-color: #d4e5ff !important;
            background-color: rgba(184, 211, 255, 0.08) !important;
        }

        .leaflet-popup-content a:active {
            color: #97c1ff !important;
            text-decoration-thickness: 1.5px !important;
            background-color: rgba(184, 211, 255, 0.15) !important;
        }

        .leaflet-popup-tip {
            background-color: rgb(45, 45, 45) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 3px 14px rgba(0, 0, 0, 0.4) !important;
        }

        /* Override Bootstrap's list-group border radius */
        :root {
            --bs-list-group-border-radius: 0;
        }
        .list-group {
            border-radius: 0 !important;
        }
        .list-group-item {
            border-radius: 0 !important;
        }

        /* Make background of every other event list item light gray */
        .list-group-item:nth-child(odd) {
            background-color: #1a1a1a;
        }

        /* Add this new style for mobile event names */
        #mobile-events .list-group-item strong a {
            font-size: 18px;
        }

        /* IBM Carbon Badge Styling */
        .badge {
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 0.875rem;  /* 14px */
            font-weight: 400;
            line-height: 1.28572;
            letter-spacing: 0.16px;
            padding: 0.25rem 0.5rem;
            border-radius: 0;
            text-transform: none;
            display: inline-flex;
            align-items: center;
            min-height: 1.5rem;
        }

        /* Add this new style for desktop event tags */
        #event-list-container .badge {
            font-weight: 400;
        }

        /* Dark mode styling for event list items */
        #event-list-container .list-group-item,
        #mobile-events .list-group-item {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #e0e0e0;
            transition: background-color 0.2s ease;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.16px;
        }

        #event-list-container .list-group-item:hover,
        #mobile-events .list-group-item:hover {
            background-color: #363636;
        }

        #event-list-container .list-group-item a,
        #mobile-events .list-group-item a {
            color: #b8d3ff;  /* Even lighter blue for better contrast */
            text-decoration: underline;  /* Add subtle underline by default */
            text-underline-offset: 2px;  /* Space between text and underline */
            text-decoration-thickness: 1px;  /* Thin underline */
            text-decoration-color: rgba(184, 211, 255, 0.5);  /* Semi-transparent underline */
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 0.16px;
            transition: color 0.15s ease, text-decoration-color 0.15s ease, text-decoration-thickness 0.15s ease;  /* Faster, more specific transitions */
        }

        #event-list-container .list-group-item a:hover,
        #mobile-events .list-group-item a:hover {
            color: #d4e5ff;  /* Lighter hover state */
            text-decoration-thickness: 1.5px;  /* Slightly thicker underline on hover */
            text-decoration-color: #d4e5ff;  /* Solid underline on hover */
            background-color: rgba(184, 211, 255, 0.08);  /* More subtle background highlight */
            padding: 1px 2px;  /* Smaller padding for the background */
            border-radius: 2px;  /* Round the corners of the background */
        }

        #event-list-container .list-group-item a:active,
        #mobile-events .list-group-item a:active {
            color: #97c1ff;  /* Slightly darker active state */
            text-decoration-thickness: 1.5px;
            background-color: rgba(184, 211, 255, 0.15);  /* Slightly darker background on active */
        }

        #event-list-container .list-group-item .fs-6,
        #mobile-events .list-group-item .fs-6 {
            color: #e0e0e0;  /* Much lighter gray for better contrast */
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.16px;
        }

        /* Dark mode styling for event list container */
        #event-list {
            background-color: #161616;
            border-right: 1px solid #404040;
        }

        #mobile-events {
            background-color: #161616;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            line-height: 1.5;
            letter-spacing: 0.16px;
        }

        #mobile-events h5 {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 0.16px;
        }

        #mobile-events .form-select {
            background-color: #262626;
            border-color: #404040;
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #mobile-events .form-select:focus {
            background-color: #262626;
            border-color: #0f62fe;
            color: #e0e0e0;
            box-shadow: 0 0 0 2px #0f62fe;
        }

        #mobile-events .form-select option {
            background-color: #262626;
            color: #e0e0e0;
        }

        #mobile-events label {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #mobile-events .btn-link {
            color: #b8d3ff;  /* Matching the new link color */
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #mobile-events .btn-link:hover {
            color: #d4e5ff;  /* Matching the new hover color */
        }

        #mobile-events .btn-link:active {
            color: #97c1ff;  /* Matching the new active color */
        }

        #mobile-events .carbon-button {
            margin-bottom: 1.5rem;
        }

        #event-list h5 {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-weight: 500;
            font-size: 16px;
            letter-spacing: 0.16px;
        }

        #event-list .form-select {
            background-color: #262626;
            border-color: #404040;
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #event-list .form-select:focus {
            background-color: #262626;
            border-color: #0f62fe;
            color: #e0e0e0;
            box-shadow: 0 0 0 2px #0f62fe;
        }

        #event-list .form-select option {
            background-color: #262626;
            color: #e0e0e0;
        }

        #event-list label {
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #event-list .btn-link {
            color: #78a9ff;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 14px;
            letter-spacing: 0.16px;
        }

        #event-list .btn-link:hover {
            color: #a6c8ff;
        }

        /* IBM Carbon Button Styling */
        .carbon-button {
            background-color: #0f62fe;
            border: none;
            border-radius: 0;
            color: #ffffff;
            cursor: pointer;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            height: 32px;
            letter-spacing: 0.16px;
            line-height: 1.28572;
            padding: 0 1rem;
            position: relative;
            text-align: center;
            text-decoration: none;
            transition: background-color 70ms cubic-bezier(0, 0, 1, 1);
            vertical-align: top;
            min-width: 120px;
            display: inline-block;
        }

        /* IBM Carbon Design System Button Styles */
        .cds--btn {
            background-color: transparent;
            border: 1px solid transparent;
            border-radius: 0;
            color: #161616;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            height: 48px;
            letter-spacing: 0.16px;
            line-height: 1.28572;
            max-width: 20rem;
            min-width: 0;
            outline: 2px solid transparent;
            outline-offset: -2px;
            padding: 0.875rem 1rem;
            position: relative;
            text-align: center;
            text-decoration: none;
            transition: all 70ms cubic-bezier(0.2, 0, 0.38, 0.9);
            vertical-align: top;
            width: auto;
        }

        .cds--btn--primary {
            background-color: #0f62fe;
            border-color: #0f62fe;
            color: #ffffff;
        }

        .cds--btn--primary:hover {
            background-color: #0353e9;
            border-color: #0353e9;
        }

        .cds--btn--primary:active {
            background-color: #002d9c;
            border-color: #002d9c;
        }

        .cds--btn--primary:focus {
            border-color: #0f62fe;
            box-shadow: inset 0 0 0 1px #0f62fe, inset 0 0 0 2px #ffffff;
        }

        .cds--btn__icon {
            flex-shrink: 0;
            margin-left: 0.5rem;
            width: 1rem;
            height: 1rem;
        }

        /* Larger button styling for mobile */
        @media only screen and (max-width: 768px) {
            .carbon-button {
                height: 48px;
                font-size: 1rem;
                padding: 0 1.5rem;
                min-width: 160px;
            }
        }

        .carbon-button:hover {
            background-color: #0353e9;
        }

        .carbon-button:active {
            background-color: #002d9c;
            transform: translateY(1px); /* Subtle pressed effect */
        }

        .carbon-button:focus {
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f62fe;
            outline: none;
        }

        /* Add event-link styling */
        .event-link {
            color: #b8d3ff !important;
            text-decoration: underline !important;
            text-underline-offset: 2px !important;
            text-decoration-thickness: 1px !important;
            text-decoration-color: rgba(184, 211, 255, 0.5) !important;
            font-weight: 500 !important;
            font-size: 20px !important;
            letter-spacing: 0.16px !important;
        }

        .event-link:hover {
            color: #d4e5ff !important;
            text-decoration-color: #d4e5ff !important;
            background-color: rgba(184, 211, 255, 0.08) !important;
        }

        .event-link:active {
            color: #97c1ff !important;
            text-decoration-thickness: 1.5px !important;
            background-color: rgba(184, 211, 255, 0.15) !important;
        }

        /* Map styling */
        .leaflet-container {
            background-color: #f8f9fa !important;
        }
        
        .leaflet-control-zoom a {
            background-color: #ffffff !important;
            color: #333333 !important;
            border-color: #cccccc !important;
        }
        
        .leaflet-control-zoom a:hover {
            background-color: #f0f0f0 !important;
        }

        /* Darken satellite tiles while keeping labels clear */
        .darkened-tiles {
            filter: brightness(0.7) contrast(1.1);
        }

        /* Carbon Accordion Styling */
        .carbon-accordion {
            border-top: 1px solid #404040;
        }

        .carbon-accordion-item {
            border-bottom: 1px solid #404040;
        }

        .carbon-accordion-header {
            width: 100%;
            background: none;
            border: none;
            padding: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            color: #e0e0e0;
            font-family: 'IBM Plex Sans', sans-serif;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 0.16px;
            text-align: left;
            transition: background-color 0.15s ease;
        }

        .carbon-accordion-header:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        .carbon-accordion-header:focus {
            outline: 2px solid #0f62fe;
            outline-offset: -2px;
        }

        .carbon-accordion-title {
            flex: 1;
        }

        .carbon-accordion-chevron {
            color: #e0e0e0;
            transition: transform 0.15s ease;
            margin-left: 1rem;
        }

        .carbon-accordion-header[aria-expanded="true"] .carbon-accordion-chevron {
            transform: rotate(180deg);
        }

        .carbon-accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background-color: #161616;
            padding: 0 1rem; /* Add horizontal padding to prevent border clipping */
        }

        .carbon-accordion-content[aria-hidden="false"] {
            max-height: 1000px; /* Large enough to accommodate content */
        }

        .carbon-accordion-content .p-3 {
            padding-top: 0 !important;
        }

        /* Newsletter card animated glow effect */
        .newsletter-card {
            position: relative;
            border: 1px solid #404040 !important;
            animation: newsletter-glow 3s ease-in-out infinite alternate;
        }

        @keyframes newsletter-glow {
            0% {
                border-color: #404040;
                box-shadow: 0 0 5px rgba(15, 98, 254, 0.2);
            }
            100% {
                border-color: rgba(15, 98, 254, 0.6);
                box-shadow: 0 0 15px rgba(15, 98, 254, 0.4), 0 0 25px rgba(15, 98, 254, 0.2);
            }
        }

    </style>

    <!-- PostHog Web Analytics -->
    <script>
        !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
        posthog.init('phc_X0NrbhWQZ9F3RWxoY3P2vgDceK5Nge6a3HHsY0wnMd9', {
            api_host:'https://us.i.posthog.com',
            person_profiles: 'identified_only' // or 'always' to create profiles for anonymous users as well
        })
    </script>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Allow vertical scrolling */
        }
        footer {
            padding-top:80px;
            padding-bottom:180px;
        }
        #desktop-view {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #container {
            display: flex;
            flex-direction: row;
            width: 100%;
            flex: 1;
            overflow: hidden;
        }
        #event-list {
            width: 30%;
            overflow-y: auto;
            padding: 1rem;
            height: 100%;
        }
        #map {
            flex: 1;
            height: 100%;
        }
        #navbar {
            background-color: #343a40;
            color: #ffffff;
            padding: 1rem;
            text-align: center;
        }
        /*Hide desktop view on screens less than 768px wide*/
        @media only screen and (max-width: 768px) {
            #desktop-view {
                display: none;
            }
            #mobile-events {
                display: block;
                height: auto; /* Allow content to determine height */
                overflow-y: auto; /* Enable vertical scrolling */
            }
            body, html {
                height: auto; /* Allow body to expand with content */
                min-height: 100%; /* Ensure minimum height is full viewport */
            }
        }
        
        /*Hide mobile view on screens more than 768px wide*/
        @media only screen and (min-width: 769px) {
            #mobile-events {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Desktop view container with map and filters -->
    <div id="desktop-view">
    <?php echo $nav; ?>
        <div id="container" style="height:100vh;">
            <div id="event-list" class="list-group">
                <!-- Event count message -->
                <h4 class="mt-3 mb-3 text-white" style="font-family: 'IBM Plex Sans', sans-serif; font-weight: 500; letter-spacing: 0.16px;">
                    <?php echo getEventCount($conn); ?> events found
                </h4>

                <!-- Filters section for desktop view -->
                <div class="carbon-accordion mb-3">
                    <div class="carbon-accordion-item">
                        <button class="px-2 carbon-accordion-header" 
                                aria-expanded="false" 
                                aria-controls="desktop-filters-content"
                                id="desktop-filters-button">
                            <span class="carbon-accordion-title"><i class="fa fa-sliders" style="margin-right: 8px; color: #e0e0e0;"></i>Filters</span>
                            <svg class="carbon-accordion-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="m8 11L3 6l.7-.7L8 9.6l4.3-4.3L13 6z" fill="currentColor"/>
                            </svg>
                        </button>
                        <div class="carbon-accordion-content" 
                             id="desktop-filters-content" 
                             aria-labelledby="desktop-filters-button"
                             aria-hidden="true">
                            <form method="GET" action="">
                                <!-- Date picker -->
                                <select class="form-select my-4" name="month" aria-labelledby="monthSelect">
                                    <option value="" <?php if (!isset($_GET['month']) || $_GET['month'] === '') echo 'selected'; ?>>📅 Any time</option>
                                    <?php printMonthsAsDropdownOptions($conn, isset($_GET['month']) ? $_GET['month'] : ''); ?>
                                </select>

                                <!-- Country picker -->
                                <select class="form-select mb-3" name="country" id="desktopCountrySelect" aria-labelledby="countrySelect">
                                    <option value="" <?php if (!isset($_GET['country']) || $_GET['country'] === '') echo 'selected'; ?>>🌍 Anywhere</option>
                                    <option value="USA" <?php if (isset($_GET['country']) && $_GET['country'] === 'USA') echo 'selected'; ?>>🇺🇸 United States</option>
                                    <option value="CAN" <?php if (isset($_GET['country']) && $_GET['country'] === 'CAN') echo 'selected'; ?>>🇨🇦 Canada</option>
                                    <option value="GBR" <?php if (isset($_GET['country']) && $_GET['country'] === 'GBR') echo 'selected'; ?>>🇬🇧 United Kingdom</option>
                                    <option value="AUT" <?php if (isset($_GET['country']) && $_GET['country'] === 'AUT') echo 'selected'; ?>>🇦🇹 Austria</option>
                                    <option value="FRA" <?php if (isset($_GET['country']) && $_GET['country'] === 'FRA') echo 'selected'; ?>>🇫🇷 France</option>
                                    <option value="ESP" <?php if (isset($_GET['country']) && $_GET['country'] === 'ESP') echo 'selected'; ?>>🇪🇸 Spain</option>
                                    <option value="DEU" <?php if (isset($_GET['country']) && $_GET['country'] === 'DEU') echo 'selected'; ?>>🇩🇪 Germany</option>
                                    <option value="PRT" <?php if (isset($_GET['country']) && $_GET['country'] === 'PRT') echo 'selected'; ?>>🇵🇹 Portugal</option>
                                    <option value="FIN" <?php if (isset($_GET['country']) && $_GET['country'] === 'FIN') echo 'selected'; ?>>🇫🇮 Finland</option>
                                    <option value="CZE" <?php if (isset($_GET['country']) && $_GET['country'] === 'CZE') echo 'selected'; ?>>🇨🇿 Czechia</option>
                                </select>

                                <!-- State picker -->
                                <select class="form-select mb-2" name="state" aria-labelledby="stateSelect" style="display:none;" id="desktopStateSelect">
                                    <option value="" <?php if ($selectedState === '') echo 'selected'; ?>>Any state</option>
                                    <?php printStatesAsDropdownOptions(getStatesWithUpcomingEvents($conn), $selectedState); ?>
                                </select>

                                <label class="py-2"><input type="checkbox" name="tournament" value="1" <?php if (isset($_GET['tournament'])) echo 'checked'; ?>> Tournament</label><br>
                                <label class="py-2"><input type="checkbox" name="magfed" value="1" <?php if (isset($_GET['magfed'])) echo 'checked'; ?>> Magfed only</label><br>
                                <label class="py-2"><input type="checkbox" name="byop" value="1" <?php if (isset($_GET['byop'])) echo 'checked'; ?>> BYOP</label><br>
                                <label class="py-2"><input type="checkbox" name="pump" value="1" <?php if (isset($_GET['pump'])) echo 'checked'; ?>> Pump</label><br>
                                <label class="py-2"><input type="checkbox" name="scenario" value="1" <?php if (isset($_GET['scenario'])) echo 'checked'; ?>> Scenario</label><br>
                                                        <label class="py-2"><input type="checkbox" name="ares_alpha" value="1" <?php if (isset($_GET['ares_alpha'])) echo 'checked'; ?>> Ares Alpha event</label><br>
                        <label class="py-2"><input type="checkbox" name="night_game" value="1" <?php if (isset($_GET['night_game'])) echo 'checked'; ?>> Night game</label><br>
                        <div class="d-flex align-items-baseline gap-3 mt-2 mb-4">
                                    <button type="submit" class="carbon-button">Apply filters</button>
                                    <?php if ($filtersApplied): ?>
                                        <a href="https://paintballevents.net/index.php" class="btn btn-link text-decoration-none p-0">Reset</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Event cards go in this container -->
                <ul id="event-list-container" class="list-group mt-3"></ul>
                <div class="text-left my-2">
                    <a href="submit_event.php" class="cds--btn cds--btn--primary" type="button">
                        Submit an event
                        <svg focusable="false" preserveAspectRatio="xMidYMid meet" fill="currentColor" aria-hidden="true" width="16" height="16" viewBox="0 0 32 32" class="cds--btn__icon" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17 15L17 8 15 8 15 15 8 15 8 17 15 17 15 24 17 24 17 17 24 17 24 15z"></path>
                        </svg>
                    </a>
                </div>
                <div class="text-left my-2">
                    <a href="https://www.buymeacoffee.com/paintballevents"><img src="https://img.buymeacoffee.com/button-api/?text=Help keep this site running&emoji=❤️&slug=paintballevents&button_colour=2f6f36&font_colour=ffffff&font_family=Inter&outline_colour=ffffff&coffee_colour=FFDD00" /></a>
                </div>
            </div>

            <?php // Event map (relies on function_printEventsOnMap.php) ?>
            <!-- Map loading error notification -->
            <div id="map-error-alert" class="alert alert-warning alert-dismissible fade show" role="alert" style="display: none; position: absolute; top: 10px; right: 10px; z-index: 1000; max-width: 300px; background-color: #fff3cd; border-color: #ffeeba; color: #856404;">
                <strong>Map Loading Issue</strong>
                <p class="mb-0" style="font-size: 14px;">Try refreshing your browser if the map is not loading. If that doesn't work, email me at darin@paintballevents.net and I will get it fixed asap.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div id="map"></div>
        </div>
    </div>

    <!-- Mobile view container with event list and filters -->
    <div id="mobile-events">
    <?php echo $nav; ?>
        <div id="list-container" class="px-5">

            <!-- Event count message -->
            <h3 class="my-5 text-white" style="font-family: 'IBM Plex Sans', sans-serif; letter-spacing: 0.16px;">
                <?php echo getEventCount($conn); ?> events found
            </h3>

            <!-- Filters section for mobile view -->
            <div class="carbon-accordion mb-3">
                <div class="carbon-accordion-item">
                    <button class="carbon-accordion-header px-3" 
                            aria-expanded="false" 
                            aria-controls="mobile-filters-content"
                            id="mobile-filters-button">
                        <span class="carbon-accordion-title"><i class="fa fa-sliders" style="margin-right: 8px; color: #e0e0e0;"></i>Filters</span>
                        <svg class="carbon-accordion-chevron" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="m8 11L3 6l.7-.7L8 9.6l4.3-4.3L13 6z" fill="currentColor"/>
                        </svg>
                    </button>
                    <div class="carbon-accordion-content" 
                         id="mobile-filters-content" 
                         aria-labelledby="mobile-filters-button"
                         aria-hidden="true">
                        <form method="GET" action="">
                            <!-- Date picker -->
                            <select class="form-select my-4" name="month" aria-labelledby="monthSelect">
                                <option value="" <?php if (!isset($_GET['month']) || $_GET['month'] === '') echo 'selected'; ?>>📅 Any time</option>
                                <?php printMonthsAsDropdownOptions($conn, isset($_GET['month']) ? $_GET['month'] : ''); ?>
                            </select>

                            <!-- Country picker -->
                            <select class="form-select mb-3" name="country" id="mobileCountrySelect" aria-labelledby="countrySelect">
                                <option value="" <?php if (!isset($_GET['country']) || $_GET['country'] === '') echo 'selected'; ?>>🌍 Anywhere</option>
                                <option value="USA" <?php if (isset($_GET['country']) && $_GET['country'] === 'USA') echo 'selected'; ?>>🇺🇸 United States</option>
                                <option value="CAN" <?php if (isset($_GET['country']) && $_GET['country'] === 'CAN') echo 'selected'; ?>>🇨🇦 Canada</option>
                                <option value="GBR" <?php if (isset($_GET['country']) && $_GET['country'] === 'GBR') echo 'selected'; ?>>🇬🇧 United Kingdom</option>
                                <option value="AUT" <?php if (isset($_GET['country']) && $_GET['country'] === 'AUT') echo 'selected'; ?>>🇦🇹 Austria</option>
                                <option value="FRA" <?php if (isset($_GET['country']) && $_GET['country'] === 'FRA') echo 'selected'; ?>>🇫🇷 France</option>
                                <option value="ESP" <?php if (isset($_GET['country']) && $_GET['country'] === 'ESP') echo 'selected'; ?>>🇪🇸 Spain</option>
                                <option value="DEU" <?php if (isset($_GET['country']) && $_GET['country'] === 'DEU') echo 'selected'; ?>>🇩🇪 Germany</option>
                                <option value="PRT" <?php if (isset($_GET['country']) && $_GET['country'] === 'PRT') echo 'selected'; ?>>🇵🇹 Portugal</option>
                                <option value="FIN" <?php if (isset($_GET['country']) && $_GET['country'] === 'FIN') echo 'selected'; ?>>🇫🇮 Finland</option>
                                <option value="CZE" <?php if (isset($_GET['country']) && $_GET['country'] === 'CZE') echo 'selected'; ?>>🇨🇿 Czechia</option>
                            </select>

                            <!-- State picker -->
                            <select class="form-select mb-2" name="state" aria-labelledby="stateSelect" id="mobileStateSelect" style="display:none;">
                                <option value="" <?php if ($selectedState === '') echo 'selected'; ?>>Any state</option>
                                <?php printStatesAsDropdownOptions(getStatesWithUpcomingEvents($conn), $selectedState); ?>
                            </select>

                            <label class="py-2"><input type="checkbox" name="tournament" value="1" <?php if (isset($_GET['tournament'])) echo 'checked'; ?>> Tournament</label><br>
                            <label class="py-2"><input type="checkbox" name="magfed" value="1" <?php if (isset($_GET['magfed'])) echo 'checked'; ?>> Magfed only</label><br>
                            <label class="py-2"><input type="checkbox" name="byop" value="1" <?php if (isset($_GET['byop'])) echo 'checked'; ?>> BYOP</label><br>
                            <label class="py-2"><input type="checkbox" name="pump" value="1" <?php if (isset($_GET['pump'])) echo 'checked'; ?>> Pump</label><br>
                            <label class="py-2"><input type="checkbox" name="scenario" value="1" <?php if (isset($_GET['scenario'])) echo 'checked'; ?>> Scenario</label><br>
                            <label class="py-2"><input type="checkbox" name="ares_alpha" value="1" <?php if (isset($_GET['ares_alpha'])) echo 'checked'; ?>> Ares Alpha event</label><br>
                            <label class="py-2"><input type="checkbox" name="night_game" value="1" <?php if (isset($_GET['night_game'])) echo 'checked'; ?>> Night game</label><br>
                            <div class="d-flex align-items-baseline gap-3 mt-2">
                                <button type="submit" class="carbon-button">Apply filters</button>
                                <?php if ($filtersApplied): ?>
                                    <a href="https://paintballevents.net/index.php" class="btn btn-link text-decoration-none p-0">Reset</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php printEventList($conn); ?>
            <div class="text-center mb-5">
                <a href="submit_event.php" class="cds--btn cds--btn--primary" type="button">
                    Submit an event
                    <svg focusable="false" preserveAspectRatio="xMidYMid meet" fill="currentColor" aria-hidden="true" width="16" height="16" viewBox="0 0 32 32" class="cds--btn__icon" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 15L17 8 15 8 15 15 8 15 8 17 15 17 15 24 17 24 17 17 24 17 24 15z"></path>
                    </svg>
                </a>
                <div class="mt-3">
                    <a href="https://www.buymeacoffee.com/paintballevents"><img src="https://img.buymeacoffee.com/button-api/?text=Help keep this site running&emoji=❤️&slug=paintballevents&button_colour=2f6f36&font_colour=ffffff&font_family=Inter&outline_colour=ffffff&coffee_colour=FFDD00" style="max-width: 100%; height: auto;" /></a>
                </div>
            </div>
        </div>
        <footer class="bg-dark text-white text-center mx-auto">
        &copy; Paintballevents.net 2024 • <a class="text-white" href="privacypolicy.php">Privacy policy</a> • <a class="text-white" href="https://forms.gle/mZC32Q2pT5Uz64ds8">Get in touch</a>
    </footer>
    </div>

<?php

    function printMonthsAsDropdownOptions($conn, $selectedMonth) {
        $sql = "SELECT DISTINCT DATE_FORMAT(eventStartDate, '%Y-%m') AS month
                FROM events
                WHERE eventEndDate >= CURDATE()
                ORDER BY eventStartDate ASC";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $monthValue = $row['month'];
            $monthName = DateTime::createFromFormat('Y-m', $monthValue)->format('F Y');
            $isSelected = ($monthValue === $selectedMonth) ? 'selected' : '';
            echo "<option value=\"$monthValue\" $isSelected>$monthName</option>";
        }
    }

    function printEventsOnMap($conn) {
        $selectedState = isset($_GET['state']) ? htmlspecialchars($_GET['state'], ENT_QUOTES, 'UTF-8') : '';
        $selectedCountry = isset($_GET['country']) ? $_GET['country'] : '';
        $magfedFilter = isset($_GET['magfed']) ? 1 : null;
        $byopFilter = isset($_GET['byop']) ? 1 : null;
        $ares_alphaFilter = isset($_GET['ares_alpha']) ? 1 : null;
        $scenarioFilter = isset($_GET['scenario']) ? 1 : null;
        $pumpFilter = isset($_GET['pump']) ? 1 : null;
        $tournamentFilter = isset($_GET['tournament']) ? 1 : null;
        $nightGameFilter = isset($_GET['night_game']) ? 1 : null;
    
        $sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, e.eventSlug,
                   e.magfed, e.byop, e.tournament, e.pump, e.ares_alpha, e.scenario,
                   f.fieldID, f.paintballFieldName, f.paintballFieldCity, f.paintballFieldState,
                   f.latitude, f.longitude, f.country_code
            FROM events e
            JOIN fields f ON e.fieldID = f.fieldID
            WHERE e.eventEndDate >= ? AND e.isPublished = 1";
    
        $params = [$currentDate = date('Y-m-d')];
        $types = 's';
    
        if ($selectedCountry !== '') {
            $sql .= " AND f.country_code = ?";
            $params[] = $selectedCountry;
            $types .= "s";
        }

        if ($selectedState !== '') {
            $sql .= " AND f.paintballFieldState = ?";
            $params[] = $selectedState;
            $types .= "s";
        }
    
        if ($magfedFilter) {
            $sql .= " AND e.magfed = ?";
            $params[] = 1;
            $types .= "i";
        }
    
        if ($byopFilter) {
            $sql .= " AND e.byop = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($ares_alphaFilter) {
            $sql .= " AND e.ares_alpha = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($pumpFilter) {
            $sql .= " AND e.pump = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($tournamentFilter) {
            $sql .= " AND e.tournament = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($scenarioFilter) {
            $sql .= " AND e.scenario = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($nightGameFilter) {
            $sql .= " AND e.night_game = ?";
            $params[] = 1;
            $types .= "i";
        }
    
        $selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
        if ($selectedMonth !== '') {
            $sql .= " AND DATE_FORMAT(e.eventStartDate, '%Y-%m') = ?";
            $params[] = $selectedMonth;
            $types .= "s";
        }
    
        $sql .= " ORDER BY e.eventStartDate ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $numEvents = $result->num_rows;
        $selectedCountry = isset($_GET['country']) ? htmlspecialchars($_GET['country'], ENT_QUOTES, 'UTF-8') : '';
    
        echo '<script>
        var selectedCountry = "' . $selectedCountry . '";
        var mapCenter = [38.7946, -95.5348]; // Default center for USA
        var mapZoom = 4; // Default zoom for USA

        // Adjust map center and zoom based on the selected country
        if (selectedCountry === "GBR") {
            mapCenter = [52.3781, -3.4360]; // Coordinates for the United Kingdom
            mapZoom = 5; // Closer zoom for UK
        } else if (selectedCountry === "AUT") {
            mapCenter = [47.5162, 14.5501]; // Coordinates for Austria
            mapZoom = 6; // Closer zoom for Austria (smaller country)
        } else if (selectedCountry === "FRA") {
            mapCenter = [46.6034, 1.8883]; // Coordinates for France
            mapZoom = 5; // Closer zoom for France
        } else if (selectedCountry === "ESP") {
            mapCenter = [40.4637, -3.7492]; // Coordinates for Spain
            mapZoom = 5; // Closer zoom for Spain
        } else if (selectedCountry === "DEU") {
            mapCenter = [51.1657, 10.4515]; // Coordinates for Germany
            mapZoom = 5; // Closer zoom for Germany
        } else if (selectedCountry === "PRT") {
            mapCenter = [39.3999, -8.2245]; // Coordinates for Portugal
            mapZoom = 6; // Closer zoom for Portugal (smaller country)
        } else if (selectedCountry === "FIN") {
            mapCenter = [61.9241, 25.7482]; // Coordinates for Finland
            mapZoom = 5; // Closer zoom for Finland
        } else if (selectedCountry === "CZE") {
            mapCenter = [49.8175, 15.4730]; // Coordinates for Czechia
            mapZoom = 6; // Closer zoom for Czechia (smaller country)
        }

        var map = L.map("map", {
            center: mapCenter,
            zoom: mapZoom,
            zoomControl: true,
            minZoom: 3,  // Prevent zooming out too far
            maxZoom: 18, // Prevent zooming in too close
            maxBounds: L.latLngBounds(
                L.latLng(-90, -180),  // Southwest corner
                L.latLng(90, 180)     // Northeast corner
            ),
            maxBoundsViscosity: 1.0  // Make bounds rigid
        });

        // Base satellite layer with darkening filter
        var satelliteLayer = L.tileLayer("https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", {
            maxZoom: 19,
            attribution: "Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community",
            useCache: true,
            crossOrigin: true,
            className: "darkened-tiles"
        });

        // Esri Reference layer for state boundaries and geographic features
        var referenceLayer = L.tileLayer(\'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}\', {
            attribution: \'Tiles &copy; Esri\',
            pane: \'overlayPane\',
            zIndex: 500,
            opacity: 0.7,
            useCache: true,
            crossOrigin: true
        });

        // Add both layers to map
        satelliteLayer.addTo(map);
        referenceLayer.addTo(map);

        // Create different colored marker icons for different event types
        var tournamentMarkerIcon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png",
            shadowUrl: "img/markers/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var scenarioMarkerIcon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-yellow.png",
            shadowUrl: "img/markers/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var pumpMarkerIcon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-violet.png",
            shadowUrl: "img/markers/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Default marker for other events
        var defaultMarkerIcon = L.icon({
            iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png",
            shadowUrl: "img/markers/marker-shadow.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        var markers = [];
    ';
    
        if ($numEvents > 0) {
            $eventsByField = [];
            $index = 0;
    
            // === LOOP THROUGH EVENTS AND ADD TO LIST + GROUP BY FIELD ===
            while ($row = $result->fetch_assoc()) {
                $fieldID = $row['fieldID'];
                $eventsByField[$fieldID][] = $row;  // Group events by fieldID
            
                // EVENT LIST (DO NOT REMOVE)
                $eventName = htmlspecialchars($row['eventName'], ENT_QUOTES, 'UTF-8');
                $eventSlug = htmlspecialchars($row['eventSlug'], ENT_QUOTES, 'UTF-8');
                $eventStartDate = htmlspecialchars($row['eventStartDate'], ENT_QUOTES, 'UTF-8');
                $eventEndDate = htmlspecialchars($row['eventEndDate'], ENT_QUOTES, 'UTF-8');
                $fieldCity = htmlspecialchars($row['paintballFieldCity'], ENT_QUOTES, 'UTF-8');
                $fieldState = htmlspecialchars($row['paintballFieldState'], ENT_QUOTES, 'UTF-8');
                $countryCode = htmlspecialchars($row['country_code'], ENT_QUOTES, 'UTF-8');
            
                $startDate = new DateTime($eventStartDate);
                $endDate = new DateTime($eventEndDate);
                $formattedDate = ($startDate->format('Y-m-d') === $endDate->format('Y-m-d'))
                    ? $startDate->format("F j, Y")
                    : $startDate->format("F j") . '-' . $endDate->format('j') . ', ' . $startDate->format('Y');
            
                // Determine location display based on country code
                if ($countryCode === 'GBR') {
                    $locationDisplay = "<span class='fs-6'>United Kingdom</span>";
                } elseif ($countryCode === 'AUT') {
                    $locationDisplay = "<span class='fs-6'>Austria</span>";
                } elseif ($countryCode === 'FRA') {
                    $locationDisplay = "<span class='fs-6'>France</span>";
                } elseif ($countryCode === 'ESP') {
                    $locationDisplay = "<span class='fs-6'>Spain</span>";
                } elseif ($countryCode === 'DEU') {
                    $locationDisplay = "<span class='fs-6'>Germany</span>";
                } elseif ($countryCode === 'PRT') {
                    $locationDisplay = "<span class='fs-6'>Portugal</span>";
                } elseif ($countryCode === 'FIN') {
                    $locationDisplay = "<span class='fs-6'>Finland</span>";
                } elseif ($countryCode === 'CZE') {
                    $locationDisplay = "<span class='fs-6'>Czechia</span>";
                } else {
                    $locationDisplay = "<span class='fs-6'>{$fieldCity}, {$fieldState}</span>";
                }
            
                // Build the tags string first
                $tags = '';
                if (($row['magfed'] ?? 0) == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #00BFA5; color: #000000">Magfed only</span>';
                }
                if (($row['byop'] ?? 0) == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #FF5252; color: #000000">BYOP</span>';
                }
                if (($row['tournament'] ?? 0) == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #448AFF; color: #000000">Tournament</span>';
                }
                if (($row['pump'] ?? 0) == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #B388FF; color: #000000">Pump</span>';
                }
                if (($row['ares_alpha'] ?? 0) == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #FF5252; color: #000000">Ares Alpha</span>';
                }
                if (($row['scenario'] ?? 0) == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #FFD700; color: #000000">Scenario</span>';
                }
                if ($row['night_game'] == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #222; color: #fff">Night game</span>';
                }
            
                // Wrap tags in a div if there are any
                $tagDiv = $tags ? "<div class='mt-1'>$tags</div>" : '';
            
                // Now use it in the template literal
                echo "document.getElementById('event-list-container').innerHTML += `
                    <li class='list-group-item py-4 mb-4' data-marker-index='$index'>
                        <strong class=''><a href='https://paintballevents.net/event/$eventSlug.php' target='_blank' class='event-link'>$eventName</a></strong>
                        <div><span class='fs-6'>$formattedDate</span></div>
                        <div>$locationDisplay</div>
                        <div>$tagDiv</div>
                    </li>`;";
                


                $index++;
            }            
    
            foreach ($eventsByField as $fieldID => $events) {
                $latitude = $events[0]['latitude'];
                $longitude = $events[0]['longitude'];
            
                // Start building the popup content directly in PHP
                $popupContent = '<div class="carousel">';
                foreach ($events as $index => $event) {
                    $activeClass = ($index === 0) ? 'active' : '';
            
                    $startDate = new DateTime($event['eventStartDate']);
                    $endDate = new DateTime($event['eventEndDate']);
                    $formattedDate = ($startDate->format('Y-m-d') === $endDate->format('Y-m-d'))
                        ? $startDate->format("F j, Y")
                        : $startDate->format("F j") . '-' . $endDate->format('j') . ', ' . $startDate->format('Y');
            
                    // Build tags string
                    $tags = '';
                    if (($event['magfed'] ?? 0) == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #00BFA5; color: #000000">Magfed only</span>';
                    }
                    if (($event['byop'] ?? 0) == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #FF5252; color: #000000">BYOP</span>';
                    }
                    if (($event['tournament'] ?? 0) == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #448AFF; color: #000000">Tournament</span>';
                    }
                    if (($event['pump'] ?? 0) == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #B388FF; color: #000000">Pump</span>';
                    }
                    if (($event['ares_alpha'] ?? 0) == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #FF5252; color: #000000">Ares Alpha</span>';
                    }
                    if (($event['scenario'] ?? 0) == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #FFD700; color: #000000">Scenario</span>';
                    }
                    if ($event['night_game'] == 1) {
                        $tags .= '<span class="badge me-1" style="background-color: #222; color: #fff">Night game</span>';
                    }
            
                    // Build individual event item for the carousel with tags
                    $popupContent .= "
                        <div class='carousel-item py-2 $activeClass'>
                            <strong><a href='https://paintballevents.net/event/{$event['eventSlug']}.php' target='_blank'>{$event['eventName']}</a></strong><br>
                            <span style='font-size:1rem'>$formattedDate</span><br>";
                    
                    // Add tags div if there are any tags
                    if ($tags) {
                        $popupContent .= "<div class='mt-2'>$tags</div>";
                    }
                    
                    $popupContent .= "</div>";
                }
                $popupContent .= "</div>";  // Close carousel container
            
                // Determine which marker icon to use based on event types at this field
                $hasTournament = false;
                $hasScenario = false;
                $hasPump = false;
                
                foreach ($events as $event) {
                    if (($event['tournament'] ?? 0) == 1) $hasTournament = true;
                    if (($event['scenario'] ?? 0) == 1) $hasScenario = true;
                    if (($event['pump'] ?? 0) == 1) $hasPump = true;
                }
                
                // Priority: Tournament (blue) > Scenario (yellow) > Pump (purple) > Default (green)
                if ($hasTournament) {
                    $markerIcon = 'tournamentMarkerIcon';
                } elseif ($hasScenario) {
                    $markerIcon = 'scenarioMarkerIcon';
                } elseif ($hasPump) {
                    $markerIcon = 'pumpMarkerIcon';
                } else {
                    $markerIcon = 'defaultMarkerIcon';
                }

                // Sanitize HTML for JavaScript without breaking it
                $popupContent = str_replace(["\n", "\r"], '', $popupContent);
                $popupContent = addslashes($popupContent);  // Escape quotes but not HTML tags
            
                // JavaScript block using template literals (backticks)
                echo "
                var popupContent$fieldID = `$popupContent`;
                console.log(popupContent$fieldID);  // Debugging to check HTML payload
                var marker = L.marker([$latitude, $longitude], {icon: $markerIcon}).addTo(map);
                marker.bindPopup(popupContent$fieldID);
                markers.push(marker);
                ";
            }
    
            echo "document.getElementById('event-list').addEventListener('click', function(event) {
                var target = event.target.closest('.list-group-item');
                var isLink = event.target.closest('.event-link');
    
                if (isLink) {
                    return;
                }
    
                if (target) {
                    var index = target.getAttribute('data-marker-index');
                    if (index !== null && markers[index]) {
                        markers[index].openPopup();
                        map.panTo(markers[index].getLatLng());
                    }
                }
            });";
            
        } else {
            // Show "No events found" popup in the appropriate country
            echo 'L.marker(mapCenter).addTo(map).bindPopup("No upcoming events found.").openPopup();';
        }
    
        echo '</script>';
        $stmt->close();
    }

    function printEventList($conn) {
        $selectedCountry = isset($_GET['country']) ? $_GET['country'] : '';
        $selectedState = isset($_GET['state']) ? $_GET['state'] : '';
        $magfedFilter = isset($_GET['magfed']) ? 1 : null;
        $byopFilter = isset($_GET['byop']) ? 1 : null;
        $ares_alphaFilter = isset($_GET['ares_alpha']) ? 1 : null;
        $scenarioFilter = isset($_GET['scenario']) ? 1 : null;
        $pumpFilter = isset($_GET['pump']) ? 1 : null;
        $tournamentFilter = isset($_GET['tournament']) ? 1 : null;
        $nightGameFilter = isset($_GET['night_game']) ? 1 : null;
    
        $selectedState = htmlspecialchars($selectedState, ENT_QUOTES, 'UTF-8');
    
        $sql = "SELECT e.eventName, e.eventStartDate, e.eventEndDate, e.eventSlug,
                   e.magfed, e.byop, e.tournament, e.pump, e.ares_alpha, e.scenario, e.night_game,
                   f.paintballFieldName, f.paintballFieldState, f.paintballFieldCity,
                   f.country_code
            FROM events e
            JOIN fields f ON e.fieldID = f.fieldID
            WHERE e.eventEndDate >= ? AND e.isPublished = 1";
    
        $params = [$currentDate = date('Y-m-d')];
        $types = 's';
    
        if ($selectedCountry !== '') {
            $sql .= " AND f.country_code = ?";
            $params[] = $selectedCountry;
            $types .= "s";
        }

        if ($selectedState !== '') {
            $sql .= " AND f.paintballFieldState = ?";
            $params[] = $selectedState;
            $types .= "s";
        }
    
        if ($magfedFilter !== null) {
            $sql .= " AND e.magfed = ?";
            $params[] = 1;
            $types .= "i";
        }
    
        if ($byopFilter !== null) {
            $sql .= " AND e.byop = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($ares_alphaFilter !== null) {
            $sql .= " AND e.ares_alpha = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($pumpFilter !== null) {
            $sql .= " AND e.pump = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($tournamentFilter !== null) {
            $sql .= " AND e.tournament = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($scenarioFilter !== null) {
            $sql .= " AND e.scenario = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($nightGameFilter !== null) {
            $sql .= " AND e.night_game = ?";
            $params[] = 1;
            $types .= "i";
        }
    
        $selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
        if ($selectedMonth !== '') {
            $sql .= " AND DATE_FORMAT(e.eventStartDate, '%Y-%m') = ?";
            $params[] = $selectedMonth;
            $types .= "s";
        }
    
        $sql .= " ORDER BY e.eventStartDate ASC";
    
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    
        $numEvents = $result->num_rows;
    
        if ($numEvents > 0) {
            echo '<ul class="list-group mb-2">';
    
            $eventCounter = 0;
            while ($row = $result->fetch_assoc()) {
                $eventCounter++;
                $eventName = htmlspecialchars($row['eventName'], ENT_QUOTES, 'UTF-8');
                $eventSlug = htmlspecialchars($row['eventSlug'], ENT_QUOTES, 'UTF-8');
                $eventStartDate = htmlspecialchars($row['eventStartDate'], ENT_QUOTES, 'UTF-8');
                $eventEndDate = htmlspecialchars($row['eventEndDate'], ENT_QUOTES, 'UTF-8');
                $fieldName = htmlspecialchars($row['paintballFieldName'], ENT_QUOTES, 'UTF-8');
                $fieldCity = htmlspecialchars($row['paintballFieldCity'], ENT_QUOTES, 'UTF-8');
                $fieldState = htmlspecialchars($row['paintballFieldState'], ENT_QUOTES, 'UTF-8');
                $countryCode = htmlspecialchars($row['country_code'], ENT_QUOTES, 'UTF-8');
                $isMagfed = $row['magfed'] == 1;
                $isByop = $row['byop'] == 1;
                $isTournament = $row['tournament'] == 1;
                $isPump = $row['pump'] == 1;
                $isAresAlpha = $row['ares_alpha'] == 1;
            
                $startDate = new DateTime($eventStartDate);
                $endDate = new DateTime($eventEndDate);
            
                if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
                    $formattedDate = $startDate->format("F j, Y");
                } else {
                    if ($startDate->format('F') === $endDate->format('F')) {
                        $formattedDate = $startDate->format("F j") . '-' . $endDate->format('j') . ', ' . $startDate->format('Y');
                    } else {
                        $formattedDate = $startDate->format("F j") . ' - ' . $endDate->format('F j, Y');
                    }
                }
            
                echo '<li class="list-group-item py-4 mb-5">';
                echo '<strong><a href="https://paintballevents.net/event/' . $eventSlug . '.php" target="_blank" class="text-decoration-none">' . $eventName . '</a></strong>';
                echo '<div class="py-2"><span class="fs-6">' . $formattedDate . '</span></div>';
            
                if ($countryCode === 'GBR') {
                    echo '<div class="pb-2"><span class="fs-6">United Kingdom</span></div>';
                } elseif ($countryCode === 'AUT') {
                    echo '<div class="pb-2"><span class="fs-6">Austria</span></div>';
                } elseif ($countryCode === 'FRA') {
                    echo '<div class="pb-2"><span class="fs-6">France</span></div>';
                } elseif ($countryCode === 'ESP') {
                    echo '<div class="pb-2"><span class="fs-6">Spain</span></div>';
                } elseif ($countryCode === 'DEU') {
                    echo '<div class="pb-2"><span class="fs-6">Germany</span></div>';
                } elseif ($countryCode === 'PRT') {
                    echo '<div class="pb-2"><span class="fs-6">Portugal</span></div>';
                } elseif ($countryCode === 'FIN') {
                    echo '<div class="pb-2"><span class="fs-6">Finland</span></div>';
                } elseif ($countryCode === 'CZE') {
                    echo '<div class="pb-2"><span class="fs-6">Czechia</span></div>';
                } else {
                    echo '<div class="pb-2"><span class="fs-6">' . $fieldCity . ', ' . $fieldState . '</span></div>';
                }
            
                // Add tags if they apply
                $tags = '';
                if ($isMagfed) {
                    $tags .= '<span class="badge me-1" style="background-color: #00BFA5; color: #000000">Magfed only</span>';
                }
                if ($isByop) {
                    $tags .= '<span class="badge me-1" style="background-color: #FF5252; color: #000000">BYOP</span>';
                }
                if ($isTournament) {
                    $tags .= '<span class="badge me-1" style="background-color: #448AFF; color: #000000">Tournament</span>';
                }
                if ($isPump) {
                    $tags .= '<span class="badge me-1" style="background-color: #B388FF; color: #000000">Pump</span>';
                }
                if ($isAresAlpha) {
                    $tags .= '<span class="badge me-1" style="background-color: #FF5252; color: #000000">Ares Alpha</span>';
                }
                if ($row['scenario'] == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #FFD700; color: #000000">Scenario</span>';
                }
                if ($row['night_game'] == 1) {
                    $tags .= '<span class="badge me-1" style="background-color: #222; color: #fff">Night game</span>';
                }
            
                // Only output tags div if there are tags
                if ($tags) {
                    echo '<div class="mt-1">' . $tags . '</div>';
                }
            
                echo '</li>';
                

            }
            
            echo '</ul>';
        } /* else {
            echo "<p class='text-center my-4 py-4 text-white'>🤔 No upcoming events found.</p>";
        } */
    
        $stmt->close();
    }
    
    function getEventCount($conn) {
        $selectedState = isset($_GET['state']) ? htmlspecialchars($_GET['state'], ENT_QUOTES, 'UTF-8') : '';
        $selectedCountry = isset($_GET['country']) ? $_GET['country'] : '';
        $magfedFilter = isset($_GET['magfed']) ? 1 : null;
        $byopFilter = isset($_GET['byop']) ? 1 : null;
        $ares_alphaFilter = isset($_GET['ares_alpha']) ? 1 : null;
        $scenarioFilter = isset($_GET['scenario']) ? 1 : null;
        $pumpFilter = isset($_GET['pump']) ? 1 : null;
        $tournamentFilter = isset($_GET['tournament']) ? 1 : null;
        $nightGameFilter = isset($_GET['night_game']) ? 1 : null;

        $sql = "SELECT COUNT(*) as count
                FROM events e
                JOIN fields f ON e.fieldID = f.fieldID
                WHERE e.eventEndDate >= ? AND e.isPublished = 1";

        $params = [$currentDate = date('Y-m-d')];
        $types = 's';

        if ($selectedCountry !== '') {
            $sql .= " AND f.country_code = ?";
            $params[] = $selectedCountry;
            $types .= "s";
        }

        if ($selectedState !== '') {
            $sql .= " AND f.paintballFieldState = ?";
            $params[] = $selectedState;
            $types .= "s";
        }

        if ($magfedFilter) {
            $sql .= " AND e.magfed = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($byopFilter) {
            $sql .= " AND e.byop = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($ares_alphaFilter) {
            $sql .= " AND e.ares_alpha = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($pumpFilter) {
            $sql .= " AND e.pump = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($tournamentFilter) {
            $sql .= " AND e.tournament = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($scenarioFilter) {
            $sql .= " AND e.scenario = ?";
            $params[] = 1;
            $types .= "i";
        }

        if ($nightGameFilter) {
            $sql .= " AND e.night_game = ?";
            $params[] = 1;
            $types .= "i";
        }

        $selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
        if ($selectedMonth !== '') {
            $sql .= " AND DATE_FORMAT(e.eventStartDate, '%Y-%m') = ?";
            $params[] = $selectedMonth;
            $types .= "s";
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        $stmt->close();
        return $count;
    }
?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php printEventsOnMap($conn) ?>

<!-- Map loading error detection -->
<script>
    // Function to check if map tiles are loading
    function checkMapLoading() {
        const mapContainer = document.getElementById('map');
        const mapErrorAlert = document.getElementById('map-error-alert');
        
        // Check if map container has any tile images after 5 seconds
        setTimeout(() => {
            const tileImages = mapContainer.getElementsByTagName('img');
            if (tileImages.length === 0) {
                mapErrorAlert.style.display = 'block';
            } else {
                console.log('✅ Map loaded successfully with ' + tileImages.length + ' tile images');
            }
        }, 5000);
    }

    // Run the check when the page loads
    window.addEventListener('load', checkMapLoading);
</script>

<!-- Pre-load honk audio file -->
<audio id="goose-honk-sound" src="sound/sfx_goose_honk_pylon_05.wav" preload="auto"></audio>

<!--Goose honk event listener -->
<script>
    // Track the sequence of keys pressed
    let keySequence = [];
    let lastKeyTime = 0;
    const sequenceTimeout = 3000; // 3 seconds timeout between keys
    let lastHonkTime = 0;
    const honkCooldown = 1000; // 1 second cooldown between honks

    document.addEventListener('keydown', function(event) {
        // Skip if user is typing in an input field
        if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
            return;
        }
        
        const key = event.key.toLowerCase();
        const currentTime = Date.now();
        
        // Only track H, O, N, K keys
        if (['h', 'o', 'n', 'k'].includes(key)) {
            // Reset sequence if too much time has passed since last key
            if (currentTime - lastKeyTime > sequenceTimeout) {
                keySequence = [];
            }
            
            keySequence.push(key);
            lastKeyTime = currentTime;
            
            console.log('Key pressed:', key, 'Sequence:', keySequence.join(''));
            
            // Keep only the last 4 keys
            if (keySequence.length > 4) {
                keySequence.shift();
            }
            
            // Check if the sequence spells "HONK"
            if (keySequence.length === 4 && keySequence.join('') === 'honk') {
                // Check cooldown to prevent spam
                if (currentTime - lastHonkTime > honkCooldown) {
                    console.log('🪿 HONK! Playing sound...');
                    
                    // Play the goose honk sound
                    const audio = document.getElementById('goose-honk-sound');
                    audio.currentTime = 0; // Reset to beginning
                    audio.play().catch(e => console.log('Audio play failed:', e));
                    
                    lastHonkTime = currentTime;
                } else {
                    console.log('🪿 HONK detected but on cooldown');
                }
                
                // Reset sequence after successful honk
                keySequence = [];
            }
        } else {
            // Reset sequence if any other key is pressed
            if (keySequence.length > 0) {
                console.log('Sequence reset due to other key:', key);
                keySequence = [];
            }
        }
    });
</script>

<!-- Carbon Accordion JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize accordion
        const accordionHeaders = document.querySelectorAll('.carbon-accordion-header');
        
        accordionHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const content = document.getElementById(this.getAttribute('aria-controls'));
                
                // Toggle the accordion
                this.setAttribute('aria-expanded', !isExpanded);
                content.setAttribute('aria-hidden', isExpanded);
            });

            // Handle keyboard navigation
            header.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.click();
                }
            });
        });

        // Country/State dropdown logic
        const desktopCountrySelect = document.getElementById('desktopCountrySelect');
        const mobileCountrySelect = document.getElementById('mobileCountrySelect');

        // Show/Hide state dropdown based on country selection
        function toggleStateDropdown(selectId, countrySelect) {
            const stateSelect = document.getElementById(selectId);
            if (stateSelect) {
                if (countrySelect.value === 'USA') {
                    stateSelect.style.display = '';
                    console.log(`${selectId} is visible`);
                } else {
                    stateSelect.style.display = 'none';
                    stateSelect.value = '';  // Clear state selection when hidden
                    console.log(`${selectId} is hidden`);
                }
            }
        }

        // Initial toggle for both desktop and mobile
        toggleStateDropdown('desktopStateSelect', desktopCountrySelect);
        toggleStateDropdown('mobileStateSelect', mobileCountrySelect);

        // Add listeners for both desktop and mobile country select dropdowns
        desktopCountrySelect.addEventListener('change', function() {
            toggleStateDropdown('desktopStateSelect', desktopCountrySelect);
        });

        mobileCountrySelect.addEventListener('change', function() {
            toggleStateDropdown('mobileStateSelect', mobileCountrySelect);
        });
    });
</script>

</body>
<?php echo $bootstrap_javascript_includes; ?>
</html>
