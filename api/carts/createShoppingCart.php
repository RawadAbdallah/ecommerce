<?php
header('Access-Control-Allow-Origin: *');
include("../../connection.php");
require '../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

$jwt = isset(apache_request_headers()['Authorization']) ? apache_request_headers()['Authorization'] : null;

$response = [];

if (!$jwt) {
  $response['status'] = 'error';
  $response['message'] = 'JWT token not provided';
  echo json_encode($response);
  exit();
}

try {
  $key = "your_secret";
  $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
  $user_id = $decoded->user_id;
  $user_type_id = $decoded->user_type_id;

  if ($user_type_id != 1) {
    $response['status'] = 'error';
    $response['message'] = 'Seller is not a customer.';
    echo json_encode($response);
    exit();
  }

  $insertQuery = $mysqli->prepare('INSERT INTO shopping_carts (user_id) VALUES (?)');
  $insertQuery->bind_param('i', $user_id);

  if ($insertQuery->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Shopping Cart created successfully';
  } else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to create shopping cart. ' . $mysqli->error;
  }

  $insertQuery->close();
} catch (ExpiredException $e) {
  http_response_code(401);
  $response['status']='error';
  $response['message'] = "Token Expired";
} catch (Exception $e) {
  http_response_code(401);
  $response['status']='error';
  $response['message'] = "Invalid token";
}

echo json_encode($response);

$mysqli->close();
?>
