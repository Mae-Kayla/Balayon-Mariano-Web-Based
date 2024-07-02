<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['case_id'])) {
    $case_id = intval($_GET['case_id']);
    
    $query = "SELECT case_id, case_number, case_type, case_subtype, stage_of_case, stage_status, custom_status 
              FROM case_details 
              WHERE case_id = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $case_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($case = $result->fetch_assoc()) {
            echo json_encode($case);
        } else {
            echo json_encode(['success' => false, 'message' => 'No case found']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No case ID provided']);
}

$conn->close();
?>
