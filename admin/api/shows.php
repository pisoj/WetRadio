<?php
include "helpers/get-only.php";
include "../../main.php";

$shows_stmt = $conn->prepare("SELECT items.id as items_id, items.title as items_title, items.subtitle as items_subtitle, items.is_replayable as items_is_replayable, categories.id as categories_id, categories.title as categories_title FROM show_items items INNER JOIN show_categories categories ON categories.id = items.category_id ORDER BY categories.priority DESC, categories.id ASC, items.priority DESC");
$shows_stmt->execute();
$raw_shows = $shows_stmt->fetchAll(PDO::FETCH_OBJ);

$shows = [];
$last_category_id = null;

foreach($raw_shows as $raw_show) {
  if($raw_show->categories_id != $last_category_id) {
    array_push($shows, [
      "category_title" => $raw_show->categories_title,
      "shows" => []
    ]);
    $last_category_id = $raw_show->categories_id;
  }

  array_push($shows[array_key_last($shows)]["shows"], [
    "id" => $raw_show->items_id,
    "title" => $raw_show->items_title,
    "subtitle" => $raw_show->items_subtitle,
    "is_replayable" => $raw_show->items_is_replayable,
  ]);
}

echo json_encode($shows);
?>