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

if (isset($_GET['case_id'])) {
    $case_id = $_GET['case_id'];
    $sql = "SELECT * FROM case_details WHERE case_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $case = $result->fetch_assoc();
            echo json_encode($case);
        } else {
            echo json_encode(['error' => 'No case found']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare statement']);
    }
} else {
    echo json_encode(['error' => 'Invalid case ID']);
}

$conn->close();
?>
