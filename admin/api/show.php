<?php
include "../../main.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
  validate_id($_GET["id"]);

  $id = (int)$_GET["id"];
  $show_stmt = $conn->prepare("SELECT title, subtitle, image, is_replayable, category_id, priority FROM show_items WHERE id = :id");
  $show_stmt->bindParam(":id", $id);
  $show_stmt->execute();
  $show = $show_stmt->fetchAll(PDO::FETCH_OBJ)[0];

  if ($show == null) {
    http_response_code(404);
    echo "Show with the ID of $id does not exist.";
    die();
  }

  echo json_encode($show);
  die();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $body = file_get_contents("php://input");
  if (!is_valid_json($body)) {
    http_response_code(400);
    echo "Invalid JSON body.";
    die();
  }
  $station = json_decode($body, false);
  validate_station_settings($station->title, $station->endpoints, $station->endpoint_names, $station->endpoint_order, $station->priority);

  $insert_stmt = $conn->prepare("INSERT INTO stations (title, description, endpoints, endpoint_names, endpoint_order, priority) VALUES (:title, :description, :endpoints, :endpoint_names, :endpoint_order, :priority)");
  bind_station_settings($insert_stmt, $station);
  $insert_stmt->execute();

  http_response_code(201);
  die();
}

if ($_SERVER["REQUEST_METHOD"] === "PUT") {
  $body = file_get_contents("php://input");
  if (!is_valid_json($body)) {
    http_response_code(400);
    echo "Invalid JSON body.";
    die();
  }
  $station = json_decode($body, false);
  validate_id(strval($station->id));
  validate_station_settings($station->title, $station->endpoints, $station->endpoint_names, $station->endpoint_order, $station->priority);

  $update_stmt = $conn->prepare("UPDATE stations SET title = :title, description = :description, endpoints = :endpoints, endpoint_names = :endpoint_names, endpoint_order = :endpoint_order, priority = :priority WHERE id = :id");
  $update_stmt->bindParam(":id", $station->id);
  bind_station_settings($update_stmt, $station);
  $update_stmt->execute();

  if ($update_stmt->rowCount() === 0) {
    http_response_code(404);
    echo "Station with the ID of $station->id does not exist.";
  }

  die();
}

if ($_SERVER["REQUEST_METHOD"] === "DELETE") {
  validate_id($_GET["id"]);

  $id = (int)$_GET["id"];
  $delete_stmt = $conn->prepare("DELETE FROM stations WHERE id = :id");
  $delete_stmt->bindParam(":id", $id);
  $delete_stmt->execute();

  if ($delete_stmt->rowCount() === 0) {
    http_response_code(404);
    echo "Station with the ID of $id does not exist.";
  }

  die();
}


function bind_station_settings(PDOStatement &$stetement, &$station)
{
  $stetement->bindParam(":title", htmlspecialchars($station->title));
  $stetement->bindParam(":description", htmlspecialchars($station->description));
  $stetement->bindParam(":endpoints", json_encode(htmlspecialchars_array(json_decode($station->endpoints), true)));
  $stetement->bindParam(":endpoint_names", json_encode(htmlspecialchars_array(json_decode($station->endpoint_names), true)));
  $stetement->bindParam(":endpoint_order", htmlspecialchars($station->endpoint_order));
  $stetement->bindParam(":priority", htmlspecialchars($station->priority));
}
function validate_station_settings(string $title, string $endpoints, string $endpoint_names, string $endpoint_order, string|int $priority)
{
  if (empty($title)) {
    http_response_code(400);
    echo "Title cannot be empty.";
    die();
  }

  $decoded_endpoints = json_decode($endpoints, true, 2);
  if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo "Invalid list of endpoints.";
    die();
  } else if (count($decoded_endpoints) === 0) {
    http_response_code(400);
    echo "List of endpoints must not be empty.";
    die();
  }
  $decoded_endpoint_names = json_decode($endpoint_names, true, 2);
  if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo "Invalid list of endpoint names.";
    die();
  } else if (count($decoded_endpoint_names) === 0) {
    http_response_code(400);
    echo "List of endpoint names must not be empty.";
    die();
  }
  if (count($decoded_endpoints) !== count($decoded_endpoint_names)) {
    http_response_code(400);
    echo "List of endpoints must have the same lenght as the list of endpoint names.";
    die();
  }


  if (!is_valid_int($priority)) {
    http_response_code(400);
    echo "Priority must be an integer.";
    die();
  }
  if (!is_valid_endpoint_order($endpoint_order)) {
    http_response_code(400);
    echo "Endpoint order must be ordered or random.";
    die();
  }
}

function validate_id(string $id)
{
  if (!isset($id) || !ctype_digit($id)) {
    http_response_code(400);
    echo "Station ID must exist and must be an integer.";
    die();
  }
}
function is_valid_int(string $input)
{
  if ($input[0] === '-') {
    return ctype_digit(substr($input, 1));
  }
  return ctype_digit($input);
}
function is_valid_json(string $json)
{
  json_decode($json);
  return json_last_error() === JSON_ERROR_NONE;
}
function htmlspecialchars_array(array $array, bool $removeEmptyItems = false): array
{
  $escaped_array = [];
  foreach ($array as $item) {
    if (empty($item) && $removeEmptyItems) continue;
    array_push($escaped_array, htmlspecialchars($item));
  }
  return $escaped_array;
}
function is_valid_endpoint_order(string $value)
{
  $valid_endpoint_orders = ["ordered", "random"];
  return in_array($value, $valid_endpoint_orders);
}

?>