<?php
header('Access-Control-Allow-Origin: *');
include("connection.php");
require __DIR__ . '/vendor/autoload.php';

if (!isset($_POST['email']) && !isset($_POST['password'])) {
  $response['status'] = 'Failed';
  $reponse['message'] = 'Credentials missing';
  echo json_encode($response);
  exit();
}

//checking if already registered
$email = $_POST['email'];
$emailQuery = $mysqli->prepare('SELECT email FROM users WHERE email = ?');
$emailQuery->bind_param('s', $email);
$emailQuery->execute();
$emailQuery->store_result();

if ($emailQuery->num_rows > 0) {
 $response['status'] = 'email already registered';
 echo json_encode($response);
 exit();
}


$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$firstname = $_POST['firstname'];
$lastname = $_POST['lastname'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$userType = $_POST['user_type'];

$typeQuery = $mysqli->prepare('SELECT user_type_id FROM user_types WHERE user_type = ?');
$typeQuery->bind_param('s', $userType);
$typeQuery->execute();
$typeQuery->bind_result($user_type_id);
$typeQuery->fetch();
$typeQuery->close();

$insertQuery = $mysqli->prepare('INSERT INTO users (firstname, lastname, email, password,  gender, age, user_type_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
$insertQuery->bind_param('sssssii', $firstname, $lastname, $email, $password, $gender, $age, $user_type_id);
$insertQuery->execute();
$insertQuery->close();

$user_id = $mysqli->insert_id;

$response['status'] = 'registered';
echo json_encode($response);

$mysqli->close();
?>
