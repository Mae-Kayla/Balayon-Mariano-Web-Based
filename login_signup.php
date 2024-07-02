<?php
session_start();
require_once 'db_connect.php'; // Include your database connection script

$errors = [];
$showSignup = false; // Flag to control which section to display initially

// Handle Signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
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
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($lastname) || empty($firstname) || empty($email) || empty($gender) || empty($mobile) || empty($address) || empty($country) || empty($state) || empty($city) || empty($username) || empty($password)) {
        $errors[] = "All fields are required except middle name.";
    }

    // If no errors, proceed with signup
    if (empty($errors)) {
        try {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into users table
            $query = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $role = 'client'; // Default role for signup
            $stmt->execute([$username, $hashed_password, $role]);

            // Get the user_id of the newly created user
            $user_id = $pdo->lastInsertId();

            // Insert client details into client table
            $query = "INSERT INTO client (user_id, lastname, firstname, middlename, email, gender, mobile, address, country, state, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $lastname, $firstname, $middlename, $email, $gender, $mobile, $address, $country, $state, $city]);

            // Set session variables for automatic login after signup
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;

            // Redirect to dashboard or profile page after successful signup
            header('Location: client_dashboard.php');
            exit();
        } catch (Exception $e) {
            $errors[] = "An error occurred: " . $e->getMessage();
        }
    }
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Fetch user data from database based on username
        $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Verify the password using password_verify()
            if (password_verify($password, $user['password'])) {
                // Set session variables upon successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Fetch client details if the role is client
                if ($user['role'] == 'client') {
                    $query = "SELECT * FROM client WHERE user_id = ? LIMIT 1";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([$user['id']]);
                    $clientDetails = $stmt->fetch();

                    // Store client details in session
                    $_SESSION['client_details'] = $clientDetails;
                }

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin_lawyer':
                        header('Location: admin_lawyer_dashboard.php');
                        exit();
                    case 'secretary':
                        header('Location: secretary_dashboard.php');
                        exit();
                    case 'client':
                        header('Location: client_dashboard.php');
                        exit();
                    default:
                        // Handle unknown role (should not occur if roles are properly set)
                        $errors[] = "Unknown user role.";
                        break;
                }
            } else {
                $errors[] = "Invalid username or password.";
            }
        } else {
            $errors[] = "Invalid username or password.";
        }
    } catch (Exception $e) {
        $errors[] = "An error occurred: " . $e->getMessage();
    }
}

// Check if the signup section should be displayed initially (e.g., after a failed login attempt)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['signup'])) {
    $showSignup = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login or Signup</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-container {
            max-width: 600px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container form label {
            font-weight: bold;
        }
        .form-container form .form-group {
            margin-bottom: 20px;
        }
        .btn-login {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-signup {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login or Signup</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Login Form -->
        <form id="loginForm" method="post" action="login_signup.php">
            <div class="form-group">
                <label for="login_username">Username:</label>
                <input type="text" class="form-control" id="login_username" name="username" required>
            </div>
            <div class="form-group">
                <label for="login_password">Password:</label>
                <input type="password" class="form-control" id="login_password" name="password" required>
            </div>
            <button type="submit" class="btn btn-success btn-block btn-login" name="login">Login</button>
            <p class="mt-3 text-center">Don't have an account? <a href="#" id="showSignup">Signup here</a></p>
        </form>

        <!-- Signup Form -->
        <form id="signupForm" method="post" action="login_signup.php" <?php echo $showSignup ? '' : 'style="display: none;"'; ?>>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="signup_lastname">Last Name:</label>
                        <input type="text" class="form-control" id="signup_lastname" name="lastname" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_firstname">First Name:</label>
                        <input type="text" class="form-control" id="signup_firstname" name="firstname" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_middlename">Middle Name:</label>
                        <input type="text" class="form-control" id="signup_middlename" name="middlename">
                    </div>
                    <div class="form-group">
                        <label for="signup_email">Email:</label>
                        <input type="email" class="form-control" id="signup_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_gender">Gender:</label>
                        <select class="form-control" id="signup_gender" name="gender" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="signup_mobile">Mobile No.:</label>
                        <input type="text" class="form-control" id="signup_mobile" name="mobile" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="signup_address">Address:</label>
                        <input type="text" class="form-control" id="signup_address" name="address" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_country">Country:</label>
                        <input type="text" class="form-control" id="signup_country" name="country" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_state">State:</label>
                        <input type="text" class="form-control" id="signup_state" name="state" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_city">City:</label>
                        <input type="text" class="form-control" id="signup_city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_username">Username:</label>
                        <input type="text" class="form-control" id="signup_username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="signup_password">Password:</label>
                        <input type="password" class="form-control" id="signup_password" name="password" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-signup" name="signup">Signup</button>
            <p class="mt-3 text-center">Already have an account? <a href="#" id="showLogin">Login here</a></p>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies (optional) -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to toggle between signup and login sections
        document.addEventListener('DOMContentLoaded', function() {
            const showSignupLink = document.querySelector('#showSignup');
            const showLoginLink = document.querySelector('#showLogin');
            const signupForm = document.querySelector('#signupForm');
            const loginForm = document.querySelector('#loginForm');

            showSignupLink.addEventListener('click', function(e) {
                e.preventDefault();
                signupForm.style.display = 'block';
                loginForm.style.display = 'none';
            });

            showLoginLink.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.style.display = 'block';
                signupForm.style.display = 'none';
            });
        });
    </script>
</body>
</html>
