<?php
// Ensure session is started at the beginning of your PHP file if needed
session_start();

// Database connection (update with your own database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is authenticated as a client; otherwise redirect to login page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: login_signup.php'); // Actual login page
    exit();
}

// Handle logout request
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: login_signup.php'); // Redirect to login page after logout
    exit();
}

// Debugging: Check if user_id is set in session
if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

// Fetch cases with client details from the database, filtered by user_id from the session
$sql_cases = "SELECT case_details.case_id, case_details.case_number, case_details.case_type, case_details.case_subtype, case_details.stage_of_case, case_details.stage_status, client.lastname, client.firstname, client.middlename
              FROM case_details
              INNER JOIN client ON case_details.user_id = client.user_id
              WHERE case_details.user_id = ?";

// Prepare and execute the statement
$stmt = $conn->prepare($sql_cases);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $_SESSION['user_id']); // Bind user_id from session
$stmt->execute();
$result_cases = $stmt->get_result();

// Debugging: Output the number of rows found
if ($result_cases === false) {
    die("Error executing query: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Cases</title>
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
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .clickable {
            cursor: pointer;
            color: blue;
        }
        .clickable:hover {
            text-decoration: underline;
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
                <a class="nav-link" href="client_profile.php">Clients</a>
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
    <div class="content">
        <h2>My Cases</h2>

        <!-- Existing Cases Table -->
        <?php if ($result_cases->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Case Number</th>
                        <th>Case Type</th>
                        <th>Case Subtype</th>
                        <th>Stage of Case</th>
                        <th>Status</th>
                        <th>Documents</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_cases->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($row['lastname'] . ', ' . $row['firstname'] . ' ' . $row['middlename']); ?>
                            </td>
                            <td><?= htmlspecialchars($row['case_number']); ?></td>
                            <td><?= htmlspecialchars($row['case_type']); ?></td>
                            <td><?= htmlspecialchars($row['case_subtype']); ?></td>
                            <td><?= htmlspecialchars($row['stage_of_case']); ?></td>
                            <td><?= htmlspecialchars($row['stage_status']); ?></td>
                            <td>
                                <a href="view_documents.php?case_id=<?= htmlspecialchars($row['case_id']); ?>" class="btn btn-primary btn-sm">View Documents</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No cases found.</p>
        <?php endif; ?>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php
// Close database connection
$conn->close();
?>
