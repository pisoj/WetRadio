<?php
include "../../main.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  http_response_code(400);
  echo "Station ID mus me an integer.";
  die();
}

$id = (int)$_GET['id'];
$stations_stmt = $conn->prepare("SELECT title, description, endpoints, endpoint_names, endpoint_order, priority FROM stations WHERE id = :id LIMIT 1");
$stations_stmt->bindParam(":id", $id);
$stations_stmt->execute();
$stations = $stations_stmt->fetchAll(PDO::FETCH_OBJ)[0];

if($stations == null) {
  http_response_code(406);
  echo "Station with the ID of $id does not exist.";
  die();
}

echo json_encode($stations);
?>