<?php
session_start();
include 'db_connect.php'; // Include database connection

// Check if user is authenticated as secretary; otherwise redirect to login page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'secretary') {
    header('Location: login_signup.php');
    exit();
}

// Handle logout request
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login_signup.php');
    exit();
}

// Message handling
$message = '';
$messageType = '';

if (isset($_POST['upload'])) {
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
        // Define upload directory
        $uploadDir = 'uploads/';
        $fileTmpPath = $_FILES['fileUpload']['tmp_name'];
        $fileName = $_FILES['fileUpload']['name'];
        $fileType = $_FILES['fileUpload']['type'];
        
        // Define the path where the file will be saved
        $destPath = $uploadDir . $fileName;
        
        // Move the file to the upload directory
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Insert file details into the database
            $sql = "INSERT INTO uploaded_files (file_name, file_path, file_type, uploaded_by) VALUES (?, ?, ?, 'secretary')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fileName, $destPath, $fileType]);

            $message = 'File uploaded successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to move uploaded file.';
            $messageType = 'danger';
        }
    } else {
        $message = 'No file uploaded or upload error occurred.';
        $messageType = 'danger';
    }
}

// Handle file archive request
if (isset($_POST['archive'])) {
    $fileId = $_POST['file_id'];

    $sql = "UPDATE uploaded_files SET archived = 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fileId]);

    $message = 'File archived successfully.';
    $messageType = 'success';
}

// Function to display uploaded files by role
function displayUploadedFiles($role, $archived = 0)
{
    global $pdo; // Access the PDO object defined in db_connect.php

    // Retrieve files from database based on role and archived status
    $sql = "SELECT * FROM uploaded_files WHERE uploaded_by = ? AND archived = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$role, $archived]);

    if ($stmt->rowCount() > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead class="thead-dark">';
        echo '<tr>';
        echo '<th scope="col">#</th>';
        echo '<th scope="col">File Name</th>';
        echo '<th scope="col">File Type</th>';
        echo '<th scope="col">Upload Date</th>';
        if ($archived === 0) {
            echo '<th scope="col">Action</th>';
        }
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $counter = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo '<tr>';
            echo "<th scope='row'>$counter</th>";
            echo "<td><a href='{$row['file_path']}' target='_blank'>{$row['file_name']}</a></td>";
            echo "<td>{$row['file_type']}</td>";
            echo "<td>{$row['upload_time']}</td>";
            if ($archived === 0) {
                echo "<td>
                        <form action='secretary_documents.php' method='post' style='display:inline;'>
                            <input type='hidden' name='file_id' value='{$row['id']}'>
                            <button type='submit' name='archive' class='btn btn-warning btn-sm'>Archive</button>
                        </form>
                      </td>";
            }
            echo '</tr>';
            $counter++;
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p>No files available.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - Secretary Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #007bff;
            color: #fff;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #f8f9fa;
        }
        .main-content {
            margin-left: 220px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .nav-tabs .nav-link {
            border: 1px solid #dee2e6;
            border-radius: .25rem;
        }
        .nav-tabs .nav-link.active {
            border-color: #e9ecef #e9ecef #fff;
            background-color: #fff;
        }
        .tab-content > .tab-pane {
            padding: 15px;
        }
    </style>
</head>
<body>

<nav class="col-md-2 d-none d-md-block sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="secretary_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Appointments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="clients.php">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="secretary_documents.php">Documents</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="view_cases.php">Cases</a>
            </li>
            <li class="nav-item">
                <form method="post">
                    <button type="submit" name="logout" class="nav-link btn btn-link text-white">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<main role="main" class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Documents</h1>
        </div>

        <!-- Upload Form -->
        <div class="content mb-4">
            <form action="secretary_documents.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fileUpload">Upload File:</label>
                    <input type="file" name="fileUpload" id="fileUpload" class="form-control-file" required>
                </div>
                <button type="submit" name="upload" class="btn btn-primary">Upload</button>
            </form>
        </div>

        <!-- Tabbed Interface -->
        <div class="content">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="secretary-docs-tab" data-toggle="tab" href="#secretary-docs" role="tab" aria-controls="secretary-docs" aria-selected="true">My Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="attorney-docs-tab" data-toggle="tab" href="#attorney-docs" role="tab" aria-controls="attorney-docs" aria-selected="false">Attorney Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="archived-docs-tab" data-toggle="tab" href="#archived-docs" role="tab" aria-controls="archived-docs" aria-selected="false">Archived Documents</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="secretary-docs" role="tabpanel" aria-labelledby="secretary-docs-tab">
                    <?php displayUploadedFiles('secretary'); ?>
                </div>
                <div class="tab-pane fade" id="attorney-docs" role="tabpanel" aria-labelledby="attorney-docs-tab">
                    <?php displayUploadedFiles('admin_lawyer'); ?>
                </div>
                <div class="tab-pane fade" id="archived-docs" role="tabpanel" aria-labelledby="archived-docs-tab">
                    <?php displayUploadedFiles('secretary', 1); ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom JavaScript for handling modals -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    <?php if ($message): ?>
        var messageType = '<?php echo $messageType; ?>';
        var messageContent = '<?php echo $message; ?>';
        var modalTitle = messageType === 'success' ? 'Success' : 'Error';
        var modalClass = messageType === 'success' ? 'modal-success' : 'modal-danger';
        
        var modalHTML = `
            <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content ${modalClass}">
                        <div class="modal-header">
                            <h5 class="modal-title" id="messageModalLabel">${modalTitle}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            ${messageContent}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        $('#messageModal').modal('show');
    <?php endif; ?>
});
</script>

<style>
.modal-success .modal-content {
    border-color: #28a745;
}

.modal-success .modal-header {
    background-color: #28a745;
    color: #fff;
}

.modal-danger .modal-content {
    border-color: #dc3545;
}

.modal-danger .modal-header {
    background-color: #dc3545;
    color: #fff;
}
</style>

</body>
</html>
