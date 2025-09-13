<?php //NOTE: THIS FILE MUST NOT CONTAIN ANY WHITESPACE ABOVE OR BETWEEN PHP OPENING/CLOSING TAGS, OR REDIRECTS MAY NOT WORK

/*********************************************/
/* Building a nav bar component. It's dynamic based on whether user is admin. */
/*********************************************/

// Add IBM Plex font and Carbon icons in the head section
$head_includes = "
<link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
<link rel=\"preconnect\" href=\"https://fonts.gstatic.com\" crossorigin>
<link href=\"https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap\" rel=\"stylesheet\">
<link href=\"https://unpkg.com/@carbon/icons/css/carbon-icons.min.css\" rel=\"stylesheet\">
<style>
  :root {
    --bs-body-font-family: 'IBM Plex Sans', sans-serif;
    --bs-font-sans-serif: 'IBM Plex Sans', sans-serif;
    --bs-font-monospace: 'IBM Plex Mono', monospace;
  }
  
  body {
    font-family: var(--bs-body-font-family);
  }
  
  .navbar {
    font-family: var(--bs-body-font-family);
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
  
  .ci {
    display: inline-block;
    width: 1em;
    height: 1em;
    vertical-align: -0.125em;
    margin-right: 0.5em;
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
</style>";

//if the user is logged in as an admin, print a nav bar that contains admin functionality.
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
}
  //if the user doesn't have admin access, print a nav without admin functionality
  else {
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
                    <a href=\"https://www.buymeacoffee.com/paintballevents\" target=\"_blank\" rel=\"noopener noreferrer\">
                      <img src=\"https://img.buymeacoffee.com/button-api/?text=Help us grow the sport&emoji=❤️&slug=paintballevents&button_colour=2f6f36&font_colour=ffffff&font_family=Inter&outline_colour=ffffff&coffee_colour=FFDD00\" alt=\"Help keep this site running\" style=\"height: 40px;\" />
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </nav>";
  }

//Code for logging out the user when they click the Log Out Button
if (array_key_exists('Logoutform', $_POST)){
session_start();
unset($_SESSION);
session_unset();
session_write_close();
//session_destroy();
$_SESSION = array();

 //Redirect the user to the login page
  header("Location: login.php");
  exit;
}

//This Javascript code goes after the closing body tag. (note 7/1/2024: the bootstrap installation guide recommends placing it BEFORE the closing body tag.)
//Bootstrap components won't work correctly without it (e.g. the nav bar won't close/open correctly)

$bootstrap_javascript_includes ="
<script src=\"https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js\" integrity=\"sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r\" crossorigin=\"anonymous\"></script>
<script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js\" integrity=\"sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy\" crossorigin=\"anonymous\"></script>";

$footer =
"<footer class=\"bg-dark text-white text-center mx-auto my-4 py-4\">
        &copy; Paintballevents.net 2024 • <a class=\"text-white\" href=\"privacypolicy.php\">Privacy policy</a> • <a class=\"text-white\" href=\"https://forms.gle/mZC32Q2pT5Uz64ds8\">Get in touch</a>
    </footer>";
?>