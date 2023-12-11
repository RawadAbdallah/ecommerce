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

  if ($user_type_id != 2) {
    $response['status'] = 'error';
    $response['message'] = 'User is not a seller';
    echo json_encode($response);
    exit();
  }

  $product_name = $_POST['product_name'];
  $product_description = $_POST['product_description'];
  $product_price = $_POST['product_price'];
  $product_stock_count = $_POST['product_stock_count'];
  $product_id = $_POST['product_id'];

  $checkQuery = $mysqli->prepare('SELECT user_id from products where product_id = ?');
  $checkQuery->bind_param('i', $product_id);
  $checkQuery->execute();
  $checkQuery->store_result();
  $checkQuery->bind_result($product_user_id);
  $checkQuery->fetch();

  if($product_user_id != $user_id){
    $response['status'] = 'error';
    $response['message'] = 'You are not authorized to edit this product.';
    echo json_encode($response);
    exit();
  }

  $insertQuery = $mysqli->prepare('UPDATE products SET product_name = ?, product_description = ?, product_price = ?, product_stock_count = ? WHERE product_id = ?');
  $insertQuery->bind_param('ssdii', $product_name, $product_description, $product_price, $product_stock_count, $product_id);

  if ($insertQuery->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Product updated successfully';
  } else {
    $response['status'] = 'error';
    $response['message'] = 'Failed to update product. ' . $mysqli->error;
  }

  $insertQuery->close();
} catch (ExpiredException $e) {
  http_response_code(401);
  echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
  http_response_code(401);
  $response['status']='error';
  $response['message'] = "Invalid token";
}


echo json_encode($response);

$mysqli->close();
?>
