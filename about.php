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
    <title>About Paintballevents.net</title>
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

    <!-- PostHog Web Analytics -->
    <script>
        !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
        posthog.init('phc_X0NrbhWQZ9F3RWxoY3P2vgDceK5Nge6a3HHsY0wnMd9', {
            api_host: 'https://us.i.posthog.com',
            person_profiles: 'identified_only' // or 'always' to create profiles for anonymous users as well
        });
    </script>
</head>

<body class="bg-dark">
    <?php echo $nav ?>
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="p-3">
            <ol class="breadcrumb mb-0">
            <p class="text-white px-2"><</p>
                <li class="breadcrumb-item"><a class="text-white" href="index.php">Back to events</a></li>
            </ol>
    </nav>
    <div class="mx-auto col-sm-12 col-md-6 col-lg-6 col-xl-4 my-4 px-4">
     
        <h1 class="text-white">Hey! 👋</h1>
        <br>  
        <p class="text-white">My name is Darin and I made this site to make it wicked easy to find paintball upcoming events.</p>
        <br>
        <p class="text-white">You can support this project by <a class="text-white" href="submit_event.php">submitting events</a>, <a class="text-white" href="https://paintballevents.canny.io/ideas">sharing your ideas</a> or clicking the button below.</p>
        <div class="my-3">
            <a href="https://www.buymeacoffee.com/paintballevents"><img src="https://img.buymeacoffee.com/button-api/?text=Help keep this site running&emoji=❤️&slug=paintballevents&button_colour=2f6f36&font_colour=ffffff&font_family=Inter&outline_colour=ffffff&coffee_colour=FFDD00" style="max-width: 100%; height: auto;" /></a>
        </div>
        <br>
        <p class="text-white">P.S. - I'm not affiliated with any paintball fields or events. <a class="text-white" href="https://forms.gle/zxHHTPaoMSWRnzcdA">Reach out</a> if you have any questions or comments.</a>
    </div>

    <?php echo $footer ?>
    <?php echo $bootstrap_javascript_includes; ?>

</body>
</html>
