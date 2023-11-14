<?php
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

function delete_show_recording(int $id) {
  global $conn;
  $select_show_recording_stmt = $conn->prepare("SELECT file FROM show_recordings WHERE id = :id");
  $select_show_recording_stmt->bindParam(":id", $id);
  $select_show_recording_stmt->execute();
  $file = $select_show_recording_stmt->fetchObject()->file;

  unlink("../assets/{$file}");

  $delete_show_recording_stmt = $conn->prepare("DELETE FROM show_recordings WHERE id = :id");
  $delete_show_recording_stmt->bindParam(":id", $id);
  $delete_show_recording_stmt->execute();
}

function delete_show_item(int $id) {
  global $conn;
  $select_show_item_stmt = $conn->prepare("SELECT image FROM show_items WHERE id = :id");
  $select_show_item_stmt->bindParam(":id", $id);
  $select_show_item_stmt->execute();
  $image = $select_show_item_stmt->fetchObject()->image;
  unlink("../assets/{$image}");

  $select_show_recordings_stmt = $conn->prepare("SELECT file FROM show_recordings WHERE show_item_id = :id");
  $select_show_recordings_stmt->bindParam(":id", $id);
  $select_show_recordings_stmt->execute();
  while($file = $select_show_recordings_stmt->fetchObject()->file) {
    unlink("../assets/{$file}");
  }

  $delete_show_item_stmt = $conn->prepare("DELETE FROM show_items WHERE id = :id");
  $delete_show_item_stmt->bindParam(":id", $id);
  $delete_show_item_stmt->execute();
}

function delete_show_category(int $id) {
  global $conn;
  $select_show_items_stmt = $conn->prepare("SELECT id, image FROM show_items WHERE category_id = :id");
  $select_show_items_stmt->bindParam(":id", $id);
  $select_show_items_stmt->execute();
  while($show_item = $select_show_items_stmt->fetchObject()) {
    unlink("../assets/{$show_item->image}");
    $select_show_recording_stmt = $conn->prepare("SELECT file FROM show_recordings WHERE show_item_id = :id");
    $select_show_recording_stmt->bindParam(":id", $show_item->id);
    $select_show_recording_stmt->execute();
    while($file = $select_show_recording_stmt->fetchObject()->file) {
      unlink("../assets/{$file}");
    }
  }

  $delete_show_category_stmt = $conn->prepare("DELETE FROM show_categories WHERE id = :id");
  $delete_show_category_stmt->bindParam(":id", $id);
  $delete_show_category_stmt->execute();
}

function delete_station(int $id) {
  global $conn;
  $delete_station_stmt = $conn->prepare("DELETE FROM stations WHERE id = :id");
  $delete_station_stmt->bindParam(":id", $id);
  $delete_station_stmt->execute();
}
?>