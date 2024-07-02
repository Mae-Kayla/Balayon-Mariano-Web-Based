<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $case_id = $_POST['case_id'];
    $case_number = $_POST['case_number'];
    $case_type = $_POST['case_type'];
    $case_subtype = $_POST['case_subtype'];
    $stage_of_case = $_POST['stage_of_case'];
    $stage_status = $_POST['stage_status'];
    $custom_status = $_POST['custom_status'];
    $custom_case_subtype_id = $_POST['custom_case_subtype_id'];
    $custom_stage_of_case_id = $_POST['custom_stage_of_case_id'];

    if ($case_subtype == 'Custom:') {
        $case_subtype = $custom_case_subtype_id;
    }
    if ($stage_of_case == 'Custom:') {
        $stage_of_case = $custom_stage_of_case_id;
    }

    $sql = "UPDATE case_details SET case_number=?, case_type=?, case_subtype=?, stage_of_case=?, stage_status=?, custom_status=?, custom_case_subtype_id=?, custom_stage_of_case_id=? WHERE case_id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssi", $case_number, $case_type, $case_subtype, $stage_of_case, $stage_status, $custom_status, $custom_case_subtype_id, $custom_stage_of_case_id, $case_id);
        if ($stmt->execute()) {
            echo "Case updated successfully";
        } else {
            echo "Error updating case: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
    }
}

$conn->close();
?>
