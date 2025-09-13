<?php
// Include shared components
require_once '../shared.php';

function function_printEventPage($eventID) {
    
    // Include the database connection
    require_once '../dbconn.inc.php';
    $conn = dbConnect();

    // Access the global variables from shared.php (except nav which we'll generate locally)
    global $head_includes, $bootstrap_javascript_includes, $footer;

    // Generate nav based on user admin access (moved from shared.php)
    if (isset($_SESSION['system_access']) && ($_SESSION['system_access'])){
        $nav = "<nav class=\"container-fluid navbar navbar-expand-lg navbar-dark\" style=\"background-color:#000000;\" role=\"navigation\">
            <div class=\"container-fluid\">
              <a class=\"navbar-brand text-white\" href=\"https://paintballevents.net/index.php\">
                <img src=\"https://paintballevents.net/img/favicon_32x32.png\" class=\"me-2\" alt=\"a shiny red and green paintball\" width=\"30\" height=\"30\" class=\"d-inline-block align-text-top\">
                <span class=\"font-mono fw-bold\" style=\"font-size: clamp(16px, 2vw, 20px);\">Paintballevents.net</span>
              </a>
              <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
                <span class=\"navbar-toggler-icon\"></span>
              </button>
              <div class=\"collapse navbar-collapse justify-content-end\" id=\"navbarSupportedContent\">
                <ul class=\"navbar-nav mb-2 mb-lg-0\">
                  <li class=\"nav-item\" style=\"margin-right: 16px;\">
                    <a class=\"nav-link active text-white font-mono\" aria-current=\"page\" href=\"https://paintballevents.net/submit_event.php\"><i class=\"ci ci-document-add\"></i> Submit an event</a>
                  </li>
                  <li class=\"nav-item\" style=\"margin-right: 16px;\">
                    <a class=\"nav-link active text-white font-mono\" href=\"https://paintballevents.net/about.php\"><i class=\"ci ci-information\"></i> About</a>
                  </li>
                  <li class=\"nav-item\" style=\"margin-right: 16px;\">
                    <a class=\"nav-link active text-white font-mono\" href=\"https://paintballevents.net/fields_map.php\"><i class=\"ci ci-location\"></i> Find a paintball field</a>
                  </li>
                  <li class=\"nav-item\" style=\"margin-right: 16px;\">
                    <a class=\"nav-link active text-white font-mono\" href=\"https://paintballevents.net/admin.php\"><i class=\"ci ci-dashboard\"></i> Dashboard</a>
                  </li>
                  <li class=\"nav-item text-center\">
                    <form action=\"\" method=\"post\">
                      <input type=\"submit\" class=\"nav-link active text-nowrap form-control btn bg-danger text-white font-mono\" name=\"Logoutform\" value=\"Log out\"/>
                    </form>
                  </li>
                </ul>
              </div>
            </div>
          </nav>";
    } else {
        // Non-admin nav
        $nav = "<nav class=\"container-fluid navbar navbar-expand-lg navbar-dark\" style=\"background-color:#000000;\" role=\"navigation\">
                <div class=\"container-fluid\">
                  <a class=\"text-decoration-none text-white d-flex align-items-center\" href=\"https://paintballevents.net/index.php\">
                      <img src=\"https://paintballevents.net/img/favicon_32x32.png\" class=\"me-2\" alt=\"a shiny red and green paintball\" width=\"30\" height=\"30\">
                      <span class=\"font-mono fw-bold\" style=\"font-size: clamp(16px, 2vw, 20px);\">Paintballevents.net</span>
                  </a>
                  <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
                    <span class=\"navbar-toggler-icon\"></span>
                  </button>
                  <div class=\"collapse navbar-collapse justify-content-end\" id=\"navbarSupportedContent\">
                    <ul class=\"navbar-nav mb-2 mb-lg-0\">
                      <li class=\"nav-item\" style=\"margin-right: 16px;\">
                        <a class=\"nav-link active text-white font-mono\" aria-current=\"page\" href=\"https://paintballevents.net/submit_event.php\"><i class=\"ci ci-document-add\"></i> Submit an event</a>
                      </li>
                      <li class=\"nav-item\" style=\"margin-right: 16px;\">
                        <a class=\"nav-link active text-white font-mono\" href=\"https://paintballevents.net/about.php\"><i class=\"ci ci-information\"></i> About</a>
                      </li>
                      <li class=\"nav-item\">
                        <a class=\"nav-link active text-white font-mono\" href=\"https://paintballevents.net/fields_map.php\"><i class=\"ci ci-location\"></i> Find a paintball field</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </nav>";
    }

    // SQL query to get event and field details based on the eventID
    $sql = "SELECT 
            e.eventName, e.eventCity, e.eventState, e.eventURL, e.eventFieldName, 
            e.eventStartDate, e.eventEndDate, e.eventSlug, e.freeCamping, e.showers,
            e.hotelAffiliateLink,
            f.fieldID, f.paintballFieldName, f.paintballFieldWebsite, f.latitude, f.longitude, 
            f.paintballFieldStreetAddress, f.paintballFieldCity, f.paintballFieldState, 
            f.paintballFieldZipcode, f.googlemapShortLink, f.paintballFieldFacebookPage, 
            f.paintballFieldInstagramPage, f.paintballFieldTiktokPage, 
            f.fullAddress, f.country_code
        FROM events e 
        JOIN fields f ON e.fieldID = f.fieldID
        WHERE e.eventID = ?";

    // Prepare statement to avoid SQL injection
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $eventID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if event exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Assign values from the database to PHP variables
        $eventName = $row['eventName'];
        $eventCity = $row['eventCity'];
        $eventState = $row['eventState'];
        $eventURL = $row['eventURL'];
        $fieldID = $row['fieldID'];  // Add this line here
        $eventFieldName = $row['eventFieldName'];
        $eventStartDate = date('F j, Y', strtotime($row['eventStartDate']));
        $eventEndDate = date('F j, Y', strtotime($row['eventEndDate']));
        $eventSlug = $row['eventSlug']; // Store eventSlug in a variable
        $paintballFieldName = str_replace("'", "\\'", $row['paintballFieldName']); // Escape single quotes
        $paintballFieldWebsite = $row['paintballFieldWebsite'];
        $fieldAddress = $row['paintballFieldStreetAddress'] . ', ' . $row['paintballFieldCity'] . ', ' . $row['paintballFieldState'] . ' ' . $row['paintballFieldZipcode'];
        $latitude = $row['latitude'];
        $longitude = $row['longitude'];
        $googlemapShortLink = $row['googlemapShortLink'];
        $facebookPage = $row['paintballFieldFacebookPage'];
        $instagramPage = $row['paintballFieldInstagramPage'];
        $tiktokPage = $row['paintballFieldTiktokPage'];
        $countryCode = $row['country_code'];
        $fullAddress = $row['fullAddress'];
        $hotelAffiliateLink = $row['hotelAffiliateLink'];



        // Check if eventStartDate and eventEndDate are the same
        if ($row['eventStartDate'] == $row['eventEndDate']) {
            $dateDisplay = date('F j, Y', strtotime($row['eventStartDate']));
        } else {
            $eventStartMonth = date('F', strtotime($row['eventStartDate']));
            $eventStartDay = date('j', strtotime($row['eventStartDate']));
            $eventEndDay = date('j', strtotime($row['eventEndDate']));
            $eventYear = date('Y', strtotime($row['eventEndDate']));
            
            // Check if the event is within the same month or not
            if ($eventStartMonth == date('F', strtotime($row['eventEndDate']))) {
                $dateDisplay = "$eventStartMonth $eventStartDay-$eventEndDay $eventYear";
            } else {
                $eventEndMonth = date('F', strtotime($row['eventEndDate']));
                $dateDisplay = "$eventStartMonth $eventStartDay - $eventEndMonth $eventEndDay, $eventYear";
            }
        }

        //If the countryCode is GBR (United Kingdom), show the full address instead of City, State format we use in USA and Canada
        $displayAddress = ($countryCode === 'GBR') ? $fullAddress : $fieldAddress;

        // Build the HTML string
        $html_string = "
        <!DOCTYPE html>
        <html lang=\"en\">
        <head>
            <meta charset=\"utf-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
            <link rel=\"canonical\" href=\"https://paintballevents.net/event/$eventSlug.php\">
            <meta name=\"description\" content=\"Find info on upcoming paintball events, big games, paintball events near $eventCity $eventState\">
            <meta name=\"keywords\" content=\"paintball, paintball events, scenario games, big game, paintball big game, scenario paintball, paintball fields, paintball scenario, paintball event listings, upcoming paintball events near $eventCity $eventState, paintball scenarios near me\">
            <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css\" rel=\"stylesheet\">
            <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css\">
            <link rel=\"stylesheet\" href=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.css\" />
            <!-- IBM Plex Sans Font -->
            <link href=\"https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap\" rel=\"stylesheet\">
            <!-- SVG Favicon -->
            <link rel=\"icon\" href=\"../img/favicon.svg\" type=\"image/svg+xml\" alt=\"A shiny red and green paintball icon\">
            <script src=\"https://unpkg.com/leaflet@1.7.1/dist/leaflet.js\"></script>
            <title>$eventName | Paintballevents.net</title>
            $head_includes

            <!-- PostHog Web Analytics -->
            <script>
                !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(\".\");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement(\"script\")).type=\"text/javascript\",p.crossOrigin=\"anonymous\",p.async=!0,p.src=s.api_host.replace(\".i.posthog.com\",\"-assets.i.posthog.com\")+\"/static/array.js\",(r=t.getElementsByTagName(\"script\")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a=\"posthog\",u.people=u.people||[],u.toString=function(t){var e=\"posthog\";return\"posthog\"!==a&&(e+=\".\"+a),t||(e+=\" (stub)\"),e},u.people.toString=function(){return u.toString(1)+\".people (stub)\"},o=\"init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug\".split(\" \"),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
                posthog.init('phc_X0NrbhWQZ9F3RWxoY3P2vgDceK5Nge6a3HHsY0wnMd9', {
                    api_host:'https://us.i.posthog.com',
                    person_profiles: 'identified_only' // or 'always' to create profiles for anonymous users as well
                })
            </script>
            <style>
                :root {
                    --bs-body-font-family: 'IBM Plex Mono', monospace;
                    --bs-font-sans-serif: 'IBM Plex Sans', sans-serif;
                    --bs-font-monospace: 'IBM Plex Mono', monospace;
                }
                
                /* Add monospace font classes with increased specificity */
                .font-mono,
                .navbar .font-mono,
                .navbar-brand .font-mono,
                span.font-mono {
                    font-family: var(--bs-font-monospace) !important;
                }
                
                pre, code {
                    font-family: var(--bs-font-monospace) !important;
                }
                
                .navbar-nav .nav-item {
                    margin-right: 16px !important;
                }
                
                .navbar-nav .nav-item:last-child {
                    margin-right: 0 !important;
                }

                @media (min-width: 992px) {
                    .navbar-brand .font-mono,
                    .navbar .font-mono {
                        font-size: 20px !important;
                    }
                }
                
                #map {
                    height: 50vh;
                }
                body, html {
                    height: 100%;
                    margin: 0;
                    display: flex;
                    flex-direction: column;
                    background-color: #161616;
                    color: #e0e0e0;
                    font-family: 'IBM Plex Sans', sans-serif;
                }
                .bg-dark {
                    background-color: #161616 !important;
                }
                .card {
                    background-color: #2d2d2d;
                    border-color: #404040;
                }
                .card-body {
                    background-color: #2d2d2d;
                }
                .btn-primary {
                    background-color: #0f62fe;
                    border: none;
                    border-radius: 0;
                    color: #ffffff;
                    font-family: 'IBM Plex Sans', sans-serif;
                    font-size: 0.875rem;
                    font-weight: 400;
                    letter-spacing: 0.16px;
                    padding: 0.5rem 1rem;
                }
                .btn-primary.btn-xl {
                    font-size: 1.25rem;
                    padding: 1rem 2rem;
                    font-weight: 500;
                }
                .btn-outline-light {
                    border-radius: 0;
                    font-family: 'IBM Plex Sans', sans-serif;
                    font-size: 0.875rem;
                    letter-spacing: 0.16px;
                }
                .btn-outline-primary {
                    border-radius: 0;
                    font-family: 'IBM Plex Sans', sans-serif;
                    font-size: 0.875rem;
                    letter-spacing: 0.16px;
                    color: #ffffff !important;
                }
                .display-5 {
                    color: #e0e0e0;
                    font-family: 'IBM Plex Sans', sans-serif;
                    font-weight: 500;
                }
                .fs-4 {
                    color: #e0e0e0;
                }
                .fs-3 {
                    color: #e0e0e0;
                }
                .fs-5 {
                    color: #e0e0e0;
                }
                a {
                    color: #b8d3ff;
                    text-decoration: underline;
                    text-underline-offset: 2px;
                    text-decoration-thickness: 1px;
                    text-decoration-color: rgba(184, 211, 255, 0.5);
                    font-weight: 500;
                }
                a:hover {
                    color: #d4e5ff;
                    text-decoration-thickness: 1.5px;
                    text-decoration-color: #d4e5ff;
                }
                .breadcrumb {
                    background-color: #161616;
                }
                .breadcrumb-item a {
                    color: #b8d3ff;
                }
                .breadcrumb-item.active {
                    color: #e0e0e0;
                }
                .navbar {
                    background-color: #000000 !important;
                }
                .navbar-dark .navbar-nav .nav-link {
                    color: #e0e0e0;
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

                /* Dark mode map styling */
                .leaflet-container {
                    background-color: #161616 !important;
                }
                
                /* Removed tile filter to show stock Esri satellite imagery */
                
                .leaflet-control-zoom a {
                    background-color: #2d2d2d !important;
                    color: #e0e0e0 !important;
                    border-color: #404040 !important;
                }
                
                .leaflet-control-zoom a:hover {
                    background-color: #363636 !important;
                }
            </style>
        </head>

        <body class=\"bg-dark\">
        " . $nav . "
        <!-- Breadcrumb -->
        <nav aria-label=\"breadcrumb\" class=\"p-3\">
            <ol class=\"breadcrumb mb-0\">
            <p class=\"text-white px-2\"><</p>
                <li class=\"breadcrumb-item\"><a class=\"text-white\" href=\"https://paintballevents.net/index.php\">Back to events</a></li>
            </ol>
        </nav>

            <main class=\"bg-dark mx-auto pb-5 px-3 col-sm-12\">
                <div class=\"container-fluid bg-dark pt-4 px-0 mx-auto\">
                    <div class=\"bg-dark rounded-3\">
                        <div class=\"container-fluid py-5 px-0 mx-auto text-center\">
                            <h1 class=\"display-5 text-white text-center fw-bold\">$eventName</h1>
                            <p class=\"col-md-8 fs-4 text-white text-center mx-auto\">$dateDisplay</p>
                            <div class='d-flex flex-column align-items-center gap-2'>
                                <a href=\"$eventURL\" target=\"_blank\" class=\"btn btn-primary btn-xl mt-3\" type=\"button\">
                                    View event website <i class=\"fa fa-external-link\"></i>
                                </a>
                                <a href=\"data:text/calendar;charset=utf8;base64," . base64_encode(generateIcsContent($row)) . "\" 
                                   download=\"" . $eventSlug . ".ics\" 
                                   class=\"btn btn-outline-primary mt-3\"
                                   id=\"add-to-calendar-button\">
                                    📅 Add to calendar
                                </a>
                            </div>
                        </div>
                    </div>

                    <div id=\"field-info\" class=\"row align-items-md-stretch mx-auto mt-5\">
                        <div class=\"col-md-8 mx-auto\">
                            <div class=\"row\">
                                <div class=\"col-md-6\">
                                    <div class=\"h-100 bg-dark rounded-3 text-center mx-auto d-flex flex-column justify-content-center align-items-center py-4\">                            
                                        <p class=\"fs-3 text-white\"><a href=\"$paintballFieldWebsite\" target=\"_blank\">$paintballFieldName</a></p>
                                        <div class=\"d-flex flex-column align-items-center justify-content-center\">
                                            <p id=\"address\" class=\"text-white fs-5 mb-3\">$displayAddress</p>
                                            <script>
                                                function copyAddress(event) {
                                                    event.preventDefault();
                                                    const addressText = document.getElementById('address').textContent;
                                                    navigator.clipboard.writeText(addressText).then(() => {
                                                        // Change button text temporarily to show success
                                                        const button = event.target.closest('button');
                                                        const originalHtml = button.innerHTML;
                                                        button.innerHTML = '<i class=\"fa fa-check\"></i> Copied!';
                                                        setTimeout(() => {
                                                            button.innerHTML = originalHtml;
                                                        }, 2000);
                                                    }).catch(err => {
                                                        console.error('Failed to copy:', err);
                                                    });
                                                }
                                            </script>
                                            <button onclick=\"copyAddress(event)\" class=\"btn btn-outline-primary text-white text-decoration-none\" title=\"Copy address\">
                                                <i class=\"fa fa-copy\"></i> Copy address
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class=\"col-md-6\">
                                    <div class=\"h-100 px-3 bg-dark rounded-3\">
                                        <div id=\"map\"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        // Check if device should use mobile map behavior
                        // Primary: screen size (mobile/tablet breakpoint)
                        // Secondary: touch capability
                        var isSmallScreen = window.innerWidth <= 768; // Bootstrap md breakpoint
                        var hasTouchSupport = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
                        var shouldUseMobileBehavior = isSmallScreen && hasTouchSupport;
                        
                        // Initialize map with conditional interactions based on device type
                        var map = L.map('map', {
                            dragging: !shouldUseMobileBehavior,
                            touchZoom: !shouldUseMobileBehavior,
                            doubleClickZoom: !shouldUseMobileBehavior,
                            scrollWheelZoom: !shouldUseMobileBehavior,
                            boxZoom: !shouldUseMobileBehavior,
                            keyboard: !shouldUseMobileBehavior,
                            tap: true,
                            zoomControl: !shouldUseMobileBehavior // Hide zoom controls on mobile
                        }).setView([$latitude, $longitude], 8);
                        
                        // On mobile, the map is completely static and allows normal page scrolling
                        
                        // Handle window resize to update behavior dynamically
                        window.addEventListener('resize', function() {
                            var newIsSmallScreen = window.innerWidth <= 768;
                            var newShouldUseMobileBehavior = newIsSmallScreen && hasTouchSupport;
                            
                            if (newShouldUseMobileBehavior !== shouldUseMobileBehavior) {
                                shouldUseMobileBehavior = newShouldUseMobileBehavior;
                                
                                if (shouldUseMobileBehavior) {
                                    // Switch to mobile behavior - disable all interactions
                                    map.dragging.disable();
                                    map.touchZoom.disable();
                                    map.doubleClickZoom.disable();
                                    map.scrollWheelZoom.disable();
                                    map.boxZoom.disable();
                                    map.keyboard.disable();
                                    map.zoomControl.remove();
                                } else {
                                    // Switch to desktop behavior - enable all interactions
                                    map.dragging.enable();
                                    map.touchZoom.enable();
                                    map.doubleClickZoom.enable();
                                    map.scrollWheelZoom.enable();
                                    map.boxZoom.enable();
                                    map.keyboard.enable();
                                    map.zoomControl.addTo(map);
                                }
                            }
                        });
                        
                        // Base satellite layer
                        var satelliteLayer = L.tileLayer(\"https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}\", {
                            maxZoom: 19,
                            attribution: \"Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community\",
                            useCache: true,
                            crossOrigin: true
                        });

                        // Esri Reference layer for state boundaries and geographic features
                        var referenceLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                            attribution: 'Tiles &copy; Esri',
                            pane: 'overlayPane',
                            zIndex: 500,
                            opacity: 0.7,
                            useCache: true,
                            crossOrigin: true
                        });

                        // Add both layers to map
                        satelliteLayer.addTo(map);
                        referenceLayer.addTo(map);

                        // Create a custom bright marker icon for better visibility in dark mode
                        var brightMarkerIcon = L.icon({
                            iconUrl: \"https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png\",
                            shadowUrl: \"https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png\",
                            iconSize: [25, 41],
                            iconAnchor: [12, 41],
                            popupAnchor: [1, -34],
                            shadowSize: [41, 41]
                        });
                        
                        L.marker([$latitude, $longitude], {icon: brightMarkerIcon}).addTo(map)
                            .bindPopup(\"<a href='$paintballFieldWebsite' target='_blank'>$paintballFieldName</a>\");
                    </script>


                    <!-- Buy me a coffee button -->
                    <div class=\"text-center pb-5\">
                        <a href=\"https://www.buymeacoffee.com/paintballevents\" target=\"_blank\" rel=\"noopener noreferrer\">
                            <img src=\"https://img.buymeacoffee.com/button-api/?text=Help us grow the sport&emoji=❤️&slug=paintballevents&button_colour=2f6f36&font_colour=ffffff&font_family=Inter&outline_colour=ffffff&coffee_colour=FFDD00\" alt=\"Help keep this site running\" style=\"max-width: 100%; height: auto;\" />
                        </a>
                    </div>
                </div>
            </main>
        " . $bootstrap_javascript_includes . "
        </body>
        </html>";

        // Print the complete HTML string
        echo $html_string;
    } else {
        echo "Event not found.";
    }

    // Close connection
    $stmt->close();
    $conn->close();
}

