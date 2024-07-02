<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

$query = "SELECT id, subtype_name FROM custom_case_subtypes";
$subtype_result = $conn->query($query);
$subtypes = $subtype_result->fetch_all(MYSQLI_ASSOC);

$query = "SELECT id, stage_name FROM custom_stages_of_case";
$stage_result = $conn->query($query);
$stages = $stage_result->fetch_all(MYSQLI_ASSOC);

echo json_encode(['success' => true, 'subtypes' => $subtypes, 'stages' => $stages]);

$conn->close();
