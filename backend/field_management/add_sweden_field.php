<?php
session_start();
require "shared.php";
require "dbconn.inc.php";

if (!isset($_SESSION['system_access'])) {
    header("Location: login.php");
    exit;
}

$conn = dbConnect();

// Swedish counties (län) array
$counties = array(
    'AB' => 'Stockholm',
    'C' => 'Uppsala',
    'D' => 'Södermanland',
    'E' => 'Östergötland',
    'F' => 'Jönköping',
    'G' => 'Kronoberg',
    'H' => 'Kalmar',
    'I' => 'Gotland',
    'K' => 'Blekinge',
    'M' => 'Skåne',
    'N' => 'Halland',
    'O' => 'Västra Götaland',
    'S' => 'Värmland',
    'T' => 'Örebro',
    'U' => 'Västmanland',
    'W' => 'Dalarna',
    'X' => 'Gävleborg',
    'Y' => 'Västernorrland',
    'Z' => 'Jämtland',
    'AC' => 'Västerbotten',
    'BD' => 'Norrbotten'
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
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'SWE', 0)";

        if ($stmt->prepare($sql)) {
            $stmt->bind_param('sssssssssssssss', 
                $paintballFieldName, $paintballFieldWebsite, $paintballFieldWebsiteEventPage,
                $paintballFieldStreetAddress, $paintballFieldCity, $paintballFieldState, 
                $paintballFieldZipcode, $googlemapShortLink, $paintballFieldFacebookPage, 
                $paintballFieldInstagramPage, $paintballFieldTiktokPage, $latitude, 
                $longitude, $_SESSION['current_user'], $todaysDate);

            if ($stmt->execute()) {
                $_SESSION['add_field_msg'] = "<div class='alert alert-success'>
                    <h4>Framgång!</h4>
                    <p>Tack för att du skickade in en svensk paintballbana. Den kommer att granskas och läggas till i databasen.</p>
                    <a href='add_sweden_field.php' class='btn btn-primary'>Skicka in en annan bana</a>
                </div>";
            } else {
                $_SESSION['add_field_msg'] = "<div class='alert alert-danger'>
                    <h4>Fel</h4>
                    <p>Det uppstod ett fel när banan skickades in: " . $stmt->error . "</p>
                </div>";
            }
        }
    } else {
        $form_message = "<div class='alert alert-warning'>
            <h4>Obligatoriska fält saknas</h4>
            <p>Vänligen fyll i följande obligatoriska fält:</p>
            <ul>";
        foreach ($missing as $m) {
            $form_message .= "<li>" . ucwords(str_replace('paintballField', '', $m)) . "</li>";
        }
        $form_message .= "</ul></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lägg till svensk paintballbana</title>
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
                <h1 class="text-center mb-4">🇸🇪 Lägg till svensk paintballbana</h1>
                
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
                            Obligatorisk information
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="paintballFieldName" class="form-label">Banans namn *</label>
                                <input type="text" class="form-control" id="paintballFieldName" name="paintballFieldName" required>
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldStreetAddress" class="form-label">Adress *</label>
                                <input type="text" class="form-control" id="paintballFieldStreetAddress" name="paintballFieldStreetAddress" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldCity" class="form-label">Stad *</label>
                                    <input type="text" class="form-control" id="paintballFieldCity" name="paintballFieldCity" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldState" class="form-label">Län *</label>
                                    <select class="form-select" id="paintballFieldState" name="paintballFieldState" required>
                                        <option value="">Välj län</option>
                                        <?php foreach ($counties as $code => $name): ?>
                                            <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldZipcode" class="form-label">Postnummer *</label>
                                    <input type="text" class="form-control" id="paintballFieldZipcode" name="paintballFieldZipcode" pattern="^\d{3}\s?\d{2}$" required>
                                    <div class="form-text">Format: 123 45 eller 12345</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Latitud *</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Longitud *</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional Fields -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            Ytterligare information (valfritt)
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="paintballFieldWebsite" class="form-label">Webbplats</label>
                                <input type="url" class="form-control" id="paintballFieldWebsite" name="paintballFieldWebsite">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldWebsiteEventPage" class="form-label">URL för evenemangsida</label>
                                <input type="url" class="form-control" id="paintballFieldWebsiteEventPage" name="paintballFieldWebsiteEventPage">
                            </div>

                            <div class="mb-3">
                                <label for="googlemapShortLink" class="form-label">Kort Google Maps-länk</label>
                                <input type="text" class="form-control" id="googlemapShortLink" name="googlemapShortLink">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldFacebookPage" class="form-label">Facebook-sida</label>
                                <input type="url" class="form-control" id="paintballFieldFacebookPage" name="paintballFieldFacebookPage">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldInstagramPage" class="form-label">Instagram-sida</label>
                                <input type="url" class="form-control" id="paintballFieldInstagramPage" name="paintballFieldInstagramPage">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldTiktokPage" class="form-label">TikTok-sida</label>
                                <input type="url" class="form-control" id="paintballFieldTiktokPage" name="paintballFieldTiktokPage">
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <button type="submit" name="submit_field" class="btn btn-primary btn-lg">Skicka in bana</button>
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

        // Swedish postal code validation (5 digits with optional space)
        document.getElementById('paintballFieldZipcode').addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            // Add space after 3 digits if there are more than 3 digits
            if (value.length > 3) {
                value = value.substring(0, 3) + ' ' + value.substring(3);
            }
            this.value = value;
        });
    </script>
</body>
</html> 