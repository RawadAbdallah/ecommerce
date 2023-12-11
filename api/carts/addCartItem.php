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
    $response['message'] = 'seller is not a buyer';
    echo json_encode($response);
    exit();
  }

  $product_id = $_POST['product_id'];
  $product_description = $_POST['product_description'];
  $product_price = $_POST['product_price'];
  $product_stock_count = $_POST['product_stock_count'];

  $insertQuery = $mysqli->prepare('INSERT INTO products (product_name, product_description, product_price, product_stock_count, user_id) VALUES (?, ?, ?, ?, ?)');
  $insertQuery->bind_param('ssdii', $product_name, $product_description, $product_price, $product_stock_count, $user_id);

  if ($insertQuery->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Product added successfully';
  } else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to add product. ' . $mysqli->error;
  }

  $insertQuery->close();
} catch (ExpiredException $e) {
  http_response_code(401);
  echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(["error" => "Invalid token"]);
}


echo json_encode($response);

$mysqli->close();
?>
