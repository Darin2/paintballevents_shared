<?php
session_start();
require "shared.php";
require "dbconn.inc.php";

if (!isset($_SESSION['system_access'])) {
    header("Location: login.php");
    exit;
}

$conn = dbConnect();

// Thai provinces array
$provinces = array(
    'BKK' => 'Bangkok',
    'S' => 'Samut Prakan',
    'N' => 'Nonthaburi',
    'PB' => 'Pathum Thani',
    'AY' => 'Phra Nakhon Si Ayutthaya',
    'AN' => 'Ang Thong',
    'LB' => 'Lopburi',
    'SP' => 'Sing Buri',
    'CB' => 'Chai Nat',
    'SN' => 'Saraburi',
    'CT' => 'Chon Buri',
    'RY' => 'Rayong',
    'CD' => 'Chanthaburi',
    'TR' => 'Trat',
    'CH' => 'Chachoengsao',
    'PC' => 'Prachin Buri',
    'NM' => 'Nakhon Nayok',
    'SA' => 'Sa Kaeo',
    'NK' => 'Nakhon Ratchasima',
    'BH' => 'Buri Ram',
    'SR' => 'Surin',
    'SI' => 'Si Sa Ket',
    'UB' => 'Ubon Ratchathani',
    'YS' => 'Yasothon',
    'CT' => 'Chaiyaphum',
    'AM' => 'Amnat Charoen',
    'BK' => 'Bueng Kan',
    'NP' => 'Nong Bua Lamphu',
    'KK' => 'Khon Kaen',
    'UD' => 'Udon Thani',
    'LE' => 'Loei',
    'NW' => 'Nong Khai',
    'MH' => 'Maha Sarakham',
    'RO' => 'Roi Et',
    'KL' => 'Kalasin',
    'SK' => 'Sakon Nakhon',
    'NP' => 'Nakhon Phanom',
    'MD' => 'Mukdahan',
    'CM' => 'Chiang Mai',
    'LH' => 'Lamphun',
    'LP' => 'Lampang',
    'UR' => 'Uttaradit',
    'PR' => 'Phrae',
    'NAN' => 'Nan',
    'PY' => 'Phayao',
    'CR' => 'Chiang Rai',
    'MH' => 'Mae Hong Son',
    'NB' => 'Nakhon Sawan',
    'UT' => 'Uthai Thani',
    'KP' => 'Kamphaeng Phet',
    'TK' => 'Tak',
    'SU' => 'Sukhothai',
    'PH' => 'Phitsanulok',
    'PI' => 'Phichit',
    'PT' => 'Phetchabun',
    'RB' => 'Ratchaburi',
    'PB' => 'Phetchaburi',
    'PK' => 'Prachuap Khiri Khan',
    'KR' => 'Kanchanaburi',
    'SP' => 'Suphan Buri',
    'NT' => 'Nakhon Pathom',
    'SM' => 'Samut Sakhon',
    'SS' => 'Samut Songkhram',
    'NS' => 'Nakhon Si Thammarat',
    'KR' => 'Krabi',
    'PG' => 'Phang Nga',
    'PU' => 'Phuket',
    'SU' => 'Surat Thani',
    'RN' => 'Ranong',
    'CU' => 'Chumphon',
    'SG' => 'Songkhla',
    'ST' => 'Satun',
    'TR' => 'Trang',
    'PT' => 'Phatthalung',
    'PL' => 'Pattani',
    'YL' => 'Yala',
    'NR' => 'Narathiwat'
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
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'THA', 0)";

        if ($stmt->prepare($sql)) {
            $stmt->bind_param('sssssssssssssss', 
                $paintballFieldName, $paintballFieldWebsite, $paintballFieldWebsiteEventPage,
                $paintballFieldStreetAddress, $paintballFieldCity, $paintballFieldState, 
                $paintballFieldZipcode, $googlemapShortLink, $paintballFieldFacebookPage, 
                $paintballFieldInstagramPage, $paintballFieldTiktokPage, $latitude, 
                $longitude, $_SESSION['current_user'], $todaysDate);

            if ($stmt->execute()) {
                $_SESSION['add_field_msg'] = "<div class='alert alert-success'>
                    <h4>Success!</h4>
                    <p>Thank you for submitting a Thai paintball field. It will be reviewed and added to the database.</p>
                    <a href='add_thailand_field.php' class='btn btn-primary'>Submit Another Field</a>
                </div>";
            } else {
                $_SESSION['add_field_msg'] = "<div class='alert alert-danger'>
                    <h4>Error</h4>
                    <p>There was an error submitting the field: " . $stmt->error . "</p>
                </div>";
            }
        }
    } else {
        $form_message = "<div class='alert alert-warning'>
            <h4>Missing Required Fields</h4>
            <p>Please fill out the following required fields:</p>
            <ul>";
        foreach ($missing as $m) {
            $form_message .= "<li>" . ucwords(str_replace('paintballField', '', $m)) . "</li>";
        }
        $form_message .= "</ul></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Thai Paintball Field</title>
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
                <h1 class="text-center mb-4">Add Thai Paintball Field</h1>
                
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
                            Required Information
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="paintballFieldName" class="form-label">Field Name *</label>
                                <input type="text" class="form-control" id="paintballFieldName" name="paintballFieldName" required>
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldStreetAddress" class="form-label">Street Address *</label>
                                <input type="text" class="form-control" id="paintballFieldStreetAddress" name="paintballFieldStreetAddress" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldCity" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="paintballFieldCity" name="paintballFieldCity" required>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldState" class="form-label">Province *</label>
                                    <select class="form-select" id="paintballFieldState" name="paintballFieldState" required>
                                        <option value="">Select Province</option>
                                        <?php foreach ($provinces as $code => $name): ?>
                                            <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="paintballFieldZipcode" class="form-label">Postal Code *</label>
                                    <input type="text" class="form-control" id="paintballFieldZipcode" name="paintballFieldZipcode" pattern="^\d{5}$" required>
                                    <div class="form-text">Format: 12345 (5 digits)</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="latitude" class="form-label">Latitude *</label>
                                    <input type="text" class="form-control" id="latitude" name="latitude" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="longitude" class="form-label">Longitude *</label>
                                    <input type="text" class="form-control" id="longitude" name="longitude" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Optional Fields -->
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            Additional Information (Optional)
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="paintballFieldWebsite" class="form-label">Website</label>
                                <input type="url" class="form-control" id="paintballFieldWebsite" name="paintballFieldWebsite">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldWebsiteEventPage" class="form-label">Event Page URL</label>
                                <input type="url" class="form-control" id="paintballFieldWebsiteEventPage" name="paintballFieldWebsiteEventPage">
                            </div>

                            <div class="mb-3">
                                <label for="googlemapShortLink" class="form-label">Google Maps Shortlink</label>
                                <input type="text" class="form-control" id="googlemapShortLink" name="googlemapShortLink">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldFacebookPage" class="form-label">Facebook Page</label>
                                <input type="url" class="form-control" id="paintballFieldFacebookPage" name="paintballFieldFacebookPage">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldInstagramPage" class="form-label">Instagram Page</label>
                                <input type="url" class="form-control" id="paintballFieldInstagramPage" name="paintballFieldInstagramPage">
                            </div>

                            <div class="mb-3">
                                <label for="paintballFieldTiktokPage" class="form-label">TikTok Page</label>
                                <input type="url" class="form-control" id="paintballFieldTiktokPage" name="paintballFieldTiktokPage">
                            </div>
                        </div>
                    </div>

                    <div class="text-center mb-4">
                        <button type="submit" name="submit_field" class="btn btn-primary btn-lg">Submit Field</button>
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

        // Thai Postal Code validation
        document.getElementById('paintballFieldZipcode').addEventListener('input', function(e) {
            let value = this.value;
            // Remove any non-numeric characters
            value = value.replace(/[^0-9]/g, '');
            // Limit to 5 digits
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            this.value = value;
        });
    </script>
</body>
</html> 