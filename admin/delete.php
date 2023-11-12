<?php
include "../main.php";

if($_SERVER['REQUEST_METHOD'] !== "POST") {
  http_response_code(405);
  die();
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function delete_send_item(int $id) {
  global $conn;
  $select_stmt = $conn->prepare("SELECT send_items.data AS data, send_types.fields AS fields FROM send_items INNER JOIN send_types ON send_types.id = send_items.send_type_id WHERE send_items.id = :id");
  $select_stmt->bindParam(":id", $id);
  $select_stmt->execute(); 
  $result = $select_stmt->fetchObject();
  $data = json_decode($result->data);
  $fields = json_decode($result->fields);

  for($i = 0; $i < count($data); $i++) {
    if($fields[$i]->type !== "record") continue;
    unlink("../assets/sends/{$data[$i]->value}");
  }

  $delete_stmt = $conn->prepare("DELETE FROM send_items WHERE id = :id");
  $delete_stmt->bindParam(":id", $id);
  $delete_stmt->execute();
}

function delete_send_type(int $id) {
  global $conn;
  $select_send_type_stmt = $conn->prepare("SELECT fields FROM send_types WHERE id = :id");
  $select_send_type_stmt->bindParam(":id", $id);
  $select_send_type_stmt->execute();
  $result = $select_send_type_stmt->fetchObject();
  $fields = json_decode($result->fields);

  $record_fields_indexes = [];
  for($i = 0; $i < count($fields); $i++) {
    if($fields[$i]->type !== "record") continue;
    array_push($record_fields_indexes, $i);
  }

  $select_send_items_stmt = $conn->prepare("SELECT data FROM send_items WHERE send_type_id = :id");
  $select_send_items_stmt->bindParam(":id", $id);
  $select_send_items_stmt->execute();

  while($data = json_decode($select_send_items_stmt->fetchObject()->data)) {
    foreach($record_fields_indexes as $index) {
      unlink("../assets/sends/{$data[$index]->value}");
    }
  }

  $delete_stmt = $conn->prepare("DELETE FROM send_types WHERE id = :id");
  $delete_stmt->bindParam(":id", $id);
  $delete_stmt->execute();
}
?>