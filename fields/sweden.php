<?php 
session_start();
require "../shared.php";
require_once "../dbconn.inc.php";

if (!isset($_SESSION['system_access'])){
    header("Location: ../login.php");
    exit;
}

// Function to retrieve and print all paintball fields as cards
function printAllFieldsAsCards() {
    $conn = dbConnect();
    $result = "<div class='col-sm-12 col-md-6 col-lg-5 col-xl-5 mx-auto my-4'>";

    $sql = "SELECT fieldID, paintballFieldName, paintballFieldWebsite, paintballFieldWebsiteEventPage, 
        fullAddress, googlemapShortLink, paintballFieldFacebookPage, paintballFieldInstagramPage, paintballFieldTiktokPage, 
        isPublished
        FROM `fields` 
        WHERE country_code = 'SWE' AND isPublished = 0
        ORDER BY paintballFieldName ASC";


    $stmt = $conn->stmt_init();

    if ($stmt->prepare($sql)) {
        $stmt->execute();
        $stmt->bind_result($fieldID, $name, $website, $eventPage, $fullAddress, 
                   $googleMap, $facebook, $instagram, $tiktok, $isPublished);

        while ($stmt->fetch()) {
            $name = htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8');
            $website = htmlspecialchars($website ?? '', ENT_QUOTES, 'UTF-8');
            $eventPage = htmlspecialchars($eventPage ?? '', ENT_QUOTES, 'UTF-8');
            $fullAddress = htmlspecialchars($fullAddress ?? '', ENT_QUOTES, 'UTF-8');
            $googleMap = htmlspecialchars($googleMap ?? '', ENT_QUOTES, 'UTF-8');
            $facebook = htmlspecialchars($facebook ?? '', ENT_QUOTES, 'UTF-8');
            $instagram = htmlspecialchars($instagram ?? '', ENT_QUOTES, 'UTF-8');
            $tiktok = htmlspecialchars($tiktok ?? '', ENT_QUOTES, 'UTF-8');

            $result .= "
            <div class='field-card col-12 mx-auto my-4'>
                <div class='card h-100 shadow-sm'>
                    <div class='card-header bg-light py-3'>
                    <div class='input-group'>
                        <input type='text' class='form-control editable-field fw-bold h5' 
                            data-field-id='$fieldID' 
                            data-field-name='paintballFieldName'
                            value='$name' 
                            placeholder='Field Name'>
                        <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                            <i class='fa fa-save'></i> Save
                        </button>
                    </div>
                </div>

                    <div class='card-body'>
                        <form class='needs-validation' novalidate>
                            <div class='row g-4'>
                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Website URL</label>
                                        <div class='input-group has-validation'>
                                            <input type='url' class='form-control editable-field' 
                                                   data-field-id='$fieldID' 
                                                   data-field-name='paintballFieldWebsite'
                                                   value='$website' 
                                                   placeholder='Website URL'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                            <div class='invalid-feedback'>Please enter a valid URL</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Events Page URL</label>
                                        <div class='input-group has-validation'>
                                            <input type='url' class='form-control editable-field' 
                                                   data-field-id='$fieldID' 
                                                   data-field-name='paintballFieldWebsiteEventPage'
                                                   value='$eventPage' 
                                                   placeholder='Events Page URL'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                            <div class='invalid-feedback'>Please enter a valid URL</div>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Full Address</label>
                                        <div class='input-group has-validation'>
                                            <input type='text' class='form-control editable-field' 
                                                data-field-id='$fieldID' 
                                                data-field-name='fullAddress'
                                                value='$fullAddress' 
                                                placeholder='Full Address'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Google Maps Link</label>
                                        <div class='input-group has-validation'>
                                            <input type='url' class='form-control editable-field' 
                                                   data-field-id='$fieldID' 
                                                   data-field-name='googlemapShortLink'
                                                   value='$googleMap' 
                                                   placeholder='Google Maps URL'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                            <div class='invalid-feedback'>Please enter a valid URL</div>
                                        </div>" . 
                                        (!empty($googleMap) ? "<div class='mt-2'>
                                            <a href='$googleMap' target='_blank' class='btn btn-outline-success btn-sm'>
                                                <i class='fa fa-map-marker'></i> Open in Google Maps
                                            </a>
                                        </div>" : "") . "
                                    </div>
                                </div>

                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Facebook</label>
                                        <div class='input-group has-validation'>
                                            <input type='url' class='form-control editable-field' 
                                                   data-field-id='$fieldID' 
                                                   data-field-name='paintballFieldFacebookPage'
                                                   value='$facebook' 
                                                   placeholder='Facebook URL'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                            <div class='invalid-feedback'>Please enter a valid URL</div>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Instagram</label>
                                        <div class='input-group has-validation'>
                                            <input type='url' class='form-control editable-field' 
                                                   data-field-id='$fieldID' 
                                                   data-field-name='paintballFieldInstagramPage'
                                                   value='$instagram' 
                                                   placeholder='Instagram URL'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                            <div class='invalid-feedback'>Please enter a valid URL</div>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>TikTok</label>
                                        <div class='input-group has-validation'>
                                            <input type='url' class='form-control editable-field' 
                                                   data-field-id='$fieldID' 
                                                   data-field-name='paintballFieldTiktokPage'
                                                   value='$tiktok' 
                                                   placeholder='TikTok URL'>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                            <div class='invalid-feedback'>Please enter a valid URL</div>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-12'>
                                    <div class='form-group'>
                                        <label class='form-label fw-semibold mb-2'>Published</label>
                                        <div class='input-group has-validation'>
                                            <select class='form-control editable-field' 
                                                    data-field-id='$fieldID' 
                                                    data-field-name='isPublished'>
                                                <option value='1' " . ($isPublished ? "selected" : "") . ">True</option>
                                                <option value='0' " . (!$isPublished ? "selected" : "") . ">False</option>
                                            </select>
                                            <button type='button' class='btn btn-primary save-button d-none' title='Save changes'>
                                                <i class='fa fa-save'></i> Save
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class='col-12 text-end'>
                                    <button type='button' class='btn btn-danger delete-button' 
                                            data-field-id='$fieldID' 
                                            title='Delete field'>
                                        <i class='fa fa-trash'></i> Delete
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>";
        }
    } else {
        $result .= "<div class='col-12'><div class='alert alert-danger'>Error fetching paintball fields.</div></div>";
    }

    $stmt->close();
    $result .= "</div>";
    return $result;
}

// Function to count fields so we don't have to deal with scope issues in printAllFieldsAsCards()
function countFields() {
    $conn = dbConnect();
    $counter = 0;

    $sql = "SELECT COUNT(*) FROM `fields` WHERE country_code = 'SWE' AND isPublished = 0";
    $stmt = $conn->stmt_init();

    if ($stmt->prepare($sql)) {
        $stmt->execute();
        $stmt->bind_result($counter);
        $stmt->fetch();
    }

    $stmt->close();
    return $counter;
}

// Handle AJAX request to update field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_field') {
    error_reporting(0);
    ini_set('display_errors', 0);
    
    header('Content-Type: application/json');
    
    try {
        $fieldID = $_POST['field_id'] ?? null;
        $fieldName = $_POST['field_name'] ?? null;
        $fieldValue = $_POST['field_value'] ?? null;

        if (!$fieldID || !$fieldName || $fieldValue === null) {
            throw new Exception('Invalid input');
        }

        $allowedFields = [
            'paintballFieldWebsite',
            'paintballFieldWebsiteEventPage',
            'fullAddress',
            'googlemapShortLink',
            'paintballFieldFacebookPage',
            'paintballFieldInstagramPage',
            'paintballFieldTiktokPage',
            'isPublished',
            'paintballFieldName'
        ];
        
        

        if (!in_array($fieldName, $allowedFields)) {
            throw new Exception('Invalid field name');
        }

        $conn = dbConnect();
        $sql = "UPDATE `fields` SET `$fieldName` = ? WHERE fieldID = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Statement preparation failed: ' . $conn->error);
        }

        $stmt->bind_param('si', $fieldValue, $fieldID);
        $result = $stmt->execute();
        
        if (!$result) {
            throw new Exception('Update failed: ' . $stmt->error);
        }

        echo json_encode(['status' => 'success']);
        $stmt->close();
        exit;
    } catch (Exception $e) {
        error_log('Field update error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        exit;
    }
}

