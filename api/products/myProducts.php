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
  $user_id = $decoded->user_id;
  $user_type_id = $decoded->user_type_id;
  if ($user_type_id == 2) {
    $query = $mysqli->prepare('SELECT product_id, product_name, product_description, product_price, product_stock_count FROM products WHERE user_id = ?');
    $query->bind_param('i', $user_id);
    $query->execute();
    $query->store_result();
    $query->bind_result($product_id, $product_name, $product_description, $product_price, $product_stock_count);

    $products = [];

    while ($query->fetch()) {
      $product = [
        'product_id' => $product_id,
        'product_name' => $product_name,
        'product_description' => $product_description,
        'product_price' => $product_price,
        'product_stock_count' => $product_stock_count,
      ];

      $products[] = $product;
    }

    $query->close();

    $response['status'] = 'success';
    $response['products'] = $products;

    echo json_encode($response);
  } else {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized - user is not a seller"]);
  }


} catch (ExpiredException $e) {
  http_response_code(401);
  echo json_encode(["error" => "expired"]);
} catch (Exception $e) {
  http_response_code(401);
  echo json_encode(["error" => "Invalid token"]);
}

