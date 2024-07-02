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

// Initialize search query
$search_query = '';

// Check if a search term has been submitted
if (isset($_POST['search'])) {
    $search_term = $_POST['search_term'];
    $search_term = trim($search_term); // Remove any extra whitespace
    $search_term = htmlspecialchars($search_term); // Sanitize input to prevent XSS
    $search_term = "%$search_term%"; // Prepare for LIKE query

    $query = "SELECT users.id, users.username, client.lastname, client.firstname, client.email, client.mobile 
              FROM users 
              JOIN client ON users.id = client.user_id 
              WHERE users.role = 'client' 
              AND (users.username LIKE :search_term 
                   OR client.lastname LIKE :search_term 
                   OR client.firstname LIKE :search_term)";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':search_term', $search_term);
    $stmt->execute();
} else {
    // Default query to fetch all clients
    $query = "SELECT users.id, users.username, client.lastname, client.firstname, client.email, client.mobile 
              FROM users 
              JOIN client ON users.id = client.user_id 
              WHERE users.role = 'client'";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
}

$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
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
                <a class="nav-link active" href="manage_clients.php">Clients</a>
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
            <h1 class="h2">Manage Clients</h1>
        </div>

        <!-- Search Form -->
        <div class="content mb-3">
            <form method="post">
                <div class="form-group">
                    <label for="search_term">Search by Username, Last Name, or First Name:</label>
                    <input type="text" class="form-control" id="search_term" name="search_term" 
                           value="<?php echo isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : ''; ?>">
                </div>
                <button type="submit" class="btn btn-primary" name="search">Search</button>
            </form>
        </div>

        <!-- Display client list -->
        <div class="content">
            <h2>Clients List</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['username']); ?></td>
                            <td><?php echo htmlspecialchars($client['lastname']); ?></td>
                            <td><?php echo htmlspecialchars($client['firstname']); ?></td>
                            <td><?php echo htmlspecialchars($client['email']); ?></td>
                            <td><?php echo htmlspecialchars($client['mobile']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Bootstrap JS and dependencies (jQuery is required for Bootstrap's JS plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