//Handle AJAX request to delete field
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_field') {
    error_reporting(0);
    ini_set('display_errors', 0);
    
    header('Content-Type: application/json');

    try {
        $fieldID = $_POST['field_id'] ?? null;

        if (!$fieldID) {
            throw new Exception('Invalid field ID');
        }

        $conn = dbConnect();
        $sql = "DELETE FROM `fields` WHERE fieldID = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception('Statement preparation failed: ' . $conn->error);
        }

        $stmt->bind_param('i', $fieldID);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception('Delete failed: ' . $stmt->error);
        }

        echo json_encode(['status' => 'success']);
        $stmt->close();
        exit;
    } catch (Exception $e) {
        error_log('Field delete error: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Paintball, Events, Scenario, Big Game, Calendar, Upcoming">
    <title>🇸🇪 Swedish fields</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- FontAwesome icons -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    
    <!-- My CSS -->
    <link rel="stylesheet" href="../styles.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    
    <!-- Favicon links -->
    <link rel="icon" href="../img/favicon.svg" type="image/svg+xml">
    <link rel="icon" href="../img/favicon_16x16.png" type="image/png" sizes="16x16">
    <link rel="icon" href="../img/favicon_32x32.png" type="image/png" sizes="32x32">
    <link rel="icon" href="../img/favicon_48x48.png" type="image/png" sizes="48x48">
    <link rel="icon" href="../img/favicon_128x128.png" type="image/png" sizes="128x128">
    
    <style>
        .card {
            transition: all 0.2s ease-in-out;
        }
        .card:hover {
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
        .form-label {
            color: #555;
        }
        .invalid-feedback {
            font-size: 80%;
        }
        .save-button {
            transition: all 0.2s ease-in-out;
        }
        .save-button:hover {
            transform: scale(1.05);
        }
        .field-card.selected {
            outline: 10px solid green;
        }
    </style>
</head>
<body>
    <?php echo $nav ?>
    
    <h1 class="text-white mx-auto mt-5">🇸🇪 We found <?php echo countFields(); ?> unpublished fields in Sweden</h1>

    <?php
    if (isset($_SESSION['field_added_message'])) { 
        echo $_SESSION['field_added_message'];
        unset($_SESSION['field_added_message']);
    }
    ?>

    <!-- Display paintball fields as cards -->
    <div class="container-fluid px-4 py-3">
        <?php echo printAllFieldsAsCards(); ?>
    </div>

    <!-- Bootstrap and custom JavaScript -->
    <?php echo $bootstrap_javascript_includes; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editableFields = document.querySelectorAll('.editable-field');
        
        editableFields.forEach(field => {
            field.addEventListener('input', function() {
                const saveButton = this.nextElementSibling;
                saveButton.classList.remove('d-none');
            });

            const saveButton = field.nextElementSibling;
            saveButton.addEventListener('click', function() {
                const fieldID = field.getAttribute('data-field-id');
                const fieldName = field.getAttribute('data-field-name');
                const fieldValue = field.value.trim();

                // AJAX request to save the field
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_field&field_id=${fieldID}&field_name=${fieldName}&field_value=${encodeURIComponent(fieldValue)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        this.classList.add('d-none');
                        field.classList.add('is-valid');
                        setTimeout(() => {
                            field.classList.remove('is-valid');
                        }, 2000);
                    } else {
                        field.classList.add('is-invalid');
                        setTimeout(() => {
                            field.classList.remove('is-invalid');
                        }, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    field.classList.add('is-invalid');
                });
            });
        });
    });
    </script>

    <!-- Javascript for highlighting field cards -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const fieldCards = document.querySelectorAll('.field-card');

        fieldCards.forEach(card => {
        card.addEventListener('click', function(e) {
        // Remove 'selected' class from all field cards
        fieldCards.forEach(c => c.classList.remove('selected'));

        // Add 'selected' class to the clicked field card
        this.classList.add('selected');

        // Prevent click from bubbling up
        e.stopPropagation();
        });
        });

        // Remove selection if clicking outside of any field card
        document.addEventListener('click', function() {
            fieldCards.forEach(c => c.classList.remove('selected'));
            });
        });
    </script>

<!-- Javascript for deleting fields -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-button');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const fieldID = this.getAttribute('data-field-id');
                const card = this.closest('.field-card');

                if (confirm('Are you sure you want to delete this field? This action cannot be undone.')) {
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete_field&field_id=${fieldID}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            card.remove();
                            alert('Field deleted successfully.');
                        } else {
                            alert('Failed to delete the field.');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the field.');
                    });
                }
            });
        });
    });
</script>


</body>
</html>