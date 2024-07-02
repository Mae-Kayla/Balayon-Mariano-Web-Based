<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

// Ensure user is authenticated as admin_lawyer; otherwise redirect to login page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin_lawyer') {
    header('Location: login_signup.php'); // Adjust login_signup.php to your actual login page
    exit();
}

// Handle logout request
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login_signup.php'); // Redirect to login page after logout
    exit();
}

// Query to get the total number of clients
$query = "SELECT COUNT(*) AS client_count FROM users JOIN client ON users.id = client.user_id WHERE users.role = 'client'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$client_count = $stmt->fetchColumn();

// Query to get the total number of cases
$query = "SELECT COUNT(*) AS case_count FROM case_details";
$stmt = $pdo->prepare($query);
$stmt->execute();
$case_count = $stmt->fetchColumn();

// Query to get the number of open cases
$query = "SELECT COUNT(*) AS open_cases FROM case_details WHERE stage_status = 'Open'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$open_cases = $stmt->fetchColumn();

// Query to get the number of closed cases
$query = "SELECT COUNT(*) AS closed_cases FROM case_details WHERE stage_status = 'Closed'";
$stmt = $pdo->prepare($query);
$stmt->execute();
$closed_cases = $stmt->fetchColumn();

// Query to get the total number of documents uploaded by the logged-in lawyer
$query = "SELECT COUNT(*) AS total_documents FROM uploaded_files WHERE uploaded_by = ? AND archived = 0";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['username']]);
$total_documents = $stmt->fetchColumn();

// Query to get the total number of archived documents uploaded by the logged-in lawyer
$query = "SELECT COUNT(*) AS archived_documents FROM uploaded_files WHERE uploaded_by = ? AND archived = 1";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['username']]);
$archived_documents = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Lawyer Dashboard</title>
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
        .card-deck .card {
            min-width: 220px;
        }
    </style>
</head>
<body>

<nav class="col-md-2 d-none d-md-block sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin_lawyer_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_clients.php">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="cases_module.php">Cases</a>
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
            <h1 class="h2">Admin Lawyer Dashboard</h1>
        </div>

        <div class="content">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
            <p>This is your admin lawyer dashboard.</p>

            <div class="card-deck mb-4">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Clients</h5>
                        <p class="card-text"><?php echo htmlspecialchars($client_count); ?></p>
                    </div>
                </div>
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Cases</h5>
                        <p class="card-text"><?php echo htmlspecialchars($case_count); ?></p>
                    </div>
                </div>
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Open Cases</h5>
                        <p class="card-text"><?php echo htmlspecialchars($open_cases); ?></p>
                    </div>
                </div>
           
                </div>
            </div>

            <div class="card-deck mb-4">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Total Documents Uploaded</h5>
                        <p class="card-text"><?php echo htmlspecialchars($total_documents); ?></p>
                    </div>
                </div>
                <div class="card text-white bg-secondary">
                    <div class="card-body">
                        <h5 class="card-title">Archived Documents</h5>
                        <p class="card-text"><?php echo htmlspecialchars($archived_documents); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Bootstrap JS and dependencies (jQuery is required for Bootstrap's JS plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
