<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "law_office_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
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

$sql = "UPDATE client SET lastname=?, firstname=?, middlename=?, email=?, gender=?, mobile=?, address=?, country=?, state=?, city=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssi", $lastname, $firstname, $middlename, $email, $gender, $mobile, $address, $country, $state, $city, $id);

if ($stmt->execute()) {
    header('Location: clients.php');
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>
