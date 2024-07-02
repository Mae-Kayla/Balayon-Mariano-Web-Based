<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'secretary') {
    header('Location: login_signup.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle updating a case
if (isset($_POST['update_case'])) {
    $case_id = $_POST['case_id'];
    $stage_of_case = $_POST['stage_of_case'];
    $stage_status = $_POST['stage_status'];

    $update_query = "UPDATE case_details SET stage_of_case = ?, stage_status = ? WHERE case_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ssi', $stage_of_case, $stage_status, $case_id);
    if ($stmt->execute()) {
        echo "<script>alert('Case updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating case: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle adding a new case
if (isset($_POST['add_case'])) {
    $user_id = $_POST['user_id'];
    $case_number = $_POST['case_number'];
    $case_type = $_POST['case_type'];
    $case_subtype = $_POST['case_subtype'];
    $stage_of_case = $_POST['stage_of_case'];
    $stage_status = $_POST['stage_status'];

    $insert_query = "INSERT INTO case_details (user_id, case_number, case_type, case_subtype, stage_of_case, stage_status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param('isssss', $user_id, $case_number, $case_type, $case_subtype, $stage_of_case, $stage_status);
    if ($stmt->execute()) {
        echo "<script>alert('Case added successfully.');</script>";
    } else {
        echo "<script>alert('Error adding case: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: login_signup.php');
    exit();
}

// Search functionality
$search_query = '';
if (isset($_POST['search'])) {
    $search_value = $conn->real_escape_string($_POST['search_value']);
    $search_query = "WHERE case_number LIKE '%$search_value%' OR CONCAT((SELECT CONCAT(firstname, ' ', lastname) FROM client WHERE user_id = case_details.user_id)) LIKE '%$search_value%'";
}

// Retrieve cases
$case_query = "SELECT case_id, user_id, case_number, case_type, case_subtype, stage_of_case, stage_status FROM case_details $search_query";
$case_result = $conn->query($case_query);

if (!$case_result) {
    die("Query failed: " . $conn->error);
}

// Retrieve clients for the dropdown
$client_query = "SELECT user_id, CONCAT(firstname, ' ', lastname) AS fullname FROM client";
$client_result = $conn->query($client_query);

if (!$client_result) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cases Module</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #007bff; /* Match sidebar color with secretary_dashboard.php */
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
        .search-bar {
            margin-bottom: 20px;
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
                <a class="nav-link" href="manage_clients.php">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Cases</a>
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
        <form method="post" class="search-bar">
            <div class="form-group">
                <input type="text" class="form-control" name="search_value" placeholder="Search by Case Number or Client Name">
            </div>
            <button type="submit" name="search" class="btn btn-primary">Search</button>
        </form>

        <h2>Cases List</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Case Number</th>
                    <th>Client</th>
                    <th>Case Type</th>
                    <th>Case Subtype</th>
                    <th>Stage of Case</th>
                    <th>Stage Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($case = $case_result->fetch_assoc()): ?>
                <?php
                $client_query_single = "SELECT lastname, firstname FROM client WHERE user_id = ?";
                $stmt = $conn->prepare($client_query_single);
                $stmt->bind_param('i', $case['user_id']);
                $stmt->execute();
                $client_result_single = $stmt->get_result();
                $client = $client_result_single->fetch_assoc();
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($case['case_number']); ?></td>
                    <td><?php echo htmlspecialchars($client['firstname'] . ' ' . $client['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($case['case_type']); ?></td>
                    <td><?php echo htmlspecialchars($case['case_subtype']); ?></td>
                    <td><?php echo htmlspecialchars($case['stage_of_case']); ?></td>
                    <td><?php echo htmlspecialchars($case['stage_status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>


</body>
</html>
