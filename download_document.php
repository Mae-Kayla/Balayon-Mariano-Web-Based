<?php
// Ensure session is started at the beginning of your PHP file if needed
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get document ID from query parameter
$docId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch document from database
$sql = "SELECT document_name, document_file FROM documents WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $docId, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($docName, $docContent);
$stmt->fetch();

if ($docName && $docContent) {
    // Send headers and file content
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($docName) . '"');
    header('Content-Length: ' . strlen($docContent));
    echo $docContent;
} else {
    echo "Document not found or access denied.";
}

// Close connection
$stmt->close();
$conn->close();
exit();
?>
