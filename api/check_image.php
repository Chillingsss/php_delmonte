<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$imageName = $_GET['image_name'] ?? '';
$imagePath = __DIR__ . '/uploads/' . $imageName;

$response = [
    'exists' => file_exists($imagePath),
    'image_name' => $imageName,
    'full_path' => $imagePath
];

echo json_encode($response);