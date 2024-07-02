<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'secretary') {
    header('Location: login_signup.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize search query
$search_query = '';

// Check if a search term has been submitted
if (isset($_POST['search'])) {
    $search_term = $conn->real_escape_string($_POST['search_term']);
    $search_query = "WHERE lastname LIKE '%$search_term%' OR firstname LIKE '%$search_term%'";
}

// Fetch clients from the database with optional search query
$sql = "SELECT id, lastname, firstname, middlename, email, gender, mobile, address, country, state, city FROM client $search_query";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients</title>
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
                <a class="nav-link" href="secretary_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Appointments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="clients.php">Clients</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Documents</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Settings</a>
            </li>
            <li class="nav-item">
                <form method="post" action="secretary_dashboard.php">
                    <button type="submit" name="logout" class="nav-link btn btn-link text-white">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</nav>

<main role="main" class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Clients</h1>
        </div>
        <div class="content">
            <!-- Search form -->
            <form method="post" class="mb-3">
                <div class="form-group">
                    <label for="search_term">Search by Last Name or First Name:</label>
                    <input type="text" class="form-control" id="search_term" name="search_term" value="<?= isset($_POST['search_term']) ? htmlspecialchars($_POST['search_term']) : '' ?>">
                </div>
                <button type="submit" class="btn btn-primary" name="search">Search</button>
            </form>

            <!-- Display clients -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Email</th>
                        <th>Gender</th>
                        <th>Mobile</th>
                        <th>Address</th>
                        <th>Country</th>
                        <th>State</th>
                        <th>City</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['lastname']}</td>
                                <td>{$row['firstname']}</td>
                                <td>{$row['middlename']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['gender']}</td>
                                <td>{$row['mobile']}</td>
                                <td>{$row['address']}</td>
                                <td>{$row['country']}</td>
                                <td>{$row['state']}</td>
                                <td>{$row['city']}</td>
                                <td><button class='btn btn-primary btn-sm edit-btn' data-id='{$row['id']}'>Edit</button></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12'>No clients found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Edit Client Modal -->
<div class="modal fade" id="editClientModal" tabindex="-1" role="dialog" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editClientForm" method="post" action="update_client.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Edit Client</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editClientId" name="id">
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control" id="editClientLastname" name="lastname">
                    </div>
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" class="form-control" id="editClientFirstname" name="firstname">
                    </div>
                    <div class="form-group">
                        <label for="middlename">Middle Name</label>
                        <input type="text" class="form-control" id="editClientMiddlename" name="middlename">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="editClientEmail" name="email">
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <input type="text" class="form-control" id="editClientGender" name="gender">
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="text" class="form-control" id="editClientMobile" name="mobile">
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" class="form-control" id="editClientAddress" name="address">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <input type="text" class="form-control" id="editClientCountry" name="country">
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" class="form-control" id="editClientState" name="state">
                    </div>
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" class="form-control" id="editClientCity" name="city">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // JavaScript to handle the edit button click
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            fetch('get_client.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editClientId').value = data.id;
                    document.getElementById('editClientLastname').value = data.lastname;
                    document.getElementById('editClientFirstname').value = data.firstname;
                    document.getElementById('editClientMiddlename').value = data.middlename;
                    document.getElementById('editClientEmail').value = data.email;
                    document.getElementById('editClientGender').value = data.gender;
                    document.getElementById('editClientMobile').value = data.mobile;
                    document.getElementById('editClientAddress').value = data.address;
                    document.getElementById('editClientCountry').value = data.country;
                    document.getElementById('editClientState').value = data.state;
                    document.getElementById('editClientCity').value = data.city;
                    $('#editClientModal').modal('show');
                });
        });
    });
</script>
</body>
</html>
