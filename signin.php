<?php
header('Access-Control-Allow-Origin: *');
include("connection.php");
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

if (!isset($_POST['email']) && !isset($_POST['password'])) {
    $response['status'] = 'Failed';
    $reponse['message'] = 'Credentials missing';
    echo json_encode($response);
    exit();
  }
  

$email = $_POST['email'];
$password = $_POST['password'];

$query = $mysqli->prepare('SELECT user_id, firstname, user_type_id, password FROM users WHERE email = ?');
$query->bind_param('s', $email);
$query->execute();
$query->store_result();
$num_rows = $query->num_rows;
$query->bind_result($user_id, $firstname, $user_type_id, $hashed_password);
$query->fetch();

$response = [];

if ($num_rows == 0) {
    $response['status'] = 'false';
    $response['message'] = 'user not found';
} else {
    if (password_verify($password, $hashed_password)) {
        $key = "your_secret";
        $payload = [
            "user_id" => $user_id,
            "firstname" => $firstname,
            "user_type_id" => $user_type_id,
            "exp" => time() + 3600,
        ];

        $response['status'] = 'logged in';
        $jwt = JWT::encode($payload, $key, 'HS256');
        $response['jwt'] = $jwt;
    } else {
        $response['status'] = 'false';
        $response['message'] = 'wrong credentials';
    }
}

echo json_encode($response);

$query->close();
$mysqli->close();
?>
