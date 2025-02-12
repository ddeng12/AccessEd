<?php
include('config.php');

// Get Swahili resources
$resources_query = "SELECT * FROM resources 
                   WHERE subject_id = (SELECT subject_id FROM subjects WHERE subject_name = 'Swahili')
                   ORDER BY resource_type, created_at DESC";
$result = mysqli_query($conn, $resources_query);

// Group resources by type
$resources = [
    'notes' => [],
    'past_paper' => []
];

while ($resource = mysqli_fetch_assoc($result)) {
    $resources[$resource['resource_type']][] = $resource;
}
?>

<!DOCTYPE html>
<!-- Rest of the code exactly matches mathematics.php structure -->