
<?php
// Ensure session is started at the beginning of your PHP file if needed
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'secretary') {
    header('Location: login_signup.php'); // Adjust login.php to  actual login page
    exit();
}

// Handle logout request
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login_signup.php'); // Redirect to login page after logout
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secretary Dashboard</title>
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
            background-color: #007bff; /* Blue background color */
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
                <a class="nav-link active" href="#">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Appointments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="clients.php">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="secretary_documents.php">Documents</a>
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
            <h1 class="h2">Secretary Dashboard</h1>
        </div>

        <!-- Your dashboard content goes here -->
        <div class="content">
            <p>This is your secretary dashboard.</p>
        </div>
    </div>
</main>

<!-- Bootstrap JS and dependencies (jQuery is required for Bootstrap's JS plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
