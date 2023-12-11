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

  $cart_id = $_POST['cart_id'];

  $checkQuery = $mysqli->prepare('SELECT user_id from shopping_carts where cart_id = ?');
  $checkQuery->bind_param('i', $cart_id);
  $checkQuery->execute();
  $checkQuery->store_result();
  $checkQuery->bind_result($cart_user_id);
  $checkQuery->fetch();

  if($cart_user_id != $user_id){
    $response['status'] = 'error';
    $response['message'] = 'You are not authorized to delete this shopping cart.';
    echo json_encode($response);
    exit();
  }

  $insertQuery = $mysqli->prepare('DELETE FROM shopping_carts where cart_id = ?');
  $insertQuery->bind_param('i', $cart_id);

  if ($insertQuery->execute()) {
    echo 'deleted shopping cart_id ', $cart_id, ' having user_id: ', $user_id;
    $response['status'] = 'success';
    $response['message'] = 'Shopping Cart deleted successfully';
  } else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to delete this shopping cart. ' . $mysqli->error;
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
