<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

// Ensure user is logged in as a client
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'client') {
    header('Location: login_signup.php'); // Redirect to login page if not logged in
    exit();
}

// Fetch client details from session
$clientDetails = $_SESSION['client_details'];

// Process form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $mobile = $_POST['mobile'];
    $address = $_POST['address'];
    $country = $_POST['country'];
    $state = $_POST['state'];
    $city = $_POST['city'];

    // Validate inputs (you can add more validation as per your requirements)
    if (empty($lastname) || empty($firstname) || empty($email) || empty($gender) || empty($mobile) || empty($address) || empty($country) || empty($state) || empty($city)) {
        $error = "All fields are required except middle name.";
    } else {
        // Update client details in the database
        $query = "UPDATE client SET lastname=?, firstname=?, middlename=?, email=?, gender=?, mobile=?, address=?, country=?, state=?, city=? WHERE user_id=?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$lastname, $firstname, $middlename, $email, $gender, $mobile, $address, $country, $state, $city, $clientDetails['user_id']]);

        // Update session with new client details
        $clientDetails['lastname'] = $lastname;
        $clientDetails['firstname'] = $firstname;
        $clientDetails['middlename'] = $middlename;
        $clientDetails['email'] = $email;
        $clientDetails['gender'] = $gender;
        $clientDetails['mobile'] = $mobile;
        $clientDetails['address'] = $address;
        $clientDetails['country'] = $country;
        $clientDetails['state'] = $state;
        $clientDetails['city'] = $city;
        $_SESSION['client_details'] = $clientDetails;

        // Optionally, you can redirect to a success page or show a success message
        // Redirect to avoid form resubmission on refresh
        header('Location: client_profile.php?update=success');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
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
            background-color: #28a745; /* Green background color */
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
                <a class="nav-link" href="client_dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Profile</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="client_cases.php">Cases</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Services</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Invoices</a>
            </li>
         
           
        </ul>
    </div>
</nav>

<main role="main" class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Client Profile</h1>
        </div>

        <!-- Profile content -->
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
                <div class="alert alert-success">Profile updated successfully.</div>
            <?php endif; ?>

            <form id="profileForm" method="post" action="client_profile.php">
                <div class="form-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($clientDetails['lastname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="firstname">First Name:</label>
                    <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($clientDetails['firstname']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="middlename">Middle Name:</label>
                    <input type="text" class="form-control" id="middlename" name="middlename" value="<?php echo htmlspecialchars($clientDetails['middlename']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($clientDetails['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="gender">Gender:</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="Male" <?php if ($clientDetails['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($clientDetails['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($clientDetails['gender'] === 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile No.:</label>
                    <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($clientDetails['mobile']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="address">Address:</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($clientDetails['address']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($clientDetails['country']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="state">State:</label>
                    <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars
($clientDetails['state']); ?>" required>
</div>
<div class="form-group">
    <label for="city">City:</label>
    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($clientDetails['city']); ?>" required>
</div>
<button type="submit" class="btn btn-primary">Update Profile</button>
</form>
</div>
</div>
</main>

<!-- Bootstrap JS and dependencies (jQuery is required for Bootstrap's JS plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
