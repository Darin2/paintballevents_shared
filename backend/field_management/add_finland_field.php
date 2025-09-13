<?php
session_start();
require "shared.php";
require "dbconn.inc.php";

if (!isset($_SESSION['system_access'])) {
    header("Location: login.php");
    exit;
}

$conn = dbConnect();

// Finnish regions (maakunnat) array
$regions = array(
    'FI-01' => 'Ahvenanmaa (Åland)',
    'FI-02' => 'Etelä-Karjala (South Karelia)',
    'FI-03' => 'Etelä-Pohjanmaa (South Ostrobothnia)',
    'FI-04' => 'Etelä-Savo (South Savo)',
    'FI-05' => 'Kainuu',
    'FI-06' => 'Kanta-Häme (Tavastia Proper)',
    'FI-07' => 'Keski-Pohjanmaa (Central Ostrobothnia)',
    'FI-08' => 'Keski-Suomi (Central Finland)',
    'FI-09' => 'Kymenlaakso',
    'FI-10' => 'Lappi (Lapland)',
    'FI-11' => 'Pirkanmaa',
    'FI-12' => 'Pohjanmaa (Ostrobothnia)',
    'FI-13' => 'Pohjois-Karjala (North Karelia)',
    'FI-14' => 'Pohjois-Pohjanmaa (North Ostrobothnia)',
    'FI-15' => 'Pohjois-Savo (North Savo)',
    'FI-16' => 'Päijät-Häme',
    'FI-17' => 'Satakunta',
    'FI-18' => 'Uusimaa',
    'FI-19' => 'Varsinais-Suomi (Southwest Finland)'
);

