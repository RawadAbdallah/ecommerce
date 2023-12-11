<?php
header('Access-Controll-Allow-Origin:*');
include("connection.php");
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;

$headers = getallheaders();

if (!isset($headers['Authorization']) || empty($headers['Authorization'])) {
  http_response_code(401);
  $response['status'] = "false";
  $response['message'] = 'Error: 401 UNAUTHORIZED';
  echo json_encode($response);
  exit();
}

$authorizationHeader = $headers['Authorization'];
$token = null;

$token = trim(str_replace("Bearer", '', $authorizationHeader));
if (!$token) {
  http_response_code(401);
  $response['status'] = "false";
  $response['message'] = 'Error: 401 UNAUTHORIZED';
  echo json_encode($response);
  exit();
}
try {
  $key = "your_secret";
  $decoded = JWT::decode($token, new Key($key, 'HS256'));
  $query = $mysqli->prepare('SELECT product_id, product_name, product_description, product_price, product_stock_count FROM products');
  $query->execute();
  $array = $query->get_result();
  $response = [];
  $response["permissions"] = true;
  while ($products = $array->fetch_assoc()) {
    $response[] = $products;
  }
  echo json_encode($response);
} catch (ExpiredException $e) {
  http_response_code(401);
  echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(["error" => "Invalid token"]);
}
