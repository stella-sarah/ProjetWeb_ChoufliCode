<?php
require_once '../../controller/transportcontroller.php';
header('Content-Type: application/json');

$transportController = new TransportController();
$reservations = $transportController->getAllReservations();

// Load governorates from GeoJSON
$geojson = json_decode(file_get_contents('tunisia-governorates.geojson'), true);
$gouvernorats = [];
foreach ($geojson['features'] as $feature) {
    $gouv_fr = $feature['properties']['gouv_fr'];
    $gouvernorats[] = $gouv_fr;
}

// Helper to normalize (remove accents, lowercase)
function normalize($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // Remove accents
    $str = preg_replace('/[^a-z ]/', '', $str); // Remove non-letters
    return trim($str);
}

// Build a map: normalized => official name
$normMap = [];
foreach ($gouvernorats as $gov) {
    $normMap[normalize($gov)] = $gov;
}

// Initialize all to 0
$govCounts = [];
foreach ($gouvernorats as $gov) {
    $govCounts[$gov] = 0;
}

// Count only depart, normalized
foreach ($reservations as $reservation) {
    // Handle both object and array (in case of ReservationTransport object)
    $depart = is_array($reservation) ? $reservation['depart'] : $reservation->getDepart();
    if ($depart) {
        $norm = normalize($depart);
        if (isset($normMap[$norm])) {
            $govCounts[$normMap[$norm]]++;
        }
    }
}

echo json_encode($govCounts); 