// Process form submission
if (isset($_POST['submit_field'])) {
    $required = array(
        "paintballFieldName",
        "paintballFieldStreetAddress",
        "paintballFieldCity",
        "paintballFieldState",
        "paintballFieldZipcode",
        "latitude",
        "longitude"
    );

    $expected = array(
        "paintballFieldName",
        "paintballFieldWebsite",
        "paintballFieldWebsiteEventPage",
        "paintballFieldStreetAddress",
        "paintballFieldCity",
        "paintballFieldState",
        "paintballFieldZipcode",
        "googlemapShortLink",
        "paintballFieldFacebookPage",
        "paintballFieldInstagramPage",
        "paintballFieldTiktokPage",
        "latitude",
        "longitude"
    );

    $missing = array();

    foreach ($expected as $input_field) {
        if (in_array($input_field, $required) && empty($_POST[$input_field])) {
            array_push($missing, $input_field);
        } else {
            ${$input_field} = isset($_POST[$input_field]) ? $_POST[$input_field] : "";
        }
    }

    if (empty($missing)) {
        $stmt = $conn->stmt_init();
        $todaysDate = date('Y-m-d');

        $sql = "INSERT INTO `fields` 
        (`paintballFieldName`, `paintballFieldWebsite`, `paintballFieldWebsiteEventPage`, 
        `paintballFieldStreetAddress`, `paintballFieldCity`, `paintballFieldState`, 
        `paintballFieldZipcode`, `googlemapShortLink`, `paintballFieldFacebookPage`, 
        `paintballFieldInstagramPage`, `paintballFieldTikTokPage`, `latitude`, 
        `longitude`, `submittedBy`, `dateAdded`, `country_code`, `isPublished`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'FIN', 0)";

        if ($stmt->prepare($sql)) {
            $stmt->bind_param('sssssssssssssss', 
                $paintballFieldName, $paintballFieldWebsite, $paintballFieldWebsiteEventPage,
                $paintballFieldStreetAddress, $paintballFieldCity, $paintballFieldState, 
                $paintballFieldZipcode, $googlemapShortLink, $paintballFieldFacebookPage, 
                $paintballFieldInstagramPage, $paintballFieldTiktokPage, $latitude, 
                $longitude, $_SESSION['current_user'], $todaysDate);

            if ($stmt->execute()) {
                $_SESSION['add_field_msg'] = "<div class='alert alert-success'>
                    <h4>Onnistui!</h4>
                    <p>Kiitos suomalaisen paintball-kentän lähettämisestä. Se tarkistetaan ja lisätään tietokantaan.</p>
                    <a href='add_finland_field.php' class='btn btn-primary'>Lähetä toinen kenttä</a>
                </div>";
            } else {
                $_SESSION['add_field_msg'] = "<div class='alert alert-danger'>
                    <h4>Virhe</h4>
                    <p>Kentän lähettämisessä tapahtui virhe: " . $stmt->error . "</p>
                </div>";
            }
        }
    } else {
        $form_message = "<div class='alert alert-warning'>
            <h4>Pakolliset kentät puuttuvat</h4>
            <p>Täytä seuraavat pakolliset kentät:</p>
            <ul>";
        foreach ($missing as $m) {
            $form_message .= "<li>" . ucwords(str_replace('paintballField', '', $m)) . "</li>";
        }
        $form_message .= "</ul></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lisää suomalainen paintball-kenttä</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <!-- SVG Favicon -->
    <link rel="icon" href="img/favicon.svg" type="image/svg+xml" alt="A shiny red and green paintball icon">
    <!-- PNG Fallback Favicons with Different Sizes -->
    <link rel="icon" href="img/favicon_16x16.png" type="image/png" sizes="16x16" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_32x32.png" type="image/png" sizes="32x32" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_48x48.png" type="image/png" sizes="48x48" alt="A shiny red and green paintball icon">
    <link rel="icon" href="img/favicon_128x128.png" type="image/png" sizes="128x128" alt="A shiny red and green paintball icon">
</head>
<body>
    <?php echo $nav; ?>
    
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h1 class="text-center mb-4">🇫🇮 Lisää suomalainen paintball-kenttä</h1>
                
                <?php 
                if (isset($form_message)) echo $form_message;
                if (isset($_SESSION['add_field_msg'])) {
                    echo $_SESSION['add_field_msg'];
                    unset($_SESSION['add_field_msg']);
                }
                ?>

                <form method="POST" class="needs-validation" novalidate>
                    <!-- Required Fields -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            Pakolliset tiedot
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="paintballFieldName" class="form-label">Kentän nimi *</label>
                                <input type="text" class="form-control" id="paintballFieldName" name="paintballFieldName" required>
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldStreetAddress" class="form-label">Osoite *</label>
                                <input type="text" class="form-control" id="paintballFieldStreetAddress" name="paintballFieldStreetAddress" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldCity" class="form-label">Kaupunki *</label>
                                    <input type="text" class="form-control" id="paintballFieldCity" name="paintballFieldCity" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldState" class="form-label">Maakunta *</label>
                                    <select class="form-select" id="paintballFieldState" name="paintballFieldState" required>
                                        <option value="">Valitse maakunta</option>
                                        <?php foreach ($regions as $code => $name): ?>
                                            <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldZipcode" class="form-label">Postinumero *</label>
                                    <input type="text" class="form-control" id="paintballFieldZipcode" name="paintballFieldZipcode" pattern="^\d{5}$" required>
                                    <div class="form-text">Muoto: 12345</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Leveysaste *</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Pituusaste *</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional Fields -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            Lisätiedot (valinnainen)
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="paintballFieldWebsite" class="form-label">Verkkosivusto</label>
                                <input type="url" class="form-control" id="paintballFieldWebsite" name="paintballFieldWebsite">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldWebsiteEventPage" class="form-label">Tapahtumien verkkosivun URL</label>
                                <input type="url" class="form-control" id="paintballFieldWebsiteEventPage" name="paintballFieldWebsiteEventPage">
                            </div>

                            <div class="mb-3">
                                <label for="googlemapShortLink" class="form-label">Lyhyt Google Maps -linkki</label>
                                <input type="text" class="form-control" id="googlemapShortLink" name="googlemapShortLink">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldFacebookPage" class="form-label">Facebook-sivu</label>
                                <input type="url" class="form-control" id="paintballFieldFacebookPage" name="paintballFieldFacebookPage">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldInstagramPage" class="form-label">Instagram-sivu</label>
                                <input type="url" class="form-control" id="paintballFieldInstagramPage" name="paintballFieldInstagramPage">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldTiktokPage" class="form-label">TikTok-sivu</label>
                                <input type="url" class="form-control" id="paintballFieldTiktokPage" name="paintballFieldTiktokPage">
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <button type="submit" name="submit_field" class="btn btn-primary btn-lg">Lähetä kenttä</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Finnish postal code validation (5 digits)
        document.getElementById('paintballFieldZipcode').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            this.value = value;
        });
    </script>
</body>
</html> 