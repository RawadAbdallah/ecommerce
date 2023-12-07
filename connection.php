<?php
  $host = "localhost";
  $db_user = "root";
  $db_password = null;
  $db = "ecommerce";

  $mysqli = new mysqli($host, $db_user, $db_password, $db);

  if($mysqli->connect_error){
    die("Error " . $mysqli->connect_error);
  }

?>