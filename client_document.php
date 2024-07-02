<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: login_signup.php'); // Adjust login_signup.php to your actual login page
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

// Create connection with error handling
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $file = $_FILES['document'];
    $fileName = basename($file['name']);
    $fileTmpName = $file['tmp_name'];
    $fileError = $file['error'];
    $fileSize = $file['size'];
    $fileType = $file['type'];

    $allowedTypes = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
        'audio/mpeg',
        'video/mp4'
    ];

    if (in_array($fileType, $allowedTypes)) {
        if ($fileError === 0) {
            if ($fileSize < 10000000) { // 10MB limit
                $fileContent = file_get_contents($fileTmpName);

                $sql_upload = "INSERT INTO documents (user_id, document_name, document_file) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql_upload);

                if ($stmt) {
                    $stmt->bind_param("iss", $_SESSION['user_id'], $fileName, $fileContent);

                    if ($stmt->execute()) {
                        echo "File uploaded successfully.";
                    } else {
                        echo "Error saving file to database: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    echo "Error preparing statement: " . $conn->error;
                }
            } else {
                echo "File size exceeds limit.";
            }
        } else {
            echo "Error uploading file: " . $fileError;
        }
    } else {
        echo "File type not allowed.";
    }
}

$sql_documents = "SELECT * FROM documents WHERE user_id = ?";
$stmt = $conn->prepare($sql_documents);
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Error preparing statement: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Documents</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #28a745;
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
    </style>
</head>
<body>

<nav class="col-md-2 d-none d-md-block sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="client_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="client_profile.php">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="client_cases.php">Cases</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="client_document.php">Documents</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Appointments</a>
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
            <h1 class="h2">Client Documents</h1>
        </div>

        <div class="content">
            <h3>Upload New Document</h3>
            <form action="client_document.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="document">Select Document</label>
                    <input type="file" class="form-control-file" id="document" name="document" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>

        <div class="content mt-4">
            <h3>Your Documents</h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Document Name</th>
                        <th>Uploaded On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['document_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['uploaded_on']); ?></td>
                        <td>
                            <a href="download_document.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-primary btn-sm">Download</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
