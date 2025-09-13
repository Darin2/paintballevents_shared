<?php
// Database connection
include 'dbconn.inc.php'; 
$conn = dbConnect();

// Begin XML output
header("Content-Type: text/xml;charset=UTF-8");
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Add static pages with specific priorities and frequencies
$static_pages = [
    'https://paintballevents.net/index.php' => ['freq' => 'daily', 'priority' => '1.0'],
    'https://paintballevents.net/submit_event.php' => ['freq' => 'monthly', 'priority' => '0.7'],
];
foreach ($static_pages as $url => $settings) {
    echo "<url>";
    echo "<loc>{$url}</loc>";
    echo "<changefreq>{$settings['freq']}</changefreq>";
    echo "<priority>{$settings['priority']}</priority>";
    echo "</url>";
}

// Retrieve URLs of all published events
$query = "SELECT DISTINCT eventSlug FROM events WHERE isPublished = 1";
$result = mysqli_query($conn, $query);

// Loop through results and generate <url> entries for events
while ($row = mysqli_fetch_assoc($result)) {
    $eventSlug = htmlspecialchars($row['eventSlug'], ENT_XML1, 'UTF-8');
    echo "<url>";
    echo "<loc>https://paintballevents.net/event/{$eventSlug}.php</loc>";
    echo "<changefreq>monthly</changefreq>";
    echo "<priority>0.8</priority>";
    echo "</url>";
}

// Close the sitemap and database connection
echo '</urlset>';
mysqli_close($conn);
?>