function generateIcsContent($event) {
    // Clean strings for iCal
    $summary = preg_replace('/[^a-zA-Z0-9 \-]/', '', $event['eventName']);
    $location = preg_replace('/[^a-zA-Z0-9 \-\,]/', '', 
        $event['paintballFieldName'] . ', ' . 
        $event['paintballFieldStreetAddress'] . ', ' . 
        $event['paintballFieldCity'] . ', ' . 
        $event['paintballFieldState'] . ' ' . 
        $event['paintballFieldZipcode']
    );
    
    // Generate unique identifier
    $uid = md5($event['eventID'] . $event['eventName'] . $event['eventStartDate']);
    
    return "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//paintballevents.net//EN
BEGIN:VEVENT
UID:{$uid}
DTSTAMP:" . date('Ymd') . "
DTSTART;VALUE=DATE:" . date('Ymd', strtotime($event['eventStartDate'])) . "
DTEND;VALUE=DATE:" . date('Ymd', strtotime($event['eventEndDate'] . ' +1 day')) . "
SUMMARY:{$summary}
LOCATION:{$location}
DESCRIPTION:Paintball event at {$event['paintballFieldName']}. More info at: {$event['eventURL']}
URL:{$event['eventURL']}
END:VEVENT
END:VCALENDAR";
}

?>