<?php
session_start();
include 'db_connect.php'; // Include database connection

// Check if user is authenticated as admin_lawyer; otherwise redirect to login page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin_lawyer') {
    header('Location: login_signup.php'); // Adjust login_signup.php to your actual login page
    exit();
}

// Handle file upload
if (isset($_POST['upload']) && isset($_FILES['fileUpload'])) {
    $file = $_FILES['fileUpload'];
    $uploadDir = 'uploads/';
    $uploadFile = $uploadDir . basename($file['name']);
    
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        $sql = "INSERT INTO uploaded_files (file_name, file_path, file_type, upload_time, uploaded_by) VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$file['name'], $uploadFile, $file['type'], $_SESSION['role']]);
        $uploadMessage = 'File uploaded successfully.';
    } else {
        $uploadMessage = 'File upload failed.';
    }
}

// Handle file archive request
if (isset($_POST['archive'])) {
    $fileId = $_POST['file_id'];
    
    $sql = "UPDATE uploaded_files SET archived = 1 WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$fileId]);

    $archiveMessage = 'File archived successfully.';
}

// Function to display uploaded files with archive option
function displayUploadedFiles($role)
{
    global $pdo;

    $sql = "SELECT * FROM uploaded_files WHERE uploaded_by = ? AND archived = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$role]);

    if ($stmt->rowCount() > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="table table-striped table-hover">';
        echo '<thead class="thead-dark">';
        echo '<tr>';
        echo '<th scope="col">#</th>';
        echo '<th scope="col">File Name</th>';
        echo '<th scope="col">File Type</th>';
        echo '<th scope="col">Upload Date</th>';
        echo '<th scope="col">Action</th>';
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
            echo "<td>
                    <form action='documents_module.php' method='post' style='display:inline;'>
                        <input type='hidden' name='file_id' value='{$row['id']}'>
                        <button type='submit' name='archive' class='btn btn-warning btn-sm' data-toggle='modal' data-target='#archiveModal'>Archive</button>
                    </form>
                  </td>";
            echo '</tr>';
            $counter++;
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p class="text-muted">No files uploaded yet.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - Admin Lawyer Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom sidebar style */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0; /* Adjust padding to avoid content under the fixed navbar */
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #563d7c; /* Purple background color */
            color: #fff; /* White text color */
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px); /* Calculate height minus top padding */
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto; /* Scrollable area */
        }
        .sidebar .nav-link {
            font-weight: 500;
            color: #fff; /* White text color */
        }
        .sidebar .nav-link.active {
            color: #f8f9fa; /* Light gray color for active link */
        }
        .main-content {
            margin-left: 220px; /* Adjust margin to accommodate sidebar width */
            padding: 20px;
            background-color: #f8f9fa; /* Light gray background color */
            min-height: 100vh; /* Ensure content covers full height */
        }
        .content {
            background-color: #fff; /* White background color for content */
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<nav class="col-md-2 d-none d-md-block sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin_lawyer_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_clients.php">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="cases_module.php">Cases</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Calendar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="documents_module.php">Documents</a>
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
        <div class="content">
            <h2>Upload a New Document</h2>
            <form action="documents_module.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fileUpload">Choose file:</label>
                    <input type="file" name="fileUpload" id="fileUpload" class="form-control-file" required>
                </div>
                <button type="submit" name="upload" class="btn btn-primary">Upload</button>
            </form>
        </div>

        <!-- Tabbed Interface for Document Management -->
        <div class="content mt-4">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="attorney-tab" data-toggle="tab" href="#attorney" role="tab" aria-controls="attorney" aria-selected="true">Attorney Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="secretary-tab" data-toggle="tab" href="#secretary" role="tab" aria-controls="secretary" aria-selected="false">Secretary Documents</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="archived-tab" data-toggle="tab" href="#archived" role="tab" aria-controls="archived" aria-selected="false">Archived Documents</a>
                </li>
            </ul>
            <div class="tab-content mt-2" id="myTabContent">
                <div class="tab-pane fade show active" id="attorney" role="tabpanel" aria-labelledby="attorney-tab">
                    <?php 
                    // Display documents uploaded by attorneys
                    displayUploadedFiles('admin_lawyer'); 
                    ?>
                </div>
                <div class="tab-pane fade" id="secretary" role="tabpanel" aria-labelledby="secretary-tab">
                    <?php 
                    // Display documents uploaded by secretaries
                    displayUploadedFiles('secretary'); 
                    ?>
                </div>
                <div class="tab-pane fade" id="archived" role="tabpanel" aria-labelledby="archived-tab">
                    <?php 
                    // Display archived documents
                    $sql = "SELECT * FROM uploaded_files WHERE archived = 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    displayUploadedFiles('archived');
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal for success or error messages -->
<div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageModalLabel">Notification</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Message content will be inserted here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS and dependencies (jQuery is required for Bootstrap's JS plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Function to display modal with messages
    function showModal(message, type) {
        var modal = $('#messageModal');
        modal.find('.modal-body').text(message);
        modal.find('.modal-content').removeClass('alert-success alert-danger');
        modal.find('.modal-content').addClass(type === 'success' ? 'alert-success' : 'alert-danger');
        modal.modal('show');
    }

    // Trigger modals based on PHP messages
    <?php if (isset($uploadMessage)): ?>
        $(document).ready(function() {
            showModal("<?php echo $uploadMessage; ?>", "success");
        });
    <?php endif; ?>
    <?php if (isset($archiveMessage)): ?>
        $(document).ready(function() {
            showModal("<?php echo $archiveMessage; ?>", "success");
        });
    <?php endif; ?>
</script>

</body>
</html>
